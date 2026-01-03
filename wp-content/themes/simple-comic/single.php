<?php get_header(); ?>

<?php if (have_posts()): while (have_posts()): the_post(); ?>
    <?php $ptype = get_post_type(); ?>

    <?php if ($ptype === 'tour_date'):
      // --- Tour Date meta ---
      $date    = get_post_meta(get_the_ID(), 'event_date', true);
      $venue   = get_post_meta(get_the_ID(), 'venue', true);
      $city    = get_post_meta(get_the_ID(), 'city', true);
      $status  = get_post_meta(get_the_ID(), 'status', true) ?: 'scheduled';

      $is_canceled = ($status === 'canceled');
      $date_disp   = $date ? date_i18n(get_option('date_format'), strtotime($date)) : '';
      $status_label = $is_canceled ? 'CANCELED' : 'Scheduled';

      // JSON-LD Event (demo-friendly)
      $event = [
        '@context' => 'https://schema.org',
        '@type' => 'Event',
        'name' => get_the_title(),
        'startDate' => $date ?: '',
        'eventStatus' => $is_canceled ? 'https://schema.org/EventCancelled' : 'https://schema.org/EventScheduled',
        'url' => get_permalink(),
        'location' => [
          '@type' => 'Place',
          'name' => $venue ?: 'TBD',
          'address' => $planet_str ?: $city
        ],
        'performer' => [
          '@type' => 'PerformingGroup',
          'name' => 'Chewbacca'
        ],
      ];
    ?>
      <article id="post-<?php the_ID(); ?>" <?php post_class(); ?> style="margin-bottom:2rem;">
        <header>
          <h1 style="margin-bottom:.25rem;">
            <?php the_title(); ?>
            <?php if ($is_canceled): ?>
              <em style="color:#b00;font-size:.7em;margin-left:.5rem;">CANCELED</em>
            <?php endif; ?>
          </h1>
          <p style="margin:0 0 1rem;opacity:.8;">
            <?php if ($date_disp): ?>
              <strong><?php echo esc_html($date_disp); ?></strong>
            <?php endif; ?>
            <?php if ($venue): ?>
              — <?php echo esc_html($venue); ?>
            <?php endif; ?>
            <?php if ($planet_str || $city): ?>
              — <?php echo esc_html($planet_str ?: $city); ?>
            <?php endif; ?>
            <?php if (!$is_canceled && $status_label): ?>
              — <span><?php echo esc_html($status_label); ?></span>
            <?php endif; ?>
          </p>
        </header>

        <div class="entry-content">
          <?php
          // Default content block (optional description of the show/venue)
          the_content();
          ?>
        </div>

        <footer style="margin-top:2rem;border-top:1px solid #ddd;padding-top:1rem;">
          <nav class="post-nav" aria-label="Tour date navigation" style="display:flex;gap:1rem;flex-wrap:wrap;">
            <span><?php previous_post_link('%link', '« Previous'); ?></span>
            <span><?php next_post_link('%link', 'Next »'); ?></span>
          </nav>
        </footer>
      </article>

      <script type="application/ld+json">
        <?php echo wp_json_encode($event, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); ?>
      </script>

    <?php else: // --- Default single post template --- 
    ?>
      <article id="post-<?php the_ID(); ?>" <?php post_class(); ?> style="margin-bottom:2rem;">
        <header>
          <h1><?php the_title(); ?></h1>
          <p><small><?php echo esc_html(get_the_date()); ?></small></p>
        </header>

        <div class="entry-content">
          <?php the_content(); ?>
        </div>

        <footer style="margin-top:2rem;border-top:1px solid #ddd;padding-top:1rem;">
          <nav class="post-nav" aria-label="Post navigation" style="display:flex;gap:1rem;flex-wrap:wrap;">
            <span><?php previous_post_link('%link', '« Previous'); ?></span>
            <span><?php next_post_link('%link', 'Next »'); ?></span>
          </nav>
        </footer>
      </article>
    <?php endif; ?>

<?php endwhile;
endif; ?>

<?php get_footer(); ?>