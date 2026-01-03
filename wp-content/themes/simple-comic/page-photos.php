<?php

/**
 * Photos page template for Simple Comic
 *
 * URL: /media/photos (slug "photos", parent "media")
 */

get_header();
?>

<section class="section page-photos">
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

        <div class="section-body gallery-body">

          <?php
          // Optional intro text from the page editor (above the grid)
          $content = trim(get_the_content());
          if ($content) : ?>
            <div class="gallery-intro">
              <?php the_content(); ?>
            </div>
          <?php endif; ?>

          <?php
          // Pagination setup
          $paged    = max(1, get_query_var('paged'), get_query_var('page'));
          $per_page = 24;

          // Query ONLY images explicitly included on the Photos page
          $photos_query = new WP_Query([
            'post_type'      => 'attachment',
            'post_status'    => 'inherit',
            'post_mime_type' => 'image',
            'posts_per_page' => $per_page,
            'paged'          => $paged,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'meta_query'     => [
              [
                'key'     => '_sc_include_photos_page',
                'value'   => '1',
                'compare' => '=',
              ],
            ],
          ]);
          ?>

          <?php if ($photos_query->have_posts()) : ?>
            <ul class="photo-grid">
              <?php while ($photos_query->have_posts()) : $photos_query->the_post(); ?>

                <?php
                $attachment_id = get_the_ID();
                $full_url      = wp_get_attachment_url($attachment_id);

                // Basic safety
                if (!$full_url) {
                  continue;
                }
                ?>

                <li class="photo-item">
                  <a
                    href="<?php echo esc_url($full_url); ?>"
                    class="photo-lightbox-trigger">
                    <?php
                    echo wp_get_attachment_image(
                      $attachment_id,
                      'medium_large',
                      false,
                      ['class' => 'photo-image']
                    );
                    ?>
                  </a>
                </li>

              <?php endwhile; ?>
            </ul>

            <nav class="gallery-pagination">
              <?php
              echo paginate_links([
                'total'   => (int) $photos_query->max_num_pages,
                'current' => (int) $paged,
              ]);
              ?>
            </nav>

            <?php wp_reset_postdata(); ?>

          <?php else : ?>
            <p>No photos yet. Check back soon.</p>
          <?php endif; ?>

        </div>

    <?php endwhile;
    endif; ?>

  </div>
</section>

<?php get_footer(); ?>