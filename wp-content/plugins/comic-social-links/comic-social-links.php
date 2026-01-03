<?php

/**
 * Plugin Name: Comic Social Links
 * Description: Simple social icon nav for a comic footer. Admin inputs for common platforms (no direct-contact links).
 * Version: 1.0.0
 * Author: @acodebeard
 * License: GPLv2 or later
 * <!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
	exit;
}

final class Comic_Social_Links_Plugin
{
	private const OPTION_KEY = 'comic_social_links';
	private const NONCE_ACTION = 'comic_social_links_save';

	/**
	 * Allowed platforms (no WhatsApp/email/SMS/etc).
	 * Add/remove items here as needed.
	 */
	private static function platforms(): array
	{
		return [
			'facebook' => [
				'label' => 'Facebook',
				'placeholder' => 'https://www.facebook.com/yourpage',
			],
			'instagram' => [
				'label' => 'Instagram',
				'placeholder' => 'https://www.instagram.com/yourhandle',
			],
			'x' => [
				'label' => 'X',
				'placeholder' => 'https://x.com/yourhandle',
			],
			'threads' => [
				'label' => 'Threads',
				'placeholder' => 'https://www.threads.net/@yourhandle',
			],
			'bluesky' => [
				'label' => 'Bluesky',
				'placeholder' => 'https://bsky.app/profile/yourhandle.bsky.social',
			],
			'mastodon' => [
				'label' => 'Mastodon',
				'placeholder' => 'https://mastodon.social/@yourhandle',
			],
			'youtube' => [
				'label' => 'YouTube',
				'placeholder' => 'https://www.youtube.com/@yourchannel',
			],
			'tiktok' => [
				'label' => 'TikTok',
				'placeholder' => 'https://www.tiktok.com/@yourhandle',
			],
			'twitch' => [
				'label' => 'Twitch',
				'placeholder' => 'https://www.twitch.tv/yourchannel',
			],
			'patreon' => [
				'label' => 'Patreon',
				'placeholder' => 'https://www.patreon.com/yourpage',
			],
			'reddit' => [
				'label' => 'Reddit',
				'placeholder' => 'https://www.reddit.com/user/youruser',
			],
			'discord' => [
				'label' => 'Discord (server invite)',
				'placeholder' => 'https://discord.gg/yourinvite',
			],
		];
	}

	public static function init(): void
	{
		add_action('admin_menu', [__CLASS__, 'admin_menu']);
		add_action('admin_init', [__CLASS__, 'register_setting']);

		add_shortcode('comic_social_nav', [__CLASS__, 'shortcode']);

		// Optional: enqueue a tiny front-end style (safe to remove if you prefer theme CSS only)
		add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_styles']);
	}

	public static function register_setting(): void
	{
		register_setting(
			'comic_social_links_group',
			self::OPTION_KEY,
			[
				'type' => 'array',
				'sanitize_callback' => [__CLASS__, 'sanitize_options'],
				'default' => [],
			]
		);
	}

	public static function sanitize_options($value): array
	{
		$platforms = self::platforms();
		$clean = [];

		if (!is_array($value)) {
			return $clean;
		}

		foreach ($platforms as $key => $_meta) {
			$raw = isset($value[$key]) ? (string) $value[$key] : '';
			$raw = trim($raw);

			if ($raw === '') {
				continue;
			}

			// Only allow http/https URLs.
			$san = esc_url_raw($raw, ['http', 'https']);
			if ($san === '') {
				continue;
			}

			$clean[$key] = $san;
		}

		return $clean;
	}

	public static function admin_menu(): void
	{
		add_options_page(
			'Comic Social Links',
			'Comic Social Links',
			'manage_options',
			'comic-social-links',
			[__CLASS__, 'render_settings_page']
		);
	}

	public static function render_settings_page(): void
	{
		if (!current_user_can('manage_options')) {
			return;
		}

		$options = get_option(self::OPTION_KEY, []);
		if (!is_array($options)) {
			$options = [];
		}

		$platforms = self::platforms();

		echo '<div class="wrap">';
		echo '<h1>Comic Social Links</h1>';
		echo '<p>Paste URLs for the platforms you want to show. Blank fields will not appear in the footer nav.</p>';

		echo '<form method="post" action="options.php">';
		settings_fields('comic_social_links_group');

		echo '<div style="max-width: 900px; padding-top: 8px;">';

		foreach ($platforms as $key => $meta) {
			$label = $meta['label'];
			$placeholder = $meta['placeholder'];
			$value = isset($options[$key]) ? (string) $options[$key] : '';

			echo '<div style="display:flex; gap:12px; align-items:center; padding:10px 0; border-bottom:1px solid #e5e5e5;">';

			// Icon
			echo '<div style="width:28px; height:28px; display:flex; align-items:center; justify-content:center;">';
			echo self::icon_svg($key, '28', '28');
			echo '</div>';

			// Label + input
			echo '<div style="flex: 1;">';
			echo '<label for="comic-social-' . esc_attr($key) . '" style="display:block; font-weight:600; margin-bottom:6px;">' . esc_html($label) . '</label>';
			echo '<input id="comic-social-' . esc_attr($key) . '" name="' . esc_attr(self::OPTION_KEY) . '[' . esc_attr($key) . ']" type="url" inputmode="url" style="width:100%; max-width: 720px;" placeholder="' . esc_attr($placeholder) . '" value="' . esc_attr($value) . '" />';
			echo '</div>';

			echo '</div>';
		}

		echo '</div>';

		submit_button('Save Changes');

		echo '</form>';
		echo '</div>';
	}

	public static function shortcode(array $atts = []): string
	{
		$atts = shortcode_atts(
			[
				'class' => '',
				'label' => 'Comic social links',
			],
			$atts,
			'comic_social_nav'
		);

		return self::render_nav((string) $atts['label'], (string) $atts['class']);
	}

	public static function render_nav(string $aria_label = 'Comic social links', string $extra_class = ''): string
	{
		$options = get_option(self::OPTION_KEY, []);
		if (!is_array($options) || empty($options)) {
			return '';
		}

		$platforms = self::platforms();

		$items = [];
		foreach ($platforms as $key => $meta) {
			if (empty($options[$key])) {
				continue;
			}

			$url = (string) $options[$key];
			$label = (string) $meta['label'];

			$items[] = [
				'key' => $key,
				'url' => $url,
				'label' => $label,
			];
		}

		if (empty($items)) {
			return '';
		}

		$classes = 'comic-social-nav';
		if (trim($extra_class) !== '') {
			$classes .= ' ' . trim($extra_class);
		}

		$html  = '<nav class="' . esc_attr($classes) . '" aria-label="' . esc_attr($aria_label) . '">';
		$html .= '<ul class="comic-social-nav-list">';

		foreach ($items as $item) {
			$key = $item['key'];
			$url = $item['url'];
			$label = $item['label'];

			$html .= '<li class="comic-social-nav-item">';
			$html .= '<a class="comic-social-nav-link" href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer" aria-label="' . esc_attr($label) . '">';
			$html .= self::icon_svg($key, '20', '20');
			$html .= '<span class="screen-reader-text">' . esc_html($label) . '</span>';
			$html .= '</a>';
			$html .= '</li>';
		}

		$html .= '</ul>';
		$html .= '</nav>';

		return $html;
	}

	public static function enqueue_styles(): void
	{
		$css = '
			.comic-social-nav-list {
				display: flex;
				flex-wrap:wrap;
				gap: 10px;
				list-style: none;
				padding: 0;
				margin: 0;
				align-items: center;
			}
			.comic-social-nav-link {
				display: inline-flex;
				width: 36px;
				height: 36px;
				align-items: center;
				justify-content: center;
				border-radius: 999px;
				text-decoration: none;
				color: #efefef;
				opacity:.8;
			}
				.comic-social-nav-link:hover,
				.comic-social-nav-link:focus-within {
				opacity:1;
	}
			.comic-social-nav-link svg {
				display: block;
			}
			.comic-social-nav-link:focus-visible {
				outline: 2px solid currentColor;
				outline-offset: 2px;
			}
		';

		wp_register_style('comic-social-links-style', false, [], '1.0.0');
		wp_enqueue_style('comic-social-links-style');
		wp_add_inline_style('comic-social-links-style', $css);
	}

	/**
	 * Minimal inline SVG icons (single-color, inherits currentColor).
	 * Swap these paths anytime; structure stays the same.
	 */
	/**
 * Paste Font Awesome SVG snippets here (the full <svg>...</svg>).
 *
 * Notes:
 * - Use the Brands set for most of these (fa-brands).
 * - Keep the original viewBox from FA (do NOT change it).
 * - You can paste either "SVG" or "SVG + JS" exported SVG; just ensure it’s plain <svg> markup.
 */
private static function fa_svg_map(): array
{
	return [
		'facebook' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path d="M576 320C576 178.6 461.4 64 320 64C178.6 64 64 178.6 64 320C64 440 146.7 540.8 258.2 568.5L258.2 398.2L205.4 398.2L205.4 320L258.2 320L258.2 286.3C258.2 199.2 297.6 158.8 383.2 158.8C399.4 158.8 427.4 162 438.9 165.2L438.9 236C432.9 235.4 422.4 235 409.3 235C367.3 235 351.1 250.9 351.1 292.2L351.1 320L434.7 320L420.3 398.2L351 398.2L351 574.1C477.8 558.8 576 450.9 576 320z"/></svg>',
		'instagram' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M224.3 141a115 115 0 1 0 -.6 230 115 115 0 1 0 .6-230zm-.6 40.4a74.6 74.6 0 1 1 .6 149.2 74.6 74.6 0 1 1 -.6-149.2zm93.4-45.1a26.8 26.8 0 1 1 53.6 0 26.8 26.8 0 1 1 -53.6 0zm129.7 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM399 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z"/></svg>',
		'x' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M357.2 48L427.8 48 273.6 224.2 455 464 313 464 201.7 318.6 74.5 464 3.8 464 168.7 275.5-5.2 48 140.4 48 240.9 180.9 357.2 48zM332.4 421.8l39.1 0-252.4-333.8-42 0 255.3 333.8z"/></svg>',
		'threads' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M331.5 235.7c2.2 .9 4.2 1.9 6.3 2.8 29.2 14.1 50.6 35.2 61.8 61.4 15.7 36.5 17.2 95.8-30.3 143.2-36.2 36.2-80.3 52.5-142.6 53l-.3 0c-70.2-.5-124.1-24.1-160.4-70.2-32.3-41-48.9-98.1-49.5-169.6l0-.5C17 184.3 33.6 127.2 65.9 86.2 102.2 40.1 156.2 16.5 226.4 16l.3 0c70.3 .5 124.9 24 162.3 69.9 18.4 22.7 32 50 40.6 81.7l-40.4 10.8c-7.1-25.8-17.8-47.8-32.2-65.4-29.2-35.8-73-54.2-130.5-54.6-57 .5-100.1 18.8-128.2 54.4-26.2 33.3-39.8 81.5-40.3 143.2 .5 61.7 14.1 109.9 40.3 143.3 28 35.6 71.2 53.9 128.2 54.4 51.4-.4 85.4-12.6 113.7-40.9 32.3-32.2 31.7-71.8 21.4-95.9-6.1-14.2-17.1-26-31.9-34.9-3.7 26.9-11.8 48.3-24.7 64.8-17.1 21.8-41.4 33.6-72.7 35.3-23.6 1.3-46.3-4.4-63.9-16-20.8-13.8-33-34.8-34.3-59.3-2.5-48.3 35.7-83 95.2-86.4 21.1-1.2 40.9-.3 59.2 2.8-2.4-14.8-7.3-26.6-14.6-35.2-10-11.7-25.6-17.7-46.2-17.8l-.7 0c-16.6 0-39 4.6-53.3 26.3l-34.4-23.6c19.2-29.1 50.3-45.1 87.8-45.1l.8 0c62.6 .4 99.9 39.5 103.7 107.7l-.2 .2 .1 0zm-156 68.8c1.3 25.1 28.4 36.8 54.6 35.3 25.6-1.4 54.6-11.4 59.5-73.2-13.2-2.9-27.8-4.4-43.4-4.4-4.8 0-9.6 .1-14.4 .4-42.9 2.4-57.2 23.2-56.2 41.8l-.1 .1z"/></svg>',
		'bluesky' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path d="M407.8 294.7c-3.3-.4-6.7-.8-10-1.3 3.4 .4 6.7 .9 10 1.3zM288 227.1C261.9 176.4 190.9 81.9 124.9 35.3 61.6-9.4 37.5-1.7 21.6 5.5 3.3 13.8 0 41.9 0 58.4S9.1 194 15 213.9c19.5 65.7 89.1 87.9 153.2 80.7 3.3-.5 6.6-.9 10-1.4-3.3 .5-6.6 1-10 1.4-93.9 14-177.3 48.2-67.9 169.9 120.3 124.6 164.8-26.7 187.7-103.4 22.9 76.7 49.2 222.5 185.6 103.4 102.4-103.4 28.1-156-65.8-169.9-3.3-.4-6.7-.8-10-1.3 3.4 .4 6.7 .9 10 1.3 64.1 7.1 133.6-15.1 153.2-80.7 5.9-19.9 15-138.9 15-155.5s-3.3-44.7-21.6-52.9c-15.8-7.1-40-14.9-103.2 29.8-66.1 46.6-137.1 141.1-163.2 191.8z"/></svg>',
		'mastodon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M433 179.1c0-97.2-63.7-125.7-63.7-125.7-62.5-28.7-228.6-28.4-290.5 0 0 0-63.7 28.5-63.7 125.7 0 115.7-6.6 259.4 105.6 289.1 40.5 10.7 75.3 13 103.3 11.4 50.8-2.8 79.3-18.1 79.3-18.1l-1.7-36.9s-36.3 11.4-77.1 10.1c-40.4-1.4-83-4.4-89.6-54-.6-4.6-.9-9.3-.9-13.9 85.6 20.9 158.7 9.1 178.7 6.7 56.1-6.7 105-41.3 111.2-72.9 9.8-49.8 9-121.5 9-121.5zM357.9 304.3l-46.6 0 0-114.2c0-49.7-64-51.6-64 6.9l0 62.5-46.3 0 0-62.5c0-58.5-64-56.6-64-6.9l0 114.2-46.7 0c0-122.1-5.2-147.9 18.4-175 25.9-28.9 79.8-30.8 103.8 6.1l11.6 19.5 11.6-19.5c24.1-37.1 78.1-34.8 103.8-6.1 23.7 27.3 18.4 53 18.4 175l0 0z"/></svg>',
		'youtube' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path d="M549.7 124.1C543.5 100.4 524.9 81.8 501.4 75.5 458.9 64 288.1 64 288.1 64S117.3 64 74.7 75.5C51.2 81.8 32.7 100.4 26.4 124.1 15 167 15 256.4 15 256.4s0 89.4 11.4 132.3c6.3 23.6 24.8 41.5 48.3 47.8 42.6 11.5 213.4 11.5 213.4 11.5s170.8 0 213.4-11.5c23.5-6.3 42-24.2 48.3-47.8 11.4-42.9 11.4-132.3 11.4-132.3s0-89.4-11.4-132.3zM232.2 337.6l0-162.4 142.7 81.2-142.7 81.2z"/></svg>',
		'tiktok' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M448.5 209.9c-44 .1-87-13.6-122.8-39.2l0 178.7c0 33.1-10.1 65.4-29 92.6s-45.6 48-76.6 59.6-64.8 13.5-96.9 5.3-60.9-25.9-82.7-50.8-35.3-56-39-88.9 2.9-66.1 18.6-95.2 40-52.7 69.6-67.7 62.9-20.5 95.7-16l0 89.9c-15-4.7-31.1-4.6-46 .4s-27.9 14.6-37 27.3-14 28.1-13.9 43.9 5.2 31 14.5 43.7 22.4 22.1 37.4 26.9 31.1 4.8 46-.1 28-14.4 37.2-27.1 14.2-28.1 14.2-43.8l0-349.4 88 0c-.1 7.4 .6 14.9 1.9 22.2 3.1 16.3 9.4 31.9 18.7 45.7s21.3 25.6 35.2 34.6c19.9 13.1 43.2 20.1 67 20.1l0 87.4z"/></svg>',
		'twitch' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M359.4 103.5l-38.6 0 0 109.7 38.6 0 0-109.7zM253.2 103l-38.6 0 0 109.8 38.6 0 0-109.8zM89 0l-96.5 91.4 0 329.2 115.8 0 0 91.4 96.5-91.4 77.3 0 173.8-164.6 0-256-366.9 0zM417.3 237.8l-77.2 73.1-77.2 0-67.6 64 0-64-86.9 0 0-274.3 308.9 0 0 201.2z"/></svg>',
		'patreon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M490 153.8c-.1-65.4-51-119-110.7-138.3-74.2-24-172-20.5-242.9 12.9-85.8 40.5-112.8 129.3-113.8 217.8-.8 72.8 6.4 264.4 114.6 265.8 80.3 1 92.3-102.5 129.5-152.3 26.4-35.5 60.5-45.5 102.4-55.9 72-17.8 121.1-74.7 121-150l-.1 0z"/></svg>',
		'reddit' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M0 256C0 114.6 114.6 0 256 0S512 114.6 512 256 397.4 512 256 512L37.1 512c-13.7 0-20.5-16.5-10.9-26.2L75 437C28.7 390.7 0 326.7 0 256zM349.6 153.6c23.6 0 42.7-19.1 42.7-42.7s-19.1-42.7-42.7-42.7c-20.6 0-37.8 14.6-41.8 34-34.5 3.7-61.4 33-61.4 68.4l0 .2c-37.5 1.6-71.8 12.3-99 29.1-10.1-7.8-22.8-12.5-36.5-12.5-33 0-59.8 26.8-59.8 59.8 0 24 14.1 44.6 34.4 54.1 2 69.4 77.6 125.2 170.6 125.2s168.7-55.9 170.6-125.3c20.2-9.6 34.1-30.2 34.1-54 0-33-26.8-59.8-59.8-59.8-13.7 0-26.3 4.6-36.4 12.4-27.4-17-62.1-27.7-100-29.1l0-.2c0-25.4 18.9-46.5 43.4-49.9 4.4 18.8 21.3 32.8 41.5 32.8l.1 .2zM177.1 246.9c16.7 0 29.5 17.6 28.5 39.3s-13.5 29.6-30.3 29.6-31.4-8.8-30.4-30.5 15.4-38.3 32.1-38.3l.1-.1zm190.1 38.3c1 21.7-13.7 30.5-30.4 30.5s-29.3-7.9-30.3-29.6 11.8-39.3 28.5-39.3 31.2 16.6 32.1 38.3l.1 .1zm-48.1 56.7c-10.3 24.6-34.6 41.9-63 41.9s-52.7-17.3-63-41.9c-1.2-2.9 .8-6.2 3.9-6.5 18.4-1.9 38.3-2.9 59.1-2.9s40.7 1 59.1 2.9c3.1 .3 5.1 3.6 3.9 6.5z"/></svg>',
		'discord' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path d="M492.5 69.8c-.2-.3-.4-.6-.8-.7-38.1-17.5-78.4-30-119.7-37.1-.4-.1-.8 0-1.1 .1s-.6 .4-.8 .8c-5.5 9.9-10.5 20.2-14.9 30.6-44.6-6.8-89.9-6.8-134.4 0-4.5-10.5-9.5-20.7-15.1-30.6-.2-.3-.5-.6-.8-.8s-.7-.2-1.1-.2c-41.3 7.1-81.6 19.6-119.7 37.1-.3 .1-.6 .4-.8 .7-76.2 113.8-97.1 224.9-86.9 334.5 0 .3 .1 .5 .2 .8s.3 .4 .5 .6c44.4 32.9 94 58 146.8 74.2 .4 .1 .8 .1 1.1 0s.7-.4 .9-.7c11.3-15.4 21.4-31.8 30-48.8 .1-.2 .2-.5 .2-.8s0-.5-.1-.8-.2-.5-.4-.6-.4-.3-.7-.4c-15.8-6.1-31.2-13.4-45.9-21.9-.3-.2-.5-.4-.7-.6s-.3-.6-.3-.9 0-.6 .2-.9 .3-.5 .6-.7c3.1-2.3 6.2-4.7 9.1-7.1 .3-.2 .6-.4 .9-.4s.7 0 1 .1c96.2 43.9 200.4 43.9 295.5 0 .3-.1 .7-.2 1-.2s.7 .2 .9 .4c2.9 2.4 6 4.9 9.1 7.2 .2 .2 .4 .4 .6 .7s.2 .6 .2 .9-.1 .6-.3 .9-.4 .5-.6 .6c-14.7 8.6-30 15.9-45.9 21.8-.2 .1-.5 .2-.7 .4s-.3 .4-.4 .7-.1 .5-.1 .8 .1 .5 .2 .8c8.8 17 18.8 33.3 30 48.8 .2 .3 .6 .6 .9 .7s.8 .1 1.1 0c52.9-16.2 102.6-41.3 147.1-74.2 .2-.2 .4-.4 .5-.6s.2-.5 .2-.8c12.3-126.8-20.5-236.9-86.9-334.5zm-302 267.7c-29 0-52.8-26.6-52.8-59.2s23.4-59.2 52.8-59.2c29.7 0 53.3 26.8 52.8 59.2 0 32.7-23.4 59.2-52.8 59.2zm195.4 0c-29 0-52.8-26.6-52.8-59.2s23.4-59.2 52.8-59.2c29.7 0 53.3 26.8 52.8 59.2 0 32.7-23.2 59.2-52.8 59.2z"/></svg>',
	];
}

/**
 * Normalize a pasted <svg> snippet:
 * - enforce width/height from caller
 * - enforce aria-hidden/focusable
 * - optionally force fill="currentColor" so icons inherit text color
 */
private static function normalize_svg(string $svg, string $w, string $h): string
{
	$svg = trim($svg);

	if ($svg === '' || stripos($svg, '<svg') === false) {
		return '';
	}

	// Ensure it starts with <svg ...>
	if (stripos($svg, '<svg') !== 0) {
		$pos = stripos($svg, '<svg');
		if ($pos !== false) {
			$svg = substr($svg, $pos);
		}
	}

	// Replace/insert width + height attributes.
	$svg = preg_replace('/\swidth="[^"]*"/i', '', $svg);
	$svg = preg_replace('/\sheight="[^"]*"/i', '', $svg);

	// Replace/insert aria-hidden + focusable.
	$svg = preg_replace('/\saria-hidden="[^"]*"/i', '', $svg);
	$svg = preg_replace('/\sfocusable="[^"]*"/i', '', $svg);
	$svg = preg_replace('/\srole="[^"]*"/i', '', $svg);

	// (Optional) Force fill to currentColor so it inherits. Comment out if you want original fills.
	$svg = preg_replace('/\sfill="[^"]*"/i', '', $svg);

	$inject = ' width="' . esc_attr($w) . '" height="' . esc_attr($h) . '" role="img" aria-hidden="true" focusable="false" fill="currentColor"';

	// Inject our attributes right after "<svg"
	$svg = preg_replace('/<svg\b/i', '<svg' . $inject, $svg, 1);

	return $svg;
}

private static function icon_svg(string $key, string $w, string $h): string
{
	$map = self::fa_svg_map();

	if (!isset($map[$key])) {
		return '';
	}

	$svg = (string) $map[$key];

	// If you left a placeholder string, treat as empty.
	if ($svg === '' || strpos($svg, 'PASTE_') !== false) {
		return '';
	}

	return self::normalize_svg($svg, $w, $h);
}
}

Comic_Social_Links_Plugin::init();

/**
 * Optional helper for themes:
 * echo comic_social_links_nav();
 */
function comic_social_links_nav(string $aria_label = 'Comic social links', string $extra_class = ''): string
{
	return Comic_Social_Links_Plugin::render_nav($aria_label, $extra_class);
}
