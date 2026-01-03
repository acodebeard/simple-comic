<?php get_header(); the_post();
$date   = get_post_meta(get_the_ID(), 'event_date', true);
$venue  = get_post_meta(get_the_ID(), 'venue', true);
$city   = get_post_meta(get_the_ID(), 'city', true);
$status = get_post_meta(get_the_ID(), 'status', true) ?: 'scheduled';
$is_canceled = ($status === 'canceled');

$event = [
  '@context' => 'https://schema.org',
  '@type'    => 'Event',
  'name'     => get_the_title(),
  'startDate'=> $date ?: '',
  'eventStatus' => $is_canceled ? 'https://schema.org/EventCancelled' : 'https://schema.org/EventScheduled',
  'url'      => get_permalink(),
  'location' => [
    '@type' => 'Place',
    'name'  => $venue ?: 'TBD',
    'address' => $city ?: '',
  ],
  'performer' => ['@type'=>'PerformingGroup','name'=>'Chewbacca'],
];
?>
<article <?php post_class(); ?> style="margin-bottom:2rem;">
  <header>
    <h1 style="margin-bottom:.25rem;">
      <?php the_title(); ?>
      <?php if ($is_canceled): ?>
        <em style="color:#b00;font-size:.7em;margin-left:.5rem;">CANCELED</em>
      <?php endif; ?>
    </h1>
    <p style="margin:0 0 1rem;opacity:.8;">
      <?php if ($date)  echo '<strong>'.esc_html(date_i18n(get_option('date_format'), strtotime($date))).'</strong>'; ?>
      <?php if ($venue) echo ' — '.esc_html($venue); ?>
      <?php if ($city)  echo ' — '.esc_html($city); ?>
      <?php if (!$is_canceled) echo ' — Scheduled'; ?>
    </p>
  </header>

  <div class="entry-content"><?php the_content(); ?></div>

  <footer style="margin-top:2rem;border-top:1px solid #ddd;padding-top:1rem;">
    <nav class="post-nav" aria-label="Tour date navigation" style="display:flex;gap:1rem;flex-wrap:wrap;">
      <span><?php previous_post_link('%link', '« Previous'); ?></span>
      <span><?php next_post_link('%link', 'Next »'); ?></span>
    </nav>
  </footer>
</article>

<script type="application/ld+json">
<?php echo wp_json_encode($event, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT); ?>
</script>
<?php get_footer(); ?>
