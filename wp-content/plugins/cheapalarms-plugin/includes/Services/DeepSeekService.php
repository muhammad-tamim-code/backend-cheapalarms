<?php

namespace CheapAlarms\Plugin\Services;

use CheapAlarms\Plugin\Config\Config;
use WP_Error;

use function wp_remote_post;
use function wp_remote_retrieve_body;
use function wp_remote_retrieve_response_code;
use function is_wp_error;
use function wp_json_encode;
use function json_decode;
use function file_get_contents;
use function is_readable;

class DeepSeekService
{
    private const API_URL = 'https://api.deepseek.com/chat/completions';

    public function __construct(
        private Config $config,
        private Logger $logger
    ) {
    }

    public function isConfigured(): bool
    {
        return $this->config->getDeepSeekApiKey() !== '';
    }

    /**
     * @return string System prompt from config/ai/safeguard-chat-system-prompt.txt
     */
    public function getSystemPrompt(): string
    {
        $path = CA_PLUGIN_PATH . 'config/ai/safeguard-chat-system-prompt.txt';
        if (!is_readable($path)) {
            return '';
        }

        $raw = file_get_contents($path);

        return is_string($raw) ? trim($raw) : '';
    }

    /**
     * @param array<string, mixed> $pageContext
     */
    public function buildSystemPrompt(array $pageContext): string
    {
        $base = $this->getSystemPrompt();
        if ($base === '') {
            return '';
        }

        $path    = isset($pageContext['path']) ? trim((string) $pageContext['path']) : '';
        $service = isset($pageContext['service']) ? trim((string) $pageContext['service']) : '';
        $title   = isset($pageContext['title']) ? trim((string) $pageContext['title']) : '';

        if ($path === '' && $service === '' && $title === '') {
            return $base;
        }

        $lines   = [];
        $lines[] = '';
        $lines[] = '## Current page context';

        if ($title !== '') {
            $lines[] = '- Page title: ' . $title;
        }

        if ($path !== '') {
            $lines[] = '- URL path: ' . $path;
        }

        if ($service !== '' && $service !== 'general') {
            $lines[] = '- Likely interest: ' . str_replace('_', ' ', $service);
            $lines[] = '- Tailor examples and questions to this service where relevant.';
            $faq = $this->getFaqSnippet($service);
            if ($faq !== '') {
                $lines[] = '- Service FAQ hint: ' . $faq;
            }
        }

        $lines[] = '- When the visitor wants a quote or callback, say you can collect their name and mobile in the chat, a form may appear below your message. Do not mention buttons or widgets they cannot see.';
        $lines[] = '- NEVER quote dollar amounts in chat. Pricing appears in the customer portal or on the online instant quote page, not in this conversation.';

        return $base . implode("\n", $lines);
    }

    private function getFaqSnippet(string $service): string
    {
        $path = CA_PLUGIN_PATH . 'config/ai/faq-snippets.php';
        if (!is_readable($path)) {
            return '';
        }

        $snippets = include $path;
        if (!is_array($snippets)) {
            return '';
        }

        $snippet = $snippets[$service] ?? '';

        return is_string($snippet) ? trim($snippet) : '';
    }

    /**
     * Raw chat completion (supports tools).
     *
     * @param array<int, array<string, mixed>> $messages
     * @param array<int, array<string, mixed>> $tools
     * @return array{message: array<string, mixed>, model: string, usage: array<string, int|null>}|WP_Error
     */
    public function chatCompletion(array $messages, array $tools = [])
    {
        $apiKey = $this->config->getDeepSeekApiKey();
        if ($apiKey === '') {
            return new WP_Error(
                'deepseek_not_configured',
                __('DeepSeek API key is not configured.', 'cheapalarms'),
                ['status' => 503]
            );
        }

        $body = [
            'model'       => $this->config->getDeepSeekModel(),
            'messages'    => $messages,
            'temperature' => 0.6,
            'max_tokens'  => 1200,
        ];

        if ($tools !== []) {
            $body['tools'] = $tools;
            $body['tool_choice'] = 'auto';
        }

        $response = wp_remote_post(self::API_URL, [
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type'  => 'application/json',
            ],
            'body'    => wp_json_encode($body),
            'timeout' => 90,
        ]);

        if (is_wp_error($response)) {
            $this->logger->error('DeepSeek request failed', [
                'error' => $response->get_error_message(),
            ]);

            return new WP_Error(
                'deepseek_request_failed',
                __('Unable to reach the AI service. Please try again.', 'cheapalarms'),
                ['status' => 502]
            );
        }

        $status = (int) wp_remote_retrieve_response_code($response);
        $raw    = wp_remote_retrieve_body($response);
        $data   = json_decode($raw, true);

        if ($status < 200 || $status >= 300 || !is_array($data)) {
            $message = is_array($data) && isset($data['error']['message'])
                ? (string) $data['error']['message']
                : __('Unexpected response from the AI service.', 'cheapalarms');

            $this->logger->error('DeepSeek API error', [
                'status'  => $status,
                'message' => $message,
            ]);

            return new WP_Error(
                'deepseek_api_error',
                $message,
                ['status' => $status >= 400 && $status < 600 ? $status : 502]
            );
        }

        $message = $data['choices'][0]['message'] ?? null;
        if (!is_array($message)) {
            return new WP_Error(
                'deepseek_empty_response',
                __('The AI returned an empty response.', 'cheapalarms'),
                ['status' => 502]
            );
        }

        $usage = is_array($data['usage'] ?? null) ? $data['usage'] : [];

        return [
            'message' => $message,
            'model'   => (string) ($data['model'] ?? $this->config->getDeepSeekModel()),
            'usage'   => [
                'prompt_tokens'     => isset($usage['prompt_tokens']) ? (int) $usage['prompt_tokens'] : null,
                'completion_tokens' => isset($usage['completion_tokens']) ? (int) $usage['completion_tokens'] : null,
                'total_tokens'      => isset($usage['total_tokens']) ? (int) $usage['total_tokens'] : null,
            ],
        ];
    }

    /**
     * Send a chat completion request to DeepSeek.
     *
     * @param array<int, array{role: string, content: string}> $messages OpenAI-format messages (no system role required).
     * @param array<string, mixed>                             $pageContext Optional page path/service hints for the visitor.
     * @return array{content: string, model: string, usage: array<string, int|null>}|WP_Error
     */
    public function chat(array $messages, array $pageContext = [])
    {
        $apiKey = $this->config->getDeepSeekApiKey();
        if ($apiKey === '') {
            return new WP_Error(
                'deepseek_not_configured',
                __('DeepSeek API key is not configured.', 'cheapalarms'),
                ['status' => 503]
            );
        }

        $payloadMessages = $messages;
        $systemPrompt    = $this->buildSystemPrompt($pageContext);
        if ($systemPrompt !== '') {
            array_unshift($payloadMessages, [
                'role'    => 'system',
                'content' => $systemPrompt,
            ]);
        }

        $body = [
            'model'       => $this->config->getDeepSeekModel(),
            'messages'    => $payloadMessages,
            'temperature' => 0.7,
            'max_tokens'  => 1024,
        ];

        $response = wp_remote_post(self::API_URL, [
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type'  => 'application/json',
            ],
            'body'    => wp_json_encode($body),
            'timeout' => 60,
        ]);

        if (is_wp_error($response)) {
            $this->logger->error('DeepSeek request failed', [
                'error' => $response->get_error_message(),
            ]);

            return new WP_Error(
                'deepseek_request_failed',
                __('Unable to reach the AI service. Please try again.', 'cheapalarms'),
                ['status' => 502]
            );
        }

        $status = (int) wp_remote_retrieve_response_code($response);
        $raw    = wp_remote_retrieve_body($response);
        $data   = json_decode($raw, true);

        if ($status < 200 || $status >= 300 || !is_array($data)) {
            $message = is_array($data) && isset($data['error']['message'])
                ? (string) $data['error']['message']
                : __('Unexpected response from the AI service.', 'cheapalarms');

            $this->logger->error('DeepSeek API error', [
                'status'  => $status,
                'message' => $message,
            ]);

            return new WP_Error(
                'deepseek_api_error',
                $message,
                ['status' => $status >= 400 && $status < 600 ? $status : 502]
            );
        }

        $content = $data['choices'][0]['message']['content'] ?? '';
        if (!is_string($content) || trim($content) === '') {
            return new WP_Error(
                'deepseek_empty_response',
                __('The AI returned an empty response.', 'cheapalarms'),
                ['status' => 502]
            );
        }

        $usage = is_array($data['usage'] ?? null) ? $data['usage'] : [];

        return [
            'content' => trim($content),
            'model'   => (string) ($data['model'] ?? $this->config->getDeepSeekModel()),
            'usage'   => [
                'prompt_tokens'     => isset($usage['prompt_tokens']) ? (int) $usage['prompt_tokens'] : null,
                'completion_tokens' => isset($usage['completion_tokens']) ? (int) $usage['completion_tokens'] : null,
                'total_tokens'      => isset($usage['total_tokens']) ? (int) $usage['total_tokens'] : null,
            ],
        ];
    }
}
