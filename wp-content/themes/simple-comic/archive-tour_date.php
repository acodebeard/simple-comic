<?php get_header(); ?>
<h1>All Dates</h1>
<ul class="date-list" style="list-style:none;padding-left:0">
  <?php if (have_posts()): while (have_posts()): the_post();
    get_template_part('template-parts/event','row');
  endwhile; else: ?>
    <li>No dates found.</li>
  <?php endif; ?>
</ul>
<?php the_posts_pagination(['prev_text'=>'« Prev','next_text'=>'Next »']); ?>
<?php get_footer(); ?>
