<?php
/**
 * Summit sponsor deck: data, assets and server-rendered fallback.
 *
 * Used by template-sponsor-deck.php. Everything here is inert unless the
 * current page uses that template.
 *
 * @package cc_starter
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const CC_STARTER_DECK_TEMPLATE = 'template-sponsor-deck.php';

/**
 * Resolve a path from defaults.json to a URL.
 * Absolute URLs stay as they are, "/path" uses home_url(), "img/x.webp" resolves against assets/summit-deck/.
 */
function cc_starter_deck_resolve_url( $value ) {
	$value = (string) $value;
	if ( '' === $value ) {
		return '';
	}
	if ( preg_match( '#^(https?:)?//#i', $value ) ) {
		return $value;
	}
	if ( '/' === $value[0] ) {
		return home_url( $value );
	}
	return get_template_directory_uri() . '/assets/summit-deck/' . ltrim( $value, '/' );
}

/**
 * Default deck data (inc/summit-deck/defaults.json), decoded once.
 */
function cc_starter_deck_defaults() {
	static $defaults = null;
	if ( null !== $defaults ) {
		return $defaults;
	}
	$raw      = file_get_contents( __DIR__ . '/defaults.json' );
	$defaults = $raw ? json_decode( $raw, true ) : null;
	if ( ! is_array( $defaults ) ) {
		$defaults = array();
	}
	$defaults['summitPage'] = cc_starter_deck_resolve_url( $defaults['summitPage'] ?? '' );
	$defaults['shareImage'] = cc_starter_deck_resolve_url( $defaults['shareImage'] ?? '' );
	if ( isset( $defaults['contact']['photo'] ) ) {
		$defaults['contact']['photo'] = cc_starter_deck_resolve_url( $defaults['contact']['photo'] );
	}
	return $defaults;
}

/**
 * Split a textarea value into trimmed, non-empty lines.
 */
function cc_starter_deck_lines( $value ) {
	$lines = preg_split( '/\R/', (string) $value );
	$lines = array_map( 'trim', $lines ? $lines : array() );
	return array_values( array_filter( $lines, 'strlen' ) );
}

/**
 * Plain text: no HTML, trimmed.
 */
function cc_starter_deck_text( $value ) {
	return trim( wp_strip_all_tags( (string) $value ) );
}

/**
 * Attachment ID from an ACF image value (ID, array or empty).
 */
function cc_starter_deck_attachment_id( $value ) {
	if ( is_array( $value ) ) {
		return (int) ( $value['ID'] ?? 0 );
	}
	return (int) $value;
}

/**
 * Number or null.
 */
function cc_starter_deck_number( $value ) {
	return ( is_numeric( $value ) && '' !== $value ) ? $value + 0 : null;
}

/**
 * Build the deck data for a page.
 *
 * Uses the ACF fields when the "tiers" repeater has at least one row, otherwise the defaults.
 *
 * @param int $post_id Page ID.
 * @return array
 */
function cc_starter_deck_data( $post_id ) {
	static $cache = array();
	$post_id = (int) $post_id;
	if ( isset( $cache[ $post_id ] ) ) {
		return $cache[ $post_id ];
	}

	$d    = cc_starter_deck_defaults();
	$rows = ( $post_id && function_exists( 'get_field' ) ) ? get_field( 'tiers', $post_id ) : null;
	$acf  = is_array( $rows ) && count( $rows ) >= 1;

	$share_size = array( 1400, 795 );

	if ( ! $acf ) {
		$data = $d;
	} else {
		$f = function ( $name ) use ( $post_id ) {
			$v = get_field( $name, $post_id );
			return is_string( $v ) ? trim( $v ) : $v;
		};
		// Scalar with fallback to the default when empty.
		$s = function ( $name, $default ) use ( $f ) {
			$v = $f( $name );
			return ( null === $v || false === $v || '' === $v ) ? $default : $v;
		};

		$compare_allowed = array( 'led', 'shirt', 'ban', 'evb', 'land', 'spon', 'swag', 'bag', 'mail', 'soc', 'spaces', 'telehash', 'thanks' );
		$tiers           = array();
		foreach ( $rows as $r ) {
			$status    = in_array( $r['status'] ?? '', array( 'open', 'low', 'sold' ), true ) ? $r['status'] : 'open';
			$compare   = isset( $r['compare'] ) ? (array) $r['compare'] : array();
			$price_max = cc_starter_deck_number( $r['price_max'] ?? '' );
			$tiers[]   = array(
				'name'            => cc_starter_deck_text( $r['name'] ?? '' ),
				'price'           => cc_starter_deck_number( $r['price'] ?? '' ) ?? 0,
				'priceMax'        => $price_max ? $price_max : null,
				'status'          => $status,
				'remaining'       => 'low' === $status ? cc_starter_deck_number( $r['remaining'] ?? '' ) : null,
				'slotsTotal'      => 'low' === $status ? cc_starter_deck_number( $r['slots_total'] ?? '' ) : null,
				'tagline'         => cc_starter_deck_text( $r['tagline'] ?? '' ),
				'intro'           => cc_starter_deck_text( $r['intro'] ?? '' ),
				'benefits'        => cc_starter_deck_lines( $r['benefits'] ?? '' ),
				'ticketsIncluded' => cc_starter_deck_number( $r['tickets_included'] ?? '' ),
				'compare'         => array_values( array_intersect( $compare_allowed, array_map( 'strval', $compare ) ) ),
				'badge'           => 'ob' === ( $r['badge'] ?? '' ) ? 'ob' : 'bv',
				'feature'         => in_array( $r['feature'] ?? '', array( 'telehash', 'veteran' ), true ) ? $r['feature'] : '',
			);
		}

		$tickets = array();
		$t_rows  = $f( 'tickets' );
		foreach ( ( is_array( $t_rows ) ? $t_rows : array() ) as $r ) {
			$url = esc_url_raw( (string) ( $r['url'] ?? '' ) );
			if ( '' === $url ) {
				continue;
			}
			$tickets[] = array(
				'day'   => cc_starter_deck_text( $r['day'] ?? '' ),
				'title' => cc_starter_deck_text( $r['title'] ?? '' ),
				'price' => cc_starter_deck_text( $r['price'] ?? '' ),
				'url'   => $url,
				'text'  => cc_starter_deck_text( $r['text'] ?? '' ),
				'best'  => ! empty( $r['best'] ),
			);
		}

		$stats  = array();
		$s_rows = $f( 'mission_stats' );
		foreach ( ( is_array( $s_rows ) ? $s_rows : array() ) as $r ) {
			$stats[] = array(
				'value'  => cc_starter_deck_number( $r['value'] ?? '' ) ?? 0,
				'suffix' => cc_starter_deck_text( $r['suffix'] ?? '' ),
				'label'  => cc_starter_deck_text( $r['label'] ?? '' ),
			);
		}
		$paragraphs = preg_split( '/\R\s*\R/', trim( (string) $f( 'mission_paragraphs' ) ) );
		$paragraphs = array_values( array_filter( array_map( 'cc_starter_deck_text', $paragraphs ? $paragraphs : array() ), 'strlen' ) );
		$audience   = array_map( 'cc_starter_deck_text', cc_starter_deck_lines( $f( 'audience' ) ) );

		$photo_id = cc_starter_deck_attachment_id( $f( 'contact_photo' ) );
		$photo    = $photo_id ? wp_get_attachment_image_url( $photo_id, 'medium' ) : '';
		$share_id = cc_starter_deck_attachment_id( $f( 'share_image' ) );
		$share    = $share_id ? wp_get_attachment_image_src( $share_id, 'full' ) : false;
		if ( $share ) {
			$share_size = array( (int) $share[1], (int) $share[2] );
		}

		$data = array(
			'form'            => $s( 'sponsor_form_url', $d['form'] ),
			'summitPage'      => $s( 'summit_page_url', $d['summitPage'] ),
			'event'           => array(
				'dates' => cc_starter_deck_text( $s( 'event_dates', $d['event']['dates'] ) ),
				'venue' => cc_starter_deck_text( $s( 'event_venue', $d['event']['venue'] ) ),
				'city'  => cc_starter_deck_text( $s( 'event_city', $d['event']['city'] ) ),
			),
			'cover'           => array( 'lede' => cc_starter_deck_text( $s( 'cover_lede', $d['cover']['lede'] ) ) ),
			'mission'         => array(
				'paragraphs' => $paragraphs ? $paragraphs : $d['mission']['paragraphs'],
				'stats'      => $stats ? $stats : $d['mission']['stats'],
				'audience'   => $audience ? $audience : $d['mission']['audience'],
			),
			'tiers'           => $tiers,
			'tickets'         => $tickets ? $tickets : $d['tickets'],
			'ticketsNote'     => cc_starter_deck_text( $s( 'tickets_note', $d['ticketsNote'] ) ),
			'contact'         => array(
				'heading'      => cc_starter_deck_text( $s( 'contact_heading', $d['contact']['heading'] ) ),
				'name'         => cc_starter_deck_text( $s( 'contact_name', $d['contact']['name'] ) ),
				'org'          => cc_starter_deck_text( $s( 'contact_org', $d['contact']['org'] ) ),
				'email'        => sanitize_email( $s( 'contact_email', $d['contact']['email'] ) ),
				'emailSubject' => cc_starter_deck_text( $s( 'contact_email_subject', $d['contact']['emailSubject'] ) ),
				// No fallback: an empty Signal handle hides the Signal button.
				'signal'       => cc_starter_deck_text( $f( 'contact_signal' ) ),
				'photo'        => $photo ? $photo : $d['contact']['photo'],
				'teamHeading'  => cc_starter_deck_text( $s( 'team_heading', $d['contact']['teamHeading'] ) ),
				'teamEmail'    => sanitize_email( $s( 'team_email', $d['contact']['teamEmail'] ) ),
				'teamText'     => cc_starter_deck_text( $s( 'team_text', $d['contact']['teamText'] ) ),
			),
			'footer'          => cc_starter_deck_text( $s( 'footer_line', $d['footer'] ) ),
			'metaDescription' => cc_starter_deck_text( $s( 'meta_description', $d['metaDescription'] ) ),
			'shareImage'      => $share ? $share[0] : $d['shareImage'],
		);
	}

	// Normalise (both sources): ids, URLs, plain text.
	foreach ( $data['tiers'] as $i => $tier ) {
		$data['tiers'][ $i ]['id'] = sanitize_title( $tier['name'] );
	}
	$data['form']       = esc_url_raw( $data['form'] ) ? esc_url_raw( $data['form'] ) : $d['form'];
	$data['summitPage'] = esc_url_raw( $data['summitPage'] ) ? esc_url_raw( $data['summitPage'] ) : $d['summitPage'];
	foreach ( $data['tickets'] as $i => $ticket ) {
		$data['tickets'][ $i ]['url'] = esc_url_raw( $ticket['url'] );
	}
	$data['shareImage']     = esc_url_raw( $data['shareImage'] );
	$data['shareImageSize'] = $share_size;
	$data['contact']['photo'] = esc_url_raw( $data['contact']['photo'] );

	$base        = get_template_directory_uri() . '/assets/summit-deck/img/';
	$data['img'] = array(
		'logo'   => $base . 'bv-logo.webp',
		'banner' => $base . 'summit-banner.webp',
		'rick'   => $base . 'contact-rick.webp',
		'ob'     => $base . 'operation-bitcoin.webp',
		'bg'     => array(),
	);
	for ( $i = 0; $i < 14; $i++ ) {
		$data['img']['bg'][] = $base . sprintf( 'bg-%02d.webp', $i );
	}
	$data['home']          = home_url( '/' );
	$data['usingDefaults'] = ! $acf;

	$cache[ $post_id ] = $data;
	return $data;
}

/**
 * Is the current request using the deck template?
 */
function cc_starter_deck_is_active() {
	return is_page_template( CC_STARTER_DECK_TEMPLATE );
}

/**
 * Swap the theme assets for the deck assets (deck template only).
 */
function cc_starter_deck_enqueue() {
	if ( ! cc_starter_deck_is_active() ) {
		return;
	}

	foreach ( array( 'cc_starter-style', 'cc_starter-googlefonts', 'cc_starter-master' ) as $handle ) {
		wp_dequeue_style( $handle );
		wp_deregister_style( $handle );
	}
	foreach ( array( 'cc_starter-custom', 'custom-js', 'cc_starter-fontsmoothie', 'cc_starter-skiplink', 'font-awesome-kit' ) as $handle ) {
		wp_dequeue_script( $handle );
	}

	$dir = get_template_directory();
	$uri = get_template_directory_uri();

	wp_enqueue_style( 'cc_starter-deck', $uri . '/assets/summit-deck/deck.css', array(), (string) filemtime( $dir . '/assets/summit-deck/deck.css' ) );

	wp_enqueue_script( 'cc_starter-deck-react', $uri . '/assets/vendor/react-18.3.1.production.min.js', array(), '18.3.1', true );
	wp_enqueue_script( 'cc_starter-deck-react-dom', $uri . '/assets/vendor/react-dom-18.3.1.production.min.js', array( 'cc_starter-deck-react' ), '18.3.1', true );
	wp_enqueue_script( 'cc_starter-deck-framer', $uri . '/assets/vendor/framer-motion-11.11.17.js', array( 'cc_starter-deck-react', 'cc_starter-deck-react-dom' ), '11.11.17', true );
	wp_enqueue_script( 'cc_starter-deck-htm', $uri . '/assets/vendor/htm-3.1.1.umd.js', array(), '3.1.1', true );
	wp_enqueue_script(
		'cc_starter-deck',
		$uri . '/assets/summit-deck/deck.js',
		array( 'cc_starter-deck-react', 'cc_starter-deck-react-dom', 'cc_starter-deck-framer', 'cc_starter-deck-htm' ),
		(string) filemtime( $dir . '/assets/summit-deck/deck.js' ),
		true
	);

	$data = cc_starter_deck_data( get_queried_object_id() );
	wp_add_inline_script(
		'cc_starter-deck',
		'window.BV_DECK=' . wp_json_encode( $data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . ';',
		'before'
	);
}
add_action( 'wp_enqueue_scripts', 'cc_starter_deck_enqueue', 1000 );

/**
 * Hummingbird: leave the deck assets alone (deck template only).
 */
add_action(
	'template_redirect',
	function () {
		if ( ! cc_starter_deck_is_active() ) {
			return;
		}
		add_filter( 'wphb_should_minify_on_page', '__return_false' );
		add_filter( 'wphb_should_add_critical_css', '__return_false' );
	}
);

function cc_starter_deck_hummingbird_skip( $value, $handle = '' ) {
	return ( is_string( $handle ) && 0 === strpos( $handle, 'cc_starter-deck' ) ) ? false : $value;
}
add_filter( 'wphb_minify_resource', 'cc_starter_deck_hummingbird_skip', 10, 2 );
add_filter( 'wphb_combine_resource', 'cc_starter_deck_hummingbird_skip', 10, 2 );

/**
 * No admin bar on the full-screen deck.
 */
add_filter(
	'show_admin_bar',
	function ( $show ) {
		return cc_starter_deck_is_active() ? false : $show;
	}
);

/**
 * Description, Open Graph, Twitter card and font preloads (deck template only).
 */
function cc_starter_deck_head() {
	if ( ! cc_starter_deck_is_active() ) {
		return;
	}
	$data  = cc_starter_deck_data( get_queried_object_id() );
	$title = get_the_title();
	$image = $data['shareImage'];
	$size  = $data['shareImageSize'];
	$fonts = get_template_directory_uri() . '/assets/fonts/';

	echo '<meta name="description" content="' . esc_attr( $data['metaDescription'] ) . '">' . "\n";
	echo '<meta property="og:type" content="website">' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
	echo '<meta property="og:description" content="' . esc_attr( $data['metaDescription'] ) . '">' . "\n";
	echo '<meta property="og:url" content="' . esc_url( get_permalink() ) . '">' . "\n";
	if ( $image ) {
		echo '<meta property="og:image" content="' . esc_url( $image ) . '">' . "\n";
		if ( ! empty( $size[0] ) && ! empty( $size[1] ) ) {
			echo '<meta property="og:image:width" content="' . esc_attr( (string) (int) $size[0] ) . '">' . "\n";
			echo '<meta property="og:image:height" content="' . esc_attr( (string) (int) $size[1] ) . '">' . "\n";
		}
	}
	echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
	foreach ( array( 'black-ops-one-400', 'barlow-400', 'barlow-condensed-600' ) as $font ) {
		echo '<link rel="preload" as="font" type="font/woff2" href="' . esc_url( $fonts . $font . '.woff2' ) . '" crossorigin>' . "\n";
	}
}
add_action( 'wp_head', 'cc_starter_deck_head', 5 );

/**
 * Escape text, then turn **x** into <strong>x</strong>.
 */
function cc_starter_deck_rich( $text ) {
	return preg_replace( '/\*\*(.+?)\*\*/s', '<strong>$1</strong>', esc_html( $text ) );
}

/**
 * "$7,000" or "$200 – $40,000".
 */
function cc_starter_deck_price( $tier ) {
	$price = '$' . number_format( (float) $tier['price'] );
	if ( ! empty( $tier['priceMax'] ) ) {
		$price .= ' – $' . number_format( (float) $tier['priceMax'] );
	}
	return $price;
}

/**
 * Static markup for SEO, no-JS visitors and the first paint. React replaces it.
 *
 * @param array $data Result of cc_starter_deck_data().
 */
function cc_starter_deck_render_static( $data ) {
	$tiers = $data['tiers'];
	$n     = count( $tiers );
	$words = array( 1 => 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve' );
	$count = $words[ $n ] ?? (string) $n;
	$ev    = $data['event'];
	$c     = $data['contact'];

	if ( ! empty( $data['usingDefaults'] ) && current_user_can( 'edit_post', get_queried_object_id() ) ) {
		echo "<!-- deck: using built-in defaults; run seed (see spec §6) -->\n";
	}
	?>
<div class="deck-static">
	<header class="ds-top">
		<a href="<?php echo esc_url( $data['home'] ); ?>">Bitcoin Veterans</a> &middot;
		<a href="<?php echo esc_url( $data['summitPage'] ); ?>">&larr; Summit 2026</a> &middot;
		<a class="btn solid" href="<?php echo esc_url( $data['form'] ); ?>" target="_blank" rel="noopener">Sponsor form</a>
	</header>
	<section id="s0">
		<h1>Sponsor Deck</h1>
		<p class="lede"><?php echo cc_starter_deck_rich( $data['cover']['lede'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in helper. ?></p>
		<p class="meta"><?php echo esc_html( implode( ' · ', array_filter( array( $ev['dates'], $ev['venue'], $ev['city'] ) ) ) ); ?></p>
	</section>
	<section id="s1">
		<h2>Why sponsor this summit</h2>
		<?php foreach ( $data['mission']['paragraphs'] as $p ) : ?>
			<p><?php echo cc_starter_deck_rich( $p ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in helper. ?></p>
		<?php endforeach; ?>
		<ul>
			<?php foreach ( $data['mission']['audience'] as $line ) : ?>
				<li><?php echo esc_html( $line ); ?></li>
			<?php endforeach; ?>
		</ul>
	</section>
	<section id="s2">
		<h2><?php echo esc_html( $count ); ?> ways to back the mission</h2>
		<?php foreach ( $tiers as $tier ) : ?>
			<article class="ds-tier">
				<h3><?php echo esc_html( $tier['name'] ); ?></h3>
				<p class="ds-price"><?php echo esc_html( cc_starter_deck_price( $tier ) ); ?></p>
				<p class="ds-status">
					<?php
					if ( 'sold' === $tier['status'] ) {
						echo 'Sold';
					} elseif ( 'low' === $tier['status'] ) {
						echo esc_html( $tier['remaining'] ? $tier['remaining'] . ' remain' : 'Few left' );
					} else {
						echo 'Open';
					}
					?>
				</p>
				<?php if ( '' !== $tier['tagline'] ) : ?>
					<p><?php echo esc_html( $tier['tagline'] ); ?></p>
				<?php endif; ?>
				<?php if ( '' !== $tier['intro'] ) : ?>
					<p><?php echo esc_html( $tier['intro'] ); ?></p>
				<?php endif; ?>
				<ul>
					<?php foreach ( $tier['benefits'] as $benefit ) : ?>
						<li><?php echo esc_html( $benefit ); ?></li>
					<?php endforeach; ?>
				</ul>
				<?php if ( 'sold' !== $tier['status'] ) : ?>
					<a href="<?php echo esc_url( $data['form'] ); ?>" target="_blank" rel="noopener">Sponsor at this level</a>
				<?php endif; ?>
			</article>
		<?php endforeach; ?>
	</section>
	<section id="s<?php echo (int) ( 3 + $n ); ?>">
		<h2>Tickets</h2>
		<?php foreach ( $data['tickets'] as $ticket ) : ?>
			<p>
				<a href="<?php echo esc_url( $ticket['url'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( implode( ' – ', array_filter( array( $ticket['day'], $ticket['title'], $ticket['price'] ), 'strlen' ) ) ); ?></a>
			</p>
		<?php endforeach; ?>
	</section>
	<section id="s<?php echo (int) ( 4 + $n ); ?>">
		<h2>Ready to sponsor?</h2>
		<p><?php echo esc_html( $c['name'] ); ?></p>
		<?php if ( $c['email'] ) : ?>
			<p><a href="<?php echo esc_url( 'mailto:' . $c['email'] ); ?>"><?php echo esc_html( $c['email'] ); ?></a></p>
		<?php endif; ?>
		<?php if ( $c['signal'] ) : ?>
			<p>Signal: <?php echo esc_html( $c['signal'] ); ?></p>
		<?php endif; ?>
		<?php if ( $c['teamEmail'] ) : ?>
			<p><?php echo esc_html( $c['teamHeading'] ); ?>: <a href="<?php echo esc_url( 'mailto:' . $c['teamEmail'] ); ?>"><?php echo esc_html( $c['teamEmail'] ); ?></a></p>
		<?php endif; ?>
	</section>
</div>
	<?php
}
