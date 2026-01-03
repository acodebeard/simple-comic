<?php get_header(); the_post(); ?>
<article <?php post_class(); ?>>
  <h1><?php the_title(); ?></h1>
  <div class="entry-content">
    <?php echo do_shortcode('[booking_form]'); ?>
  </div>
</article>
<?php get_footer(); ?>
