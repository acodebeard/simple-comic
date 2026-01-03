<?php
define('WP_DEBUG_LOG', WP_CONTENT_DIR . '/_logs/wp-debug.log');
add_action('init', function () {
  $log = WP_CONTENT_DIR . '/_logs/php-errors.log';

  $result = @ini_set('error_log', $log);
  error_log('SC TEST: error_log() is writing now.');

  // Optional: see what PHP thinks the current error_log is
  // error_log('SC TEST: ini_get(error_log)=' . (string) ini_get('error_log'));
});

add_action('after_setup_theme', function () {
  add_theme_support('title-tag');
  add_theme_support('post-thumbnails');
  register_nav_menus(['primary' => 'Primary Navigation']);
  add_post_type_support('page', 'excerpt');
});

add_action('wp_enqueue_scripts', function () {
  wp_enqueue_style('simple-comic', get_stylesheet_uri(), [], '1.0');
});

// ---- Custom Post Type: Tour Dates ----

add_action('init', function () {
  register_post_type('tour_date', [
    'labels' => [
      'name' => 'Tour Dates',
      'singular_name' => 'Tour Date',
      'add_new_item' => 'Add New Tour Date',
      'edit_item' => 'Edit Tour Date',
      'menu_name' => 'Tour Dates',
    ],
    'public' => true,
    'has_archive' => true,
    'menu_icon' => 'dashicons-calendar-alt',
    'supports' => ['title', 'editor', 'thumbnail'],
    'rewrite' => ['slug' => 'dates'],
    'show_in_rest' => true,
  ]);
});

// -------- Meta fields for Tour Dates --------
// Fields: event_date (Y-m-d), venue, city, status (scheduled|canceled)
add_action('add_meta_boxes', function () {
  add_meta_box('td_meta', 'Tour Details', 'simple_comic_td_meta_box', 'tour_date', 'normal', 'default');
});


add_action('save_post_tour_date', function ($post_id) {
  if (!isset($_POST['td_meta_nonce']) || !wp_verify_nonce($_POST['td_meta_nonce'], 'td_meta_save')) return;
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
  if (!current_user_can('edit_post', $post_id)) return;

  update_post_meta($post_id, 'event_date', sanitize_text_field($_POST['event_date'] ?? ''));
  update_post_meta($post_id, 'venue',      sanitize_text_field($_POST['venue'] ?? ''));
  update_post_meta($post_id, 'city',       sanitize_text_field($_POST['city'] ?? ''));
  $status = in_array(($_POST['status'] ?? ''), ['scheduled', 'canceled'], true) ? $_POST['status'] : 'scheduled';
  update_post_meta($post_id, 'status', $status);
});
// Ensure CPT rewrites are ready after theme switch
add_action('after_switch_theme', function () {
  flush_rewrite_rules();
});
// Register post meta with sanitize and REST schema
add_action('init', function () {
  register_post_meta('tour_date', 'event_date', [
    'show_in_rest' => [
      'schema' => ['type' => 'string', 'format' => 'date'],
    ],
    'single' => true,
    'type'   => 'string',
    'sanitize_callback' => function ($v) {
      $v = trim((string)$v);
      // Expect Y-m-d; normalize if possible
      $ts = strtotime($v);
      return $ts ? date('Y-m-d', $ts) : '';
    }
  ]);

  foreach (['venue', 'city'] as $field) {
    register_post_meta('tour_date', $field, [
      'show_in_rest' => true,
      'single' => true,
      'type'   => 'string',
      'sanitize_callback' => 'sanitize_text_field',
    ]);
  }

  register_post_meta('tour_date', 'status', [
    'show_in_rest' => [
      'schema' => ['type' => 'string', 'enum' => ['scheduled', 'canceled']],
    ],
    'single' => true,
    'type'   => 'string',
    'sanitize_callback' => function ($v) {
      return in_array($v, ['scheduled', 'canceled'], true) ? $v : 'scheduled';
    }
  ]);
});
// Columns
add_filter('manage_edit-tour_date_columns', function ($cols) {
  $new = [];
  foreach ($cols as $k => $v) {
    if ($k === 'date') continue;
    $new[$k] = $v;
    if ($k === 'title') {
      $new['event_date'] = 'Event Date';
      $new['venue'] = 'Venue';
      $new['city']  = 'City';
      $new['status'] = 'Status';
    }
  }
  return $new;
});
add_action('manage_tour_date_posts_custom_column', function ($col, $post_id) {
  if ($col === 'event_date') echo esc_html(get_post_meta($post_id, 'event_date', true));
  if ($col === 'venue')      echo esc_html(get_post_meta($post_id, 'venue', true));
  if ($col === 'city')       echo esc_html(get_post_meta($post_id, 'city', true));
  if ($col === 'status')     echo esc_html(get_post_meta($post_id, 'status', true) ?: 'scheduled');
}, 10, 2);

// Sortable
add_filter('manage_edit-tour_date_sortable_columns', function ($cols) {
  $cols['event_date'] = 'event_date';
  return $cols;
});
add_action('pre_get_posts', function ($q) {
  if (!is_admin() || !$q->is_main_query()) return;
  if ($q->get('post_type') === 'tour_date' && $q->get('orderby') === 'event_date') {
    $q->set('meta_key', 'event_date');
    $q->set('orderby', 'meta_value');
    $q->set('order', $q->get('order') ?: 'ASC');
  }
});
// Ensure /dates archive orders by event_date ascending
add_action('pre_get_posts', function ($q) {
  if (is_admin() || !$q->is_main_query()) return;
  if ($q->is_post_type_archive('tour_date')) {
    $q->set('meta_key', 'event_date');
    $q->set('orderby', 'meta_value');
    $q->set('order', 'ASC');
    // Optional: only upcoming
    // $q->set('meta_query', [[
    //   'key' => 'event_date', 'value' => date('Y-m-d'), 'compare' => '>=', 'type' => 'DATE'
    // ]]);
  }
});
function simple_comic_td_meta_box($post)
{
  wp_nonce_field('td_meta_save', 'td_meta_nonce');
  $date   = get_post_meta($post->ID, 'event_date', true);
  $venue  = get_post_meta($post->ID, 'venue', true);
  $city   = get_post_meta($post->ID, 'city', true);
  $status = get_post_meta($post->ID, 'status', true) ?: 'scheduled'; ?>
  <p><label>Date*:
      <input type="date" name="event_date" value="<?php echo esc_attr($date); ?>" required></label></p>
  <p><label>Venue*:
      <input type="text" name="venue" value="<?php echo esc_attr($venue); ?>" class="widefat" required></label></p>
  <p><label>City/Region*:
      <input type="text" name="city" value="<?php echo esc_attr($city); ?>" class="widefat" required></label></p>
  <p><label>Status:
      <select name="status">
        <option value="scheduled" <?php selected($status, 'scheduled'); ?>>Scheduled</option>
        <option value="canceled" <?php selected($status, 'canceled');  ?>>Canceled</option>
      </select></label></p>
<?php }
add_action('init', function () {
  add_rewrite_rule('^dates\.ics$', 'index.php?sc_ics=1', 'top');
});
add_filter('query_vars', fn($v) => array_merge($v, ['sc_ics']));
add_action('template_redirect', function () {
  if (!get_query_var('sc_ics')) return;
  header('Content-Type: text/calendar; charset=utf-8');
  header('Content-Disposition: inline; filename="tour-dates.ics"');

  $q = new WP_Query([
    'post_type' => 'tour_date',
    'posts_per_page' => -1,
    'meta_key' => 'event_date',
    'orderby' => 'meta_value',
    'order' => 'ASC'
  ]);

  $out = [];
  $out[] = 'BEGIN:VCALENDAR';
  $out[] = 'VERSION:2.0';
  $out[] = 'PRODID:-//Simple Comic//EN';

  while ($q->have_posts()) {
    $q->the_post();
    $date  = get_post_meta(get_the_ID(), 'event_date', true);
    $venue = get_post_meta(get_the_ID(), 'venue', true);
    $city  = get_post_meta(get_the_ID(), 'city', true);
    $status = get_post_meta(get_the_ID(), 'status', true) ?: 'scheduled';
    if (!$date) continue;

    $start = gmdate('Ymd\THis\Z', strtotime($date . ' 20:00:00')); // assume 8pm
    $uid   = get_the_ID() . '@' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    $sum   = wp_strip_all_tags(get_the_title());
    $loc   = trim(($venue ?: '') . ($city ? (' — ' . $city) : ''));
    $st    = ($status === 'canceled') ? 'CANCELLED' : 'CONFIRMED';
    $url   = get_permalink();
    $desc  = trim(wp_strip_all_tags(get_the_content()));

    $out[] = 'BEGIN:VEVENT';
    $out[] = 'UID:' . $uid;
    $out[] = 'DTSTAMP:' . gmdate('Ymd\THis\Z');
    $out[] = 'DTSTART:' . $start;
    $out[] = 'SUMMARY:' . $sum;
    if ($loc) $out[] = 'LOCATION:' . $loc;
    $out[] = 'STATUS:' . $st;
    $out[] = 'URL;VALUE=URI:' . $url;
    if ($desc) $out[] = 'DESCRIPTION:' . str_replace(["\r", "\n"], ['', '\n'], $desc);
    $out[] = 'END:VEVENT';
  }
  wp_reset_postdata();

  $out[] = 'END:VCALENDAR';
  echo implode("\r\n", $out);
  exit;
});
// Handle Contact form submissions (free, no plugins)
add_action('admin_post_nopriv_sc_contact', 'simple_comic_handle_contact');
add_action('admin_post_sc_contact', 'simple_comic_handle_contact');

function simple_comic_handle_contact()
{
  if (
    !isset($_POST['sc_contact_nonce']) ||
    !wp_verify_nonce($_POST['sc_contact_nonce'], 'sc_contact_form')
  ) {
    wp_safe_redirect(add_query_arg('contact', 'error', wp_get_referer() ?: home_url('/')));
    exit;
  }

  // Honeypot trap
  if (!empty($_POST['website'])) {
    wp_safe_redirect(add_query_arg('contact', 'sent', wp_get_referer() ?: home_url('/')));
    exit;
  }

  $name    = sanitize_text_field($_POST['name'] ?? '');
  $email   = sanitize_email($_POST['email'] ?? '');
  $subject = sanitize_text_field($_POST['subject'] ?? '');
  $message = trim(wp_kses_post($_POST['message'] ?? ''));

  if (!$name || !$email || !$subject || !$message) {
    wp_safe_redirect(add_query_arg('contact', 'error', wp_get_referer() ?: home_url('/')));
    exit;
  }

  $to      = get_option('admin_email');
  $subject = '[Simple Comic Contact] ' . $subject;

  $body  = "Name: {$name}\n";
  $body .= "Email: {$email}\n\n";
  $body .= "Message:\n{$message}\n";

  $headers = [];
  if ($email) {
    $headers[] = 'Reply-To: ' . $name . ' <' . $email . '>';
  }

  $sent = wp_mail($to, $subject, $body, $headers);

  $redirect = wp_get_referer() ?: home_url('/');
  $redirect = add_query_arg('contact', $sent ? 'sent' : 'error', $redirect);
  wp_safe_redirect($redirect);
  exit;
}

// Add a "Headshot (press kit)" checkbox to media attachments
add_filter('attachment_fields_to_edit', function ($form_fields, $post) {

  // Only show for images
  if (0 === strpos($post->post_mime_type, 'image/')) {
    $value = get_post_meta($post->ID, '_is_headshot', true);

    $form_fields['is_headshot'] = [
      'label' => 'Headshot (press kit)',
      'input' => 'html',
      'html'  => sprintf(
        '<label><input type="checkbox" name="attachments[%d][is_headshot]" value="1"%s> Mark as headshot for press kit</label>',
        $post->ID,
        checked($value, '1', false)
      ),
      'helps' => 'Headshots will be excluded from the main photo gallery and shown on the press kit page.',
    ];
  }

  return $form_fields;
}, 10, 2);


// Save the "Headshot" checkbox value
add_filter('attachment_fields_to_save', function ($post, $attachment) {

  if (isset($attachment['is_headshot']) && '1' === $attachment['is_headshot']) {
    update_post_meta($post['ID'], '_is_headshot', '1');
  } else {
    delete_post_meta($post['ID'], '_is_headshot');
  }

  return $post;
}, 10, 2);

// TEC Views V2: Hide the entire "Subscribe to calendar" block everywhere.
add_filter('tec_views_v2_subscribe_links', function ($subscribe_links) {
  return []; // empty array makes the template bail and not render the block
}, 100);

?>
<?php
add_action('acf/input/admin_footer', function () {
  $screen = function_exists('get_current_screen') ? get_current_screen() : null;
  if (!$screen || !in_array($screen->base, ['post', 'post-new'], true)) {
    return;
  }
?>
  <style>
    /* Hide only the original ACF textarea (storage), not our injected ones */
    .acf-field[data-name="presskit_video_urls"] .acf-input>textarea {
      display: none !important;
    }

    .presskit-videos-ui {
      margin-top: 8px;
    }

    .presskit-videos-ui .video-help {
      margin: 6px 0 10px;
      color: #50575e;
      font-size: 12px;
      max-width: 980px;
    }

    .presskit-videos-ui .video-row {
      display: flex;
      gap: 12px;
      align-items: flex-start;
      margin-bottom: 12px;
      max-width: 980px;
    }

    .presskit-videos-ui .video-row textarea {
      flex: 1 1 auto;
      min-height: 60px;
      resize: vertical;
      margin: 0;
    }

    .presskit-videos-ui .video-preview {
      flex: 0 0 220px;
      width: 220px;
      max-width: 220px;
      min-height: 124px;
      border: 1px solid #dcdcde;
      border-radius: 6px;
      background: #f6f7f7;
      overflow: hidden;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .presskit-videos-ui .video-preview.is-empty {
      color: #50575e;
      font-size: 12px;
      padding: 8px;
      text-align: center;
    }

    .presskit-videos-ui .video-preview iframe {
      width: 100%;
      height: 100%;
      border: 0;
      display: block;
    }

    .presskit-videos-ui .video-actions {
      display: flex;
      gap: 8px;
      align-items: center;
      margin-top: 8px;
    }

    .presskit-videos-ui .video-counter {
      color: #50575e;
      font-size: 12px;
    }
  </style>

  <script>
    (function() {
      function initPresskitVideosUI() {
        var fieldWrap = document.querySelector('.acf-field[data-name="presskit_video_urls"]');
        if (!fieldWrap) return;

        var storageTextarea = fieldWrap.querySelector('.acf-input > textarea');
        if (!storageTextarea) return;

        // Avoid double-init
        if (fieldWrap.querySelector('.presskit-videos-ui')) return;

        var MAX_VIDEOS = 5;

        function parseStoredValue(value) {
          return (value || '')
            .split(/\r\n|\n|\r/)
            .map(function(s) {
              return s.trim();
            })
            .filter(Boolean)
            .slice(0, MAX_VIDEOS);
        }

        function isSafeHttpUrl(url) {
          try {
            var u = new URL(url, window.location.href);
            return (u.protocol === 'https:' || u.protocol === 'http:');
          } catch (e) {
            return false;
          }
        }

        function extractUrlFromIframeSnippet(snippet) {
          // If they pasted <iframe ...>, parse safely and pull src.
          // We do NOT insert snippet into DOM as HTML.
          try {
            var doc = new DOMParser().parseFromString(snippet, 'text/html');
            var iframe = doc.querySelector('iframe');
            if (iframe) {
              var src = iframe.getAttribute('src') || '';
              return src.trim();
            }
          } catch (e) {
            // ignore
          }
          return '';
        }

        function normalizeToEmbeddableUrl(raw) {
          var v = (raw || '').trim();
          if (!v) return '';

          // If it looks like an iframe snippet, extract src.
          if (v.indexOf('<iframe') !== -1) {
            var extracted = extractUrlFromIframeSnippet(v);
            if (extracted) v = extracted;
          }

          // If it's still not a URL, bail.
          if (!isSafeHttpUrl(v)) return '';

          // Normalize YouTube URLs to /embed/ID
          try {
            var u = new URL(v, window.location.href);
            var host = u.hostname.replace(/^www\./, '');

            // youtu.be/VIDEOID
            if (host === 'youtu.be') {
              var id = u.pathname.replace('/', '').trim();
              if (id) return 'https://www.youtube.com/embed/' + id;
            }

            // youtube.com/watch?v=VIDEOID
            if (host === 'youtube.com' || host === 'm.youtube.com') {
              if (u.pathname === '/watch') {
                var vid = u.searchParams.get('v');
                if (vid) return 'https://www.youtube.com/embed/' + vid;
              }
              // youtube.com/embed/VIDEOID
              if (u.pathname.indexOf('/embed/') === 0) {
                return 'https://www.youtube.com' + u.pathname;
              }
            }

            // youtube-nocookie.com/embed/VIDEOID
            if (host === 'youtube-nocookie.com') {
              if (u.pathname.indexOf('/embed/') === 0) {
                return 'https://www.youtube-nocookie.com' + u.pathname;
              }
            }

            // Vimeo: vimeo.com/123456 -> player.vimeo.com/video/123456
            if (host === 'vimeo.com') {
              var vimeoId = u.pathname.split('/').filter(Boolean)[0];
              if (vimeoId && /^\d+$/.test(vimeoId)) {
                return 'https://player.vimeo.com/video/' + vimeoId;
              }
            }

            // If they already pasted player.vimeo.com/video/ID, keep it
            if (host === 'player.vimeo.com') {
              if (u.pathname.indexOf('/video/') === 0) {
                return 'https://player.vimeo.com' + u.pathname;
              }
            }

            // Fallback: allow other http(s) URLs as-is (some oEmbed providers use iframes directly)
            return u.href;
          } catch (e) {
            return '';
          }
        }

        function setPreview(previewEl, embedUrl) {
          // Clear existing
          while (previewEl.firstChild) previewEl.removeChild(previewEl.firstChild);

          if (!embedUrl) {
            previewEl.classList.add('is-empty');
            previewEl.textContent = 'Paste a YouTube/Vimeo URL (or iframe). Preview loads on blur.';
            return;
          }

          previewEl.classList.remove('is-empty');

          var iframe = document.createElement('iframe');
          iframe.src = embedUrl;
          iframe.loading = 'lazy';
          iframe.allowFullscreen = true;

          // A reasonable allow list for video players
          iframe.setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share');

          previewEl.appendChild(iframe);
        }

        function syncToStorage() {
          var urls = [];
          ui.querySelectorAll('textarea[data-video-url="1"]').forEach(function(ta) {
            var v = (ta.value || '').trim();
            if (v) urls.push(v);
          });

          urls = urls.slice(0, MAX_VIDEOS);
          var joined = urls.join("\n");

          // Prefer ACF JS API so Gutenberg stays happy
          if (window.acf && typeof window.acf.getField === 'function') {
            var acfField = window.acf.getField(fieldWrap);
            if (acfField && typeof acfField.val === 'function') {
              acfField.val(joined);
              return;
            }
          }

          // Fallback
          storageTextarea.value = joined;
        }

        function addRow(initialValue) {
          var count = ui.querySelectorAll('textarea[data-video-url="1"]').length;
          if (count >= MAX_VIDEOS) return;

          var row = document.createElement('div');
          row.className = 'video-row';

          var ta = document.createElement('textarea');
          ta.setAttribute('data-video-url', '1');
          ta.placeholder = 'Paste a video URL (YouTube/Vimeo) or an <iframe> embed snippet';
          ta.value = initialValue || '';

          var preview = document.createElement('div');
          preview.className = 'video-preview is-empty';
          preview.textContent = 'Paste a YouTube/Vimeo URL (or iframe). Preview loads on blur.';

          // Live sync on input (just stores raw; blur normalizes)
          ta.addEventListener('input', function() {
            syncToStorage();
          });

          // On blur: normalize and build preview
          ta.addEventListener('blur', function() {
            var normalized = normalizeToEmbeddableUrl(ta.value);

            if (normalized) {
              // Replace whatever they pasted (iframe, messy URL) with a clean embeddable URL
              // so storage stays consistent and line-based.
              ta.value = normalized;
            }

            setPreview(preview, normalized);
            syncToStorage();
          });

          row.appendChild(ta);
          row.appendChild(preview);

          list.appendChild(row);

          // Seed preview if initial value exists
          var seeded = normalizeToEmbeddableUrl(ta.value);
          if (seeded) {
            ta.value = seeded;
            setPreview(preview, seeded);
          } else {
            setPreview(preview, '');
          }

          updateButtons();
          syncToStorage();
        }

        function removeLastRow() {
          var rows = list.querySelectorAll('.video-row');
          if (rows.length <= 1) {
            var firstTa = rows[0].querySelector('textarea');
            var firstPrev = rows[0].querySelector('.video-preview');
            firstTa.value = '';
            setPreview(firstPrev, '');
            syncToStorage();
            return;
          }

          rows[rows.length - 1].remove();
          updateButtons();
          syncToStorage();
        }

        function updateButtons() {
          var count = ui.querySelectorAll('textarea[data-video-url="1"]').length;
          addBtn.disabled = (count >= MAX_VIDEOS);
          removeBtn.disabled = (count <= 1);
          counter.textContent = count + ' / ' + MAX_VIDEOS;
        }

        // Build UI
        var ui = document.createElement('div');
        ui.className = 'presskit-videos-ui';

        var help = document.createElement('div');
        help.className = 'video-help';
        help.textContent = 'Tip: keep videos under 5 minutes. Paste a URL or iframe. Preview loads on blur. Max 5. Order is top to bottom.';
        ui.appendChild(help);

        var list = document.createElement('div');
        ui.appendChild(list);

        var actions = document.createElement('div');
        actions.className = 'video-actions';

        var addBtn = document.createElement('button');
        addBtn.type = 'button';
        addBtn.className = 'button';
        addBtn.textContent = 'Add another video';
        addBtn.addEventListener('click', function() {
          addRow('');
        });

        var removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'button';
        removeBtn.textContent = 'Remove last';
        removeBtn.addEventListener('click', function() {
          removeLastRow();
        });

        var counter = document.createElement('span');
        counter.className = 'video-counter';

        actions.appendChild(addBtn);
        actions.appendChild(removeBtn);
        actions.appendChild(counter);
        ui.appendChild(actions);

        storageTextarea.insertAdjacentElement('afterend', ui);

        // Seed from stored value (or start with one empty)
        var existing = parseStoredValue(storageTextarea.value || '');
        if (existing.length === 0) {
          addRow('');
        } else {
          existing.forEach(function(url) {
            addRow(url);
          });
        }

        updateButtons();
        syncToStorage();
      }

      document.addEventListener('DOMContentLoaded', initPresskitVideosUI);

      if (window.acf && window.acf.addAction) {
        window.acf.addAction('append', initPresskitVideosUI);
        window.acf.addAction('ready', initPresskitVideosUI);
      }
    })();
  </script>
<?php
});


/**
 * Media Library: checkbox to include an image on the Photos page.
 * Stores on the attachment as post meta: _sc_include_photos_page = "1"
 */

add_filter('attachment_fields_to_edit', function (array $form_fields, WP_Post $post): array {

  $checked = (get_post_meta($post->ID, '_sc_include_photos_page', true) === '1');

  $field_name = 'attachments[' . $post->ID . '][sc_include_photos_page]';

  $form_fields['sc_include_photos_page'] = [
    'label' => 'Photos page',
    'input' => 'html',
    'html'  => ''
      . '<label class="sc-include-photos-wrap">'
      . '  <input type="checkbox" class="sc-include-photos-checkbox" name="' . esc_attr($field_name) . '" value="1" ' . checked($checked, true, false) . '>'
      . '  <span class="sc-include-photos-label">Include on Photos page</span>'
      . '</label>'
      . '<p class="help">Unchecked by default. Turn this on to feature this image in the Photos page gallery.</p>',
  ];

  return $form_fields;
}, 10, 2);


add_action('admin_enqueue_scripts', function (string $hook): void {

  // Load everywhere, because the Media modal can appear on many screens.
  $css = '
    .compat-field-sc_include_photos_page .sc-include-photos-wrap{display:inline-flex;align-items:center;gap:10px;padding:10px 12px;border:2px solid #111;border-radius:10px;background:#fff}.compat-field-sc_include_photos_page .sc-include-photos-checkbox{width:22px;height:22px;transform:scale(1.25);accent-color:#111;outline:2px solid #111;outline-offset:2px;border-radius:4px;margin:0!important}.compat-field-sc_include_photos_page .sc-include-photos-checkbox:checked:before{margin:0!important;transform:translateX(-2px);}.compat-field-sc_include_photos_page .sc-include-photos-label{font-weight:800;letter-spacing:.03em;text-transform:uppercase}.compat-field-sc_include_photos_page .help{margin-top:8px;font-size:12px}
  ';

  wp_register_style('sc-admin-media', false);
  wp_enqueue_style('sc-admin-media');
  wp_add_inline_style('sc-admin-media', $css);
});


add_filter('attachment_fields_to_save', function (array $post, array $attachment): array {

  $is_on = (isset($attachment['sc_include_photos_page']) && $attachment['sc_include_photos_page'] === '1');

  if ($is_on) {
    update_post_meta((int)$post['ID'], '_sc_include_photos_page', '1');
  } else {
    delete_post_meta((int)$post['ID'], '_sc_include_photos_page');
  }

  return $post;
}, 10, 2);


/**
 * Simple 404 logger
 * Logs to: wp-content/_logs/404.log
 */

add_action('template_redirect', function (): void {
  if (!is_404()) {
    return;
  }

  $dir = WP_CONTENT_DIR . '/_logs';
  if (!is_dir($dir)) {
    @wp_mkdir_p($dir);
  }

  $log_file = $dir . '/404.log';

  $uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
  $ref = isset($_SERVER['HTTP_REFERER']) ? (string) $_SERVER['HTTP_REFERER'] : '';
  $ua  = isset($_SERVER['HTTP_USER_AGENT']) ? (string) $_SERVER['HTTP_USER_AGENT'] : '';

  // Optional: hash IP (privacy-friendly) instead of logging raw IP
  $ip = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '';
  $ip_hash = $ip ? hash('sha256', $ip) : '';

  $line = sprintf(
    "[%s] 404 uri=%s ref=%s ua=%s ip_hash=%s\n",
    gmdate('c'),
    $uri ? esc_url_raw($uri) : '-',
    $ref ? esc_url_raw($ref) : '-',
    $ua ? substr(preg_replace('/\s+/', ' ', $ua), 0, 200) : '-',
    $ip_hash ?: '-'
  );

  @file_put_contents($log_file, $line, FILE_APPEND | LOCK_EX);
}, 1);
