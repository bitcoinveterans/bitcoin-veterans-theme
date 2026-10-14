<?php
/**
 * Minimal read-only nostr WebSocket client (no composer, no WP dependency).
 *
 * Speaks just enough of RFC 6455 to run a NIP-01 subscription:
 * `["REQ", <sub>, <filter>[, <filter>…]]` → `EVENT` messages → stop at `EOSE`,
 * answer with `CLOSE`. Never publishes (`EVENT` writes are never sent).
 *
 * Transport details implemented here:
 * - HTTP/1.1 Upgrade handshake (`Sec-WebSocket-Accept` verify).
 * - Outbound frames masked per RFC 6455 §5.3 (client requirement).
 * - 7/16/64-bit payload lengths; continuation frames reassembled.
 * - `PING` answered with `PONG`; relay `CLOSE` honored.
 * - One `REQ` per connection, overall 20 s read deadline.
 *
 * Deliberately standalone (no ABSPATH guard, no WordPress calls) so it can be
 * linted and smoke-tested outside WordPress.
 *
 * @package cc_starter
 * @since 1.2.0
 */

/**
 * Class BV_Nostr_WS.
 */
class BV_Nostr_WS {

	/** Default total read deadline (seconds) for one subscription. */
	const READ_DEADLINE = 20;

	/** Connect / handshake timeout (seconds). */
	const CONNECT_TIMEOUT = 10;

	/**
	 * Fetch events for filters from one relay.
	 *
	 * @param string $relay_url wss:// (or ws://) relay URL.
	 * @param array  $filters   List of NIP-01 filter assoc arrays.
	 * @param int    $timeout   Overall read deadline in seconds.
	 * @return array {
	 *     @type bool     $ok      True when the relay answered without transport error
	 *                             (even if it returned zero events).
	 *     @type string   $relay
	 *     @type array[]  $events  Decoded nostr event objects.
	 *     @type string[] $notices Relay NOTICE/CLOSED messages.
	 *     @type string   $error   Transport/protocol error (when $ok is false).
	 *     @type bool     $eose    True when EOSE was seen (closed by us, not the relay).
	 * }
	 */
	public static function request( $relay_url, array $filters, $timeout = self::READ_DEADLINE ) {
		$result = array(
			'ok'      => false,
			'relay'   => $relay_url,
			'events'  => array(),
			'notices' => array(),
			'error'   => null,
			'eose'    => false,
		);

		$parts = self::parse_relay_url( $relay_url );
		if ( false === $parts ) {
			$result['error'] = 'unparseable relay URL';
			return $result;
		}

		$errno  = 0;
		$errstr = '';
		$remote = ( $parts['secure'] ? 'tls' : 'tcp' ) . '://' . $parts['host'] . ':' . $parts['port'];
		$ctx    = stream_context_create(
			array(
				'ssl' => array(
					'verify_peer'       => true,
					'verify_peer_name'  => true,
					'allow_self_signed' => false,
					'peer_name'         => $parts['host'],
					'SNI_enabled'       => true,
				),
			)
		);
		$sock = @stream_socket_client( $remote, $errno, $errstr, self::CONNECT_TIMEOUT, STREAM_CLIENT_CONNECT, $ctx ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- error text captured below.
		if ( ! $sock ) {
			$result['error'] = sprintf( 'connect failed: %s', $errstr ? $errstr : 'unknown' );
			return $result;
		}
		stream_set_blocking( $sock, true );

		try {
			if ( ! self::handshake( $sock, $parts ) ) {
				$result['error'] = 'websocket handshake failed';
				fclose( $sock );
				return $result;
			}

			$sub_id = 'bv-dib-1';
			$req    = array_merge( array( 'REQ', $sub_id ), array_values( $filters ) );
			if ( ! self::send_text( $sock, self::json( $req ) ) ) {
				$result['error'] = 'failed to send REQ';
				fclose( $sock );
				return $result;
			}

			$deadline      = microtime( true ) + max( 1, (int) $timeout );
			$buffer        = '';
			$fragmented    = false;
			$events_by_id  = array();

			while ( microtime( true ) < $deadline ) {
				$frame = self::read_frame( $sock, $deadline );
				if ( false === $frame ) {
					break; // Timeout or EOF.
				}
				list( $fin, $opcode, $payload ) = $frame;

				if ( 0x8 === $opcode ) { // Close.
					break;
				}
				if ( 0x9 === $opcode ) { // Ping -> Pong.
					self::send_frame( $sock, 0xA, $payload );
					continue;
				}
				if ( 0xA === $opcode ) { // Pong.
					continue;
				}
				if ( 0x1 === $opcode || 0x2 === $opcode ) { // Text/binary: start of message.
					$buffer     = $payload;
					$fragmented = ! $fin;
				} elseif ( 0x0 === $opcode && $fragmented ) { // Continuation.
					$buffer    .= $payload;
					$fragmented = ! $fin;
				} else {
					continue; // Unknown/orphan frame: ignore.
				}
				if ( $fragmented ) {
					continue; // Wait for the rest of the message.
				}

				$msg = json_decode( $buffer, true );
				if ( ! is_array( $msg ) || ! isset( $msg[0] ) ) {
					continue;
				}
				if ( 'EVENT' === $msg[0] && isset( $msg[1], $msg[2] ) && $sub_id === $msg[1] && is_array( $msg[2] ) ) {
					$event = $msg[2];
					if ( isset( $event['id'] ) ) {
						$events_by_id[ $event['id'] ] = $event;
					}
				} elseif ( 'EOSE' === $msg[0] ) {
					$result['eose'] = true;
					break;
				} elseif ( 'NOTICE' === $msg[0] ) {
					$result['notices'][] = isset( $msg[1] ) ? (string) $msg[1] : '';
				} elseif ( 'CLOSED' === $msg[0] ) {
					$result['notices'][] = 'CLOSED: ' . ( isset( $msg[2] ) ? (string) $msg[2] : '' );
					break;
				}
			}

			// Politely end the subscription (spec: CLOSE on EOSE / done). Ignore failures.
			self::send_text( $sock, self::json( array( 'CLOSE', $sub_id ) ) );

			$result['ok']     = true;
			$result['events'] = array_values( $events_by_id );
			fclose( $sock );
			return $result;
		} catch ( Exception $e ) {
			$result['error'] = 'exception: ' . $e->getMessage();
			fclose( $sock );
			return $result;
		}
	}

	/**
	 * HTTP/1.1 Upgrade handshake; validates the 101 and Sec-WebSocket-Accept.
	 *
	 * @param resource $sock  Socket.
	 * @param array    $parts URL parts from wp_nostr_ws_parse_url().
	 * @return bool
	 */
	private static function handshake( $sock, $parts ) {
		$key  = base64_encode( random_bytes( 16 ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions -- no WP dependency here.
		$path = $parts['path'] ? $parts['path'] : '/';
		// A real UA is required: relays behind Cloudflare's Browser Integrity
		// Check (e.g. nostr.wine) 403 handshakes without one.
		$headers = "GET {$path} HTTP/1.1\r\n"
			. "Host: {$parts['host']}:{$parts['port']}\r\n"
			. "User-Agent: bv-dib-mirror/1.0 (WordPress; +https://bitcoinveterans.org)\r\n"
			. "Origin: https://{$parts['host']}\r\n"
			. "Upgrade: websocket\r\n"
			. "Connection: Upgrade\r\n"
			. "Sec-WebSocket-Key: {$key}\r\n"
			. "Sec-WebSocket-Version: 13\r\n"
			. "\r\n";
		if ( ! self::write_all( $sock, $headers ) ) {
			return false;
		}

		$deadline = microtime( true ) + self::CONNECT_TIMEOUT;
		$response = '';
		while ( microtime( true ) < $deadline && false === strpos( $response, "\r\n\r\n" ) ) {
			$chunk = self::read_exact( $sock, 1, $deadline );
			if ( false === $chunk ) {
				return false;
			}
			$response .= $chunk;
			if ( strlen( $response ) > 16384 ) {
				return false;
			}
		}

		if ( ! preg_match( '#^HTTP/\d+(?:\.\d+)? 101#', $response ) ) {
			return false;
		}
		$expected = base64_encode( sha1( $key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true ) );
		return (bool) preg_match( '/^sec-websocket-accept:\s*(\S+)\s*$/mi', $response, $m ) && trim( $m[1] ) === $expected;
	}

	/**
	 * Send one masked text frame.
	 *
	 * @param resource $sock Socket.
	 * @param string   $data UTF-8 payload.
	 * @return bool
	 */
	private static function send_text( $sock, $data ) {
		return self::send_frame( $sock, 0x1, $data );
	}

	/**
	 * Send one masked frame (RFC 6455 §5.3: clients MUST mask).
	 *
	 * @param resource $sock    Socket.
	 * @param int      $opcode  Frame opcode.
	 * @param string   $payload Raw payload.
	 * @return bool
	 */
	private static function send_frame( $sock, $opcode, $payload ) {
		$len  = strlen( $payload );
		$head = chr( 0x80 | ( $opcode & 0x0F ) ); // FIN + opcode.
		if ( $len <= 125 ) {
			$head .= chr( 0x80 | $len );
		} elseif ( $len <= 0xFFFF ) {
			$head .= chr( 0x80 | 126 ) . pack( 'n', $len );
		} else {
			$head .= chr( 0x80 | 127 ) . pack( 'NN', 0, $len ); // 64-bit, high word 0.
		}
		$mask   = random_bytes( 4 ); // phpcs:ignore WordPress.WP.AlternativeFunctions -- no WP dependency here.
		$masked = '';
		for ( $i = 0; $i < $len; $i++ ) {
			$masked .= $payload[ $i ] ^ $mask[ $i % 4 ];
		}
		return self::write_all( $sock, $head . $mask . $masked );
	}

	/**
	 * Read one frame; continuation assembly happens in the caller.
	 *
	 * @param resource $sock     Socket.
	 * @param float    $deadline Absolute microtime deadline.
	 * @return array|false [fin(bool), opcode(int), payload(string)] or false on timeout/EOF.
	 */
	private static function read_frame( $sock, $deadline ) {
		$hdr = self::read_exact( $sock, 2, $deadline );
		if ( false === $hdr ) {
			return false;
		}
		$b1     = ord( $hdr[0] );
		$b2     = ord( $hdr[1] );
		$fin    = (bool) ( $b1 & 0x80 );
		$opcode = $b1 & 0x0F;
		$masked = (bool) ( $b2 & 0x80 );
		$len    = $b2 & 0x7F;

		if ( 126 === $len ) {
			$ext = self::read_exact( $sock, 2, $deadline );
			if ( false === $ext ) {
				return false;
			}
			$len = unpack( 'n', $ext )[1];
		} elseif ( 127 === $len ) {
			$ext = self::read_exact( $sock, 8, $deadline );
			if ( false === $ext ) {
				return false;
			}
			$u   = unpack( 'N2', $ext );
			$len = $u[1] * 4294967296 + $u[2];
			if ( $len > 64 * 1024 * 1024 || $len < 0 ) {
				return false; // Absurd frame; bail.
			}
		}

		$mask = '';
		if ( $masked ) { // Servers MUST NOT mask, but tolerate.
			$mask = self::read_exact( $sock, 4, $deadline );
			if ( false === $mask ) {
				return false;
			}
		}

		$payload = $len > 0 ? self::read_exact( $sock, $len, $deadline ) : '';
		if ( false === $payload ) {
			return false;
		}
		if ( $masked ) {
			for ( $i = 0; $i < $len; $i++ ) {
				$payload[ $i ] = $payload[ $i ] ^ $mask[ $i % 4 ];
			}
		}
		return array( $fin, $opcode, $payload );
	}

	/**
	 * Read exactly $len bytes with an absolute deadline (null-tolerant loop).
	 *
	 * @param resource $sock     Socket.
	 * @param int      $len      Bytes to read.
	 * @param float    $deadline Absolute microtime deadline.
	 * @return string|false
	 */
	private static function read_exact( $sock, $len, $deadline ) {
		$out = '';
		while ( strlen( $out ) < $len ) {
			$remaining = $deadline - microtime( true );
			if ( $remaining <= 0 ) {
				return false;
			}
			stream_set_timeout( $sock, (int) $remaining, (int) ( ( $remaining - (int) $remaining ) * 1000000 ) );
			$chunk = fread( $sock, $len - strlen( $out ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			if ( false === $chunk || '' === $chunk ) {
				$meta = stream_get_meta_data( $sock );
				if ( ! empty( $meta['timed_out'] ) || ! empty( $meta['eof'] ) ) {
					return false;
				}
				continue;
			}
			$out .= $chunk;
		}
		return $out;
	}

	/**
	 * Write a full buffer, looping over partial writes.
	 *
	 * @param resource $sock Socket.
	 * @param string   $data Bytes.
	 * @return bool
	 */
	private static function write_all( $sock, $data ) {
		$len     = strlen( $data );
		$written = 0;
		while ( $written < $len ) {
			$n = fwrite( $sock, substr( $data, $written ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			if ( false === $n || 0 === $n ) {
				return false;
			}
			$written += $n;
		}
		return true;
	}

	/**
	 * NIP-01-safe JSON encode (unescaped unicode + slashes).
	 *
	 * @param mixed $value Value.
	 * @return string
	 */
	private static function json( $value ) {
		return json_encode( $value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	}

	/**
	 * Parse a relay URL into socket parts.
	 *
	 * @param string $url Relay URL (wss://, ws://, https:// tolerated).
	 * @return array|false {secure, host, port, path}
	 */
	private static function parse_relay_url( $url ) {
		$url = trim( (string) $url );
		if ( ! preg_match( '#^(wss?|https?)://#i', $url ) ) {
			return false;
		}
		$parts = parse_url( $url ); // phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url, WordPress.WP.AlternativeFunctions -- standalone class.
		if ( ! is_array( $parts ) || empty( $parts['host'] ) ) {
			return false;
		}
		$scheme = strtolower( $parts['scheme'] );
		$secure = in_array( $scheme, array( 'wss', 'https' ), true );
		return array(
			'secure' => $secure,
			'host'   => $parts['host'],
			'port'   => isset( $parts['port'] ) ? (int) $parts['port'] : ( $secure ? 443 : 80 ),
			'path'   => isset( $parts['path'] ) ? $parts['path'] . ( isset( $parts['query'] ) ? '?' . $parts['query'] : '' ) : '/',
		);
	}
}
