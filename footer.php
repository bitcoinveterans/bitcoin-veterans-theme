<?php
// ACF Theme Settings Variables
$site_logos = get_field('site_logos', 'option');
$footer_logo = $site_logos['footer_logo'];
$site_icon = $site_logos['site_icon'];

$mission_statement = get_field('mission_statement', 'option');
$mission_headline = $mission_statement['headline'];
$mission_content = $mission_statement['content'];

$social_media_accounts = get_field('social_media_accounts', 'option');

$footer_logos = get_field('footer_logos', 'option');
$footer_logo_url = wp_get_attachment_image_url($footer_logo, 'full');
$footer_logo_alt = get_post_meta($footer_logo, '_wp_attachment_image_alt', true);
?>


<footer class="site-footer">
    <div class="container container-1360">
        <div class="footer-content">
            <div class="footer-content__column footer-content__column--logo">
                <?php if ($footer_logo_url): ?>
                <div class="footer-content__logo grit">
                    <a href="<?php echo esc_url(home_url()); ?>" class="footer-content__logo-link" aria-label="Bitcoin Veterans home">
                       <img src="<?php echo esc_url($footer_logo_url); ?>" alt="<?php echo esc_attr($footer_logo_alt); ?>" class="footer-content__logo-image">
                    </a>
                </div>
                <?php endif; ?>
            </div>
            <div class="footer-content__column footer-content__column--main">
                <div class="footer-content__connect">
                    <?php if ($mission_headline): ?>
                        <h3 class="footer-content__heading grit"><?php echo esc_html($mission_headline); ?></h3>
                    <?php endif; ?>
                    <?php if (!empty($social_media_accounts) && is_array($social_media_accounts)) : ?>
                    <ul class="footer-content__social-icons">
                        <?php foreach ($social_media_accounts as $account) : ?>
                            <?php if (!empty($account['social_url']) && !empty($account['icon'])) : ?>
                                <li>
                                    <a href="<?php echo esc_url($account['social_url']); ?>" 
                                       target="_blank" 
                                       rel="noopener noreferrer" 
                                       aria-label="<?php echo esc_attr($account['aria_label']); ?>" 
                                       class="footer-content__social-link">
                                        <?php echo wp_kses_post($account['icon']); ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>
                <?php if ($mission_content): ?>
                    <div class="footer-content__description entry-content"><?php echo wp_kses_post($mission_content); ?></div>
                <?php endif; ?>
                <p class="footer-content__copyright">&copy; <?php echo date("Y"); ?>  Bitcoin Veterans. All rights reserved.</p>
                <?php if (!empty($footer_logos) && is_array($footer_logos)) : ?>
                <ul class="footer-content__partner-logos">
                    <?php foreach ($footer_logos as $logo) : ?>
                        <?php
                        $logo_url = wp_get_attachment_image_url($logo['logo_image'], 'full');
                        $logo_alt = get_post_meta($logo['logo_image'], '_wp_attachment_image_alt', true);
                        ?>
                        <li class="footer-content__partner-logo-item logo-plate">
                            <?php if (!empty($logo['url'])) : ?>
                                <?php
                                $footer_logo_link_label = $logo_alt ? $logo_alt : wp_parse_url( $logo['url'], PHP_URL_HOST );
                                ?>
                                <a href="<?php echo esc_url($logo['url']); ?>" target="_blank" rel="noopener noreferrer" class="footer-content__partner-logo-link" aria-label="<?php echo esc_attr($footer_logo_link_label); ?>">
                            <?php endif; ?>
                                <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($logo_alt); ?>" class="footer-content__partner-logo-image">
                            <?php if (!empty($logo['url'])) : ?>
                                </a>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</footer>

<?php if (is_front_page()) : ?>
<div class="basement">
    <a href="https://coplancreativeconsulting.com/" target="_blank" referrerpolicy="origin" rel="nofollow">Custom WordPress Themes by Coplan Consulting</a>
</div>
<?php endif; ?>
<?php wp_footer(); ?>

</body>
</html>
