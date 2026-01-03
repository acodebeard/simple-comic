<?php

/**
 * Footer template for the Simple Comic theme
 */
?>
</main>

<footer class="site-footer">
  <div class="site-footer-inner">
    <div class="site-footer-col">
      <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?></p>
    </div>

    <div class="site-footer-col">
      <?php
      wp_nav_menu([
        'theme_location' => 'footer',
        'container'      => false,
        'menu_class'     => 'footer-menu',
        'fallback_cb'    => false,
      ]);
      ?>
    </div>

    <div class="site-footer-col site-footer-col-right">
      <?php echo comic_social_links_nav('Comic social links');
?>
    </div>
  </div>
</footer>
<div class="lightbox" id="photo-lightbox" hidden>
  <div class="lightbox-backdrop" data-lightbox-close></div>
  <div class="lightbox-inner" role="dialog" aria-modal="true" aria-label="Image preview">
    <button type="button" class="lightbox-close" data-lightbox-close aria-label="Close image">
      ×
    </button>
    <img src="" alt="" class="lightbox-image">
  </div>
</div>
</div><!-- .site-wrapper -->
<?php wp_footer(); ?>
<!-- bring me the hydrospanner -->
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const lightbox = document.getElementById('photo-lightbox');
    if (!lightbox) return;

    const lightboxImage = lightbox.querySelector('.lightbox-image');
    const closeButtons = lightbox.querySelectorAll('[data-lightbox-close]');
    const triggers = Array.from(document.querySelectorAll('.page-photos .photo-lightbox-trigger'));

    if (!triggers.length) return;

    function openLightbox(href, altText) {
      lightboxImage.src = href;
      lightboxImage.alt = altText || '';
      lightbox.hidden = false;
      lightbox.classList.add('is-open');
      document.body.classList.add('lightbox-open');
      lightbox.querySelector('.lightbox-close').focus();
    }

    function closeLightbox() {
      lightbox.classList.remove('is-open');
      document.body.classList.remove('lightbox-open');
      lightbox.hidden = true;
      lightboxImage.src = '';
      lightboxImage.alt = '';
    }

    // Click on thumbnails
    triggers.forEach(trigger => {
      trigger.addEventListener('click', (event) => {
        event.preventDefault();
        const href = trigger.getAttribute('href');
        const img = trigger.querySelector('img');
        const altText = img ? img.alt : '';
        openLightbox(href, altText);
      });
    });

    // Close on backdrop / button
    closeButtons.forEach(btn => {
      btn.addEventListener('click', (event) => {
        event.preventDefault();
        closeLightbox();
      });
    });

    // Close on Escape
    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && lightbox.classList.contains('is-open')) {
        closeLightbox();
      }
    });
  });
</script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    var toggle = document.querySelector('.menu-toggle');
    var nav = document.getElementById('primary-nav');

    if (!toggle || !nav) return;

    function setMenuOpen(isOpen) {
      if (isOpen) {
        nav.classList.add('is-open');
        toggle.setAttribute('aria-expanded', 'true');
        document.body.classList.add('nav-open'); // lock scroll
      } else {
        nav.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('nav-open'); // unlock scroll
      }
    }

    // Toggle on button click
    toggle.addEventListener('click', function(event) {
      event.stopPropagation();
      var isOpen = !nav.classList.contains('is-open');
      setMenuOpen(isOpen);
    });

    // Close when clicking outside nav + toggle
    document.addEventListener('click', function(event) {
      if (!nav.classList.contains('is-open')) return;

      var clickInsideNav = nav.contains(event.target);
      var clickOnToggle = toggle.contains(event.target);

      if (!clickInsideNav && !clickOnToggle) {
        setMenuOpen(false);
      }
    });

    // Close on Escape
    document.addEventListener('keydown', function(event) {
      if (event.key === 'Escape' || event.key === 'Esc') {
        if (nav.classList.contains('is-open')) {
          setMenuOpen(false);
          toggle.focus();
        }
      }
    });
  });
</script>

</body>

</html>