<?php
$date   = get_post_meta(get_the_ID(), 'event_date', true);
$venue  = get_post_meta(get_the_ID(), 'venue', true);
$city   = get_post_meta(get_the_ID(), 'city', true);
$status = get_post_meta(get_the_ID(), 'status', true) ?: 'scheduled';

$is_cancel = ($status === 'canceled');
?>
<li style="margin:0 0 .5rem;<?php echo $is_cancel ? 'opacity:.7;text-decoration:line-through;' : ''; ?>">
  <a href="<?php the_permalink(); ?>"><strong><?php the_title(); ?></strong></a>
  <?php if ($date): ?>
    — <span><?php echo esc_html( date_i18n(get_option('date_format'), strtotime($date)) ); ?></span>
  <?php endif; ?>
  <?php if ($venue) echo ' — ' . esc_html($venue); ?>
  <?php if ($city)  echo ' — ' . esc_html($city); ?>
  <?php if ($is_cancel) echo ' <em style="color:#b00;">(Canceled)</em>'; ?>
</li>
