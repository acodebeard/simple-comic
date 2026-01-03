<?php

/**
 * Front Page template for Simple Comic
 *
 * Used when a static page is set as the front page.
 */

get_header();
?>

<?php if (have_posts()) : ?>
  <?php while (have_posts()) : the_post(); ?>

    <?php if (has_post_thumbnail()) : ?>
      <figure class="hero-image hero-image-main">
        <?php the_post_thumbnail('large'); ?>
      </figure>
    <?php endif; ?>

    <section class="section home-hero">
      <div class="section-inner home-hero-inner">
        <div class="home-hero-inner-content flex flex-wrap">
          <div class="hero-block hero-block-text flex flex-column">
            <article class="hero-bio flex flex-middle">
              <div class="hero-text">
                <?php the_content(); ?>
              </div>
            </article>
          </div>
          <div class="hero-block-links">
            <?php include 'template-parts/tiny-nav.php'; ?>
          </div>
        </div>
      </div>
    </section>
    <section class="section home-shows">
      <?php include 'template-parts/home-events.php'; ?>
    </section>


  <?php endwhile; ?>
<?php endif; ?>


<?php get_footer(); ?>