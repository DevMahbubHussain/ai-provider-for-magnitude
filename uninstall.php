<?php
/**
 * Removes plugin data on uninstall.
 *
 * @package AiProviderForMagnitude
 */

declare( strict_types=1 );

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

if ( is_multisite() ) {
	foreach ( get_sites(
		array(
			'fields' => 'ids',
			'number' => 0,
		)
	) as $ai_provider_for_magnitude_site_id ) {
		switch_to_blog( (int) $ai_provider_for_magnitude_site_id );
		delete_option( 'ai_provider_for_magnitude_host' );
		restore_current_blog();
	}
} else {
	delete_option( 'ai_provider_for_magnitude_host' );
}
