<?php

namespace CheapAlarms\Plugin\Services;

use function home_url;
use function sanitize_text_field;
use function strtolower;

/**
 * Scores visitor answers to recommend a Safeguard service page.
 */
class ChatRouterService
{
    /** @var array<string, array{label: string, url: string, keywords: array<int, string>}> */
    private const SERVICES = [
        'alarms' => [
            'label'    => 'Wireless alarm systems (Ajax)',
            'url'      => '/alarm-systems/',
            'keywords' => ['alarm', 'burglar', 'break in', 'break-in', 'motion', 'sensor', 'ajax', 'wireless', 'theft', 'intruder', 'siren'],
        ],
        'cctv' => [
            'label'    => 'CCTV & IP cameras',
            'url'      => '/cctv/',
            'keywords' => ['cctv', 'camera', 'cameras', 'video', 'record', 'footage', 'dome', '4k', 'surveillance', 'nvr'],
        ],
        'access_control' => [
            'label'    => 'Access control',
            'url'      => '/access-control/',
            'keywords' => ['access', 'card', 'swipe', 'door entry', 'credential', 'fob', 'reader', 'strata', 'office door', 'keypad entry'],
        ],
        'intercom' => [
            'label'    => 'Video intercom',
            'url'      => '/intercom/',
            'keywords' => ['intercom', 'doorbell', 'video door', 'visitor', 'buzzer', 'speak', ' apartment entry'],
        ],
        'monitoring' => [
            'label'    => 'Alarm monitoring',
            'url'      => '/monitoring/',
            'keywords' => ['monitor', 'monitoring', 'control room', '24/7', '24 hour', 'back to base', 'response'],
        ],
    ];

    /**
     * @param array<string, mixed> $answers
     * @return array<string, mixed>
     */
    public function recommend(string $message = '', array $answers = []): array
    {
        $scores = [];
        foreach (array_keys(self::SERVICES) as $key) {
            $scores[$key] = 0;
        }

        $haystack = strtolower($message);
        foreach ($answers as $value) {
            if (is_string($value) && $value !== '') {
                $haystack .= ' ' . strtolower($value);
            }
        }

        foreach (self::SERVICES as $key => $service) {
            foreach ($service['keywords'] as $keyword) {
                if (str_contains($haystack, strtolower($keyword))) {
                    $scores[$key]++;
                }
            }
        }

        $selection = sanitize_text_field((string) ($answers['service'] ?? ''));
        if ($selection !== '' && isset($scores[$selection])) {
            $scores[$selection] += 5;
        }

        $property = sanitize_text_field((string) ($answers['propertyType'] ?? ''));
        if ($property === 'shop' || $property === 'business') {
            $scores['access_control'] += 2;
            $scores['cctv'] += 1;
        }

        $concern = sanitize_text_field((string) ($answers['concern'] ?? ''));
        if ($concern !== '' && isset($scores[$concern])) {
            $scores[$concern] += 4;
        }

        arsort($scores);
        $topKey    = array_key_first($scores);
        $topScore  = $scores[$topKey] ?? 0;
        $secondKey = array_keys($scores)[1] ?? null;

        if ($topScore < 1) {
            return [
                'ok'             => true,
                'recommendation' => 'general',
                'label'          => 'Security consultation',
                'url'            => home_url('/contact/'),
                'quoteUrl'       => home_url('/get-an-instant-quote/'),
                'reason'         => 'Tell us a bit more about your property and we can point you to the right system.',
                'scores'         => $scores,
                'alternatives'   => $this->alternatives($scores, null),
            ];
        }

        $service = self::SERVICES[$topKey];
        $reason  = 'Based on what you described, ' . $service['label'] . ' looks like the best fit.';

        if ($secondKey !== null && ($scores[$secondKey] ?? 0) >= $topScore - 1) {
            $alt = self::SERVICES[$secondKey]['label'] ?? '';
            if ($alt !== '') {
                $reason .= ' You may also want to consider ' . $alt . '.';
            }
        }

        return [
            'ok'             => true,
            'recommendation' => $topKey,
            'label'          => $service['label'],
            'url'            => home_url($service['url']),
            'quoteUrl'       => home_url('/get-an-instant-quote/'),
            'reason'         => $reason,
            'scores'         => $scores,
            'alternatives'   => $this->alternatives($scores, $topKey),
        ];
    }

    /**
     * @param array<string, int> $scores
     * @return array<int, array{key: string, label: string, url: string}>
     */
    private function alternatives(array $scores, ?string $exclude): array
    {
        $items = [];
        foreach ($scores as $key => $score) {
            if ($score < 1 || $key === $exclude || !isset(self::SERVICES[$key])) {
                continue;
            }
            $items[] = [
                'key'   => $key,
                'label' => self::SERVICES[$key]['label'],
                'url'   => home_url(self::SERVICES[$key]['url']),
            ];
            if (count($items) >= 2) {
                break;
            }
        }

        return $items;
    }

    /**
     * @return array<int, array{key: string, label: string, concern: string}>
     */
    public function quizOptions(): array
    {
        return [
            ['key' => 'alarms', 'label' => 'Alarm / intrusion', 'concern' => 'alarms'],
            ['key' => 'cctv', 'label' => 'Cameras / CCTV', 'concern' => 'cctv'],
            ['key' => 'access_control', 'label' => 'Door access', 'concern' => 'access_control'],
            ['key' => 'intercom', 'label' => 'Intercom / doorbell', 'concern' => 'intercom'],
        ];
    }
}
