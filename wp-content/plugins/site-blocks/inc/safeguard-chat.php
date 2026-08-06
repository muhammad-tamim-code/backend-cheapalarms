<?php
/**
 * Safeguard AI chat widget (DeepSeek via cheapalarms-plugin REST).
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the floating chat widget should load on this request.
 */
function site_blocks_should_show_chat_widget(): bool {
	if ( is_admin() || wp_doing_ajax() || wp_is_json_request() ) {
		return false;
	}

	return true;
}

/**
 * Whether DeepSeek is configured in cheapalarms-plugin secrets.
 */
function site_blocks_is_deepseek_chat_available(): bool {
	if ( defined( 'CA_PLUGIN_PATH' ) ) {
		$secrets = CA_PLUGIN_PATH . 'config/secrets.php';
		if ( is_readable( $secrets ) ) {
			$data = include $secrets;
			if ( is_array( $data ) && ! empty( $data['deepseek_api_key'] ) ) {
				return true;
			}
		}
	}

	$env = getenv( 'CA_DEEPSEEK_API_KEY' );

	return is_string( $env ) && $env !== '';
}

/**
 * Whether OTP runs in demo mode (no SMS, any 6 digits accepted).
 */
function site_blocks_otp_is_demo(): bool {
	if ( ! defined( 'CA_PLUGIN_PATH' ) ) {
		return true;
	}

	$secrets = CA_PLUGIN_PATH . 'config/secrets.php';
	if ( ! is_readable( $secrets ) ) {
		return true;
	}

	$data = include $secrets;
	if ( ! is_array( $data ) ) {
		return true;
	}

	if ( ! empty( $data['otp_demo_mode'] ) ) {
		return true;
	}

	$sid   = trim( (string) ( $data['twilio_account_sid'] ?? '' ) );
	$token = trim( (string) ( $data['twilio_auth_token'] ?? '' ) );
	$from  = trim( (string) ( $data['twilio_from_number'] ?? '' ) );

	return $sid === '' || $token === '' || $from === '';
}

/**
 * Page context for AI chat (service-aware hints).
 *
 * @return array{path: string, service: string, title: string}
 */
function site_blocks_chat_page_context(): array {
	global $wp;

	$path = '/';
	if ( is_front_page() ) {
		$path = '/';
	} elseif ( isset( $wp->request ) && is_string( $wp->request ) && $wp->request !== '' ) {
		$path = '/' . trim( $wp->request, '/' ) . '/';
	}

	$service = 'general';
	$map     = array(
		'access-control'     => 'access_control',
		'cctv'               => 'cctv',
		'alarm'              => 'alarms',
		'alarms'             => 'alarms',
		'intercom'           => 'intercom',
		'monitoring'         => 'monitoring',
		'get-an-instant-quote' => 'quote',
	);

	foreach ( $map as $slug => $intent ) {
		if ( str_contains( $path, $slug ) ) {
			$service = $intent;
			break;
		}
	}

	return array(
		'path'    => $path,
		'service' => $service,
		'title'   => wp_strip_all_tags( wp_get_document_title() ),
	);
}

/**
 * Enqueue chat widget assets on public pages.
 */
function site_blocks_enqueue_safeguard_chat(): void {
	if ( ! site_blocks_should_show_chat_widget() ) {
		return;
	}

	wp_enqueue_style(
		'safeguard-chat',
		SITE_BLOCKS_URL . 'assets/css/safeguard-chat.css',
		array(),
		SITE_BLOCKS_VERSION
	);

	wp_enqueue_script(
		'safeguard-chat',
		SITE_BLOCKS_URL . 'assets/js/safeguard-chat.js',
		array(),
		SITE_BLOCKS_VERSION,
		true
	);

	wp_localize_script(
		'safeguard-chat',
		'sgChatConfig',
		array(
			'apiUrl'       => esc_url_raw( rest_url( 'ca/v1/chat' ) ),
			'leadUrl'      => esc_url_raw( rest_url( 'ca/v1/chat/lead' ) ),
			'pollUrl'      => esc_url_raw( rest_url( 'ca/v1/chat/poll' ) ),
			'quoteSubmitUrl' => esc_url_raw( rest_url( 'ca/v1/chat/quote' ) ),
			'otpSendUrl'   => esc_url_raw( rest_url( 'ca/v1/otp/send' ) ),
			'otpVerifyUrl' => esc_url_raw( rest_url( 'ca/v1/otp/verify' ) ),
			'routeUrl'     => esc_url_raw( rest_url( 'ca/v1/chat/route' ) ),
			'statusUrl'    => esc_url_raw( rest_url( 'ca/v1/chat/status' ) ),
			'pageContext'  => site_blocks_chat_page_context(),
			'phone'        => '1300 225 276',
			'phoneHref'    => 'tel:1300225276',
			'quoteUrl'     => esc_url_raw( home_url( '/get-an-instant-quote/' ) ),
			'welcome'      => __( 'Hi, I\'m the Safeguard assistant. I can answer questions, help you choose a system, or take your details for a callback.', 'site-blocks' ),
			'placeholder'  => __( 'Type your question…', 'site-blocks' ),
			'sendLabel'    => __( 'Send', 'site-blocks' ),
			'openLabel'    => __( 'Chat with Safeguard', 'site-blocks' ),
			'closeLabel'   => __( 'Close chat', 'site-blocks' ),
			'newChatLabel' => __( 'New chat', 'site-blocks' ),
			'thinking'     => __( 'Thinking…', 'site-blocks' ),
			'errorGeneric' => __( 'Something went wrong. Please try again or call us.', 'site-blocks' ),
			'unavailable'  => __( 'The AI assistant is temporarily unavailable. Tap Talk to a person below and our team will help you live.', 'site-blocks' ),
			'leadIntro'    => __( 'Great, leave your details and our team will call you back (usually within business hours). Pricing is shared via your portal after we connect.', 'site-blocks' ),
			'leadSubmit'   => __( 'Send my details', 'site-blocks' ),
			'leadSuccess'  => __( 'Thanks, we\'ve got your details. Our team will call you shortly.', 'site-blocks' ),
			'leadError'    => __( 'Could not send your details. Please call 1300 225 276.', 'site-blocks' ),
			'handoffIntro' => __( 'Sure, I can connect you with our team. Please share your name, email, address, and phone below.', 'site-blocks' ),
			'handoffSubmit'=> __( 'Connect me to the team', 'site-blocks' ),
			'handoffSuccess'=> __( 'Thanks, connecting you with our team now. Stay on this chat and someone will reply shortly.', 'site-blocks' ),
			'handoffError' => __( 'Could not start live chat. Please call 1300 225 276.', 'site-blocks' ),
			'handoffResolved' => __( 'This chat has been closed by our team. You can keep chatting with the assistant or call 1300 225 276.', 'site-blocks' ),
			'handoffReturned' => __( 'You are back with the assistant. How else can we help?', 'site-blocks' ),
			'postLeadHelp' => __( 'Check your spam folder if you do not hear from us within a few hours. For urgent matters call 1300 225 276.', 'site-blocks' ),
			'quoteIntro'   => __( 'Verify your mobile to receive your quote by email and in your portal, pricing is not shown in chat.', 'site-blocks' ),
			'quoteSubmit'  => __( 'Send my quote', 'site-blocks' ),
			'quoteSuccess' => __( 'Thank you, check your email for your portal link. Your quote and pricing are there.', 'site-blocks' ),
			'quoteError'   => __( 'Could not send your quote. Please try again or call 1300 225 276.', 'site-blocks' ),
			'otpSendLabel' => __( 'Send code', 'site-blocks' ),
			'otpVerifyLabel' => __( 'Verify', 'site-blocks' ),
			'otpDemo'      => site_blocks_otp_is_demo(),
			'otpDemoHint'  => __( 'Demo: enter any 6 digits', 'site-blocks' ),
			'quizPrompt'   => __( 'What is your main security need? Pick one below.', 'site-blocks' ),
			'starterChoices' => array(
				array(
					'label'  => __( 'Quote my alarm', 'site-blocks' ),
					'action' => 'quote_chat',
				),
				array(
					'label'  => __( 'Help me choose', 'site-blocks' ),
					'action' => 'service_picker',
				),
				array(
					'label'  => __( 'Talk to a person', 'site-blocks' ),
					'action' => 'agent_handoff',
				),
				array(
					'label' => __( 'Call us', 'site-blocks' ),
					'href'  => 'tel:1300225276',
				),
			),
			'available'    => site_blocks_is_deepseek_chat_available(),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'site_blocks_enqueue_safeguard_chat', 35 );

/**
 * Render floating chat shell in footer.
 */
function site_blocks_render_safeguard_chat_widget(): void {
	if ( ! site_blocks_should_show_chat_widget() ) {
		return;
	}
	$api_url   = esc_url_raw( rest_url( 'ca/v1/chat' ) );
	$available = site_blocks_is_deepseek_chat_available();
	?>
	<div
		class="sg-chat"
		id="sg-chat"
		data-api-url="<?php echo esc_url( $api_url ); ?>"
		data-available="<?php echo $available ? '1' : '0'; ?>"
	>
		<div class="sg-chat__panel" id="sg-chat-panel" role="dialog" aria-modal="true" aria-labelledby="sg-chat-title" aria-hidden="true">
			<header class="sg-chat__head">
				<div class="sg-chat__head-text">
					<p class="sg-chat__eyebrow"><?php esc_html_e( 'Safeguard Assistant', 'site-blocks' ); ?></p>
					<h2 class="sg-chat__title" id="sg-chat-title"><?php esc_html_e( 'How can we help?', 'site-blocks' ); ?></h2>
				</div>
				<div class="sg-chat__head-actions">
					<button type="button" class="sg-chat__new" id="sg-chat-new" aria-label="<?php esc_attr_e( 'Start a new chat', 'site-blocks' ); ?>">
						<?php esc_html_e( 'New chat', 'site-blocks' ); ?>
					</button>
					<button type="button" class="sg-chat__close" id="sg-chat-close" aria-label="<?php esc_attr_e( 'Close chat', 'site-blocks' ); ?>">
						<?php site_blocks_lucide_icon( 'x', 20 ); ?>
					</button>
				</div>
			</header>
			<div class="sg-chat__messages" id="sg-chat-messages" role="log" aria-live="polite" aria-relevant="additions"></div>
			<form class="sg-chat__form" id="sg-chat-form">
				<label class="sg-chat__sr-only" for="sg-chat-input"><?php esc_html_e( 'Your message', 'site-blocks' ); ?></label>
				<textarea
					class="sg-chat__input"
					id="sg-chat-input"
					name="message"
					rows="1"
					maxlength="2000"
					required
					autocomplete="off"
				></textarea>
				<button type="button" class="sg-chat__send" id="sg-chat-send" aria-label="<?php esc_attr_e( 'Send message', 'site-blocks' ); ?>">
					<?php site_blocks_lucide_icon( 'send', 20 ); ?>
				</button>
			</form>
		</div>
		<button type="button" class="sg-chat__toggle" id="sg-chat-toggle" aria-expanded="false" aria-controls="sg-chat-panel">
			<span class="sg-chat__toggle-icon sg-chat__toggle-icon--open" aria-hidden="true">
				<?php site_blocks_lucide_icon( 'message-circle', 26 ); ?>
			</span>
			<span class="sg-chat__toggle-icon sg-chat__toggle-icon--close" aria-hidden="true">
				<?php site_blocks_lucide_icon( 'x', 26 ); ?>
			</span>
			<span class="sg-chat__toggle-label"><?php esc_html_e( 'Chat', 'site-blocks' ); ?></span>
		</button>
	</div>
	<?php
}
add_action( 'wp_footer', 'site_blocks_render_safeguard_chat_widget', 100 );
