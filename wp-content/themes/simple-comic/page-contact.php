<?php

/**
 * Template Name: Contact
 */

if (!defined('ABSPATH')) {
  exit;
}

get_header();
?>


<header class="page-header contact-header">
  <h1 class="page-title"><?php the_title(); ?></h1>
  <p class="page-intro">
    For booking inquiries, press, or general questions, use the form below.
    Detailed booking information is available in the press kit.
  </p>
</header>

<section class="contact-content">
  <?php
  // Optional: show page content above the form
  if (have_posts()) :
    while (have_posts()) : the_post();
      the_content();
    endwhile;
  endif;
  ?>

  <?php
  // Simple success/error messaging via query string
  $status = isset($_GET['contact']) ? sanitize_text_field($_GET['contact']) : '';
  if ($status === 'sent') : ?>
    <p class="contact-message contact-message-success">
      Thanks for reaching out. Your message has been sent.
    </p>
  <?php elseif ($status === 'error') : ?>
    <p class="contact-message contact-message-error">
      Sorry, something went wrong. Please try again later.
    </p>
  <?php endif; ?>
  <?php
  /*
  <form class="contact-form"
    action="<?php //echo esc_url(admin_url('admin-post.php')); ?>"
    method="post"
    novalidate>
    <?php wp_nonce_field('sc_contact_form', 'sc_contact_nonce'); ?>
    */ ?>
  <!-- demo only -->
  <style>
    form.contact-form,
    form.contact-form *{
      pointer-events: none;
    }

    form.contact-form:before {
      content: "Demo Form";
      display: flex;
      position: absolute;
      font-size: 3rem;
      inset: 0;
      align-items: center;
      justify-content: center;
      rotate: -25deg;
      text-shadow: 0px 1px 31px #ffaf00;
      z-index: 1000;
      pointer-events: none;
    }
  </style>
  <!-- demo header -->
  <form class="contact-form"
    action="#"
    method="post"
    novalidate
    data-demo="1">
    <input type="hidden" name="action" value="sc_contact">

    <!-- Honeypot for bots -->
    <div class="contact-field contact-field-honeypot" aria-hidden="true">
      <label for="cf-website">Leave this field empty:
        <input id="cf-website" type="text" name="website" tabindex="-1" autocomplete="off" required value="website">
      </label>
    </div>

    <fieldset class="contact-fieldset">
      <legend class="">Get in touch</legend>

      <div class="contact-field">
        <label for="cf-name">
          Name <span class="contact-required-indicator" aria-hidden="true">*</span>
        </label>
        <input
          id="cf-name"
          name="name"
          type="text"
          required
          autocomplete="name">
      </div>

      <div class="contact-field">
        <label for="cf-email">
          Email <span class="contact-required-indicator" aria-hidden="true">*</span>
        </label>
        <input
          id="cf-email"
          name="email"
          type="email"
          required
          autocomplete="email">
      </div>

      <div class="contact-field">
        <label for="cf-subject">
          Subject <span class="contact-required-indicator" aria-hidden="true">*</span>
        </label>
        <select name="subject" id="cf-subject" required>
          <option value="booking">Booking / Events</option>
          <option value="press">Press / Media</option>
          <option value="licensing">Licensing / Permissions</option>
          <option value="prints">Prints / Merch</option>
          <option value="website-issue">Website Issue / Bug</option>
          <option value="feedback">Feedback / Suggestion</option>
          <option value="other">Other</option>
        </select>

      </div>

      <div class="contact-field">
        <label for="cf-message">
          Message <span class="contact-required-indicator" aria-hidden="true">*</span>
        </label>
        <textarea
          id="cf-message"
          name="message"
          rows="6"
          required></textarea>
      </div>
    </fieldset>

    <p class="contact-required-note">
      <span class="contact-required-indicator" aria-hidden="true">*</span>
      Required fields
    </p>

    <button type="submit" class="btn btn-primary contact-submit">
      Send Message
    </button>
  </form>
  <script>
    (function() {
      const form = document.querySelector('form.contact-form[data-demo="1"]');
      if (!form) return;

      form.addEventListener('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        alert('Just a demo form!');
      }, true);
    })();
  </script>

</section>


<?php
get_footer();
