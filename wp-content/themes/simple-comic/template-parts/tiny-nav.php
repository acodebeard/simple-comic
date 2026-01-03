<?php

/**
 * Media nav that reuses URLs from the main WP menu.
 * - Finds menu items by label (Videos / Photos / Press Kit)
 * - Only renders items that exist in that menu
 * - Uses <img> placeholders for icons
 */

/**
 * Try to get menu items from a theme location (preferred).
 * Falls back to a menu by name if needed.
 */
function viazen_get_menu_items_from_location_or_name(array $location_candidates, array $menu_name_candidates = []): array
{
  $locations = get_nav_menu_locations();

  foreach ($location_candidates as $loc) {
    if (!empty($locations[$loc])) {
      $menu_id = (int) $locations[$loc];
      $items = wp_get_nav_menu_items($menu_id);
      if (is_array($items)) {
        return $items;
      }
    }
  }

  foreach ($menu_name_candidates as $name) {
    $menu_obj = wp_get_nav_menu_object($name);
    if ($menu_obj && !is_wp_error($menu_obj)) {
      $items = wp_get_nav_menu_items((int) $menu_obj->term_id);
      if (is_array($items)) {
        return $items;
      }
    }
  }

  return [];
}

/**
 * Find a menu item URL by its visible label/title (case-insensitive).
 */
function viazen_find_menu_item_url_by_label(array $menu_items, string $label): string
{
  $needle = mb_strtolower(trim($label));

  foreach ($menu_items as $item) {
    // $item->title is what you see in the menu editor (Navigation Label)
    $title = isset($item->title) ? mb_strtolower(trim(wp_strip_all_tags((string) $item->title))) : '';
    if ($title === $needle && !empty($item->url)) {
      return (string) $item->url;
    }
  }

  return '';
}

/**
 * 1) Pull the items from your "main nav".
 * Replace these with your actual location slug if you know it.
 * Common defaults: 'primary', 'menu-1', 'main-menu'
 */
$menu_items = viazen_get_menu_items_from_location_or_name(
  ['primary', 'menu-1', 'main-menu'],
  ['Main Menu', 'Primary Menu']
);

/**
 * 2) Map the labels you want to reuse.
 * Icon paths are placeholders—swap to your real files.
 */
$wanted = [
  [
    'key'   => 'videos',
    'label' => 'Videos',
    'icon'  => get_template_directory_uri() . '/assets/img/icon-camera.png',
  ],
  [
    'key'   => 'photos',
    'label' => 'Photos',
    'icon'  => get_template_directory_uri() . '/assets/img/icon-photo.png',
  ],
  [
    'key'   => 'press-kit',
    'label' => 'Press Kit',
    'icon'  => get_template_directory_uri() . '/assets/img/icon-press-kit.png',
  ],
];

/**
 * 3) Resolve URLs from the menu, filter out missing ones.
 */
$items = [];

foreach ($wanted as $w) {
  $url = viazen_find_menu_item_url_by_label($menu_items, $w['label']);
  if ($url !== '') {
    $items[] = [
      'key'   => $w['key'],
      'label' => $w['label'],
      'url'   => $url,
      'icon'  => $w['icon'],
    ];
  }
}

if (!empty($items)) :
?>
  <nav class="media-nav" aria-label="Media links">
    <ul class="media-nav-list">
      <?php foreach ($items as $item) : ?>
        <li class="media-nav-item media-nav-item--<?php echo esc_attr($item['key']); ?>">
          <a class="media-nav-link" href="<?php echo esc_url($item['url']); ?>">
            <img
              class="media-nav-icon"
              src="<?php echo esc_url($item['icon']); ?>"
              alt=""
              aria-hidden="true"
              loading="lazy"
              width="28"
              height="28">
            <span class="media-nav-text"><?php echo esc_html($item['label']); ?></span>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </nav>
<?php endif; ?>