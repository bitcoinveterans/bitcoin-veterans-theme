<?php //Template Name: Home ?>

<?php get_header(); ?>
<main id="content" class="site-main home-content" role="main">

    <section class="hero section" aria-labelledby="hero-title">
        <div class="container container-1360">
            <div class="hero__content">
                <h1 class="hero__title">Your Next Mission:<br>Freedom Through Bitcoin</h1>
                <p class="hero__description">Join our mission to empower veterans and communities by leveraging the power of Bitcoin.</p>
                <div class="hero__actions">
                    <nav aria-label="Additional mission actions">
                        <ul class="hero__actions-list">
                            <li class="hero__action-item hero__action-item--primary">
                                <a href="#join-mission" class="btn btn--primary">
                                    <span class="btn__icon" aria-hidden="true">
                                        <i class="fa-solid fa-rocket"></i>
                                    </span>
                                    <span class="btn__text">Join the Mission</span>
                                </a>
                            </li>
                            <li class="hero__action-item hero__action-item--secondary">
                                <a href="#support-mission" class="btn btn--secondary">
                                    <span class="btn__icon" aria-hidden="true">
                                    <i class="fa-solid fa-clipboard-check"></i>
                                    </span>
                                    <span class="btn__text">Support the Mission</span>
                                </a>
                            </li>
                            <li class="hero__action-item hero__action-item--secondary">
                                <a href="#about-mission" class="btn btn--secondary">
                                    <span class="btn__icon" aria-hidden="true">
                                        <i class="fa-regular fa-comment"></i>
                                    </span>
                                    <span class="btn__text">About the Mission</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </section>

    <section class="founders section" aria-labelledby="founders-title">
        <div class="container container-1024">
            <div class="section-title">
                <i class="fa-solid fa-flag-usa" aria-hidden="true"></i>
                <h2 class="section-title__headline">Meet Our Founders</h2>
                <p class="section-title__description">Get to know the visionaries behind it all—meet our founders and their inspiring stories.</p>
            </div>
            <div class="founders-grid" role="list">
                <article class="founder-card" role="listitem">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/img/alex.jpg" alt="Portrait of Alex" class="founder-card__image">
                    <div class="founder-card__content">
                        <h3 class="founder-card__name">Alex</h3>
                    </div>
                </article>
                <article class="founder-card" role="listitem">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/img/gabe.jpg" alt="Portrait of John Doe" class="founder-card__image">
                    <div class="founder-card__content">
                        <h3 class="founder-card__name">Gabe</h3>
                    </div>
                </article>
                <article class="founder-card" role="listitem">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/img/jordan.jpg" alt="Portrait of John Doe" class="founder-card__image">
                    <div class="founder-card__content">
                        <h3 class="founder-card__name">Jordan</h3>
                    </div>
                </article>
                <article class="founder-card" role="listitem">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/img/mike.jpg" alt="Portrait of John Doe" class="founder-card__image">
                    <div class="founder-card__content">
                        <h3 class="founder-card__name">Mike</h3>
                    </div>
                </article>
                <article class="founder-card" role="listitem">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/img/shane.jpg" alt="Portrait of John Doe" class="founder-card__image">
                    <div class="founder-card__content">
                        <h3 class="founder-card__name">Shane</h3>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <section class="partners section" aria-labelledby="partners-title">
        <div class="container container-1360">
            <div class="section-title">
                <p class="section-title__description">Meet the partners driving our mission forward with their invaluable support and commitment.</p>
            </div>
            <ul class="partners-logo-list">
                <li>
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/img/logo-black-sheep.png" alt="Black Sheep Logo" class="partner-logo">
                </li>
                <li>
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/img/logo-slnt.png" alt="SLNT Logo" class="partner-logo">
                </li>
                <li>
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/img/logo-kc-cattle.png" alt="KC Cattle Logo" class="partner-logo">
                </li>
                <li>
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/img/logo-solo-satoshi.jpg" alt="Solo Satoshi Logo" class="partner-logo">
                </li>
            </ul>
            <div class="partners-cta">
                <a href="#" class="btn btn--secondary">
                    <i class="fa-regular fa-handshake"></i>
                    <span class="btn-text">Become A Partner</span>
                </a>
            </div>
        </div>
    </section>
    <section class="content-grid section" aria-labelledby="content-grid-title">
        <div class="container container-1360">
            <div class="section-title">
                <div class="section-title__icon">
                    <i class="fa-solid fa-compass" aria-hidden="true"></i>
                </div>
                <h2 class="section-title__headline">Take A Deeper Dive</h2>
                <p class="section-title__description">Get curious! Find out more about Events, Podcasts, MeetUps, and our BV Authors.</p>
            </div>
            <div class="content-grid__grid">
                <article class="content-grid-card">
                    
                        <h3 class="content-grid__card-title">
                            <a href="#" class="content-grid__card-link">
                                <i class="fa-solid fa-podcast" aria-hidden="true"></i>
                                Listen to Podcasts
                            </a>
                        </h3>
                        <p class="content-grid__card-description">Tune into our podcasts for insightful discussions and stories.</p>
                </article>
                <article class="content-grid-card">
                    
                        <h3 class="content-grid__card-title">
                            <a href="#" class="content-grid__card-link">
                                <i class="fa-solid fa-calendar-days"></i>
                                Upcoming Events
                            </a>
                        </h3>
                        <p class="content-grid__card-description">Check out the latest events and join us for exciting experiences!</p>
                </article>
                <article class="content-grid-card">
                    
                        <h3 class="content-grid__card-title">
                            <a href="#" class="content-grid__card-link">
                                <i class="fa-solid fa-user-pen"></i>
                                BV Authors
                            </a>
                        </h3>
                        <p class="content-grid__card-description">Discover and read works by our spotlighted authors.</p>
                </article>
                <article class="content-grid-card">
                    
                        <h3 class="content-grid__card-title">
                            <a href="#" class="content-grid__card-link">
                                <i class="fa-solid fa-users"></i>
                                Join MeetUps
                            </a>
                        </h3>
                        <p class="content-grid__card-description">Connect with like-minded people at our fun and engaging MeetUps.</p>
                </article>
            </div>
        </div>
    </section>
    <section class="recent-posts section" aria-labelledby="recent-posts-title">
        <div class="container container-1360">
            <div class="section-title">
                <div class="section-title__icon">
                    <i class="fa-solid fa-newspaper" aria-hidden="true"></i>
                </div>
                <h2 class="section-title__headline">Latest Insights</h2>
                <p class="section-title__description">Stay updated with our most recent articles and insights.</p>
            </div>
            <div class="recent-posts__grid">
                <article class="post-card">
                    <header class="post-card__header">
                        <div class="post-card__author">
                            <i class="fa-solid fa-user" aria-hidden="true"></i>
                            <a href="#" class="post-card__author-link">John Doe</a>
                        </div>
                    </header>
                    <div class="post-card__content">
                        <h3 class="post-card__title">
                            DIB 12 AUG 2024
                        </h3>
                        <div class="post-card__excerpt">
                            <p>Exploring how emerging technologies are reshaping the landscape of education and learning experiences.</p>
                        </div>
                    </div>
                    <footer class="post-card__footer">
                        <a href="#" class="post-card__read-more">Read More</a>
                    </footer>
                </article>
                <article class="post-card">
                    <header class="post-card__header">
                        <div class="post-card__author">
                            <i class="fa-solid fa-user" aria-hidden="true"></i>
                            <a href="#" class="post-card__author-link">John Doe</a>
                        </div>
                    </header>
                    <div class="post-card__content">
                        <h3 class="post-card__title">
                            DIB 12 AUG 2024
                        </h3>
                        <div class="post-card__excerpt">
                            <p>Exploring how emerging technologies are reshaping the landscape of education and learning experiences.</p>
                        </div>
                    </div>
                    <footer class="post-card__footer">
                        <a href="#" class="post-card__read-more">Read More</a>
                    </footer>
                </article>
                <article class="post-card">
                    <header class="post-card__header">
                        <div class="post-card__author">
                            <i class="fa-solid fa-user" aria-hidden="true"></i>
                            <a href="#" class="post-card__author-link">John Doe</a>
                        </div>
                    </header>
                    <div class="post-card__content">
                        <h3 class="post-card__title">
                            DIB 12 AUG 2024
                        </h3>
                        <div class="post-card__excerpt">
                            <p>Exploring how emerging technologies are reshaping the landscape of education and learning experiences.</p>
                        </div>
                    </div>
                    <footer class="post-card__footer">
                        <a href="#" class="post-card__read-more">Read More</a>
                    </footer>
                </article>
            </div>
        </div>
    </section>
    <section class="faqs section" aria-labelledby="faqs-title">
        <div class="container container-768">
            <div class="section-title">
                <i class="fa-solid fa-comments" aria-hidden="true"></i>
                <h2 class="section-title__headline">Frequently Asked Questions</h2>
                <p class="section-title__description">Curious about something? Explore our FAQs for quick answers and helpful insights.</p>
            </div>
            <div class="accordion" id="faqAccordion">
                <details class="accordion-item details accordion">
                    <summary class="accordion-header">
                        <h3 class="heading summary">Elit hac eu primis ultrices himenaeos cras senectus nec.</h3>
                    </summary>
                    <div class="details__content">
                        <div class="entry-content">
                            <p>Adipiscing ipsum ligula vestibulum nullam varius ullamcorper nam nec. Eu fringilla hendrerit morbi sapien at placerat.</p>
                        </div>
                    </div>
                </details>
                <details class="accordion-item details accordion">
                    <summary class="accordion-header">
                        <h3 class="heading summary">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</h3>
                    </summary>
                    <div class="details__content">
                        <div class="entry-content">
                            <p>Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.</p>
                        </div>
                    </div>
                </details>
                <details class="accordion-item details accordion">
                    <summary class="accordion-header">
                        <h3 class="heading summary">Duis aute irure dolor in reprehenderit in voluptate velit.</h3>
                    </summary>
                    <div class="details__content">
                        <div class="entry-content">
                            <p>Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
                        </div>
                    </div>
                </details>
                <details class="accordion-item details accordion">
                    <summary class="accordion-header">
                        <h3 class="heading summary">Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit.</h3>
                    </summary>
                    <div class="details__content">
                        <div class="entry-content">
                            <p>Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt.</p>
                        </div>
                    </div>
                </details>
                <details class="accordion-item details accordion">
                    <summary class="accordion-header">
                        <h3 class="heading summary">Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit.</h3>
                    </summary>
                    <div class="details__content">
                        <div class="entry-content">
                            <p>Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur?</p>
                        </div>
                    </div>
                </details>
            </div>
            <div class="faqs-cta">
                <a href="#" class="btn btn--secondary btn--icon-right">
                    <span class="btn__icon" aria-hidden="true">
                        <i class="fa-solid fa-arrow-right"></i>
                    </span>
                    <span class="btn-text">View All FAQs</span>
                </a>
            </div>
        </div>
    </section>
    <?php //get_template_part( 'template-parts/loop', 'flex' ); ?>
</main>

<?php get_footer(); ?>
