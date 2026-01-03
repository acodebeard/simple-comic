<?php

/**
 * Header template for the Simple Comic theme
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="hi">
  <link href="<?php echo esc_url(get_theme_file_uri('assets/img/bricks.webp')); ?>" rel="preload" as="image" type="image/webp">

  <!-- super critical styles -->
  <style>
    :root {
      --font-base: "Jost", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      -sl-font-headline: "Belanosima", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      --sl-font-body: var(--font-base);
      --font-heading: var(-sl-font-headline);
      --sl-font-accent: "Belanosima", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      --color-bg: #f7f7f9;
      --color-surface: #ffffff;
      --color-border: #d7d7dd;
      --color-text: #111217;
      --color-text-muted: #6b6d76;
      --color-accent: #c93bff;
      /* placeholder accent, easy to change later */
      --site-width: 1920px;
      --inner-width: 980px;
      --mobile-breakpoint-m: 980px;
      --mobile-breakpoint: 840px;
      --global-transition: 222ms cubic-bezier(0, 0, 0.2, 1);
      --gap-small: 8px;
      --gap-medium: 16px;
      --gap-large: 24px;
      --font-size-small: .9rem;
      --font-size-medium: 1.2rem;
      --font-size-large: 1.5rem;
      --font-size-xlarge: 2.2rem;
    }

    #main-content {
      display: block;
      background: url(<?php echo esc_url(get_theme_file_uri('assets/img/bricks.webp')); ?>) top center no-repeat;
      background-size: cover;
      background-attachment: fixed;
      min-height: 90dvh;
      width: 100%;
      position: relative;
    }

    main#main-content:before {
      content: "";
      display: block;
      background: linear-gradient(180deg, #000000f7 50%, #000000f2 85%, #000000d9);
      position: absolute;
      z-index: 0;
      width: 100%;
      height: 100%;
      background-attachment: fixed;
      pointer-events: none;
    }
  </style><?php
          $critical_css_path = get_theme_file_path('assets/css/critical.min.css');

          if (is_readable($critical_css_path)) {
            $css = file_get_contents($critical_css_path);

            // Fix relative font URLs when CSS is inlined.
            // critical.min.css is in /assets/css/, so "../fonts/" should become "/assets/fonts/" (absolute URL).
            $fonts_base = get_theme_file_uri('assets/fonts/');

            $css = str_replace('url("../fonts/', 'url("' . $fonts_base, $css);
            $css = str_replace("url('../fonts/", "url('" . $fonts_base, $css);
            $css = str_replace('url(../fonts/',  'url('  . $fonts_base, $css);

            echo "<style id=\"critical-css\">\n";
            echo $css;
            echo "\n</style>\n";
          } else {
          ?>
    <link rel="stylesheet"
      href="<?php echo esc_url(get_theme_file_uri('assets/css/critical.min.css')); ?>"
      type="text/css">
  <?php
    }
  /**
   * WordPress will output:
   * - <title> (because you added add_theme_support('title-tag'))
   * - Any enqueued styles/scripts that belong in <head>
   * - Other plugin/theme hooks
   */
  wp_head();
  ?>
</head>

<body <?php body_class([
        'setlist-style-back-room-blue',
      ]); ?>>
  <?php
  // For accessibility + plugins that hook into body_open
  if (function_exists('wp_body_open')) {
    wp_body_open();
  }
  ?>
  <div class="site-wrapper">
    <a class="skip-link" href="#main-content">Skip to main content</a>

    <header class="site-header">
      <div class="site-header-inner">
        <div class="site-branding">
          <?php if (has_custom_logo()) : ?>
            <?php the_custom_logo(); ?>
          <?php else : ?>
            <h1 class="hero-title">
              <a href="<?php echo esc_url(home_url('/')); ?>" class="site-title">
                <?php echo esc_html(get_bloginfo('name')); ?>
              </a>
            </h1>
          <?php endif; ?>

          <?php if (get_bloginfo('description')) : ?>
            <p class="site-tagline"><?php bloginfo('description'); ?></p>
          <?php endif; ?>
        </div>

        <button aria-label="Open Main Navigation" class="menu-toggle" aria-expanded="false" aria-controls="primary-nav" type="button"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
            <path d="M96 160C96 142.3 110.3 128 128 128L512 128C529.7 128 544 142.3 544 160C544 177.7 529.7 192 512 192L128 192C110.3 192 96 177.7 96 160zM96 320C96 302.3 110.3 288 128 288L512 288C529.7 288 544 302.3 544 320C544 337.7 529.7 352 512 352L128 352C110.3 352 96 337.7 96 320zM544 480C544 497.7 529.7 512 512 512L128 512C110.3 512 96 497.7 96 480C96 462.3 110.3 448 128 448L512 448C529.7 448 544 462.3 544 480z" />
          </svg></button>

        <nav class="site-nav" id="primary-nav" aria-label="Main navigation">
          <?php
          wp_nav_menu([
            'theme_location' => 'primary',
            'container'      => false,
            'menu_class'     => 'menu',
            'fallback_cb'    => false,
          ]);
          ?>
        </nav>
      </div>
    </header>

    <main id="main-content" class="site-main">