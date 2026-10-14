<?php
/**
 * DIB nostr mirror importer: mirrors the org's BTC Daily Intelligence Bulletin
 * (DIB) kind-1 nostr notes into plain WP posts (the existing /resources/dib/
 * archive), and back-fills provenance meta onto the 229 hand-posted legacy DIBs.
 *
 * - Runs via the hourly WP-Cron hook `bv_import_dib_hook` or manually via WP-CLI
 *   (`wp bv dib-import`, `wp bv dib-status`). Same lock/dedupe/CLI shape as
 *   inc/podcast-feed.php.
 * - Sources: outbound-only nostr REQ against public relays (see
 *   inc/lib/class-bv-nostr-ws.php). Read-only: never publishes to relays.
 * - Dedupe by nostr event id (post meta `dib_event_id`). When no event id matches
 *   but a legacy post titled `DIB {d M Y}` exists for the note's date, the post is
 *   *bridged*: the four provenance metas are set and its content/title/date stay
 *   untouched.
 * - Never trashes or deletes (NIP-09 deletions move posts to `draft` and are
 *   reported). A run that would create more than BV_DIB_MAX_CREATES posts aborts
 *   with an error and writes nothing.
 *
 * wp-config.php overrides:
 *   define( 'BV_DIB_AUTHORS', '<hex64>[,<hex64>…]' );
 *   define( 'BV_DIB_RELAYS', 'wss://relay1,wss://relay2' );
 *   define( 'BV_DIB_MAX_CREATES', 8 );
 *
 * Security note (v1 threat model): events are checked for (a) pinned author
 * pubkey, (b) NIP-01 id = sha256(serialization), (c) DIB content gate, and are
 * fetched from a fixed set of public relays (>=2 must answer per run). The
 * Schnorr signature (BIP-340) is NOT verified in v1: there is no audited
 * pure-PHP schnorr implementation we can run on shared hosting. With schnorr
 * unchecked, a *compromised relay* could in theory serve a forged event that
 * still carries a valid id hash under the pinned pubkey and passes the content
 * gate; every honest nostr client would show such a note with a bad signature,
 * and the org would notice its own bulletin being wrong. Impact is bounded to
 * content injection into a mirror of already-public first-party data; the raw
 * event JSON is stored in `dib_event_json` meta so signatures can be verified
 * retroactively (and forged posts found & drafted) if a verify lib ever ships.
 * Fetching from several independent relays and comparing results is the v1
 * mitigation; the hour-level cron and the max-create valve bound the blast
 * radius of any single bad run.
 *
 * @package cc_starter
 * @since 1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once dirname( __FILE__ ) . '/lib/class-bv-nostr-ws.php';

/** Cron hook for the hourly import. */
define( 'BV_DIB_CRON_HOOK', 'bv_import_dib_hook' );

/** Option used as an import lock. */
define( 'BV_DIB_LOCK_OPTION', 'bv_dib_import_lock' );

/** Seconds after which a lock is considered stale (crashed run). */
define( 'BV_DIB_LOCK_TTL', 15 * MINUTE_IN_SECONDS );

/** Option storing the last non-dry run summary (used by `wp bv dib-status`). */
define( 'BV_DIB_LAST_RUN_OPTION', 'bv_dib_last_run' );

/** Default authors: BV DIB key first, then the org main key (both import DIBs). */
define( 'BV_DIB_DEFAULT_AUTHORS', '01f31c5f1fbf33747b4d6d5ef6d68d2615f7aff663ab87f537796161a98b406a,0596b8167de4d926ba1658286936eebd22532ea67ec12a90c0780ad66df1fc99' );

/** Default relays: the DIB event `r`-tag publication set. */
define( 'BV_DIB_DEFAULT_RELAYS', 'wss://nostr.wine,wss://relay.damus.io,wss://nos.lol,wss://relay.primal.net,wss://relay.snort.social' );

/** Default author for imported posts (BV2A, the manual-era author). */
define( 'BV_DIB_DEFAULT_POST_AUTHOR', 6 );

/** Lookback window for scheduled runs (no --since): DIBs are weekday-daily. */
define( 'BV_DIB_DEFAULT_LOOKBACK', 7 * DAY_IN_SECONDS );

/** Default per-author REQ limit for kind-1 events. */
define( 'BV_DIB_DEFAULT_REQ_LIMIT', 500 );

/**
 * Safety valve: if a single run would create more posts than this, nothing is
 * written and an error is logged (e.g. a relay served a poisoned window). A
 * weekday-daily bulletin never legitimately needs 8+ new posts in one run.
 * Override in wp-config.php with BV_DIB_MAX_CREATES, or per run with
 * `wp bv dib-import --max-create=<n>`.
 */
if ( ! defined( 'BV_DIB_MAX_CREATES' ) ) {
	define( 'BV_DIB_MAX_CREATES', 8 );
}

/* -------------------------------------------------------------------------
 * Configuration
 * ---------------------------------------------------------------------- */

/**
 * Whitelisted author pubkeys (hex64), DIB key first by default.
 *
 * @return string[]
 */
function bv_dib_authors() {
	$raw  = defined( 'BV_DIB_AUTHORS' ) && BV_DIB_AUTHORS ? BV_DIB_AUTHORS : BV_DIB_DEFAULT_AUTHORS;
	$list = is_array( $raw ) ? $raw : preg_split( '/[\s,]+/', $raw, -1, PREG_SPLIT_NO_EMPTY );
	$out  = array();
	foreach ( (array) $list as $hex ) {
		$hex = strtolower( trim( (string) $hex ) );
		if ( preg_match( '/^[0-9a-f]{64}$/', $hex ) ) {
			$out[] = $hex;
		}
	}
	return array_values( array_unique( $out ) );
}

/**
 * Relay list (wss:// URLs).
 *
 * @return string[]
 */
function bv_dib_relays() {
	$raw  = defined( 'BV_DIB_RELAYS' ) && BV_DIB_RELAYS ? BV_DIB_RELAYS : BV_DIB_DEFAULT_RELAYS;
	$list = is_array( $raw ) ? $raw : preg_split( '/[\s,]+/', $raw, -1, PREG_SPLIT_NO_EMPTY );
	$out  = array();
	foreach ( (array) $list as $url ) {
		$url = strtolower( trim( (string) $url ) );
		if ( preg_match( '#^wss?://#', $url ) ) {
			$out[] = untrailingslashit( $url );
		}
	}
	return array_values( array_unique( $out ) );
}

/**
 * Post author ID for imported DIBs (filterable; default BV2A = 6).
 *
 * @return int
 */
function bv_dib_post_author() {
	return (int) apply_filters( 'bv_dib_post_author', BV_DIB_DEFAULT_POST_AUTHOR );
}

/* -------------------------------------------------------------------------
 * Pure helpers (gate, dates, normalization) — no DB access
 * ---------------------------------------------------------------------- */

/**
 * Strip Unicode directional isolates (U+2066..U+2069) that wrap the 📷 marks.
 *
 * @param string $text Text.
 * @return string
 */
function bv_dib_strip_isolates( $text ) {
	return (string) preg_replace( '/[\x{2066}-\x{2069}\x{202A}-\x{202E}]/u', '', (string) $text );
}

/**
 * Remove leading cover-URL lines and 📷 photo marks from the top of a note.
 *
 * The current-era format starts with the cover image URL on its own line,
 * followed by optional `📷` marks (inside isolates) before the bulletin header.
 *
 * @param string $content Note content.
 * @return string
 */
function bv_dib_strip_leading_cover( $content ) {
	$content = str_replace( array( "\r\n", "\r" ), "\n", (string) $content );
	$content = bv_dib_strip_isolates( $content );
	$lines   = explode( "\n", $content );
	while ( $lines ) {
		// Look at the line with any 📷 marks removed.
		$probe = trim( str_replace( "\xF0\x9F\x93\xB7", '', trim( $lines[0] ) ) );
		if ( '' === $probe || preg_match( '#^https?://\S+$#i', $probe ) ) {
			array_shift( $lines ); // Blank/camera-only line, or a bare cover URL.
			continue;
		}
		// First real line: drop any leading 📷 marks ("📷 BTC Daily…" → "BTC Daily…").
		$lines[0] = preg_replace( '/^(\s*\xF0\x9F\x93\xB7)+\s*/', '', $lines[0] );
		break;
	}
	return implode( "\n", $lines );
}

/**
 * The DIB gate: is this note a BTC Daily Intelligence Bulletin?
 *
 * After stripping leading URLs and isolates, the content must match
 * /BTC Daily Intelligence Bulletin/i AND contain both `Block:` and `BLUF:`
 * within the first 1,200 chars. The two header fields defeat the
 * OPN-GABRIEL-type notes that only mention the phrase.
 *
 * @param string $content Note content.
 * @return bool
 */
function bv_dib_is_dib_content( $content ) {
	$text = bv_dib_strip_leading_cover( (string) $content );
	$head = function_exists( 'mb_substr' ) ? mb_substr( $text, 0, 1200, 'UTF-8' ) : substr( $text, 0, 1200 );
	if ( ! preg_match( '/BTC Daily Intelligence Bulletin/i', $text ) ) {
		return false;
	}
	return false !== strpos( $head, 'Block:' ) && false !== strpos( $head, 'BLUF:' );
}

/**
 * Parse the bulletin date from the DTG/ICOD header line.
 *
 * Matches `DTG/ICOD: 1200Z 14Oct2026`, `DTG/ICOD: 0800EST 04Mar24`, tolerates
 * case typos (`29JUl2025`) and extra spaces. 2-digit years map to 20xx.
 *
 * @param string $content Note content.
 * @return int|null Unix timestamp of the bulletin day (00:00 UTC), or null.
 */
function bv_dib_dtg_timestamp( $content ) {
	if ( ! preg_match( '/DTG\/ICOD:\s*\S+\s+(\d{1,2})\s*([A-Za-z]{3})\s*(\d{2,4})/i', (string) $content, $m ) ) {
		return null;
	}
	$months = array(
		'jan' => 1, 'feb' => 2, 'mar' => 3, 'apr' => 4, 'may' => 5, 'jun' => 6,
		'jul' => 7, 'aug' => 8, 'sep' => 9, 'oct' => 10, 'nov' => 11, 'dec' => 12,
	);
	$mon = isset( $months[ strtolower( $m[2] ) ] ) ? $months[ strtolower( $m[2] ) ] : 0;
	$day = (int) $m[1];
	$yr  = (int) $m[3];
	if ( $yr < 100 ) {
		$yr += 2000;
	}
	if ( ! $mon || ! checkdate( $mon, $day, $yr ) ) {
		return null;
	}
	return gmmktime( 0, 0, 0, $mon, $day, $yr );
}

/**
 * The bulletin's effective date: DTG header date, falling back to the event's
 * created_at (UTC). Returns the 00:00 UTC timestamp of that day.
 *
 * @param array $event Nostr event.
 * @return array{0:int,1:string} (timestamp, 'dtg'|'created_at').
 */
function bv_dib_event_day( $event ) {
	$dtg = bv_dib_dtg_timestamp( isset( $event['content'] ) ? $event['content'] : '' );
	if ( null !== $dtg ) {
		return array( $dtg, 'dtg' );
	}
	$ts = isset( $event['created_at'] ) ? (int) $event['created_at'] : time();
	return array( gmmktime( 0, 0, 0, (int) gmdate( 'n', $ts ), (int) gmdate( 'j', $ts ), (int) gmdate( 'Y', $ts ) ), 'created_at' );
}

/**
 * Legacy-style DIB post title for a bulletin day: `DIB 14 OCT 2026`.
 *
 * @param int $day_ts 00:00 UTC timestamp of the bulletin day.
 * @return string
 */
function bv_dib_title_for_day( $day_ts ) {
	return 'DIB ' . gmdate( 'j', $day_ts ) . ' ' . strtoupper( gmdate( 'M', $day_ts ) ) . ' ' . gmdate( 'Y', $day_ts );
}

/**
 * Canonical key for legacy title matching. Parses titles like
 * `DIB 2 APR 2025` (any case, extra whitespace) into `dib-YYYY-M-D`.
 *
 * @param string $title Post title.
 * @return string|null Canonical key, or null when the title is not a DIB title.
 */
function bv_dib_canonical_title_key( $title ) {
	if ( ! preg_match( '/^\s*DIB\s+(\d{1,2})\s+([A-Za-z]{3})[A-Za-z]*\s+(\d{4})\s*$/i', (string) $title, $m ) ) {
		return null;
	}
	$months = array(
		'jan' => 1, 'feb' => 2, 'mar' => 3, 'apr' => 4, 'may' => 5, 'jun' => 6,
		'jul' => 7, 'aug' => 8, 'sep' => 9, 'oct' => 10, 'nov' => 11, 'dec' => 12,
	);
	$mon = isset( $months[ strtolower( $m[2] ) ] ) ? $months[ strtolower( $m[2] ) ] : 0;
	if ( ! $mon || ! checkdate( $mon, (int) $m[1], (int) $m[3] ) ) {
		return null;
	}
	return sprintf( 'dib-%04d-%02d-%02d', (int) $m[3], $mon, (int) $m[1] );
}

/**
 * The block height from the header, digits only (tolerates `971,400` and the
 * `884, 317` space typo).
 *
 * @param string $content Note content.
 * @return string
 */
function bv_dib_block_height( $content ) {
	if ( preg_match( '/Block:\s*([0-9][0-9,\s]*)/', (string) $content, $m ) ) {
		return preg_replace( '/\D+/', '', $m[1] );
	}
	return '';
}

/**
 * The raw DTG/ICOD header value (e.g. `1200Z 14Oct2026`), trimmed.
 *
 * @param string $content Note content.
 * @return string
 */
function bv_dib_dtg_line( $content ) {
	if ( preg_match( '/DTG\/ICOD:\s*(.+)/i', (string) $content, $m ) ) {
		return trim( preg_replace( '/\s+/', ' ', $m[1] ) );
	}
	return '';
}

/**
 * The BLUF paragraph (label stripped, whitespace collapsed), capped for use as
 * post_excerpt.
 *
 * @param string $content Note content.
 * @param int    $max     Max characters.
 * @return string
 */
function bv_dib_bluf_excerpt( $content, $max = 500 ) {
	$text = bv_dib_strip_leading_cover( (string) $content );
	if ( ! preg_match( '/BLUF:\s*(.+?)(\n\s*\n|$)/is', $text, $m ) ) {
		return '';
	}
	$bluf = trim( preg_replace( '/\s+/u', ' ', $m[1] ) );
	$bluf = rtrim( $bluf, " /\t" );
	if ( strlen( $bluf ) > $max ) {
		$cut  = strpos( wordwrap( $bluf, $max, "\n", true ), "\n" );
		$bluf = substr( $bluf, 0, false === $cut ? $max : $cut ) . ' …';
	}
	return $bluf;
}

/**
 * First image URL found in the note (the cover, in the current era).
 *
 * @param string $content Raw note content.
 * @return string URL or ''.
 */
function bv_dib_first_image_url( $content ) {
	if ( preg_match( '#https?://\S+?\.(?:png|jpe?g|gif|webp)(?:\?\S*)?#i', (string) $content, $m ) ) {
		return rtrim( $m[0], ".,;:!?)\"'" );
	}
	return '';
}

/**
 * Normalized body saved as post_content: CRLF→LF, leading cover-URL line and
 * 📷-isolate marks removed, trimmed. Body text is otherwise byte-preserved.
 *
 * @param string $content Raw note content.
 * @return string
 */
function bv_dib_normalize_content( $content ) {
	$text = str_replace( array( "\r\n", "\r" ), "\n", (string) $content );
	$text = bv_dib_strip_leading_cover( $text );
	$text = bv_dib_strip_isolates( $text );
	return trim( $text );
}

/**
 * NIP-01 event id check: id must be the lowercase sha256 of the serialized
 * array [0, pubkey, created_at, kind, tags, content] (unescaped unicode/slashes).
 *
 * @param array $event Decoded event.
 * @return bool
 */
function bv_dib_event_id_ok( $event ) {
	$payload = array(
		0,
		(string) $event['pubkey'],
		(int) $event['created_at'],
		(int) $event['kind'],
		array_values( (array) $event['tags'] ),
		(string) $event['content'],
	);
	$json = wp_json_encode( $payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	if ( ! $json ) {
		return false;
	}
	return hash_equals( strtolower( (string) $event['id'] ), hash( 'sha256', $json ) );
}

/**
 * Structural + crypto sanity for any event kind. Returns null when OK.
 *
 * @param mixed $event Decoded JSON value.
 * @return string|null Rejection reason.
 */
function bv_dib_event_reject_reason( $event ) {
	if ( ! is_array( $event ) ) {
		return 'not a JSON object';
	}
	foreach ( array( 'id', 'pubkey', 'created_at', 'kind', 'content' ) as $field ) {
		if ( ! isset( $event[ $field ] ) ) {
			return 'missing ' . $field;
		}
	}
	if ( ! is_string( $event['id'] ) || ! preg_match( '/^[0-9a-fA-F]{64}$/', $event['id'] ) ) {
		return 'bad id';
	}
	if ( ! is_string( $event['pubkey'] ) || ! preg_match( '/^[0-9a-fA-F]{64}$/', $event['pubkey'] ) ) {
		return 'bad pubkey';
	}
	if ( ! is_numeric( $event['created_at'] ) || ! is_numeric( $event['kind'] ) || ! is_string( $event['content'] ) || ! is_array( $event['tags'] ) ) {
		return 'malformed fields';
	}
	if ( ! bv_dib_event_id_ok( $event ) ) {
		return 'id-hash mismatch';
	}
	return null;
}

/* -------------------------------------------------------------------------
 * Fetching
 * ---------------------------------------------------------------------- */

/**
 * Iterate relays, one-shot REQ each, until >= $min_success answered (spec) or
 * the list is exhausted. Returns merged, id-deduped events + per-relay status.
 *
 * @param array  $filters     NIP-01 filters for a single REQ.
 * @param array  $relays      Relay list (in order).
 * @param int    $min_success Stop after this many relays succeeded.
 * @param int    $timeout     Per-relay read deadline.
 * @return array{events: array<string,array>, relay_status: array<string,string>, seen_on: array<string,string[]>}
 */
function bv_dib_relay_collect( $filters, $relays, $min_success = 2, $timeout = 20 ) {
	$events       = array();
	$seen_on      = array();
	$relay_status = array();
	$ok           = 0;

	foreach ( (array) $relays as $relay ) {
		$res = BV_Nostr_WS::request( $relay, $filters, $timeout );
		if ( ! $res['ok'] ) {
			$relay_status[ $relay ] = 'FAIL: ' . $res['error'];
			continue;
		}
		++$ok;
		$extra                  = $res['notices'] ? ' notices: ' . implode( '; ', $res['notices'] ) : '';
		$relay_status[ $relay ] = sprintf( 'ok (%d events%s)', count( $res['events'] ), $extra );
		foreach ( $res['events'] as $event ) {
			if ( ! isset( $event['id'] ) ) {
				continue;
			}
			$id = strtolower( (string) $event['id'] );
			if ( ! isset( $events[ $id ] ) ) {
				$events[ $id ] = $event;
			}
			$seen_on[ $id ]   = isset( $seen_on[ $id ] ) ? $seen_on[ $id ] : array();
			$seen_on[ $id ][] = $relay;
		}
		if ( $ok >= $min_success ) {
			break;
		}
	}

	return array(
		'events'       => $events,
		'relay_status' => $relay_status,
		'seen_on'      => $seen_on,
	);
}

/**
 * Read events from an ndjson fixture file (offline / test-vector mode). Each
 * non-empty line is a raw event object or a relay `["EVENT", sub, event]`
 * message.
 *
 * @param string $path File path.
 * @return array{events: array<string,array>, seen_on: array<string,string[]>, errors: string[]}
 */
function bv_dib_read_events_file( $path ) {
	$events  = array();
	$seen_on = array();
	$errors  = array();
	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local fixture path from --file.
	$raw = file_get_contents( $path );
	if ( false === $raw ) {
		return array( 'events' => array(), 'seen_on' => array(), 'errors' => array( 'cannot read file: ' . $path ) );
	}
	$line_no = 0;
	foreach ( preg_split( '/\r\n|\r|\n/', $raw ) as $line ) {
		++$line_no;
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		$msg = json_decode( $line, true );
		if ( is_array( $msg ) && isset( $msg[0] ) && 'EVENT' === $msg[0] && isset( $msg[2] ) && is_array( $msg[2] ) ) {
			$msg = $msg[2]; // Relay message wrapper.
		}
		if ( ! is_array( $msg ) || ! isset( $msg['id'] ) ) {
			$errors[] = sprintf( 'line %d: unparseable, skipped', $line_no );
			continue;
		}
		$id = strtolower( (string) $msg['id'] );
		if ( ! isset( $events[ $id ] ) ) {
			$events[ $id ] = $msg;
		}
		$seen_on[ $id ]   = isset( $seen_on[ $id ] ) ? $seen_on[ $id ] : array();
		$seen_on[ $id ][] = 'file';
	}
	return array( 'events' => $events, 'seen_on' => $seen_on, 'errors' => $errors );
}

/* -------------------------------------------------------------------------
 * Lock (same atomic-option pattern as the podcast importer)
 * ---------------------------------------------------------------------- */

/**
 * Acquire the import lock atomically (INSERT IGNORE on the unique option_name).
 *
 * @return string|false Lock token if acquired, false otherwise.
 */
function bv_dib_acquire_lock() {
	global $wpdb;

	for ( $attempt = 0; $attempt < 2; $attempt++ ) {
		$token    = time() . ':' . wp_generate_password( 16, false );
		$inserted = $wpdb->query(
			$wpdb->prepare(
				"INSERT IGNORE INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, %s, 'off')",
				BV_DIB_LOCK_OPTION,
				$token
			)
		);
		if ( 1 === $inserted ) {
			wp_cache_delete( BV_DIB_LOCK_OPTION, 'options' );
			return $token;
		}

		$locked_at = $wpdb->get_var(
			$wpdb->prepare( "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", BV_DIB_LOCK_OPTION )
		);
		if ( null === $locked_at || (int) $locked_at > time() - BV_DIB_LOCK_TTL ) {
			return false;
		}
		// Compare-and-delete: only removes the exact stale value we just read.
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name = %s AND option_value = %s",
				BV_DIB_LOCK_OPTION,
				$locked_at
			)
		);
	}
	return false;
}

/**
 * Release the import lock (only if it is still ours).
 *
 * @param string $token Token returned by bv_dib_acquire_lock().
 */
function bv_dib_release_lock( $token ) {
	global $wpdb;
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name = %s AND option_value = %s",
			BV_DIB_LOCK_OPTION,
			(string) $token
		)
	);
	wp_cache_delete( BV_DIB_LOCK_OPTION, 'options' );
}

/* -------------------------------------------------------------------------
 * Existing-post index (dedupe + legacy bridge)
 * ---------------------------------------------------------------------- */

/**
 * Build lookup indexes of existing (non-trashed) `post`s.
 *
 * @return array{event_id: array<string,int>, title: array<string,int[]>, has_event: array<int,bool>}
 */
function bv_dib_existing_index() {
	global $wpdb;

	$excluded = array( 'trash', 'auto-draft', 'inherit' );
	$in       = implode( ',', array_fill( 0, count( $excluded ), '%s' ) );

	// phpcs:disable WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$posts = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type = 'post' AND post_status NOT IN ($in)",
			$excluded
		)
	);
	$metas = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT pm.post_id, pm.meta_value FROM {$wpdb->postmeta} pm
			INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			WHERE pm.meta_key = 'dib_event_id' AND p.post_type = 'post' AND p.post_status NOT IN ($in)",
			$excluded
		)
	);
	// phpcs:enable

	$index = array(
		'event_id'  => array(),
		'title'     => array(),
		'has_event' => array(),
	);
	foreach ( (array) $metas as $row ) {
		$meta_value = strtolower( (string) $row->meta_value );
		if ( '' !== $meta_value && ! isset( $index['event_id'][ $meta_value ] ) ) {
			$index['event_id'][ $meta_value ] = (int) $row->post_id;
		}
		$index['has_event'][ (int) $row->post_id ] = true;
	}
	foreach ( (array) $posts as $row ) {
		$key = bv_dib_canonical_title_key( $row->post_title );
		if ( null !== $key ) {
			$index['title'][ $key ][] = (int) $row->ID;
		}
	}
	return $index;
}

/* -------------------------------------------------------------------------
 * Post create/bridge/draft
 * ---------------------------------------------------------------------- */

/**
 * Create the WP post for a gated DIB event.
 *
 * @param array    $event   Validated event.
 * @param int      $day_ts  Bulletin day (00:00 UTC).
 * @param string[] $seen_on Relays the event was seen on.
 * @param string   $status  Post status (publish|draft).
 * @return int|WP_Error Post ID or error.
 */
function bv_dib_create_post( $event, $day_ts, $seen_on, $status ) {
	$title = bv_dib_title_for_day( $day_ts );
	// Never let "publish" become "future" (relay clock ahead of ours).
	$date_gmt = gmdate( 'Y-m-d H:i:s', min( (int) $event['created_at'], time() ) );

	$postarr = array(
		'post_type'     => 'post',
		'post_status'   => $status,
		'post_title'    => $title,
		'post_content'  => bv_dib_normalize_content( $event['content'] ),
		'post_excerpt'  => bv_dib_bluf_excerpt( $event['content'] ),
		'post_author'   => bv_dib_post_author(),
		'post_date'     => get_date_from_gmt( $date_gmt ),
		'post_date_gmt' => $date_gmt,
		'meta_input'    => array(
			'dib_event_id'     => strtolower( $event['id'] ),
			'dib_event_pubkey' => strtolower( $event['pubkey'] ),
			'dib_block'        => bv_dib_block_height( $event['content'] ),
			'dib_dtg'          => bv_dib_dtg_line( $event['content'] ),
			'dib_relays'       => wp_json_encode( array_values( (array) $seen_on ) ),
			'dib_imported_at'  => time(),
			'dib_event_json'   => wp_json_encode( $event, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ),
		),
	);
	$post_id = wp_insert_post( wp_slash( $postarr ), true );
	if ( is_wp_error( $post_id ) ) {
		return $post_id;
	}
	return $post_id;
}

/**
 * Bridge a legacy (hand-posted) DIB: set the four provenance metas only. Never
 * touches post_content/post_title/post_date. Missing metas only — existing
 * meta is left alone (first-imported wins).
 *
 * @param int   $post_id Post ID.
 * @param array $event   Validated event.
 */
function bv_dib_bridge_post( $post_id, $event ) {
	$wanted = array(
		'dib_event_id'     => strtolower( $event['id'] ),
		'dib_event_pubkey' => strtolower( $event['pubkey'] ),
		'dib_block'        => bv_dib_block_height( $event['content'] ),
		'dib_dtg'          => bv_dib_dtg_line( $event['content'] ),
	);
	foreach ( $wanted as $key => $value ) {
		if ( '' !== $value && '' === (string) get_post_meta( $post_id, $key, true ) ) {
			update_post_meta( $post_id, $key, wp_slash( $value ) );
		}
	}
}

/**
 * Sideload the cover image and set it as the featured image. A media failure
 * never blocks the import: it returns a WP_Error which the caller counts as a
 * warning.
 *
 * @param int    $post_id Post ID.
 * @param string $url     Image URL.
 * @param string $desc    Attachment title/description.
 * @return int|WP_Error Attachment ID or error.
 */
function bv_dib_sideload_cover( $post_id, $url, $desc ) {
	if ( '' === $url ) {
		return new WP_Error( 'bv_dib_no_media', 'no image URL in note' );
	}
	if ( ! function_exists( 'media_sideload_image' ) ) {
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
	}
	$attach_id = media_sideload_image( esc_url_raw( $url ), $post_id, $desc, 'id' );
	if ( is_wp_error( $attach_id ) ) {
		return $attach_id;
	}
	set_post_thumbnail( $post_id, $attach_id );
	update_post_meta( $post_id, 'dib_media_source_url', esc_url_raw( $url ) );
	return $attach_id;
}

/**
 * Event ids referenced by a kind-5 deletion event (e-tags).
 *
 * @param array $event Kind-5 event.
 * @return string[]
 */
function bv_dib_deletion_targets( $event ) {
	$out = array();
	foreach ( (array) $event['tags'] as $tag ) {
		if ( is_array( $tag ) && isset( $tag[0], $tag[1] ) && 'e' === $tag[0] && preg_match( '/^[0-9a-fA-F]{64}$/', $tag[1] ) ) {
			$out[] = strtolower( $tag[1] );
		}
	}
	return array_values( array_unique( $out ) );
}

/* -------------------------------------------------------------------------
 * The import
 * ---------------------------------------------------------------------- */

/**
 * Import DIBs from nostr.
 *
 * @param array $args {
 *     @type bool   $dry_run    Report only; touch nothing (no DB writes at all).
 *     @type string $since      Y-m-d (UTC) lower bound; default: now - lookback.
 *     @type string $until      Y-m-d (UTC) upper bound (inclusive); default: none.
 *     @type int    $limit      Keep only the newest N kind-1 events (0 = no cap).
 *     @type int    $max_create Abort if more posts than this would be created.
 *     @type bool   $media      Sideload cover images (default true).
 *     @type array  $relays     Relay list (default: configured).
 *     @type string $status     Post status for created posts (filterable default).
 *     @type string $file       Offline ndjson source instead of relays.
 *     @type bool   $verbose    (CLI concern, kept in summary for the run record.)
 * }
 * @return array Summary: dry_run, source, relays_used, events_in, gate_fail,
 *               created[], bridged[], skipped (int), skipped_detail[],
 *               duplicates_day[], deletions_applied[], media[], errors[].
 */
function bv_dib_import( $args = array() ) {
	$args = array_merge(
		array(
			'dry_run'    => false,
			'since'      => '',
			'until'      => '',
			'limit'      => 0,
			'max_create' => (int) apply_filters( 'bv_dib_max_creates', BV_DIB_MAX_CREATES ),
			'media'      => true,
			'relays'     => bv_dib_relays(),
			'status'     => (string) apply_filters( 'bv_dib_post_status', 'publish' ),
			'file'       => '',
			'verbose'    => false,
		),
		(array) $args
	);
	$dry_run = (bool) $args['dry_run'];

	$summary = array(
		'dry_run'            => $dry_run,
		'source'             => $args['file'] ? 'file' : 'relay',
		'relays_used'        => array(),
		'relay_status'       => array(),
		'events_in'          => 0,
		'events_valid'       => 0,
		'gate_fail'          => 0,
		'created'            => array(),
		'bridged'            => array(),
		'skipped'            => 0,
		'skipped_detail'     => array(),
		'duplicates_day'     => array(),
		'deletions_applied'  => array(),
		'deletion_candidates' => array(),
		'media'              => array(),
		'errors'             => array(),
	);

	// Resolve the window.
	$since_ts = 0;
	if ( '' !== (string) $args['since'] ) {
		$since_ts = strtotime( $args['since'] . ' 00:00:00 UTC' );
		if ( false === $since_ts ) {
			$summary['errors'][] = 'invalid --since date';
			return $summary;
		}
	}
	$until_ts = 0;
	if ( '' !== (string) $args['until'] ) {
		$until_ts = strtotime( $args['until'] . ' 23:59:59 UTC' );
		if ( false === $until_ts ) {
			$summary['errors'][] = 'invalid --until date';
			return $summary;
		}
	}

	$lock = false;
	if ( ! $dry_run ) {
		$lock = bv_dib_acquire_lock();
		if ( false === $lock ) {
			$summary['errors'][] = 'Import already running (lock held); skipped.';
			error_log( 'BV DIB import: another import is running; skipped.' );
			return $summary;
		}
	}

	try {
		$authors = bv_dib_authors();
		if ( ! $authors ) {
			$summary['errors'][] = 'no valid author keys configured';
			return $summary;
		}
		$authors_map = array_fill_keys( $authors, true );

		/* 1. Gather events (file, or relays with the >=2-success policy). */
		$deletion_since = 0;
		$seen_on        = array();
		if ( $args['file'] ) {
			$read         = bv_dib_read_events_file( $args['file'] );
			$raw_events   = $read['events'];
			$seen_on      = $read['seen_on'];
			$summary['errors']       = array_merge( $summary['errors'], $read['errors'] );
			$summary['relays_used']  = array( 'file:' . $args['file'] );
		} else {
			if ( ! $since_ts ) {
				$since_ts = time() - BV_DIB_DEFAULT_LOOKBACK;
			}
			if ( ! $args['relays'] ) {
				$summary['errors'][] = 'no relays configured';
				return $summary;
			}
			// NIP-09 window: deletions newer than the last non-dry run (0 = all).
			$last_run       = get_option( BV_DIB_LAST_RUN_OPTION );
			$deletion_since = ( is_array( $last_run ) && ! empty( $last_run['time'] ) ) ? (int) $last_run['time'] : 0;

			$kind1_filter = array(
				'authors' => $authors,
				'kinds'   => array( 1 ),
				'since'   => $since_ts,
			);
			if ( $until_ts ) {
				$kind1_filter['until'] = $until_ts;
			}
			$kind1_filter['limit'] = $args['limit'] ? (int) $args['limit'] : BV_DIB_DEFAULT_REQ_LIMIT;
			$kind5_filter          = array(
				'authors' => $authors,
				'kinds'   => array( 5 ),
				'since'   => $deletion_since,
				'limit'   => 200,
			);

			$collected               = bv_dib_relay_collect( array( $kind1_filter, $kind5_filter ), (array) $args['relays'], 2 );
			$raw_events              = $collected['events'];
			$seen_on                 = $collected['seen_on'];
			$summary['relay_status'] = $collected['relay_status'];
			$summary['relays_used']  = array_keys( array_filter(
				$collected['relay_status'],
				function ( $s ) {
					return 0 === strpos( $s, 'ok' );
				}
			) );
			if ( ! $summary['relays_used'] ) {
				$summary['errors'][] = 'all relays failed; nothing imported';
				return $summary;
			}
		}
		$summary['events_in'] = count( $raw_events );

		/* 2. Validate (well-formed, whitelisted author, id-hash) and partition. */
		$kind1 = array();
		$kind5 = array();
		foreach ( $raw_events as $id => $event ) {
			$reason = bv_dib_event_reject_reason( $event );
			if ( null !== $reason ) {
				$summary['skipped_detail'][] = sprintf( '%s: rejected (%s)', substr( (string) $id, 0, 12 ), $reason );
				++$summary['skipped'];
				continue;
			}
			$pubkey = strtolower( $event['pubkey'] );
			if ( ! isset( $authors_map[ $pubkey ] ) ) {
				$summary['skipped_detail'][] = sprintf( '%s: rejected (author not whitelisted)', substr( (string) $id, 0, 12 ) );
				++$summary['skipped'];
				continue;
			}
			if ( 1 === (int) $event['kind'] ) {
				$kind1[] = $event;
			} elseif ( 5 === (int) $event['kind'] ) {
				$kind5[] = $event;
			}
			// Other kinds are ignored silently (not DIB-relevant).
		}

		// Newest-first sort, cap by --limit (--limit keeps the newest, mirroring
		// relay-limit semantics), then process oldest-first.
		usort(
			$kind1,
			function ( $a, $b ) {
				return (int) $b['created_at'] - (int) $a['created_at'];
			}
		);
		if ( $args['limit'] && count( $kind1 ) > (int) $args['limit'] ) {
			$kind1 = array_slice( $kind1, 0, (int) $args['limit'] );
		}
		$kind1 = array_reverse( $kind1 );

		// Window restriction applies to file mode too (test vectors rely on it).
		if ( $args['file'] && ( $since_ts || $until_ts ) ) {
			$kind1 = array_values(
				array_filter(
					$kind1,
					function ( $e ) use ( $since_ts, $until_ts ) {
						// Window is applied to the DTG date (= post date), not the
						// relay arrival time, so fixtures behave like relay windows.
						list( $day_ts ) = bv_dib_event_day( $e );
						if ( $since_ts && $day_ts < gmmktime( 0, 0, 0, (int) gmdate( 'n', $since_ts ), (int) gmdate( 'j', $since_ts ), (int) gmdate( 'Y', $since_ts ) ) ) {
							return false;
						}
						if ( $until_ts && $day_ts > $until_ts ) {
							return false;
						}
						return true;
					}
				)
			);
		}
		$summary['events_valid'] = count( $kind1 );

		/* 3. The DIB gate. */
		$gated = array();
		foreach ( $kind1 as $event ) {
			if ( ! bv_dib_is_dib_content( $event['content'] ) ) {
				++$summary['gate_fail'];
				$summary['skipped_detail'][] = sprintf( '%s: not a DIB (gate)', substr( strtolower( $event['id'] ), 0, 12 ) );
				continue;
			}
			$gated[] = $event;
		}

		/* 4. Resolve: dedupe by event id, else legacy date-bridge, else create. */
		$index       = bv_dib_existing_index();
		$to_create   = array();
		$to_bridge   = array(); // event => list of post ids.
		$claimed_day = array(); // canonical title key => true once any event claims it this run.
		foreach ( $gated as $event ) {
			$id = strtolower( $event['id'] );
			if ( isset( $index['event_id'][ $id ] ) ) {
				++$summary['skipped'];
				$summary['skipped_detail'][] = sprintf( '%s: already imported (post #%d)', substr( $id, 0, 12 ), $index['event_id'][ $id ] );
				continue;
			}

			list( $day_ts, $day_src ) = bv_dib_event_day( $event );
			$key                      = bv_dib_canonical_title_key( bv_dib_title_for_day( $day_ts ) );

			// Same-day rule: one bulletin per day wins; later same-day notes from
			// the other whitelisted key are skipped and logged for manual triage.
			if ( isset( $claimed_day[ $key ] ) ) {
				$summary['duplicates_day'][] = sprintf(
					'%s (%s): same-day note for %s skipped (already claimed this run)',
					substr( $id, 0, 12 ),
					$day_src,
					bv_dib_title_for_day( $day_ts )
				);
				continue;
			}

			if ( $key && ! empty( $index['title'][ $key ] ) ) {
				$candidates = $index['title'][ $key ];
				$bridged_any = false;
				$blocked     = array();
				foreach ( $candidates as $post_id ) {
					if ( isset( $index['has_event'][ $post_id ] ) ) {
						$blocked[] = $post_id; // Already mirrored: another day-note targeted it.
						continue;
					}
					$bridged_any = true;
					$to_bridge[] = array( 'event' => $event, 'post_id' => $post_id, 'day_ts' => $day_ts );
				}
				if ( $bridged_any ) {
					$claimed_day[ $key ] = true;
					continue;
				}
				if ( $blocked ) {
					$summary['duplicates_day'][] = sprintf(
						'%s (%s): same-day note for %s skipped (post #%d already mirrored)',
						substr( $id, 0, 12 ),
						$day_src,
						bv_dib_title_for_day( $day_ts ),
						$blocked[0]
					);
					$claimed_day[ $key ] = true;
					continue;
				}
			}

			$claimed_day[ $key ] = true;
			$to_create[]         = array( 'event' => $event, 'day_ts' => $day_ts, 'day_src' => $day_src );
		}

		/* 5. Safety valve: never write on a runaway create count. */
		$max_creates = (int) $args['max_create'];
		if ( count( $to_create ) > $max_creates ) {
			foreach ( $to_create as $item ) {
				$summary['created'][] = bv_dib_title_for_day( $item['day_ts'] ) . ' [would create]';
			}
			$message = sprintf(
				'Refusing to run: %d posts would be created (limit %d). Check the relay window, then run `wp bv dib-import --dry-run --verbose` and `--max-create=<n>` if intended. Nothing was written.',
				count( $to_create ),
				$max_creates
			);
			$summary['errors'][] = $message;
			error_log( 'BV DIB import: ' . $message );
			return $summary;
		}

		/* 6a. Bridge legacy posts (meta only). */
		foreach ( $to_bridge as $item ) {
			$event   = $item['event'];
			$post_id = $item['post_id'];
			$summary['bridged'][] = sprintf(
				'#%d %s <= %s',
				$post_id,
				bv_dib_title_for_day( $item['day_ts'] ),
				substr( strtolower( $event['id'] ), 0, 12 )
			);
			if ( ! $dry_run ) {
				bv_dib_bridge_post( $post_id, $event );
				$index['has_event'][ $post_id ] = true;
				$index['event_id'][ strtolower( $event['id'] ) ] = $post_id;
			}
		}

		/* 6b. Create new posts (+ optional media). */
		foreach ( $to_create as $item ) {
			$event = $item['event'];
			$title = bv_dib_title_for_day( $item['day_ts'] );
			$line  = sprintf( '%s (%s, %s)', $title, substr( strtolower( $event['id'] ), 0, 12 ), $item['day_src'] );
			if ( $dry_run ) {
				$summary['created'][] = $line . ' [would create]';
			} else {
				$post_id = bv_dib_create_post( $event, $item['day_ts'], isset( $seen_on[ strtolower( $event['id'] ) ] ) ? $seen_on[ strtolower( $event['id'] ) ] : array(), $args['status'] );
				if ( is_wp_error( $post_id ) || ! $post_id ) {
					$message             = sprintf( 'Failed to create "%s": %s', $title, is_wp_error( $post_id ) ? $post_id->get_error_message() : 'unknown error' );
					$summary['errors'][] = $message;
					error_log( 'BV DIB import: ' . $message );
					continue;
				}
				$summary['created'][]                          = sprintf( '#%d %s', $post_id, $line );
				$index['event_id'][ strtolower( $event['id'] ) ] = $post_id;
				$index['has_event'][ $post_id ]                = true;
				error_log( sprintf( 'BV DIB import: created post %d for "%s".', $post_id, $title ) );

				if ( $args['media'] ) {
					$img = bv_dib_first_image_url( $event['content'] );
					if ( '' !== $img ) {
						$attach = bv_dib_sideload_cover( $post_id, $img, $title );
						if ( is_wp_error( $attach ) ) {
							$summary['media'][] = sprintf( '#%d media FAILED (non-blocking): %s', $post_id, $attach->get_error_message() );
							error_log( sprintf( 'BV DIB import: media sideload failed for post %d: %s', $post_id, $attach->get_error_message() ) );
						} else {
							$summary['media'][] = sprintf( '#%d media: attachment #%d from %s', $post_id, $attach, $img );
						}
					}
				}
			}
		}

		/* 6c. NIP-09 deletions: referenced imported events move to draft. */
		foreach ( $kind5 as $event ) {
			foreach ( bv_dib_deletion_targets( $event ) as $target_id ) {
				if ( ! isset( $index['event_id'][ $target_id ] ) ) {
					continue; // Deletion of something we never imported.
				}
				$post_id = $index['event_id'][ $target_id ];
				$post    = get_post( $post_id );
				if ( ! $post || 'draft' === $post->post_status ) {
					continue;
				}
				if ( $dry_run ) {
					$summary['deletion_candidates'][] = sprintf( '#%d would move to draft (event %s)', $post_id, substr( $target_id, 0, 12 ) );
					continue;
				}
				$done = wp_update_post(
					array(
						'ID'          => $post_id,
						'post_status' => 'draft',
					),
					true
				);
				if ( is_wp_error( $done ) ) {
					$summary['errors'][] = sprintf( 'Failed to draft deleted post #%d: %s', $post_id, $done->get_error_message() );
				} else {
					$summary['deletions_applied'][] = sprintf( '#%d moved to draft (event %s)', $post_id, substr( $target_id, 0, 12 ) );
					error_log( sprintf( 'BV DIB import: NIP-09 deletion moved post %d to draft.', $post_id ) );
				}
			}
		}

		/* 7. Record the run (non-dry runs only — dry runs write nothing at all). */
		if ( ! $dry_run ) {
			update_option(
				BV_DIB_LAST_RUN_OPTION,
				array(
					'time'               => time(),
					'source'             => $summary['source'],
					'relays_used'        => $summary['relays_used'],
					'window'             => array( 'since' => $since_ts, 'until' => $until_ts ),
					'events_in'          => $summary['events_in'],
					'created'            => count( $summary['created'] ),
					'bridged'            => count( $summary['bridged'] ),
					'skipped'            => $summary['skipped'] + $summary['gate_fail'],
					'duplicates_day'     => count( $summary['duplicates_day'] ),
					'deletions_applied'  => count( $summary['deletions_applied'] ),
					'errors'             => $summary['errors'],
				),
				false
			);
		}
	} finally {
		if ( false !== $lock ) {
			bv_dib_release_lock( $lock );
		}
	}

	return $summary;
}

/**
 * Cron callback (wrapper so cron args can never trigger a dry run by accident).
 */
function bv_dib_cron_import() {
	bv_dib_import( array( 'dry_run' => false ) );
}
add_action( BV_DIB_CRON_HOOK, 'bv_dib_cron_import' );

/**
 * Count scheduled events for a hook (any args). Reads the autoloaded cron array.
 *
 * @param string $hook Hook name.
 * @return int
 */
function bv_dib_count_scheduled( $hook ) {
	$count = 0;
	$crons = function_exists( '_get_cron_array' ) ? _get_cron_array() : array();
	foreach ( (array) $crons as $events ) {
		if ( isset( $events[ $hook ] ) && is_array( $events[ $hook ] ) ) {
			$count += count( $events[ $hook ] );
		}
	}
	return $count;
}

/**
 * Make sure the hourly import is scheduled exactly once. Cheap: only reads the
 * autoloaded `cron` option; writes happen only when something needs changing.
 */
function bv_dib_maybe_schedule() {
	$count = bv_dib_count_scheduled( BV_DIB_CRON_HOOK );
	if ( 1 === $count ) {
		return;
	}
	if ( $count > 1 ) {
		// Two first requests raced and both scheduled; collapse to one.
		wp_clear_scheduled_hook( BV_DIB_CRON_HOOK );
	}
	wp_schedule_event( time() + HOUR_IN_SECONDS, 'hourly', BV_DIB_CRON_HOOK );
}
add_action( 'init', 'bv_dib_maybe_schedule' );

/**
 * Unschedule the import when switching away from this theme.
 */
function bv_dib_unschedule() {
	wp_clear_scheduled_hook( BV_DIB_CRON_HOOK );
}
add_action( 'switch_theme', 'bv_dib_unschedule' );

/* -------------------------------------------------------------------------
 * WP-CLI
 * ---------------------------------------------------------------------- */

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	/**
	 * Import DIB nostr notes into posts.
	 *
	 * ## OPTIONS
	 *
	 * [--dry-run]
	 * : Report what would be created/bridged without writing anything.
	 *
	 * [--verbose]
	 * : List affected events/posts one per line.
	 *
	 * [--since=<Y-m-d>]
	 * : Only consider notes dated on/after this UTC date (DTG header date).
	 *   Default: the last 7 days.
	 *
	 * [--until=<Y-m-d>]
	 * : Only consider notes dated on/before this UTC date.
	 *
	 * [--limit=<n>]
	 * : Keep only the newest <n> kind-1 events (relay-limit semantics; also
	 *   used as the REQ limit).
	 *
	 * [--max-create=<n>]
	 * : Abort without writing if more than this many posts would be created.
	 *   Defaults to BV_DIB_MAX_CREATES (8).
	 *
	 * [--no-media]
	 * : Skip cover-image sideloading (media is on by default).
	 *
	 * [--relay=<relay-list>]
	 * : Use this comma-separated relay list (wss://…,wss://…) instead of the
	 *   configured default.
	 *
	 * [--status=<publish|draft>]
	 * : Post status for created posts. Default publish.
	 *
	 * [--file=<events-file>]
	 * : Import from a local ndjson fixture (e.g. dib-test-vector.ndjson)
	 *   instead of the relays (offline import / test vectors). Each line is a
	 *   raw event or ["EVENT",…].
	 *
	 * ## EXAMPLES
	 *
	 *     wp bv dib-import --dry-run --verbose --since=2026-10-01
	 *     wp bv dib-import --since=2025-03-25 --until=2025-04-02 --max-create=400
	 *     wp bv dib-import --dry-run --file=events.ndjson
	 *
	 * @param array $args       Positional args (unused).
	 * @param array $assoc_args Flags.
	 */
	function bv_dib_cli_import( $args, $assoc_args ) {
		$dry_run = (bool) \WP_CLI\Utils\get_flag_value( $assoc_args, 'dry-run', false );
		$verbose = (bool) \WP_CLI\Utils\get_flag_value( $assoc_args, 'verbose', false );

		$import = array( 'dry_run' => $dry_run, 'verbose' => $verbose );

		foreach ( array( 'since', 'until' ) as $flag ) {
			if ( isset( $assoc_args[ $flag ] ) ) {
				if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', (string) $assoc_args[ $flag ] ) ) {
					WP_CLI::error( "--{$flag} must be Y-m-d." );
				}
				$import[ $flag ] = (string) $assoc_args[ $flag ];
			}
		}
		if ( isset( $assoc_args['limit'] ) ) {
			if ( ! is_numeric( $assoc_args['limit'] ) || (int) $assoc_args['limit'] < 1 ) {
				WP_CLI::error( '--limit must be a positive integer.' );
			}
			$import['limit'] = (int) $assoc_args['limit'];
		}
		if ( isset( $assoc_args['max-create'] ) ) {
			if ( ! is_numeric( $assoc_args['max-create'] ) || (int) $assoc_args['max-create'] < 0 ) {
				WP_CLI::error( '--max-create must be a non-negative integer.' );
			}
			$import['max_create'] = (int) $assoc_args['max-create'];
		}
		if ( isset( $assoc_args['no-media'] ) ) {
			$import['media'] = ! (bool) $assoc_args['no-media'];
		}
		if ( isset( $assoc_args['relay'] ) ) {
			$relays = array_values( array_filter( array_map( 'trim', explode( ',', (string) $assoc_args['relay'] ) ) ) );
			foreach ( $relays as $relay ) {
				if ( ! preg_match( '#^wss?://#', $relay ) ) {
					WP_CLI::error( '--relay entries must be wss:// URLs.' );
				}
			}
			$import['relays'] = $relays;
		}
		if ( isset( $assoc_args['status'] ) ) {
			if ( ! in_array( $assoc_args['status'], array( 'publish', 'draft' ), true ) ) {
				WP_CLI::error( '--status must be publish or draft.' );
			}
			$import['status'] = (string) $assoc_args['status'];
		}
		if ( isset( $assoc_args['file'] ) ) {
			$file = (string) $assoc_args['file'];
			if ( ! is_readable( $file ) ) {
				WP_CLI::error( "--file not readable: {$file}" );
			}
			$import['file'] = $file;
		}

		$summary = bv_dib_import( $import );
		$verb    = $dry_run ? 'Would ' : '';

		if ( $summary['relay_status'] ) {
			foreach ( $summary['relay_status'] as $relay => $status ) {
				WP_CLI::log( sprintf( 'Relay %-30s %s', $relay . ':', $status ) );
			}
		}
		WP_CLI::log( sprintf( 'Source: %s | events in: %d | gated DIBs: %d | gate failures: %d', $summary['source'], $summary['events_in'], $summary['events_valid'] - $summary['gate_fail'], $summary['gate_fail'] ) );
		WP_CLI::log( sprintf( '%screate: %d', $verb, count( $summary['created'] ) ) );
		if ( $verbose ) {
			foreach ( $summary['created'] as $line ) {
				WP_CLI::log( '  + ' . $line );
			}
		}
		WP_CLI::log( sprintf( '%sbridge (legacy meta): %d', $verb, count( $summary['bridged'] ) ) );
		if ( $verbose ) {
			foreach ( $summary['bridged'] as $line ) {
				WP_CLI::log( '  ~ ' . $line );
			}
		}
		WP_CLI::log( sprintf( 'Skipped (in sync / rejected): %d', $summary['skipped'] ) );
		if ( $verbose ) {
			foreach ( $summary['skipped_detail'] as $line ) {
				WP_CLI::log( '  - ' . $line );
			}
		}
		if ( $summary['duplicates_day'] ) {
			WP_CLI::log( sprintf( 'Same-day duplicates (manual triage): %d', count( $summary['duplicates_day'] ) ) );
			foreach ( $summary['duplicates_day'] as $line ) {
				WP_CLI::log( '  = ' . $line );
			}
		}
		if ( $summary['deletions_applied'] || $summary['deletion_candidates'] ) {
			foreach ( $summary['deletions_applied'] as $line ) {
				WP_CLI::log( '  ! NIP-09: ' . $line );
			}
			foreach ( $summary['deletion_candidates'] as $line ) {
				WP_CLI::log( '  ! NIP-09 (dry-run): ' . $line );
			}
		}
		if ( $verbose ) {
			foreach ( $summary['media'] as $line ) {
				WP_CLI::log( '  m ' . $line );
			}
		}
		foreach ( $summary['errors'] as $error ) {
			WP_CLI::warning( $error );
		}
		if ( $summary['errors'] ) {
			WP_CLI::halt( 1 );
		}
		WP_CLI::success( $dry_run ? 'Dry run complete; nothing was written.' : 'Import complete.' );
	}
	WP_CLI::add_command( 'bv dib-import', 'bv_dib_cli_import' );

	/**
	 * Show DIB importer status: cron schedule, lock, last run summary, and
	 * mirror counts.
	 *
	 * ## EXAMPLES
	 *
	 *     wp bv dib-status
	 */
	function bv_dib_cli_status() {
		global $wpdb;

		WP_CLI::log( 'Authors: ' . implode( ', ', bv_dib_authors() ) );
		WP_CLI::log( 'Relays:  ' . implode( ', ', bv_dib_relays() ) );
		WP_CLI::log( 'Max creates per run: ' . (int) apply_filters( 'bv_dib_max_creates', BV_DIB_MAX_CREATES ) . ' [--max-create overrides]' );

		$count = bv_dib_count_scheduled( BV_DIB_CRON_HOOK );
		$next  = wp_next_scheduled( BV_DIB_CRON_HOOK );
		WP_CLI::log( sprintf(
			'Cron: %d event(s) scheduled for %s%s',
			$count,
			BV_DIB_CRON_HOOK,
			$next ? '; next run ' . gmdate( 'Y-m-d H:i:s', $next ) . ' UTC' : ' (not scheduled)'
		) );

		$lock = $wpdb->get_var( $wpdb->prepare( "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", BV_DIB_LOCK_OPTION ) );
		WP_CLI::log( 'Lock: ' . ( $lock ? 'held since ' . gmdate( 'Y-m-d H:i:s', (int) $lock ) . ' UTC (stale after 15 min)' : 'free' ) );

		$last = get_option( BV_DIB_LAST_RUN_OPTION );
		if ( is_array( $last ) && ! empty( $last['time'] ) ) {
			WP_CLI::log( sprintf(
				'Last run: %s UTC (%s, relays: %s)',
				gmdate( 'Y-m-d H:i:s', (int) $last['time'] ),
				isset( $last['source'] ) ? $last['source'] : '?',
				isset( $last['relays_used'] ) && $last['relays_used'] ? implode( ', ', (array) $last['relays_used'] ) : 'n/a'
			) );
			WP_CLI::log( sprintf(
				'  events in %d | created %d | bridged %d | skipped %d | same-day dups %d | deletions %d | errors %d',
				isset( $last['events_in'] ) ? (int) $last['events_in'] : 0,
				isset( $last['created'] ) ? (int) $last['created'] : 0,
				isset( $last['bridged'] ) ? (int) $last['bridged'] : 0,
				isset( $last['skipped'] ) ? (int) $last['skipped'] : 0,
				isset( $last['duplicates_day'] ) ? (int) $last['duplicates_day'] : 0,
				isset( $last['deletions_applied'] ) ? (int) $last['deletions_applied'] : 0,
				isset( $last['errors'] ) ? count( (array) $last['errors'] ) : 0
			) );
			foreach ( (array) ( isset( $last['errors'] ) ? $last['errors'] : array() ) as $error ) {
				WP_CLI::log( '  error: ' . $error );
			}
		} else {
			WP_CLI::log( 'Last run: never (no import has run yet).' );
		}

		$mirrored = $wpdb->get_var(
			"SELECT COUNT(DISTINCT pm.post_id) FROM {$wpdb->postmeta} pm INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE pm.meta_key = 'dib_event_id' AND p.post_type = 'post' AND p.post_status NOT IN ('trash','auto-draft','inherit')"
		);
		WP_CLI::log( sprintf( 'Posts carrying dib_event_id: %d', (int) $mirrored ) );
	}
	WP_CLI::add_command( 'bv dib-status', 'bv_dib_cli_status' );
}
