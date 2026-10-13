<?php
/**
 * Podcast importer: pulls episodes from the public podcast RSS feed into the
 * `cc_starter_podcast` post type.
 *
 * - Runs only via a daily WP-Cron event (`bv_import_podcast_episodes_hook`)
 *   or manually via WP-CLI (`wp bv podcast-import [--dry-run]`).
 * - Uses an atomic option lock so overlapping runs cannot double-import.
 * - De-duplicates by feed guid (post meta `podcast_guid`), falling back to a
 *   normalized title match for posts imported before guids were stored. A title
 *   match never creates a new post; it backfills the guid / episode URLs instead.
 *
 * The feed URL can be overridden in wp-config.php:
 *   define( 'BV_PODCAST_FEED_URL', 'https://example.com/feed.xml' );
 *
 * @package cc_starter
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Default public RSS feed (Fountain). */
define( 'BV_PODCAST_DEFAULT_FEED_URL', 'https://feeds.fountain.fm/5MveuCvTFOfBjfigAD8M' );

/** Post type episodes are imported into. */
define( 'BV_PODCAST_POST_TYPE', 'cc_starter_podcast' );

/** Cron hook for the daily import. */
define( 'BV_PODCAST_CRON_HOOK', 'bv_import_podcast_episodes_hook' );

/** Legacy Spotify cron hook, cleared once on upgrade. */
define( 'BV_PODCAST_LEGACY_CRON_HOOK', 'import_spotify_episodes_hook' );

/** Option flag recording that the legacy Spotify schedule has been cleared. */
define( 'BV_PODCAST_MIGRATED_OPTION', 'bv_podcast_feed_migrated' );

/** Option used as an import lock. */
define( 'BV_PODCAST_LOCK_OPTION', 'bv_podcast_import_lock' );

/** Seconds after which a lock is considered stale (crashed run). */
define( 'BV_PODCAST_LOCK_TTL', 15 * MINUTE_IN_SECONDS );

/**
 * Get the feed URL (constant override, else the Fountain default).
 *
 * @return string
 */
function bv_podcast_feed_url() {
	if ( defined( 'BV_PODCAST_FEED_URL' ) && BV_PODCAST_FEED_URL ) {
		return BV_PODCAST_FEED_URL;
	}
	return BV_PODCAST_DEFAULT_FEED_URL;
}

/**
 * ACF field keys for the "Podcast Fields" group (group_66d082af378a5).
 *
 * Keys are used instead of field names because the name `description` exists
 * in several field groups; update_field() by name on a brand-new post can
 * resolve to the wrong field.
 *
 * @return array<string,string> Field name => field key.
 */
function bv_podcast_acf_field_keys() {
	return array(
		'episode_date'   => 'field_66d082af54672',
		'description'    => 'field_66d0831354673',
		'episode_number' => 'field_66d0836454674',
	);
}

/**
 * Set an episode field via ACF when available, else as plain post meta.
 *
 * @param int    $post_id Post ID.
 * @param string $name    Field name.
 * @param mixed  $value   Value.
 */
function bv_podcast_set_field( $post_id, $name, $value ) {
	$keys = bv_podcast_acf_field_keys();
	if ( function_exists( 'update_field' ) && isset( $keys[ $name ] ) ) {
		update_field( $keys[ $name ], $value, $post_id );
		return;
	}
	update_post_meta( $post_id, $name, $value );
}

/**
 * Normalize an episode title for matching: decode entities, unify quotes and
 * whitespace, trim, lowercase.
 *
 * @param string $title Raw title (may contain HTML entities).
 * @return string
 */
function bv_podcast_normalize_title( $title ) {
	$title = (string) $title;
	// Decode twice to cope with double-encoded titles (e.g. "&amp;amp;").
	$title = html_entity_decode( $title, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	$title = html_entity_decode( $title, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	$title = wp_strip_all_tags( $title );
	$title = str_replace(
		array( "\u{2018}", "\u{2019}", "\u{201A}", "\u{2032}", "\u{201C}", "\u{201D}", "\u{201E}", "\u{2033}" ),
		array( "'", "'", "'", "'", '"', '"', '"', '"' ),
		$title
	);
	$title = preg_replace( '/[\s\x{00A0}\x{200B}\x{FEFF}]+/u', ' ', $title );
	$title = trim( (string) $title );
	return function_exists( 'mb_strtolower' ) ? mb_strtolower( $title, 'UTF-8' ) : strtolower( $title );
}

/**
 * Parse an episode number from a title such as "Freedom Friday Ep. 278" or
 * "... Ep 270". Returns null if no number is present.
 *
 * @param string $title Title.
 * @return int|null
 */
function bv_podcast_parse_episode_number( $title ) {
	if ( preg_match_all( '/\bEp(?:isode)?\.?\s*#?\s*(\d+)\b/i', (string) $title, $matches ) ) {
		return (int) end( $matches[1] );
	}
	return null;
}

/**
 * Convert feed description HTML to plain text for the ACF textarea.
 *
 * @param string $html Description HTML.
 * @return string
 */
function bv_podcast_description_to_text( $html ) {
	if ( '' === trim( (string) $html ) ) {
		return '';
	}
	$text = preg_replace( '#</p>\s*<p[^>]*>|<br\s*/?>#i', "\n\n", (string) $html );
	$text = wp_strip_all_tags( $text );
	$text = html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	$text = preg_replace( "/\n{3,}/", "\n\n", $text );
	return trim( (string) $text );
}

/**
 * Disable SimplePie's transient cache for the importer's feed request. The
 * feed is ~700 KB; caching it in wp_options is wasteful for a daily job, and
 * dry runs must not write to the database at all.
 *
 * @param SimplePie $feed Feed object (passed by reference via do_action_ref_array).
 */
function bv_podcast_disable_feed_cache( $feed ) {
	if ( is_object( $feed ) && method_exists( $feed, 'enable_cache' ) ) {
		$feed->enable_cache( false );
	}
}

/**
 * Fetch and parse the feed into a list of plain episode arrays.
 *
 * @return array|WP_Error List of episodes or error.
 */
function bv_podcast_fetch_episodes() {
	if ( ! function_exists( 'fetch_feed' ) ) {
		require_once ABSPATH . WPINC . '/feed.php';
	}

	add_action( 'wp_feed_options', 'bv_podcast_disable_feed_cache' );
	$feed = fetch_feed( bv_podcast_feed_url() );
	remove_action( 'wp_feed_options', 'bv_podcast_disable_feed_cache' );

	if ( is_wp_error( $feed ) ) {
		return $feed;
	}

	$episodes = array();
	foreach ( (array) $feed->get_items() as $item ) {
		$title = trim( html_entity_decode( (string) $item->get_title(), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
		$guid  = trim( (string) $item->get_id() );
		if ( '' === $title || '' === $guid ) {
			continue;
		}

		$enclosure = $item->get_enclosure();
		$audio_url = $enclosure ? (string) $enclosure->get_link() : '';
		$timestamp = $item->get_date( 'U' );

		$episodes[] = array(
			'guid'        => $guid,
			'title'       => $title,
			'link'        => (string) $item->get_permalink(),
			'audio_url'   => $audio_url,
			'timestamp'   => $timestamp ? (int) $timestamp : null,
			'description' => bv_podcast_description_to_text( (string) $item->get_description( true ) ),
		);
	}

	// Oldest first, so new posts are created in chronological order.
	return array_reverse( $episodes );
}

/**
 * Build lookup indexes of existing (non-trashed) podcast posts.
 *
 * @return array{guid: array<string,int>, title: array<string,int[]>, raw: array<int,string>}
 */
function bv_podcast_existing_index() {
	global $wpdb;

	$excluded = array( 'trash', 'auto-draft', 'inherit' );
	$in       = implode( ',', array_fill( 0, count( $excluded ), '%s' ) );

	// phpcs:disable WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$posts = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type = %s AND post_status NOT IN ($in) ORDER BY ID ASC",
			array_merge( array( BV_PODCAST_POST_TYPE ), $excluded )
		)
	);
	$guids = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT pm.post_id, pm.meta_value FROM {$wpdb->postmeta} pm
			INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			WHERE pm.meta_key = 'podcast_guid' AND p.post_type = %s AND p.post_status NOT IN ($in)",
			array_merge( array( BV_PODCAST_POST_TYPE ), $excluded )
		)
	);
	// phpcs:enable

	$index = array(
		'guid'  => array(),
		'title' => array(),
		'raw'   => array(),
	);
	foreach ( (array) $guids as $row ) {
		if ( '' !== $row->meta_value && ! isset( $index['guid'][ $row->meta_value ] ) ) {
			$index['guid'][ $row->meta_value ] = (int) $row->post_id;
		}
	}
	foreach ( (array) $posts as $row ) {
		$index['title'][ bv_podcast_normalize_title( $row->post_title ) ][] = (int) $row->ID;
		$index['raw'][ (int) $row->ID ]                                       = $row->post_title;
	}
	// Prime the meta cache in one query (read-only) for the get_post_meta() checks later.
	if ( $posts ) {
		update_meta_cache( 'post', wp_list_pluck( $posts, 'ID' ) );
	}
	return $index;
}

/**
 * Acquire the import lock atomically (INSERT IGNORE on the unique option_name).
 *
 * @return bool True if acquired.
 */
function bv_podcast_acquire_lock() {
	global $wpdb;

	for ( $attempt = 0; $attempt < 2; $attempt++ ) {
		$inserted = $wpdb->query(
			$wpdb->prepare(
				"INSERT IGNORE INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, %s, 'off')",
				BV_PODCAST_LOCK_OPTION,
				(string) time()
			)
		);
		if ( 1 === $inserted ) {
			wp_cache_delete( BV_PODCAST_LOCK_OPTION, 'options' );
			return true;
		}

		// Lock held: break it only if stale (previous run crashed).
		$locked_at = $wpdb->get_var(
			$wpdb->prepare( "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", BV_PODCAST_LOCK_OPTION )
		);
		if ( null === $locked_at || (int) $locked_at > time() - BV_PODCAST_LOCK_TTL ) {
			return false;
		}
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name = %s AND option_value = %s",
				BV_PODCAST_LOCK_OPTION,
				$locked_at
			)
		);
	}
	return false;
}

/**
 * Release the import lock.
 */
function bv_podcast_release_lock() {
	global $wpdb;
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name = %s", BV_PODCAST_LOCK_OPTION ) );
	wp_cache_delete( BV_PODCAST_LOCK_OPTION, 'options' );
}

/**
 * Work out which meta keys are missing on an existing post.
 *
 * @param int   $post_id Post ID.
 * @param array $episode Episode data.
 * @return array<string,string> Meta key => value to backfill.
 */
function bv_podcast_missing_meta( $post_id, $episode ) {
	$wanted = array(
		'podcast_guid'      => $episode['guid'],
		'episode_url'       => $episode['link'],
		'episode_audio_url' => $episode['audio_url'],
	);
	$missing = array();
	foreach ( $wanted as $key => $value ) {
		if ( '' !== $value && '' === (string) get_post_meta( $post_id, $key, true ) ) {
			$missing[ $key ] = $value;
		}
	}
	return $missing;
}

/**
 * Create a post for a feed episode.
 *
 * @param array $episode Episode data.
 * @return int|WP_Error Post ID or error.
 */
function bv_podcast_create_post( $episode ) {
	$timestamp = $episode['timestamp'] ? $episode['timestamp'] : time();
	$date_gmt  = gmdate( 'Y-m-d H:i:s', $timestamp );

	$post_id = wp_insert_post(
		wp_slash(
			array(
				'post_type'     => BV_PODCAST_POST_TYPE,
				'post_status'   => 'publish',
				'post_title'    => $episode['title'],
				'post_content'  => '',
				'post_date'     => get_date_from_gmt( $date_gmt ),
				'post_date_gmt' => $date_gmt,
				'meta_input'    => array(
					'podcast_guid'      => $episode['guid'],
					'episode_url'       => $episode['link'],
					'episode_audio_url' => $episode['audio_url'],
				),
			)
		),
		true
	);
	if ( is_wp_error( $post_id ) ) {
		return $post_id;
	}

	// ACF date_picker stores Ymd; use the site timezone for the calendar date.
	bv_podcast_set_field( $post_id, 'episode_date', wp_date( 'Ymd', $timestamp ) );
	$number = bv_podcast_parse_episode_number( $episode['title'] );
	bv_podcast_set_field( $post_id, 'episode_number', null === $number ? '' : $number );
	if ( '' !== $episode['description'] ) {
		bv_podcast_set_field( $post_id, 'description', $episode['description'] );
	}

	return $post_id;
}

/**
 * Reduce a normalized title to letters/digits separated by single spaces.
 *
 * @param string $norm Normalized title.
 * @return string
 */
function bv_podcast_loose_title( $norm ) {
	return trim( (string) preg_replace( '/[^\p{L}\p{N}]+/u', ' ', $norm ) );
}

/**
 * Conservative fuzzy fallback for items whose title was edited after the
 * original (Spotify) import, e.g. a "SITREP:" prefix added or a typo fixed.
 *
 * A candidate must be an existing title no feed item matched exactly, have an
 * episode_date within 2 days of the item's pubDate, and either contain / be
 * contained in the item title (ignoring punctuation, min 20 chars) or be at
 * least 85% similar. Exactly one candidate must qualify, otherwise no match.
 *
 * @param array               $episode Episode data.
 * @param array<string,int[]> $orphans Normalized title => post IDs.
 * @return string|null Matching normalized title key, or null.
 */
function bv_podcast_fuzzy_match( $episode, $orphans ) {
	if ( empty( $episode['timestamp'] ) ) {
		return null;
	}
	$item_day = DateTime::createFromFormat( '!Ymd', wp_date( 'Ymd', $episode['timestamp'] ), new DateTimeZone( 'UTC' ) )->getTimestamp();
	$item     = bv_podcast_loose_title( bv_podcast_normalize_title( $episode['title'] ) );
	$found    = array();

	foreach ( $orphans as $norm => $post_ids ) {
		$date_ok = false;
		foreach ( $post_ids as $post_id ) {
			// Stored as Y-m-d (legacy Spotify import) or Ymd (ACF native).
			$stored = preg_replace( '/\D/', '', (string) get_post_meta( $post_id, 'episode_date', true ) );
			$day    = 8 === strlen( $stored ) ? DateTime::createFromFormat( '!Ymd', $stored, new DateTimeZone( 'UTC' ) ) : false;
			if ( $day && abs( $day->getTimestamp() - $item_day ) <= 2 * DAY_IN_SECONDS ) {
				$date_ok = true;
				break;
			}
		}
		if ( ! $date_ok ) {
			continue;
		}

		$candidate = bv_podcast_loose_title( $norm );
		$shorter   = strlen( $candidate ) < strlen( $item ) ? $candidate : $item;
		$longer    = $shorter === $candidate ? $item : $candidate;
		similar_text( $item, $candidate, $percent );
		if ( ( strlen( $shorter ) >= 20 && false !== strpos( $longer, $shorter ) ) || $percent >= 85 ) {
			$found[] = $norm;
		}
	}

	return 1 === count( $found ) ? $found[0] : null;
}

/**
 * Import episodes from the podcast RSS feed.
 *
 * @param bool $dry_run When true, nothing is written; the summary reports what would happen.
 * @return array {
 *     @type bool     $dry_run
 *     @type string[] $would_create   Titles that would be / were created.
 *     @type string[] $would_backfill Titles whose existing post(s) would get / got guid/URL meta backfilled.
 *     @type string[] $fuzzy_matched  "Feed title => WP title" pairs matched by the fuzzy fallback.
 *     @type int      $skipped        Items already fully in sync.
 *     @type string[] $errors         Error messages.
 * }
 */
function bv_import_podcast_episodes( $dry_run = false ) {
	$dry_run = (bool) $dry_run;
	$summary = array(
		'dry_run'        => $dry_run,
		'would_create'   => array(),
		'would_backfill' => array(),
		'fuzzy_matched'  => array(),
		'skipped'        => 0,
		'errors'         => array(),
	);

	if ( ! $dry_run && ! bv_podcast_acquire_lock() ) {
		$summary['errors'][] = 'Import already running (lock held); skipped.';
		error_log( 'BV podcast import: another import is running; skipped.' );
		return $summary;
	}

	try {
		$episodes = bv_podcast_fetch_episodes();
		if ( is_wp_error( $episodes ) ) {
			$summary['errors'][] = 'Feed error: ' . $episodes->get_error_message();
			error_log( 'BV podcast import: feed error: ' . $episodes->get_error_message() );
			return $summary;
		}
		if ( empty( $episodes ) ) {
			$summary['errors'][] = 'Feed returned no items.';
			error_log( 'BV podcast import: feed returned no items.' );
			return $summary;
		}

		$index = bv_podcast_existing_index();

		// Pass 1: resolve each item to existing post(s) by guid, then exact normalized title.
		$resolved = array();
		$claimed  = array();
		foreach ( $episodes as $i => $episode ) {
			$norm = bv_podcast_normalize_title( $episode['title'] );
			if ( isset( $index['guid'][ $episode['guid'] ] ) ) {
				$resolved[ $i ] = array( $index['guid'][ $episode['guid'] ] );
				$claimed[ bv_podcast_normalize_title( $index['raw'][ $index['guid'][ $episode['guid'] ] ] ) ] = true;
			} elseif ( ! empty( $index['title'][ $norm ] ) ) {
				// Title match: backfill every surviving copy (duplicates may still exist).
				$resolved[ $i ] = $index['title'][ $norm ];
				$claimed[ $norm ] = true;
			}
		}

		// Pass 2: for still-unmatched items, try a conservative fuzzy match against
		// existing titles that no feed item claimed (titles edited after import).
		$orphans = array_diff_key( $index['title'], $claimed );
		foreach ( $episodes as $i => $episode ) {
			if ( isset( $resolved[ $i ] ) || ! $orphans ) {
				continue;
			}
			$match = bv_podcast_fuzzy_match( $episode, $orphans );
			if ( null !== $match ) {
				$resolved[ $i ]             = $orphans[ $match ];
				$summary['fuzzy_matched'][] = sprintf( '%s => %s', $episode['title'], $index['raw'][ $orphans[ $match ][0] ] );
				unset( $orphans[ $match ] );
			}
		}

		// Pass 3: backfill matched posts, create the rest.
		foreach ( $episodes as $i => $episode ) {
			if ( isset( $resolved[ $i ] ) ) {
				$changed = false;
				foreach ( $resolved[ $i ] as $post_id ) {
					$missing = bv_podcast_missing_meta( $post_id, $episode );
					if ( ! $missing ) {
						continue;
					}
					$changed = true;
					if ( ! $dry_run ) {
						foreach ( $missing as $key => $value ) {
							update_post_meta( $post_id, $key, $value );
						}
					}
				}
				if ( $changed ) {
					$summary['would_backfill'][] = $episode['title'];
				} else {
					++$summary['skipped'];
				}
				continue;
			}

			// Guard against duplicate titles/guids later in the same feed.
			$norm = bv_podcast_normalize_title( $episode['title'] );
			if ( isset( $index['guid'][ $episode['guid'] ] ) || ! empty( $index['title'][ $norm ] ) ) {
				++$summary['skipped'];
				continue;
			}

			$summary['would_create'][] = $episode['title'];
			if ( $dry_run ) {
				$index['guid'][ $episode['guid'] ] = 0;
				$index['title'][ $norm ][]         = 0;
				continue;
			}

			$post_id = bv_podcast_create_post( $episode );
			if ( is_wp_error( $post_id ) ) {
				array_pop( $summary['would_create'] );
				$message             = sprintf( 'Failed to create "%s": %s', $episode['title'], $post_id->get_error_message() );
				$summary['errors'][] = $message;
				error_log( 'BV podcast import: ' . $message );
				continue;
			}

			$index['guid'][ $episode['guid'] ] = $post_id;
			$index['title'][ $norm ][]         = $post_id;
			error_log( sprintf( 'BV podcast import: created post %d for "%s".', $post_id, $episode['title'] ) );
		}
	} finally {
		if ( ! $dry_run ) {
			bv_podcast_release_lock();
		}
	}

	return $summary;
}

/**
 * Cron callback (wrapper so cron args can never trigger a dry run by accident).
 */
function bv_podcast_cron_import() {
	bv_import_podcast_episodes( false );
}
add_action( BV_PODCAST_CRON_HOOK, 'bv_podcast_cron_import' );

/**
 * Clear the legacy Spotify cron event once and make sure the daily import is
 * scheduled. Cheap: reads the autoloaded cron/option values only.
 */
function bv_podcast_maybe_schedule() {
	if ( ! get_option( BV_PODCAST_MIGRATED_OPTION ) ) {
		wp_clear_scheduled_hook( BV_PODCAST_LEGACY_CRON_HOOK );
		update_option( BV_PODCAST_MIGRATED_OPTION, time(), false );
	}
	if ( ! wp_next_scheduled( BV_PODCAST_CRON_HOOK ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', BV_PODCAST_CRON_HOOK );
	}
}
add_action( 'init', 'bv_podcast_maybe_schedule' );

/**
 * Unschedule the import when switching away from this theme.
 */
function bv_podcast_unschedule() {
	wp_clear_scheduled_hook( BV_PODCAST_CRON_HOOK );
}
add_action( 'switch_theme', 'bv_podcast_unschedule' );

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	/**
	 * Import podcast episodes from the RSS feed.
	 *
	 * ## OPTIONS
	 *
	 * [--dry-run]
	 * : Report what would be created/backfilled without writing anything.
	 *
	 * [--verbose]
	 * : List affected episode titles.
	 *
	 * ## EXAMPLES
	 *
	 *     wp bv podcast-import --dry-run
	 *     wp bv podcast-import
	 *
	 * @param array $args       Positional args (unused).
	 * @param array $assoc_args Flags.
	 */
	function bv_podcast_cli_import( $args, $assoc_args ) {
		$dry_run = (bool) \WP_CLI\Utils\get_flag_value( $assoc_args, 'dry-run', false );
		$verbose = (bool) \WP_CLI\Utils\get_flag_value( $assoc_args, 'verbose', false );
		$summary = bv_import_podcast_episodes( $dry_run );
		$verb    = $dry_run ? 'Would ' : '';

		WP_CLI::log( sprintf( 'Feed: %s', bv_podcast_feed_url() ) );
		WP_CLI::log( sprintf( '%screate: %d', $verb, count( $summary['would_create'] ) ) );
		if ( $verbose ) {
			foreach ( $summary['would_create'] as $title ) {
				WP_CLI::log( '  + ' . $title );
			}
		}
		WP_CLI::log( sprintf( '%sbackfill: %d', $verb, count( $summary['would_backfill'] ) ) );
		if ( $verbose ) {
			foreach ( $summary['would_backfill'] as $title ) {
				WP_CLI::log( '  ~ ' . $title );
			}
		}
		WP_CLI::log( sprintf( 'Skipped (in sync): %d', $summary['skipped'] ) );
		WP_CLI::log( sprintf( 'Fuzzy title matches: %d', count( $summary['fuzzy_matched'] ) ) );
		foreach ( $summary['fuzzy_matched'] as $pair ) {
			WP_CLI::log( '  ≈ ' . $pair );
		}
		foreach ( $summary['errors'] as $error ) {
			WP_CLI::warning( $error );
		}
		if ( $summary['errors'] ) {
			WP_CLI::halt( 1 );
		}
		WP_CLI::success( $dry_run ? 'Dry run complete; nothing was written.' : 'Import complete.' );
	}
	WP_CLI::add_command( 'bv podcast-import', 'bv_podcast_cli_import' );
}
