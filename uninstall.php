<?php
/**
 * Removes plugin data on uninstall.
 *
 * @package AiProviderForMagnitude
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'ai_provider_for_magnitude_host' );
