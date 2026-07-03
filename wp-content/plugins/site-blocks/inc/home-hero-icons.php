<?php
/**
 * Inline SVG icons for home hero.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shield center icon.
 */
function site_blocks_home_icon_shield(): void {
	?>
	<svg width="64" height="72" viewBox="0 0 64 72" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
		<path d="M32 2L6 14v20c0 17.7 11.1 34.2 26 40 14.9-5.8 26-22.3 26-40V14L32 2z" fill="url(#shield-grad)" stroke="oklch(75% 0.12 200 / 0.5)" stroke-width="1.5"/>
		<path d="M22 36l7 7 15-16" stroke="oklch(96% 0.02 260)" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
		<defs>
			<linearGradient id="shield-grad" x1="32" y1="2" x2="32" y2="74" gradientUnits="userSpaceOnUse">
				<stop stop-color="oklch(55% 0.14 240)"/>
				<stop offset="1" stop-color="oklch(38% 0.10 260)"/>
			</linearGradient>
		</defs>
	</svg>
	<?php
}

/**
 * Camera orbit icon.
 */
function site_blocks_home_icon_camera(): void {
	?>
	<svg width="40" height="40" viewBox="0 0 40 40" fill="none" aria-hidden="true">
		<rect x="4" y="12" width="32" height="22" rx="4" fill="oklch(42% 0.08 250)" stroke="oklch(70% 0.12 200 / 0.6)" stroke-width="1.2"/>
		<circle cx="20" cy="23" r="7" fill="oklch(28% 0.06 260)" stroke="oklch(78% 0.14 195)" stroke-width="1.5"/>
		<path d="M14 12l4-6h8l4 6" stroke="oklch(70% 0.12 200 / 0.6)" stroke-width="1.2" fill="none"/>
	</svg>
	<?php
}

/**
 * Alarm orbit icon.
 */
function site_blocks_home_icon_alarm(): void {
	?>
	<svg width="40" height="40" viewBox="0 0 40 40" fill="none" aria-hidden="true">
		<rect x="8" y="14" width="24" height="18" rx="3" fill="oklch(42% 0.08 250)" stroke="oklch(70% 0.12 200 / 0.6)" stroke-width="1.2"/>
		<circle cx="20" cy="23" r="3" fill="oklch(78% 0.14 195)"/>
		<path d="M14 10h12M17 10V8h6v2" stroke="oklch(70% 0.12 200 / 0.6)" stroke-width="1.2" stroke-linecap="round"/>
	</svg>
	<?php
}

/**
 * Intercom orbit icon.
 */
function site_blocks_home_icon_intercom(): void {
	?>
	<svg width="40" height="40" viewBox="0 0 40 40" fill="none" aria-hidden="true">
		<rect x="12" y="6" width="16" height="28" rx="4" fill="oklch(42% 0.08 250)" stroke="oklch(70% 0.12 200 / 0.6)" stroke-width="1.2"/>
		<circle cx="20" cy="16" r="4" fill="oklch(28% 0.06 260)" stroke="oklch(78% 0.14 195)" stroke-width="1.2"/>
		<rect x="16" y="26" width="8" height="4" rx="1" fill="oklch(78% 0.14 195)"/>
	</svg>
	<?php
}

/**
 * Monitor orbit icon.
 */
function site_blocks_home_icon_monitor(): void {
	?>
	<svg width="40" height="40" viewBox="0 0 40 40" fill="none" aria-hidden="true">
		<rect x="5" y="10" width="30" height="20" rx="3" fill="oklch(42% 0.08 250)" stroke="oklch(70% 0.12 200 / 0.6)" stroke-width="1.2"/>
		<path d="M16 34h8M20 30v4" stroke="oklch(70% 0.12 200 / 0.6)" stroke-width="1.2" stroke-linecap="round"/>
		<rect x="10" y="14" width="8" height="6" rx="1" fill="oklch(78% 0.14 195 / 0.5)"/>
	</svg>
	<?php
}

/**
 * Access control orbit icon.
 */
function site_blocks_home_icon_access(): void {
	?>
	<svg width="40" height="40" viewBox="0 0 40 40" fill="none" aria-hidden="true">
		<rect x="10" y="8" width="20" height="26" rx="3" fill="oklch(42% 0.08 250)" stroke="oklch(70% 0.12 200 / 0.6)" stroke-width="1.2"/>
		<circle cx="20" cy="21" r="5" fill="oklch(28% 0.06 260)" stroke="oklch(78% 0.14 195)" stroke-width="1.2"/>
		<path d="M20 21v-3" stroke="oklch(78% 0.14 195)" stroke-width="1.5" stroke-linecap="round"/>
	</svg>
	<?php
}
