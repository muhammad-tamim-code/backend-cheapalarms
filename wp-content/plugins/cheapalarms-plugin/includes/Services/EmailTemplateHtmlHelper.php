<?php

namespace CheapAlarms\Plugin\Services;

use CheapAlarms\Plugin\Config\Config;

use function esc_attr;
use function esc_html;
use function esc_url;
use function __;

/**
 * Pure HTML fragment helpers for email templates (header, footer, CTAs).
 * Used by EmailTemplateService and PortalService fallbacks.
 */
class EmailTemplateHtmlHelper
{
    public static function getPrimaryColor(Config $config): string
    {
        return $config->getBrandPrimaryColor();
    }

    public static function getAccentColor(Config $config): string
    {
        return $config->getBrandAccentColor();
    }

    /**
     * Inline style for a primary CTA button (anchor).
     */
    public static function primaryButtonStyle(Config $config, string $extra = ''): string
    {
        $color = esc_attr(self::getPrimaryColor($config));
        $base = 'display: inline-block; padding: 12px 24px; background-color: ' . $color
            . '; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold;';

        if ($extra !== '') {
            return $base . ' ' . $extra;
        }

        return $base;
    }

    /**
     * Inline style for a gradient hero CTA.
     */
    public static function gradientButtonStyle(Config $config): string
    {
        $accent = esc_attr(self::getAccentColor($config));
        $primary = esc_attr(self::getPrimaryColor($config));

        return 'display: inline-block; padding: 16px 32px; background: linear-gradient(135deg, '
            . $accent . ', ' . $primary
            . '); color: #ffffff; text-decoration: none; border-radius: 50px; font-weight: bold; font-size: 16px;';
    }

    /**
     * Primary CTA anchor wrapped for email body fragments.
     */
    public static function inlineCtaButton(Config $config, string $href, string $text, string $extraStyle = ''): string
    {
        return '<a href="' . esc_url($href) . '" style="' . self::primaryButtonStyle($config, $extraStyle) . '">'
            . esc_html($text) . '</a>';
    }

    public static function getEmailHeader(Config $config): string
    {
        $brandName = esc_html($config->getBrandName());
        $tagline = esc_html($config->getBrandTagline());
        $logoUrl = esc_url($config->getBrandLogoHorizontalUrl());
        $primary = esc_attr(self::getPrimaryColor($config));

        return '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <div style="background-color: #ffffff; padding: 24px 20px; text-align: center; border-bottom: 2px solid ' . $primary . ';">
                <img src="' . $logoUrl . '" alt="' . $brandName . '" style="max-width: 280px; height: auto; display: block; margin: 0 auto;" />
                <p style="color: #666666; font-size: 12px; margin: 12px 0 0 0; font-family: Arial, sans-serif;">' . $tagline . '</p>
            </div>';
    }

    public static function getEmailFooter(Config $config): string
    {
        $brandName = esc_html($config->getBrandName());
        $tagline = esc_html($config->getBrandTagline());

        return '<div style="background-color: #f5f5f5; padding: 30px 20px; text-align: center; border-top: 1px solid #e0e0e0; font-size: 12px; color: #666666; font-family: Arial, sans-serif;">
            <div style="max-width: 600px; margin: 0 auto;">
                <p style="margin: 0 0 10px 0;"><strong style="color: #333333;">' . $brandName . '</strong></p>
                <p style="margin: 0 0 10px 0;">' . $tagline . '</p>
                <p style="margin: 0; font-size: 11px; color: #999999;">' . esc_html(__('This email was sent to you regarding your account. If you have any questions, please contact our support team.', 'cheapalarms')) . '</p>
            </div>
        </div>
    </div>';
    }

    /**
     * @param array<int, array{href: string, text: string, primary?: bool}> $ctas
     */
    public static function getCTAs(array $ctas, Config $config): string
    {
        if (empty($ctas)) {
            return '';
        }

        $primaryColor = esc_attr(self::getPrimaryColor($config));
        $accentColor = esc_attr(self::getAccentColor($config));
        $html = '<div style="text-align: center; margin: 30px 0;">';
        $visibleIndex = 0;

        foreach ($ctas as $cta) {
            if (empty($cta['href'])) {
                continue;
            }

            if ($visibleIndex > 0) {
                $html .= '<div style="margin-top: 15px;">';
            }

            if ($cta['primary'] ?? false) {
                $html .= '<a href="' . esc_url($cta['href']) . '" style="display: inline-block; padding: 12px 24px; background-color: ' . $primaryColor . '; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px; text-align: center; font-family: Arial, sans-serif;">' . esc_html($cta['text'] ?? '') . '</a>';
            } else {
                $html .= '<a href="' . esc_url($cta['href']) . '" style="color: ' . $accentColor . '; text-decoration: underline; font-size: 14px;">' . esc_html($cta['text'] ?? '') . '</a>';
            }

            if ($visibleIndex > 0) {
                $html .= '</div>';
            }

            $visibleIndex++;
        }

        $html .= '</div>';
        return $html;
    }
}
