<?php get_header(); ?>

<section>
  <h2>Blog / News</h2>
  <?php if (have_posts()): ?>
    <?php while (have_posts()): the_post(); ?>
      <article id="post-<?php the_ID(); ?>" <?php post_class(); ?> style="margin-bottom:2rem;">
        <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
        <p><small><?php echo get_the_date(); ?></small></p>
        <div class="entry">
          <?php the_excerpt(); ?>
        </div>
      </article>
    <?php endwhile; ?>

    <nav class="pagination">
      <?php
        the_posts_pagination([
          'prev_text' => '&laquo; Previous',
          'next_text' => 'Next &raquo;',
        ]);
      ?>
    </nav>

  <?php else: ?>
    <p>No posts found.</p>
  <?php endif; ?>
</section>

<?php get_footer(); ?>
