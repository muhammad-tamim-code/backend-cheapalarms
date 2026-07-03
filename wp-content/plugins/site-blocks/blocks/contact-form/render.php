<?php
/**
 * Contact Form block render.
 *
 * @package Site_Blocks
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$form_title    = isset( $attributes['formTitle'] ) ? $attributes['formTitle'] : __( 'Send a message', 'site-blocks' );
$submit_label  = isset( $attributes['submitLabel'] ) ? $attributes['submitLabel'] : __( 'Send message', 'site-blocks' );
$form_status   = site_blocks_get_form_status();
$form_id       = 'site-contact-form';
$is_success    = 'sent' === $form_status;
$is_error      = in_array( $form_status, array( 'error', 'invalid', 'failed' ), true );

$status_messages = array(
	'sent'    => __( 'Your message was sent. We will reply shortly.', 'site-blocks' ),
	'invalid' => __( 'Please complete all required fields with a valid email address.', 'site-blocks' ),
	'failed'  => __( 'The message could not be sent. Please try again or email us directly.', 'site-blocks' ),
	'error'   => __( 'Something went wrong. Please try again.', 'site-blocks' ),
);

$status_message = isset( $status_messages[ $form_status ] ) ? $status_messages[ $form_status ] : '';
?>
<section class="contact-form-wrap" aria-labelledby="contact-form-heading">
	<div class="contact-form-card">
		<h2 id="contact-form-heading" class="contact-form-card__title"><?php echo esc_html( $form_title ); ?></h2>

		<?php if ( $status_message ) : ?>
			<div
				class="contact-form-status <?php echo $is_success ? 'contact-form-status--success' : 'contact-form-status--error'; ?>"
				role="status"
				aria-live="polite"
			>
				<?php echo esc_html( $status_message ); ?>
			</div>
		<?php endif; ?>

		<form
			id="<?php echo esc_attr( $form_id ); ?>"
			class="contact-form<?php echo $is_success ? ' contact-form--success' : ''; ?><?php echo $is_error ? ' contact-form--error' : ''; ?>"
			method="post"
			action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
			novalidate
		>
			<input type="hidden" name="action" value="site_contact_form">
			<input type="hidden" name="site_contact_action" value="submit">
			<?php wp_nonce_field( 'site_contact_form', 'site_contact_nonce' ); ?>

			<div class="contact-form__field">
				<label class="contact-form__label" for="contact_name">
					<?php esc_html_e( 'Name', 'site-blocks' ); ?>
					<span class="contact-form__required" aria-hidden="true">*</span>
				</label>
				<div class="contact-form__control">
					<input
						type="text"
						id="contact_name"
						name="contact_name"
						class="contact-form__input"
						required
						autocomplete="name"
						aria-required="true"
						placeholder="<?php esc_attr_e( 'Your name', 'site-blocks' ); ?>"
					>
				</div>
				<p class="contact-form__helper" aria-live="polite"></p>
			</div>

			<div class="contact-form__field">
				<label class="contact-form__label" for="contact_email">
					<?php esc_html_e( 'Email', 'site-blocks' ); ?>
					<span class="contact-form__required" aria-hidden="true">*</span>
				</label>
				<div class="contact-form__control">
					<input
						type="email"
						id="contact_email"
						name="contact_email"
						class="contact-form__input"
						required
						autocomplete="email"
						inputmode="email"
						aria-required="true"
						placeholder="<?php esc_attr_e( 'you@company.com', 'site-blocks' ); ?>"
					>
				</div>
				<p class="contact-form__helper" aria-live="polite"></p>
			</div>

			<div class="contact-form__field">
				<label class="contact-form__label" for="contact_subject">
					<?php esc_html_e( 'Subject', 'site-blocks' ); ?>
				</label>
				<div class="contact-form__control">
					<input
						type="text"
						id="contact_subject"
						name="contact_subject"
						class="contact-form__input"
						autocomplete="off"
						placeholder="<?php esc_attr_e( 'What is this regarding?', 'site-blocks' ); ?>"
					>
				</div>
				<p class="contact-form__helper" aria-live="polite"></p>
			</div>

			<div class="contact-form__field">
				<label class="contact-form__label" for="contact_message">
					<?php esc_html_e( 'Message', 'site-blocks' ); ?>
					<span class="contact-form__required" aria-hidden="true">*</span>
				</label>
				<div class="contact-form__control">
					<textarea
						id="contact_message"
						name="contact_message"
						class="contact-form__input contact-form__textarea"
						required
						rows="6"
						aria-required="true"
						placeholder="<?php esc_attr_e( 'Tell us what you need.', 'site-blocks' ); ?>"
					></textarea>
				</div>
				<p class="contact-form__helper" aria-live="polite"></p>
			</div>

			<div class="contact-form__actions">
				<button type="submit" class="contact-form__submit">
					<span class="contact-form__submit-text"><?php echo esc_html( $submit_label ); ?></span>
					<span class="contact-form__submit-spinner" aria-hidden="true"></span>
				</button>
			</div>
		</form>
	</div>
</section>

<script>
(function () {
	var form = document.getElementById('<?php echo esc_js( $form_id ); ?>');
	if (!form) return;

	form.addEventListener('submit', function () {
		form.setAttribute('data-state', 'loading');
		form.querySelector('.contact-form__submit').disabled = true;
	});

	var fields = form.querySelectorAll('.contact-form__input');
	fields.forEach(function (field) {
		field.addEventListener('blur', function () {
			validateField(field);
		});
		field.addEventListener('input', function () {
			if (field.dataset.touched === 'true') {
				validateField(field);
			}
		});
	});

	function validateField(field) {
		field.dataset.touched = 'true';
		var helper = field.closest('.contact-form__field').querySelector('.contact-form__helper');
		var valid = field.checkValidity();

		if (!valid) {
			field.setAttribute('aria-invalid', 'true');
			field.setAttribute('data-state', 'error');
			if (field.validity.valueMissing) {
				helper.textContent = '<?php echo esc_js( __( 'This field is required.', 'site-blocks' ) ); ?>';
			} else if (field.validity.typeMismatch) {
				helper.textContent = '<?php echo esc_js( __( 'Enter a valid email address.', 'site-blocks' ) ); ?>';
			}
			helper.classList.add('contact-form__helper--error');
		} else {
			field.removeAttribute('aria-invalid');
			field.setAttribute('data-state', 'success');
			helper.textContent = '';
			helper.classList.remove('contact-form__helper--error');
		}
	}
})();
</script>
