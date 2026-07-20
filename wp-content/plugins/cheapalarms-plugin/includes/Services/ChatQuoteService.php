<?php

namespace CheapAlarms\Plugin\Services;

use CheapAlarms\Plugin\Calculators\CalculatorResolverRegistry;
use CheapAlarms\Plugin\Calculators\ResolveTokenStore;
use CheapAlarms\Plugin\Calculators\Resolvers\AjaxResolver;
use CheapAlarms\Plugin\Config\Config;
use CheapAlarms\Plugin\REST\Controllers\QuoteRequestController;
use CheapAlarms\Plugin\Support\AustralianPhone;
use WP_Error;
use WP_REST_Request;

use function sanitize_text_field;
use function sanitize_email;
use function is_wp_error;
use function wp_json_encode;
use function json_decode;
use function file_get_contents;
use function is_readable;
use function __;

/**
 * Conversational Ajax quote via DeepSeek tools (resolve_build + submit_quote).
 * Prices always come from PHP resolver, never from the model.
 */
class ChatQuoteService
{
    private const MAX_TOOL_ROUNDS = 5;
    private const BRAND           = 'ajax';
    private const MTX_CAPACITY    = 18;

    public function __construct(
        private DeepSeekService $deepSeek,
        private Container $container,
        private Config $config,
        private Logger $logger
    ) {
    }

    /**
     * @param array<int, array{role: string, content: string}> $messages
     * @param array<string, mixed>                             $pageContext
     * @param array<string, mixed>                             $quoteSession
     * @return array<string, mixed>|WP_Error
     */
    public function runAgent(array $messages, array $pageContext = [], array $quoteSession = [])
    {
        if (!$this->deepSeek->isConfigured()) {
            return new WP_Error(
                'deepseek_not_configured',
                __('DeepSeek API key is not configured.', 'cheapalarms'),
                ['status' => 503]
            );
        }

        $apiMessages   = $this->buildApiMessages($messages, $pageContext);
        $sessionToken  = sanitize_text_field((string) ($quoteSession['resolveToken'] ?? ''));
        $quoteState    = null;
        $quoteSubmitted = false;
        $submitResult  = null;

        for ($round = 0; $round < self::MAX_TOOL_ROUNDS; $round++) {
            $completion = $this->deepSeek->chatCompletion($apiMessages, $this->getToolDefinitions());
            if (is_wp_error($completion)) {
                return $completion;
            }

            $message = $completion['message'] ?? null;
            if (!is_array($message)) {
                return new WP_Error('deepseek_empty_response', __('The AI returned an empty response.', 'cheapalarms'), ['status' => 502]);
            }

            $toolCalls = $message['tool_calls'] ?? null;
            if (is_array($toolCalls) && $toolCalls !== []) {
                $apiMessages[] = [
                    'role'       => 'assistant',
                    'content'    => $message['content'] ?? null,
                    'tool_calls' => $toolCalls,
                ];

                foreach ($toolCalls as $toolCall) {
                    if (!is_array($toolCall)) {
                        continue;
                    }

                    $toolId   = (string) ($toolCall['id'] ?? '');
                    $fn       = $toolCall['function'] ?? [];
                    $name     = is_array($fn) ? (string) ($fn['name'] ?? '') : '';
                    $argsRaw  = is_array($fn) ? (string) ($fn['arguments'] ?? '{}') : '{}';
                    $args     = json_decode($argsRaw, true);
                    if (!is_array($args)) {
                        $args = [];
                    }

                    $result = $this->executeTool($name, $args, $sessionToken);
                    if (is_array($result)) {
                        $result = $this->stripPricesFromToolResult($result);
                    }

                    if ($name === 'resolve_build' && is_array($result) && !empty($result['resolveToken'])) {
                        $sessionToken = (string) $result['resolveToken'];
                        $quoteState   = [
                            'resolveToken' => $sessionToken,
                            'summary'      => $result['summary'] ?? [],
                        ];
                    }

                    $apiMessages[] = [
                        'role'         => 'tool',
                        'tool_call_id' => $toolId,
                        'content'      => wp_json_encode($result),
                    ];
                }

                continue;
            }

            $content = isset($message['content']) && is_string($message['content']) ? trim($message['content']) : '';
            if ($content === '') {
                return new WP_Error('deepseek_empty_response', __('The AI returned an empty response.', 'cheapalarms'), ['status' => 502]);
            }

            $response = [
                'content' => $content,
                'model'   => $completion['model'] ?? $this->config->getDeepSeekModel(),
                'usage'   => $completion['usage'] ?? [],
            ];

            if ($quoteState !== null) {
                $response['quoteSession'] = $quoteState;
            }

            if ($quoteSubmitted && is_array($submitResult)) {
                $response['quoteSubmitted'] = true;
                $response['quoteResult']    = [
                    'estimateId' => $submitResult['estimateId'] ?? null,
                    'message'    => $submitResult['message'] ?? null,
                ];
            }

            return $response;
        }

        return new WP_Error(
            'chat_tool_limit',
            __('I need a moment, please try again or call 1300 225 276.', 'cheapalarms'),
            ['status' => 502]
        );
    }

    /**
     * Direct quote submit (fallback when user confirms via widget form).
     *
     * @param array<string, mixed> $body
     * @return array<string, mixed>|WP_Error
     */
    public function submitQuote(array $body)
    {
        $firstName = sanitize_text_field((string) ($body['firstName'] ?? ''));
        $lastName  = sanitize_text_field((string) ($body['lastName'] ?? ''));
        $phoneRaw  = sanitize_text_field((string) ($body['phone'] ?? ''));
        $email     = sanitize_email((string) ($body['email'] ?? ''));
        $token     = sanitize_text_field((string) ($body['resolveToken'] ?? ''));
        $otpToken  = sanitize_text_field((string) ($body['otpVerifiedToken'] ?? ''));

        if ($firstName === '' || $lastName === '' || $phoneRaw === '' || $token === '') {
            return new WP_Error(
                'missing_params',
                __('Name, phone, and a configured kit are required.', 'cheapalarms'),
                ['status' => 400]
            );
        }

        if ($otpToken === '') {
            return new WP_Error(
                'otp_required',
                __('Please verify your mobile number before submitting your quote.', 'cheapalarms'),
                ['status' => 400]
            );
        }

        $phone = AustralianPhone::toE164($phoneRaw);
        if ($phone === null) {
            return new WP_Error(
                'invalid_phone',
                __('Please enter a valid Australian phone number (e.g. 04XX XXX XXX).', 'cheapalarms'),
                ['status' => 400]
            );
        }

        return $this->dispatchQuoteRequest([
            'firstName'        => $firstName,
            'lastName'         => $lastName,
            'phone'            => $phone,
            'email'            => $email,
            'brand'            => self::BRAND,
            'resolveToken'     => $token,
            'otpVerifiedToken' => $otpToken,
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getToolDefinitions(): array
    {
        return [
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'resolve_build',
                    'description' => 'Build an Ajax wireless alarm kit from visitor requirements. Returns device summary and resolveToken only, no prices in chat. Call when you have enough detail (property type, doors, motion, monitoring).',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'mode'             => ['type' => 'string', 'enum' => ['build', 'avg', 'upgrade'], 'description' => 'build=custom kit, avg=average home package, upgrade=wired-to-wireless'],
                            'property'         => ['type' => 'string', 'enum' => ['apartment', 'house1', 'house2', 'shop'], 'description' => 'Property type for build mode'],
                            'monitoring'       => ['type' => 'string', 'enum' => ['none', 'ip', 'mobile4g']],
                            'motion'           => ['type' => 'integer', 'description' => 'Indoor motion sensors (build mode)'],
                            'doors'            => ['type' => 'integer', 'description' => 'External door sensors'],
                            'windows'          => ['type' => 'integer', 'description' => 'Window sensors (counted as door sensors with window tag)'],
                            'keypad'           => ['type' => 'boolean'],
                            'siren_in'         => ['type' => 'boolean'],
                            'siren_out'        => ['type' => 'boolean'],
                            'fobs'             => ['type' => 'integer'],
                            'cctv'             => ['type' => 'boolean'],
                            'cctv_count'       => ['type' => 'integer'],
                            'cctv_resolution'  => ['type' => 'string', 'enum' => ['cam_5mp', 'cam_8mp']],
                            'intercom'         => ['type' => 'boolean'],
                            'zones'            => ['type' => 'integer', 'description' => 'Wired zones for upgrade mode'],
                            'kit'              => [
                                'type'        => 'array',
                                'description' => 'Optional explicit kit lines [{key, qty, tag?}] instead of wizard counts',
                                'items'       => [
                                    'type'       => 'object',
                                    'properties' => [
                                        'key' => ['type' => 'string'],
                                        'qty' => ['type' => 'integer'],
                                        'tag' => ['type' => 'string'],
                                    ],
                                    'required'   => ['key', 'qty'],
                                ],
                            ],
                        ],
                        'required'   => ['mode'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param array<string, mixed> $result
     * @return array<string, mixed>
     */
    private function stripPricesFromToolResult(array $result): array
    {
        unset(
            $result['hardwareSubtotal'],
            $result['installEstimate'],
            $result['total'],
            $result['currency'],
            $result['gstNote']
        );

        return $result;
    }

    /**
     * @param array<string, mixed> $args
     * @return array<string, mixed>|WP_Error
     */
    private function executeTool(string $name, array $args, string $sessionToken)
    {
        if ($name === 'resolve_build') {
            return $this->resolveBuild($args);
        }

        return ['ok' => false, 'error' => 'Unknown tool: ' . $name];
    }

    /**
     * @param array<string, mixed> $args
     * @return array<string, mixed>|WP_Error
     */
    private function resolveBuild(array $args)
    {
        $registry = $this->container->get(CalculatorResolverRegistry::class);
        $resolver = $registry->get(self::BRAND);
        if (is_wp_error($resolver)) {
            return ['ok' => false, 'error' => $resolver->get_error_message()];
        }

        $kit = $args['kit'] ?? null;
        if (!is_array($kit) || $kit === []) {
            $kit = $this->buildKitFromWizard($args);
        }

        $selections = [
            'mode'       => sanitize_text_field((string) ($args['mode'] ?? 'build')),
            'property'   => isset($args['property']) ? sanitize_text_field((string) $args['property']) : null,
            'monitoring' => sanitize_text_field((string) ($args['monitoring'] ?? 'none')),
            'kit'        => $kit,
        ];

        $valid = $resolver->validate($selections);
        if (is_wp_error($valid)) {
            return ['ok' => false, 'error' => $valid->get_error_message()];
        }

        $locationId = $this->config->getLocationId();
        if ($locationId === '' && defined('WP_DEBUG') && WP_DEBUG) {
            $locationId = 'local-dev';
        }
        if ($locationId === '') {
            return ['ok' => false, 'error' => 'Pricing is not configured on this site.'];
        }

        $lineItems = $resolver->toLineItems($selections, $locationId);
        if ($lineItems === []) {
            return ['ok' => false, 'error' => 'Could not price kit, products may not be seeded.'];
        }

        $hardwareSubtotal = $resolver instanceof AjaxResolver
            ? $resolver->hardwareSubtotal($lineItems)
            : 0.0;
        $install = $resolver->installEstimate($selections, $lineItems);

        $tokenStore = $this->container->get(ResolveTokenStore::class);
        $token      = $tokenStore->create(self::BRAND, $selections);

        return [
            'ok'           => true,
            'summary'      => $resolver->toSummary($selections, $locationId),
            'resolveToken' => $token,
            'message'      => 'Kit configured. Ask the visitor to verify their mobile in the form below, pricing is sent to their portal, not in chat.',
        ];
    }

    /**
     * Mirror ajax-calculator buildKitFromWizard().
     *
     * @param array<string, mixed> $args
     * @return array<int, array<string, mixed>>
     */
    private function buildKitFromWizard(array $args): array
    {
        $kit  = [];
        $mode = sanitize_text_field((string) ($args['mode'] ?? 'build'));

        $add = static function (array &$kit, string $key, int $qty, ?string $tag = null): void {
            if ($qty < 1) {
                return;
            }
            $line = ['key' => $key, 'qty' => $qty, 'colour' => 'white'];
            if ($tag !== null && $tag !== '') {
                $line['tag'] = $tag;
            }
            $kit[] = $line;
        };

        if ($mode === 'avg') {
            $add($kit, 'hub_plus', 1);
            $add($kit, 'keypad', 1);
            $add($kit, 'motion', 3);
            $add($kit, 'siren_in', 1);
            $add($kit, 'siren_out', 1);
        } elseif ($mode === 'upgrade') {
            $zones = max(1, (int) ($args['zones'] ?? 8));
            $extra = max(0, (int) ceil($zones / self::MTX_CAPACITY) - 1);
            $add($kit, 'upgrade', 1);
            if ($extra > 0) {
                $add($kit, 'multitx', $extra);
            }
            $add($kit, 'keypad', 1);
        } else {
            $add($kit, 'hub_plus', 1);
            if ($args['keypad'] ?? true) {
                $add($kit, 'keypad', 1);
            }
            $add($kit, 'door', (int) ($args['doors'] ?? 0));
            $add($kit, 'door', (int) ($args['windows'] ?? 0), 'window');
            $add($kit, 'motion', (int) ($args['motion'] ?? 0));
            if ($args['siren_in'] ?? true) {
                $add($kit, 'siren_in', 1);
            }
            if ($args['siren_out'] ?? true) {
                $add($kit, 'siren_out', 1);
            }
            $add($kit, 'fob', (int) ($args['fobs'] ?? 0));

            if (!empty($args['cctv'])) {
                $res   = sanitize_text_field((string) ($args['cctv_resolution'] ?? 'cam_5mp'));
                $count = max(0, (int) ($args['cctv_count'] ?? 2));
                if ($res !== 'cam_5mp' && $res !== 'cam_8mp') {
                    $res = 'cam_5mp';
                }
                $add($kit, $res, $count);
                if ($count > 0) {
                    $add($kit, 'nvr', 1);
                }
            }

            if (!empty($args['intercom'])) {
                $add($kit, 'doorbell', 1);
            }
        }

        return $kit;
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>|WP_Error
     */
    private function dispatchQuoteRequest(array $payload)
    {
        if ($this->config->getGhlToken() === '' || $this->config->getLocationId() === '') {
            return new WP_Error(
                'ghl_not_configured',
                __('Quote submission is temporarily unavailable. Please call 1300 225 276.', 'cheapalarms'),
                ['status' => 503]
            );
        }

        $controller = new QuoteRequestController($this->container);
        $request    = new WP_REST_Request('POST', '/ca/v1/quote-request');
        $request->set_header('Content-Type', 'application/json');
        $request->set_body(wp_json_encode($payload));

        $response = $controller->handleQuoteRequest($request);
        $data     = $response->get_data();

        if (!is_array($data)) {
            return new WP_Error('quote_failed', __('Could not submit quote.', 'cheapalarms'), ['status' => 502]);
        }

        if (empty($data['ok'])) {
            $message = (string) ($data['error'] ?? $data['err'] ?? __('Could not submit quote.', 'cheapalarms'));

            return ['ok' => false, 'error' => $message];
        }

        return $data;
    }

    /**
     * @param array<int, array{role: string, content: string}> $messages
     * @param array<string, mixed>                             $pageContext
     * @return array<int, array<string, mixed>>
     */
    private function buildApiMessages(array $messages, array $pageContext): array
    {
        $system = $this->deepSeek->buildSystemPrompt($pageContext);
        $tools  = $this->getQuoteToolsPrompt();
        if ($tools !== '') {
            $system .= "\n\n" . $tools;
        }

        $apiMessages = [];
        if ($system !== '') {
            $apiMessages[] = ['role' => 'system', 'content' => $system];
        }

        foreach ($messages as $entry) {
            $apiMessages[] = [
                'role'    => $entry['role'],
                'content' => $entry['content'],
            ];
        }

        return $apiMessages;
    }

    private function getQuoteToolsPrompt(): string
    {
        $path = CA_PLUGIN_PATH . 'config/ai/safeguard-chat-quote-tools.txt';
        if (!is_readable($path)) {
            return '';
        }

        $raw = file_get_contents($path);

        return is_string($raw) ? trim($raw) : '';
    }
}
