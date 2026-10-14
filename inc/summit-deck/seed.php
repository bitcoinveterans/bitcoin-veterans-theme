<?php
/**
 * Copy the built-in deck defaults into the ACF fields of a page.
 *
 * Usage: wp eval-file wp-content/themes/bitcoin-veterans/inc/summit-deck/seed.php <post_id> [overwrite]
 *
 * @package cc_starter
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit;
}

$post_id = isset( $args[0] ) ? (int) $args[0] : 0;
if ( ! $post_id || 'page' !== get_post_type( $post_id ) ) {
	WP_CLI::error( 'Usage: wp eval-file seed.php <page_id> [overwrite]. The first argument must be the ID of a page.' );
}

$existing = get_field( 'field_bv_deck_tiers', $post_id );
if ( is_array( $existing ) && count( $existing ) > 0 && ( $args[1] ?? '' ) !== 'overwrite' ) {
	WP_CLI::log( 'already seeded, nothing to do' );
	return;
}

$d = cc_starter_deck_defaults();

$tiers = array();
foreach ( $d['tiers'] as $t ) {
	$tiers[] = array(
		'name'             => $t['name'],
		'status'           => $t['status'],
		'remaining'        => $t['remaining'],
		'slots_total'      => $t['slotsTotal'],
		'price'            => $t['price'],
		'price_max'        => $t['priceMax'],
		'tagline'          => $t['tagline'],
		'intro'            => $t['intro'],
		'benefits'         => implode( "\n", $t['benefits'] ),
		'tickets_included' => $t['ticketsIncluded'],
		'compare'          => $t['compare'],
		'badge'            => $t['badge'],
		'feature'          => $t['feature'],
	);
}

$tickets = array();
foreach ( $d['tickets'] as $k ) {
	$tickets[] = array(
		'day'   => $k['day'],
		'title' => $k['title'],
		'price' => $k['price'],
		'url'   => $k['url'],
		'text'  => $k['text'],
		'best'  => empty( $k['best'] ) ? 0 : 1,
	);
}

$stats = array();
foreach ( $d['mission']['stats'] as $s ) {
	$stats[] = array(
		'value'  => $s['value'],
		'suffix' => $s['suffix'],
		'label'  => $s['label'],
	);
}

$values = array(
	'tiers'                 => $tiers,
	'sponsor_form_url'      => $d['form'],
	'summit_page_url'       => $d['summitPage'], // already absolute via home_url().
	'tickets'               => $tickets,
	'tickets_note'          => $d['ticketsNote'],
	'event_dates'           => $d['event']['dates'],
	'event_venue'           => $d['event']['venue'],
	'event_city'            => $d['event']['city'],
	'cover_lede'            => $d['cover']['lede'],
	'mission_paragraphs'    => implode( "\n\n", $d['mission']['paragraphs'] ),
	'mission_stats'         => $stats,
	'audience'              => implode( "\n", $d['mission']['audience'] ),
	'contact_heading'       => $d['contact']['heading'],
	'contact_name'          => $d['contact']['name'],
	'contact_org'           => $d['contact']['org'],
	'contact_email'         => $d['contact']['email'],
	'contact_email_subject' => $d['contact']['emailSubject'],
	'contact_signal'        => $d['contact']['signal'],
	'contact_hide_photo'    => 0,
	'team_heading'          => $d['contact']['teamHeading'],
	'team_email'            => $d['contact']['teamEmail'],
	'team_text'             => $d['contact']['teamText'],
	'footer_line'           => $d['footer'],
	'meta_description'      => $d['metaDescription'],
);
// contact_photo and share_image are left empty: the theme defaults apply.

foreach ( $values as $name => $value ) {
	update_field( 'field_bv_deck_' . $name, $value, $post_id );
}

WP_CLI::success( sprintf( 'Seeded page %d with %d tiers, %d tickets and %d stats (%d fields).', $post_id, count( $tiers ), count( $tickets ), count( $stats ), count( $values ) ) );
