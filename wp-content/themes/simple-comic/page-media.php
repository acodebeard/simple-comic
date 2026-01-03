<?php

/**
 * Media hub page for Simple Comic
 *
 * URL: /media  (create a page with slug "media")
 */

get_header();
?>

<main class="section page-media">
  <div class="section-inner">

    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

        <header class="section-header">
          <h1 class="section-title"><?php the_title(); ?></h1>

          <?php if (has_excerpt()) : ?>
            <p class="section-tagline">
              <?php echo esc_html(get_the_excerpt()); ?>
            </p>
          <?php else : ?>
            <p class="section-tagline">
              Photos and videos you can actually use to promote the show.
            </p>
          <?php endif; ?>
        </header>

        <div class="section-body media-body">
          <?php
          // Optional intro content from the editor.
          $content = trim(get_the_content());
          if ($content) :
          ?>
            <div class="media-intro">
              <?php the_content(); ?>
            </div>
          <?php endif; ?>

          <?php
          // Try to auto-find the Photos and Videos pages by slug.
          $photos_page = get_page_by_path('photos', OBJECT, 'page');
          $videos_page = get_page_by_path('videos', OBJECT, 'page');

          $photos_url = $photos_page ? get_permalink($photos_page) : '#';
          $videos_url = $videos_page ? get_permalink($videos_page) : '#';
          ?>

          <div class="media-grid">
            <article class="media-card media-card--photos margin-bottom-medium">
              <h2 class="media-card-title">
                <a href="<?php echo esc_url($photos_url); ?>">
                  Photos
                </a>
              </h2>
              <p class="media-card-text">
                Photos!
              </p>
              <p class="media-card-link">
                <a href="<?php echo esc_url($photos_url); ?>">
                  View photos →
                </a>
              </p>
            </article>

            <article class="media-card media-card--videos margin-bottom-medium">
              <h2 class="media-card-title">
                <a href="<?php echo esc_url($videos_url); ?>">
                  Videos
                </a>
              </h2>
              <p class="media-card-text">
                Clips and full sets!
              </p>
              <p class="media-card-link">
                <a href="<?php echo esc_url($videos_url); ?>">
                  View videos →
                </a>
              </p>
            </article>
          </div>
        </div>

    <?php endwhile;
    endif; ?>

  </div>
</main>

<?php get_footer();
