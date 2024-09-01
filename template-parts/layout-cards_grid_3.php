<?php
/**
 * Cards Grid 3
 *
 * @package adaptdev/base
 * @since 1.0.0
 */

$cards_grid_3 = array(
    'cards_grid_3' => get_field('cards_grid_3'),
);

$args = array(
    'cards_grid_3' => $cards_grid_3,
);
// echo '<pre>cards_grid_3 ';
// 	print_r($cards_grid_3);
// echo '</pre>';
?>
<section class="cards-grid-3 section">
    <div class="container container-1360">
        <?php get_template_part( 'template-parts/partials/cards_grid_3', '', $args );  ?>
    </div>
</section>