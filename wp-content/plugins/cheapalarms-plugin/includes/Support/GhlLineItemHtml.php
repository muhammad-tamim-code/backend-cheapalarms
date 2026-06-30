<?php

namespace CheapAlarms\Plugin\Support;

use function esc_attr;
use function esc_html;
use function esc_url_raw;
use function wp_strip_all_tags;

/**
 * GHL estimate line-item description HTML (text + optional product image).
 */
final class GhlLineItemHtml
{
    public static function buildDescription(string $text, string $imageUrl = ''): string
    {
        $text = trim($text);
        $imageUrl = esc_url_raw(trim($imageUrl));

        if ($text !== '' && preg_match('/<img\s/i', $text)) {
            return $text;
        }

        $parts = [];
        if ($text !== '') {
            $parts[] = '<p>' . esc_html(wp_strip_all_tags($text)) . '</p>';
        }
        if ($imageUrl !== '') {
            $parts[] = '<img src="' . esc_attr($imageUrl) . '" width="170" style="border-radius:8px;margin:6px 0;display:block;" alt="">';
        }

        return implode("\n", $parts);
    }
}
