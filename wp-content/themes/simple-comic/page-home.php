<?php get_header(); the_post(); ?>
<section class="hero" style="text-align:center;">
  <?php if (has_post_thumbnail()) the_post_thumbnail('large',['style'=>'max-width:280px;border-radius:9999px;']); ?>
  <h1 style="margin-top:1rem;"><?php bloginfo('name'); ?></h1>
  <p class="subheadline">Intergalactic stand-up. Loud, clean, occasionally translated.</p>
  <nav style="display:flex;gap:.75rem;justify-content:center;flex-wrap:wrap;margin-top:1rem;">
    <a class="btn" href="<?php echo esc_url( site_url('/calendar/') ); ?>">See Dates</a>
    <a class="btn" href="<?php echo esc_url( site_url('/bio/') ); ?>">Bio</a>
    <a class="btn" href="<?php echo esc_url( site_url('/photos/') ); ?>">Photos</a>
    <a class="btn" href="<?php echo esc_url( site_url('/videos/') ); ?>">Videos</a>
    <a class="btn" href="<?php echo esc_url( site_url('/booking/') ); ?>">Booking</a>
  </nav>
</section>
<?php get_footer(); ?>
