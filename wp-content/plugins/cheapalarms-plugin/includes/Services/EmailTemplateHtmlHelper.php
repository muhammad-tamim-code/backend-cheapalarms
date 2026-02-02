<?php

namespace CheapAlarms\Plugin\Services;

/**
 * Pure HTML fragment helpers for email templates (header, footer, CTAs).
 * Used by EmailTemplateService; no payment/auth logic.
 */
class EmailTemplateHtmlHelper
{
    /**
     * Get email header HTML
     */
    public static function getEmailHeader(): string
    {
        return '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <div style="background-color: #ffffff; padding: 20px; text-align: center; border-bottom: 2px solid #c95375;">
                <h1 style="color: #c95375; font-size: 24px; font-weight: bold; margin: 0; font-family: Arial, sans-serif;">CheapAlarms</h1>
                <p style="color: #666666; font-size: 12px; margin: 5px 0 0 0; font-family: Arial, sans-serif;">' . esc_html(__('Your Security Partner', 'cheapalarms')) . '</p>
            </div>';
    }

    /**
     * Get email footer HTML
     */
    public static function getEmailFooter(): string
    {
        return '<div style="background-color: #f5f5f5; padding: 30px 20px; text-align: center; border-top: 1px solid #e0e0e0; font-size: 12px; color: #666666; font-family: Arial, sans-serif;">
            <div style="max-width: 600px; margin: 0 auto;">
                <p style="margin: 0 0 10px 0;"><strong style="color: #333333;">CheapAlarms</strong></p>
                <p style="margin: 0 0 10px 0;">' . esc_html(__('Your trusted security partner', 'cheapalarms')) . '</p>
                <p style="margin: 0; font-size: 11px; color: #999999;">' . esc_html(__('This email was sent to you regarding your account. If you have any questions, please contact our support team.', 'cheapalarms')) . '</p>
            </div>
        </div>
    </div>';
    }

    /**
     * Get CTA buttons HTML
     *
     * @param array<int, array{href: string, text: string, primary?: bool}> $ctas
     */
    public static function getCTAs(array $ctas): string
    {
        if (empty($ctas)) {
            return '';
        }

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
                $html .= '<a href="' . esc_url($cta['href']) . '" style="display: inline-block; padding: 12px 24px; background-color: #c95375; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px; text-align: center; font-family: Arial, sans-serif;">' . esc_html($cta['text'] ?? '') . '</a>';
            } else {
                $html .= '<a href="' . esc_url($cta['href']) . '" style="color: #1EA6DF; text-decoration: underline; font-size: 14px;">' . esc_html($cta['text'] ?? '') . '</a>';
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
