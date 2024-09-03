<?php
/**
 * Cards Grid 2
 * @package adaptdev/base
 * @since 1.0.0
 */

$cards_grid_2 = array(
    'cards_grid_2' => get_field('cards_grid_2'),
);

$args = array(
    'cards_grid_2' => $cards_grid_2,
);
echo '<pre>cards_grid_2 ';
	print_r($cards_grid_2);
echo '</pre>';

?>
<section class="cards-grid-2 section">
    <div class="container container-1360">
        <?php get_template_part( 'template-parts/partials/cards_grid_2', '', $args );  ?>
    </div>
</section>