<?php
/**
 * Inline SVG icons for Safeguard homepage sections.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Funnel: I know what I need.
 */
function site_blocks_sg_icon_funnel_quote(): void {
	echo '<svg width="64" height="58" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect x="19" y="12" width="28" height="38" rx="3" stroke="#1769A1" stroke-width="2.8"/><path d="M27 12H39L37 8H29L27 12Z" stroke="#1769A1" stroke-width="2.8" stroke-linejoin="round"/><path d="M26 24H40M26 31H38M26 38H34" stroke="#1769A1" stroke-width="2.8" stroke-linecap="round"/><path d="M44 42L54 32C55.2 30.8 57.2 30.8 58.4 32L59 32.6C60.2 33.8 60.2 35.8 59 37L49 47L43 49L44 42Z" stroke="#1769A1" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M53 33L58 38" stroke="#FB7523" stroke-width="2.8" stroke-linecap="round"/></svg>';
}

/**
 * Funnel: Help me choose.
 */
function site_blocks_sg_icon_funnel_help(): void {
	echo '<svg width="64" height="58" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M18 34V28C18 20.3 24.3 14 32 14C39.7 14 46 20.3 46 28V34" stroke="#1769A1" stroke-width="2.8" stroke-linecap="round"/><rect x="12" y="30" width="8" height="13" rx="3" stroke="#1769A1" stroke-width="2.8"/><rect x="44" y="30" width="8" height="13" rx="3" stroke="#1769A1" stroke-width="2.8"/><path d="M46 43C46 49 41 53 34 53H31" stroke="#1769A1" stroke-width="2.8" stroke-linecap="round"/><circle cx="28" cy="53" r="3" stroke="#FB7523" stroke-width="2.8"/><path d="M52 38H57C59 38 60 39 60 41V49C60 51 59 52 57 52H52L47 56V52" stroke="#1769A1" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';
}

/**
 * Service: Alarm Systems.
 */
function site_blocks_sg_icon_service_alarm(): void {
	echo '<svg width="64" height="58" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M32 8L49 15V28C49 42 40 51 32 55C24 51 15 42 15 28V15L32 8Z" stroke="#1769A1" stroke-width="2.8" stroke-linejoin="round"/><path d="M25 31L30 36L40 25" stroke="#FB7523" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M24 19L32 16L40 19" stroke="#1769A1" stroke-width="2.8" stroke-linecap="round"/></svg>';
}

/**
 * Service: CCTV & Cameras.
 */
function site_blocks_sg_icon_service_cctv(): void {
	echo '<svg width="64" height="58" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect x="13" y="22" width="32" height="15" rx="3" transform="rotate(-12 13 22)" stroke="#1769A1" stroke-width="2.8"/><circle cx="24" cy="30" r="4" stroke="#FB7523" stroke-width="2.8"/><path d="M42 34L50 40" stroke="#1769A1" stroke-width="2.8" stroke-linecap="round"/><path d="M50 40V51" stroke="#1769A1" stroke-width="2.8" stroke-linecap="round"/><path d="M43 51H57" stroke="#1769A1" stroke-width="2.8" stroke-linecap="round"/></svg>';
}

/**
 * Service: Video Intercoms.
 */
function site_blocks_sg_icon_service_intercom(): void {
	echo '<svg width="64" height="58" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect x="22" y="9" width="24" height="43" rx="4" stroke="#1769A1" stroke-width="2.8"/><circle cx="34" cy="22" r="6" stroke="#1769A1" stroke-width="2.8"/><path d="M27 36H41" stroke="#1769A1" stroke-width="2.8" stroke-linecap="round"/><path d="M30 43H38" stroke="#1769A1" stroke-width="2.8" stroke-linecap="round"/><circle cx="48" cy="18" r="5" stroke="#FB7523" stroke-width="2.8"/><path d="M46 18H50" stroke="#FB7523" stroke-width="2.8" stroke-linecap="round"/></svg>';
}

/**
 * Service: Access Control.
 */
function site_blocks_sg_icon_service_access(): void {
	echo '<svg width="64" height="58" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect x="22" y="10" width="22" height="44" rx="3" stroke="#1769A1" stroke-width="2.8"/><path d="M28 16H38V48H28" stroke="#1769A1" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/><circle cx="39" cy="32" r="2" fill="#FB7523"/><path d="M46 25L52 29V35L46 39" stroke="#1769A1" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';
}

/**
 * Service: Monitoring & Response.
 */
function site_blocks_sg_icon_service_monitoring(): void {
	echo '<svg width="64" height="58" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect x="12" y="14" width="40" height="30" rx="3" stroke="#1769A1" stroke-width="2.8"/><path d="M26 52H38" stroke="#1769A1" stroke-width="2.8" stroke-linecap="round"/><path d="M32 44V52" stroke="#1769A1" stroke-width="2.8" stroke-linecap="round"/><path d="M32 22L42 26V33C42 40 37 44 32 46C27 44 22 40 22 33V26L32 22Z" stroke="#1769A1" stroke-width="2.8" stroke-linejoin="round"/><path d="M28 34L31 37L37 30" stroke="#FB7523" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';
}

/**
 * Service: Preventative Maintenance.
 */
function site_blocks_sg_icon_service_maintenance(): void {
	echo '<svg width="64" height="58" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M41 13C45 17 45 23 41 27L36 32L32 28L37 23C39 21 39 18 37 16L41 13Z" stroke="#1769A1" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M34 30L15 49C13.5 50.5 13.5 53 15 54.5C16.5 56 19 56 20.5 54.5L39.5 35.5" stroke="#1769A1" stroke-width="2.8" stroke-linecap="round"/><path d="M42 35L52 45C54 47 54 50 52 52C50 54 47 54 45 52L35 42" stroke="#1769A1" stroke-width="2.8" stroke-linecap="round"/><path d="M18 50L21 53" stroke="#FB7523" stroke-width="2.8" stroke-linecap="round"/></svg>';
}

/**
 * Service: Commercial.
 */
function site_blocks_sg_icon_service_commercial(): void {
	echo '<svg width="64" height="58" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect x="12" y="22" width="16" height="30" rx="2" stroke="#1769A1" stroke-width="2.8"/><rect x="28" y="14" width="18" height="38" rx="2" stroke="#1769A1" stroke-width="2.8"/><rect x="46" y="30" width="10" height="22" rx="2" stroke="#1769A1" stroke-width="2.8"/><path d="M17 29H23M17 36H23M17 43H23" stroke="#1769A1" stroke-width="2.8" stroke-linecap="round"/><path d="M34 23H40M34 30H40M34 37H40" stroke="#1769A1" stroke-width="2.8" stroke-linecap="round"/><path d="M32 52V45H38V52" stroke="#FB7523" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';
}

/**
 * Service: Residential.
 */
function site_blocks_sg_icon_service_residential(): void {
	echo '<svg width="64" height="58" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M11 32L32 14L53 32" stroke="#1769A1" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M17 29V52H47V29" stroke="#1769A1" stroke-width="2.8" stroke-linejoin="round"/><path d="M27 52V39H37V52" stroke="#1769A1" stroke-width="2.8" stroke-linejoin="round"/><path d="M24 31H30M34 31H40" stroke="#FB7523" stroke-width="2.8" stroke-linecap="round"/></svg>';
}

/**
 * Ajax trust: Grade 2 security.
 */
function site_blocks_sg_icon_ajax_grade2(): void {
	echo '<svg width="44" height="40" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M22 5L34 10V20C34 29 28 35 22 38C16 35 10 29 10 20V10L22 5Z" stroke="#1769A1" stroke-width="2.5"/><path d="M17 22L21 26L28 18" stroke="#FB7523" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
}

/**
 * Ajax trust: Encrypted end-to-end.
 */
function site_blocks_sg_icon_ajax_encrypted(): void {
	echo '<svg width="44" height="40" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M22 5L34 10V20C34 29 28 35 22 38C16 35 10 29 10 20V10L22 5Z" stroke="#1769A1" stroke-width="2.5"/><rect x="16" y="20" width="12" height="10" rx="2" stroke="#1769A1" stroke-width="2.5"/><path d="M18 20V17C18 14.8 19.8 13 22 13C24.2 13 26 14.8 26 17V20" stroke="#1769A1" stroke-width="2.5"/><circle cx="22" cy="25" r="1.5" fill="#FB7523"/></svg>';
}

/**
 * Ajax trust: Scalable and future-ready.
 */
function site_blocks_sg_icon_ajax_scalable(): void {
	echo '<svg width="44" height="40" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><circle cx="22" cy="22" r="4" stroke="#1769A1" stroke-width="2.5"/><circle cx="10" cy="14" r="3" stroke="#1769A1" stroke-width="2.5"/><circle cx="34" cy="14" r="3" stroke="#1769A1" stroke-width="2.5"/><circle cx="10" cy="32" r="3" stroke="#1769A1" stroke-width="2.5"/><circle cx="34" cy="32" r="3" stroke="#1769A1" stroke-width="2.5"/><path d="M18.5 20L12.5 15.5M25.5 20L31.5 15.5M18.5 24L12.5 30.5M25.5 24L31.5 30.5" stroke="#1769A1" stroke-width="2.5" stroke-linecap="round"/><circle cx="22" cy="22" r="1.5" fill="#FB7523"/></svg>';
}

/**
 * Ajax trust: Designed in Europe.
 */
function site_blocks_sg_icon_ajax_europe(): void {
	echo '<svg width="44" height="40" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><circle cx="22" cy="22" r="5" stroke="#FB7523" stroke-width="2.5"/><path d="M22 5V10M22 34V39M5 22H10M34 22H39M10 10L14 14M30 30L34 34M34 10L30 14M14 30L10 34" stroke="#1769A1" stroke-width="2.5" stroke-linecap="round"/><path d="M18 22L21 25L27 18" stroke="#1769A1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
}

/**
 * Benefit: remote review (cloud).
 */
function site_blocks_sg_icon_benefit_remote(): void {
	echo '<svg width="64" height="58" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M18 38C13.6 38 10 34.4 10 30C10 25.9 13.1 22.4 17.1 22C18.4 16.5 23.3 12 29 12C35.1 12 40 16.9 40 23C40 23.3 40 23.7 40 24C44.4 24.3 48 27.9 48 32.5C48 37.2 44.2 41 39.5 41H22C19.8 41 18 39.2 18 37V38Z" stroke="#1769A1" stroke-width="2.8" stroke-linejoin="round"/><path d="M34 30L30 38H38L34 46" stroke="#FB7523" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';
}

/**
 * Benefit: site visits (calendar).
 */
function site_blocks_sg_icon_benefit_visit(): void {
	echo '<svg width="64" height="58" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect x="14" y="16" width="36" height="34" rx="3" stroke="#1769A1" stroke-width="2.8"/><path d="M14 26H50" stroke="#1769A1" stroke-width="2.8"/><path d="M24 12V20M40 12V20" stroke="#1769A1" stroke-width="2.8" stroke-linecap="round"/><circle cx="32" cy="38" r="9" stroke="#1769A1" stroke-width="2.8"/><path d="M32 34V38L35 41" stroke="#FB7523" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';
}

/**
 * Benefit: better estimates (chart).
 */
function site_blocks_sg_icon_benefit_estimate(): void {
	echo '<svg width="64" height="58" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M12 48H52" stroke="#1769A1" stroke-width="2.8" stroke-linecap="round"/><rect x="16" y="34" width="8" height="14" stroke="#1769A1" stroke-width="2.8"/><rect x="28" y="26" width="8" height="22" stroke="#1769A1" stroke-width="2.8"/><rect x="40" y="18" width="8" height="30" stroke="#1769A1" stroke-width="2.8"/><path d="M44 22L50 16L56 22" stroke="#FB7523" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';
}

/**
 * Benefit: estimate includes checkmark.
 */
function site_blocks_sg_icon_benefit_check(): void {
	echo '<svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><circle cx="9" cy="9" r="8" stroke="#1F9D57" stroke-width="1.5"/><path d="M5.5 9L8 11.5L12.5 6.5" stroke="#1F9D57" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';
}

/**
 * Benefit: transparent trust shield.
 */
function site_blocks_sg_icon_benefit_shield(): void {
	echo '<svg width="56" height="56" viewBox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M28 6L44 12V26C44 38.2 37.2 46.8 28 50C18.8 46.8 12 38.2 12 26V12L28 6Z" stroke="#FB7523" stroke-width="2.5" stroke-linejoin="round"/><path d="M28 18L31.5 25.5L39.5 26.5L33.5 32L35 40L28 36L21 40L22.5 32L16.5 26.5L24.5 25.5L28 18Z" stroke="#FB7523" stroke-width="2.5" stroke-linejoin="round"/></svg>';
}

/**
 * Team: experienced technicians.
 */
function site_blocks_sg_icon_team_technician(): void {
	echo '<svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><circle cx="20" cy="13" r="6" stroke="#1769A1" stroke-width="2.2"/><path d="M10 34C10 27.4 14.5 23 20 23C25.5 23 30 27.4 30 34" stroke="#1769A1" stroke-width="2.2" stroke-linecap="round"/></svg>';
}

/**
 * Team: residential and commercial.
 */
function site_blocks_sg_icon_team_buildings(): void {
	echo '<svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M6 32V18L14 14V32" stroke="#1769A1" stroke-width="2.2" stroke-linejoin="round"/><path d="M10 22H12M10 26H12" stroke="#1769A1" stroke-width="2.2" stroke-linecap="round"/><rect x="16" y="10" width="12" height="22" stroke="#1769A1" stroke-width="2.2"/><path d="M20 16H24M20 20H24M20 24H24" stroke="#1769A1" stroke-width="2.2" stroke-linecap="round"/><rect x="30" y="20" width="6" height="12" stroke="#1769A1" stroke-width="2.2"/><path d="M14 32H34" stroke="#1769A1" stroke-width="2.2" stroke-linecap="round"/></svg>';
}

/**
 * Team: support and documentation.
 */
function site_blocks_sg_icon_team_docs(): void {
	echo '<svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect x="9" y="8" width="18" height="24" rx="2" stroke="#1769A1" stroke-width="2.2"/><path d="M14 15H22M14 20H20M14 25H18" stroke="#1769A1" stroke-width="2.2" stroke-linecap="round"/><rect x="17" y="12" width="14" height="20" rx="2" stroke="#1769A1" stroke-width="2.2"/><path d="M22 19H26M22 23H26" stroke="#FB7523" stroke-width="2.2" stroke-linecap="round"/></svg>';
}

/**
 * Team: maintenance and monitoring.
 */
function site_blocks_sg_icon_team_monitor(): void {
	echo '<svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect x="6" y="10" width="28" height="18" rx="2" stroke="#1769A1" stroke-width="2.2"/><path d="M14 32H26" stroke="#1769A1" stroke-width="2.2" stroke-linecap="round"/><path d="M20 28V32" stroke="#1769A1" stroke-width="2.2" stroke-linecap="round"/><path d="M20 14L24 18V22C24 25.3 22.2 27.5 20 28.5C17.8 27.5 16 25.3 16 22V18L20 14Z" stroke="#1769A1" stroke-width="2.2" stroke-linejoin="round"/><circle cx="26" cy="16" r="2" fill="#FB7523"/></svg>';
}
