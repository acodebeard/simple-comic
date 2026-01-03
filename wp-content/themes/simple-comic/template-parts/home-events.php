<div class="section-inner">
            <header class="section-header">
              <h2 class="section-title">Upcoming Shows</h2>
              <?php
              // Prefer The Events Calendar "all events" link if available,
              // otherwise fall back to the tour_date archive.
              $events_link = '';

              if (function_exists('tribe_get_events_link')) {
                $events_link = tribe_get_events_link();
              } else {
                $events_link = get_post_type_archive_link('tour_date');
              }

              if ($events_link) : ?>
                <a class="section-link" href="<?php echo esc_url($events_link); ?>">
                  View all dates
                </a>
              <?php endif; ?>
            </header>

            <?php
            // If The Events Calendar is active, use its events.
            if (function_exists('tribe_get_events')) :

              // Retrieve the next 5 upcoming events.
              $events = tribe_get_events([
                'posts_per_page' => 5,
                'start_date'     => 'now',   // only events starting today or later
                'order'          => 'ASC',
              ]);

              if (! empty($events)) : ?>
                <ul class="show-list">
                  <?php
                  foreach ($events as $event) :
                    // Make WP template tags (the_title(), the_permalink(), etc.) work.
                    $post = $event;
                    setup_postdata($post);

                    // Use The Events Calendar start date helpers.
                    if (function_exists('tribe_get_start_date')) {
                      $machine_date = tribe_get_start_date($post->ID, false, 'Y-m-d');
                      $human_date   = tribe_get_start_date($post->ID, false, 'M j, Y');
                    } else {
                      // Fallback: use the post date if helper is unavailable.
                      $machine_date = get_the_date('Y-m-d', $post);
                      $human_date   = get_the_date('M j, Y', $post);
                    }
                  ?>
                    <li class="show-item">
                      <a class="show-link" href="<?php the_permalink(); ?>">
                        <h3 class="show-title"><?php the_title(); ?></h3>
                        <time
                          class="show-date"
                          datetime="<?php echo esc_attr($machine_date); ?>">
                          <?php echo esc_html($human_date); ?>
                        </time>
                        <?php if (has_excerpt()) : ?>
                          <p class="show-excerpt">
                            <?php echo esc_html(wp_trim_words(get_the_excerpt(), 18)); ?>
                          </p>
                        <?php endif; ?>
                      </a>
                    </li>
                  <?php endforeach; ?>
                </ul>
                <?php wp_reset_postdata(); ?>
              <?php else : ?>
                <p>No upcoming shows yet. Check back soon.</p>
              <?php endif; ?>

              <?php
            // Fallback: no Events Calendar, keep your original tour_date query.
            else :

              $tour_query = new WP_Query([
                'post_type'      => 'tour_date',
                'posts_per_page' => 5,
                'orderby'        => 'date',
                'order'          => 'ASC',
              ]);

              if ($tour_query->have_posts()) : ?>
                <ul class="show-list">
                  <?php while ($tour_query->have_posts()) : $tour_query->the_post(); ?>
                    <li class="show-item">
                      <a class="show-link" href="<?php the_permalink(); ?>">
                        <h3 class="show-title"><?php the_title(); ?></h3>
                        <time
                          class="show-date"
                          datetime="<?php echo esc_attr(get_the_date('Y-m-d')); ?>">
                          <?php echo esc_html(get_the_date('M j, Y')); ?>
                        </time>
                        <?php if (has_excerpt()) : ?>
                          <p class="show-excerpt">
                            <?php echo esc_html(wp_trim_words(get_the_excerpt(), 18)); ?>
                          </p>
                        <?php endif; ?>
                      </a>
                    </li>
                  <?php endwhile; ?>
                </ul>
                <?php wp_reset_postdata(); ?>
              <?php else : ?>
                <p>No upcoming shows yet. Check back soon.</p>
              <?php endif; ?>

            <?php endif; ?>
          </div>