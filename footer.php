        <footer class="site-footer">
            <div class="module">
                <div class="logo">
                    <a href="<?php echo esc_url( home_url() ); ?>">
                        <img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/img/logo-footer.png" alt="<?php bloginfo('name'); ?> footer logo">
                    </a>
                </div>
                <div class="copy">&copy;<?php echo date("Y"); ?> Copyright Site Name.</div>
            </div><!--module-->
        </footer>
    <?php wp_footer(); ?>

    </body>
    </html>
