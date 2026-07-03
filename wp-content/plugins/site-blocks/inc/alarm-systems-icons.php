<?php
/**
 * Inline SVG icons for Alarm Systems page sections.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wireless & smart alarms.
 */
function site_blocks_alarm_icon_wireless(): void {
	echo '<svg width="42" height="42" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M10 18.5C18 11.8 30 11.8 38 18.5" stroke="#1769A1" stroke-width="3" stroke-linecap="round"/><path d="M16 25C20.7 21.2 27.3 21.2 32 25" stroke="#1769A1" stroke-width="3" stroke-linecap="round"/><path d="M21.5 31C23 29.8 25 29.8 26.5 31" stroke="#1769A1" stroke-width="3" stroke-linecap="round"/><circle cx="24" cy="36" r="2.4" fill="#1769A1"/></svg>';
}

/**
 * Home alarm systems.
 */
function site_blocks_alarm_icon_home(): void {
	echo '<svg width="42" height="42" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M8 24L24 10L40 24" stroke="#1769A1" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 22.5V39H34V22.5" stroke="#1769A1" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/><path d="M20 39V28H28V39" stroke="#1769A1" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/><path d="M32 14V9H37V18" stroke="#1769A1" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>';
}

/**
 * Business alarm systems.
 */
function site_blocks_alarm_icon_business(): void {
	echo '<svg width="42" height="42" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M11 40H37" stroke="#1769A1" stroke-width="3" stroke-linecap="round"/><path d="M17 40V13H31V40" stroke="#1769A1" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 17H34" stroke="#1769A1" stroke-width="3" stroke-linecap="round"/><path d="M13 40V27H17" stroke="#1769A1" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/><path d="M31 27H35V40" stroke="#1769A1" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/><path d="M22 20H26" stroke="#1769A1" stroke-width="3" stroke-linecap="round"/><path d="M22 26H26" stroke="#1769A1" stroke-width="3" stroke-linecap="round"/><path d="M22 32H26" stroke="#1769A1" stroke-width="3" stroke-linecap="round"/></svg>';
}

/**
 * Alarm upgrades.
 */
function site_blocks_alarm_icon_upgrade(): void {
	echo '<svg width="42" height="42" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><circle cx="24" cy="24" r="15" stroke="#1769A1" stroke-width="3"/><path d="M24 31V17" stroke="#1769A1" stroke-width="3" stroke-linecap="round"/><path d="M17.5 23.5L24 17L30.5 23.5" stroke="#1769A1" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>';
}

/**
 * Servicing & repairs.
 */
function site_blocks_alarm_icon_service(): void {
	echo '<svg width="42" height="42" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M34.5 8.5L29.5 13.5L34.5 18.5L39.5 13.5C40.1 17.2 38.9 21 36.2 23.7C33.4 26.5 29.3 27.6 25.6 26.7L14.8 37.5C13 39.3 10.1 39.3 8.3 37.5C6.5 35.7 6.5 32.8 8.3 31L19.1 20.2C18.2 16.5 19.3 12.4 22.1 9.6C24.8 6.9 30.8 7.9 34.5 8.5Z" stroke="#1769A1" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/><circle cx="11.5" cy="34.3" r="1.4" fill="#1769A1"/></svg>';
}

/**
 * Back-to-base monitoring.
 */
function site_blocks_alarm_icon_monitoring(): void {
	echo '<svg width="42" height="42" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M12 27V22C12 15.4 17.4 10 24 10C30.6 10 36 15.4 36 22V27" stroke="#1769A1" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/><rect x="8" y="25" width="7" height="11" rx="2" stroke="#1769A1" stroke-width="3"/><rect x="33" y="25" width="7" height="11" rx="2" stroke="#1769A1" stroke-width="3"/><path d="M36 36V37C36 40 33.5 42 30.5 42H26" stroke="#1769A1" stroke-width="3" stroke-linecap="round"/><path d="M22 42H26" stroke="#1769A1" stroke-width="3" stroke-linecap="round"/></svg>';
}

/**
 * Why: designed around your home.
 */
function site_blocks_alarm_icon_why_design(): void {
	echo '<svg width="56" height="56" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M14 30L32 14L50 30V50H14V30Z" stroke="#1769A1" stroke-width="2.8" stroke-linejoin="round"/><path d="M24 50V36H40V50" stroke="#1769A1" stroke-width="2.8" stroke-linejoin="round"/><path d="M18 30H46" stroke="#FB7523" stroke-width="2.8" stroke-linecap="round"/><path d="M40 44L52 52V40L40 32V44Z" stroke="#1769A1" stroke-width="2.8" stroke-linejoin="round"/><path d="M44 36L48 40" stroke="#FB7523" stroke-width="2.8" stroke-linecap="round"/></svg>';
}

/**
 * Why: installed properly.
 */
function site_blocks_alarm_icon_why_install(): void {
	echo '<svg width="56" height="56" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect x="18" y="12" width="28" height="40" rx="3" stroke="#1769A1" stroke-width="2.8"/><path d="M24 12H40L38 8H26L24 12Z" stroke="#1769A1" stroke-width="2.8" stroke-linejoin="round"/><path d="M24 24H40M24 32H36" stroke="#1769A1" stroke-width="2.8" stroke-linecap="round"/><circle cx="32" cy="42" r="9" stroke="#FB7523" stroke-width="2.8"/><path d="M28 42L31 45L37 39" stroke="#FB7523" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';
}

/**
 * Why: supported for years.
 */
function site_blocks_alarm_icon_why_support(): void {
	echo '<svg width="56" height="56" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M18 34V28C18 20.3 24.3 14 32 14C39.7 14 46 20.3 46 28V34" stroke="#1769A1" stroke-width="2.8" stroke-linecap="round"/><rect x="12" y="30" width="8" height="13" rx="3" stroke="#1769A1" stroke-width="2.8"/><rect x="44" y="30" width="8" height="13" rx="3" stroke="#1769A1" stroke-width="2.8"/><path d="M46 43C46 49 41 53 34 53H31" stroke="#1769A1" stroke-width="2.8" stroke-linecap="round"/><circle cx="28" cy="53" r="3" stroke="#1769A1" stroke-width="2.8"/><circle cx="40" cy="20" r="2" fill="#FB7523"/><circle cx="46" cy="16" r="2" fill="#FB7523"/><circle cx="52" cy="20" r="2" fill="#FB7523"/></svg>';
}

/**
 * Step 1: Tell us what you need.
 */
function site_blocks_alarm_icon_step_tell(): void {
	echo '<svg width="72" height="72" viewBox="0 0 72 72" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M20 18H50C53.3137 18 56 20.6863 56 24V42C56 45.3137 53.3137 48 50 48H39L31 56V48H20C16.6863 48 14 45.3137 14 42V24C14 20.6863 16.6863 18 20 18Z" stroke="#1769A1" stroke-width="3" stroke-linejoin="round"/><path d="M25 29H45" stroke="#1769A1" stroke-width="3" stroke-linecap="square"/><path d="M25 36H42" stroke="#1769A1" stroke-width="3" stroke-linecap="square"/><path d="M25 43H36" stroke="#1769A1" stroke-width="3" stroke-linecap="square"/><circle cx="55" cy="50" r="3" fill="#FB7523"/><circle cx="63" cy="44" r="3" fill="#FB7523"/></svg>';
}

/**
 * Step 2: Share a few photos.
 */
function site_blocks_alarm_icon_step_photos(): void {
	echo '<svg width="72" height="72" viewBox="0 0 72 72" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M18 25H28L31 19H41L44 25H54C57.3137 25 60 27.6863 60 31V51C60 54.3137 57.3137 57 54 57H18C14.6863 57 12 54.3137 12 51V31C12 27.6863 14.6863 25 18 25Z" stroke="#1769A1" stroke-width="3" stroke-linejoin="round"/><circle cx="36" cy="42" r="11" stroke="#1769A1" stroke-width="3"/><circle cx="36" cy="42" r="7" stroke="#FB7523" stroke-width="3"/><circle cx="25" cy="22" r="3" fill="#FB7523"/></svg>';
}

/**
 * Step 3: Get a tailored price.
 */
function site_blocks_alarm_icon_step_price(): void {
	echo '<svg width="72" height="72" viewBox="0 0 72 72" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M22 15H42L58 31V51L51 58H31L15 42V22L22 15Z" stroke="#1769A1" stroke-width="3" stroke-linejoin="round"/><circle cx="29" cy="29" r="5" stroke="#1769A1" stroke-width="3"/><path d="M31 43L44 56" stroke="#FB7523" stroke-width="3" stroke-linecap="square"/></svg>';
}

/**
 * Step 4: Reviewed by a technician.
 */
function site_blocks_alarm_icon_step_review(): void {
	echo '<svg width="72" height="72" viewBox="0 0 72 72" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><circle cx="36" cy="20" r="10" stroke="#1769A1" stroke-width="3"/><path d="M18 58V51C18 40.5066 26.5066 32 37 32C47.4934 32 56 40.5066 56 51V58H18Z" stroke="#1769A1" stroke-width="3" stroke-linejoin="round"/><path d="M45 42L53 45V51C53 55 50.5 58.5 45 61C39.5 58.5 37 55 37 51V45L45 42Z" stroke="#FB7523" stroke-width="3" stroke-linejoin="round"/><path d="M42 51L44.5 53.5L49 48" stroke="#FB7523" stroke-width="2.5" stroke-linecap="square" stroke-linejoin="round"/></svg>';
}
