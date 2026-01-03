<?php

/**
 * Template Name: Bio Page
 * Description: Bio page with featured image and bio layout.
 */

get_header();
?>

  <?php if (have_posts()) : ?>
    <?php while (have_posts()) : the_post(); ?>

      <section class="section bio-hero">
        <div class="section-inner bio-hero-inner">

          <?php if (has_post_thumbnail()) : ?>
            <figure class="bio-image">
              <?php the_post_thumbnail('large'); ?>
            </figure>
          <?php endif; ?>

          <article class="bio-content">
            <header class="bio-header">
              <h1 class="bio-title"><?php the_title(); ?></h1>

              <?php if (has_excerpt()) : ?>
                <p class="bio-tagline">
                  <?php echo esc_html(get_the_excerpt()); ?>
                </p>
              <?php endif; ?>
            </header>

            <div class="bio-text">
              <?php the_content(); ?>
            </div>
          </article>

        </div>
      </section>

    <?php endwhile; ?>
  <?php endif; ?>



<?php get_footer(); ?>