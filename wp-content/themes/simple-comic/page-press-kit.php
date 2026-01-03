<?php

/**
 * Press Kit page template for Simple Comic
 *
 * URL: /press-kit  (create a page with slug "press-kit")
 */

get_header();
?>


<section class="section press-body">

  <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

      <header class="section-header flex flex-middle">
        <div class="section-header-inner flex-1">
          <h1 class="section-title"><?php the_title(); ?></h1>

          <?php if (has_excerpt()) : ?>
            <p class="section-tagline"><?php echo esc_html(get_the_excerpt()); ?></p>
  
          <?php endif; ?>
        </div>

        <?php
        $pdf = function_exists('get_field') ? get_field('presskit_pdf') : null;

        // ACF File field can return array or URL depending on config
        $pdf_url = '';
        if (is_array($pdf) && !empty($pdf['url'])) {
          $pdf_url = $pdf['url'];
        } elseif (is_string($pdf) && $pdf !== '') {
          $pdf_url = $pdf;
        }

        if ($pdf_url) :
        ?>
          <a class="button-default"
            href="<?php echo esc_url($pdf_url); ?>"
            download
            target="_blank"
            rel="noopener">
            Download PDF Press Kit
          </a>
        <?php endif; ?>
      </header>


      <?php
      // Main press kit content (bios, credits, boilerplate) from the editor.
      $content = trim(get_the_content());
      if ($content) : ?>
        <div class="press-intro">
          <?php the_content(); ?>
        </div>
      <?php endif; ?>

      <?php
      // --------------------------------------------------------------------
      // Headshots section – only images marked with "Headshot (press kit)"
      // via _is_headshot = '1' in attachment meta.
      // --------------------------------------------------------------------

      $headshots = get_posts([
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'post_mime_type' => 'image',
        'posts_per_page' => -1,
        'meta_key'       => '_is_headshot',
        'meta_value'     => '1',
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
      ]);

      if ($headshots) : ?>
        <article class="article press-headshots">
          <h2 class="section-subtitle">Headshots</h2>

          <p class="press-note visually-hidden">
            High-resolution headshots for print and web. Minimum 2000px on the long edge;
            ideal for posters, flyers, and social graphics.
          </p>

          <ul class="headshot-grid">
            <?php foreach ($headshots as $attachment) : ?>
              <?php
              // Get metadata for optional resolution display.
              $meta        = wp_get_attachment_metadata($attachment->ID);
              $dimensions  = '';
              if (! empty($meta['width']) && ! empty($meta['height'])) {
                $dimensions = sprintf(
                  '%d × %d px',
                  (int) $meta['width'],
                  (int) $meta['height']
                );
              }

              $full_url    = wp_get_attachment_url($attachment->ID);
              $caption     = wp_get_attachment_caption($attachment->ID);
              ?>
              <li class="headshot-item">
                <a class="headshot-link" href="<?php echo esc_url($full_url); ?>">
                  <?php
                  echo wp_get_attachment_image(
                    $attachment->ID,
                    'large',
                    false,
                    ['class' => 'headshot-image']
                  );
                  ?>
                </a>

                <?php if ($caption) : ?>
                  <p class="headshot-caption">
                    <?php echo esc_html($caption); ?>
                  </p>
                <?php endif; ?>

                <p class="headshot-meta">
                  <?php if ($dimensions) : ?>
                    <span class="headshot-size">
                      <?php echo esc_html($dimensions); ?>
                    </span>
                  <?php endif; ?>
                </p>

                <p class="headshot-download">
                  <a href="<?php echo esc_url($full_url); ?>" download>
                    Download high-res
                  </a>
                </p>
              </li>
            <?php endforeach; ?>
          </ul>
        </article>
      <?php endif; ?>

      <?php
      function sc_extract_src_from_iframe(string $s): string
      {
        if (stripos($s, '<iframe') === false) {
          return $s;
        }

        // Extract src="..."
        if (preg_match('/\ssrc\s*=\s*["\']([^"\']+)["\']/i', $s, $m)) {
          return $m[1];
        }

        return '';
      }

      function sc_normalize_video_url(string $raw): string
      {
        $raw = trim($raw);
        $raw = trim($raw, "\"'");

        if ($raw === '') {
          return '';
        }

        $raw = sc_extract_src_from_iframe($raw);
        $raw = trim($raw);
        $raw = trim($raw, "\"'");

        if ($raw === '') {
          return '';
        }

        // If they pasted protocol-relative URLs
        if (strpos($raw, '//') === 0) {
          $raw = 'https:' . $raw;
        }

        $parts = wp_parse_url($raw);
        if (empty($parts['scheme']) || !in_array($parts['scheme'], ['http', 'https'], true)) {
          return '';
        }

        $host = strtolower(preg_replace('/^www\./', '', $parts['host'] ?? ''));
        $path = $parts['path'] ?? '';
        $query = $parts['query'] ?? '';

        // YouTube normalization -> https://www.youtube.com/embed/ID
        if ($host === 'youtu.be') {
          $id = trim($path, '/');
          if ($id !== '') {
            return 'https://www.youtube.com/embed/' . $id;
          }
        }

        if ($host === 'youtube.com' || $host === 'm.youtube.com') {
          parse_str($query, $q);
          if (($path === '/watch') && !empty($q['v'])) {
            return 'https://www.youtube.com/embed/' . $q['v'];
          }
          if (strpos($path, '/embed/') === 0) {
            return 'https://www.youtube.com' . $path;
          }
        }

        if ($host === 'youtube-nocookie.com' && strpos($path, '/embed/') === 0) {
          return 'https://www.youtube-nocookie.com' . $path;
        }

        // Vimeo normalization -> https://player.vimeo.com/video/ID
        if ($host === 'vimeo.com') {
          $id = trim($path, '/');
          if ($id !== '' && ctype_digit($id)) {
            return 'https://player.vimeo.com/video/' . $id;
          }
        }

        if ($host === 'player.vimeo.com' && strpos($path, '/video/') === 0) {
          return 'https://player.vimeo.com' . $path;
        }

        // Otherwise keep as-is (might be another oEmbed provider)
        return $raw;
      }

      $raw = (string) get_field('presskit_video_urls');
      $video_urls = array_values(array_filter(array_map('trim', preg_split("/\r\n|\n|\r/", $raw))));
      $video_urls = array_slice($video_urls, 0, 5);
      ?>

      <?php if (!empty($video_urls)) : ?>
        <article class="article press-videos">
          <h2 class="section-subtitle">Videos</h2>

          <div class="press-videos-row">
            <?php foreach ($video_urls as $row) : ?>
              <?php
              $url = sc_normalize_video_url($row);
              if ($url === '') {
                continue; // skip bad rows instead of rendering empty links
              }

              // If it's a YouTube/Vimeo embed URL, render iframe directly.
              $host = strtolower(preg_replace('/^www\./', '', wp_parse_url($url, PHP_URL_HOST) ?? ''));

              if (
                ($host === 'youtube.com' && strpos($url, 'https://www.youtube.com/embed/') === 0) ||
                ($host === 'youtube-nocookie.com' && strpos($url, 'https://www.youtube-nocookie.com/embed/') === 0) ||
                ($host === 'player.vimeo.com' && strpos($url, 'https://player.vimeo.com/video/') === 0)
              ) {
                $embed_html = '<iframe src="' . esc_url($url) . '" loading="lazy" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"></iframe>';
              } else {
                // Try oEmbed for other providers
                $embed_html = wp_oembed_get($url);
                if (!$embed_html) {
                  $embed_html = '<a class="press-video-link" href="' . esc_url($url) . '" target="_blank" rel="noopener">Watch video</a>';
                }
              }
              ?>
              <div class="press-video">
                <?php echo $embed_html; ?>
              </div>
            <?php endforeach; ?>
          </div>
        </article>
      <?php endif; ?>


      </div><!-- .press-body -->

  <?php endwhile;
  endif; ?>

</section>

<?php get_footer();
