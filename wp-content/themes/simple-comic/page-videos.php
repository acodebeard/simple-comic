<?php

/**
 * Videos page template for Simple Comic
 *
 * URL: /media/videos (create a page with slug "videos", parent "media")
 */

get_header();
?>

<main class="section page-videos">
  <div class="section-inner">

    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

        <header class="section-header">
          <h1 class="section-title"><?php the_title(); ?></h1>

          <?php if (has_excerpt()) : ?>
            <p class="section-tagline">
              <?php echo esc_html(get_the_excerpt()); ?>
            </p>
          <?php endif; ?>
        </header>

        <div class="section-body videos-body">

          <div class="videos-grid">
            <?php
            // User just drops YouTube/Vimeo URLs or video blocks in the editor;
            // we handle layout here.
            the_content();
            ?>
          </div>

        </div>

    <?php endwhile;
    endif; ?>

  </div>
</main>

<?php get_footer();
