<?php
/**
 * Plugin composition root.
 *
 * @package AiProviderForMagnitude
 */

declare( strict_types=1 );

namespace AiProviderForMagnitude;

use AiProviderForMagnitude\Admin\Action_Links;
use AiProviderForMagnitude\Connection\Registry_Connection_Inspector;
use AiProviderForMagnitude\Contracts\Hook_Registrar;
use AiProviderForMagnitude\Host\Host_Resolver;
use AiProviderForMagnitude\Http\Local_Host_Allowance;
use AiProviderForMagnitude\Integration\Ai_Plugin_Credentials;
use AiProviderForMagnitude\Provider\Provider_Registrar;
use AiProviderForMagnitude\Settings\Host_Validator;
use AiProviderForMagnitude\Settings\Settings_Page;
use AiProviderForMagnitude\Settings\Settings_Registrar;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds the plugin components and lets each one register its hooks.
 *
 * @since 0.1.0
 */
final class Plugin {

	/**
	 * Main plugin file path.
	 *
	 * @since 0.1.0
	 *
	 * @var string
	 */
	private $plugin_file;

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param string $plugin_file Main plugin file path.
	 */
	public function __construct( string $plugin_file ) {
		$this->plugin_file = $plugin_file;
	}

	/**
	 * Registers the hooks of every component.
	 *
	 * @since 0.1.0
	 */
	public function init(): void {
		foreach ( $this->get_registrars() as $registrar ) {
			$registrar->register();
		}
	}

	/**
	 * Creates the components that attach to WordPress hooks.
	 *
	 * @since 0.1.0
	 *
	 * @return list<Hook_Registrar> The components.
	 */
	private function get_registrars(): array {
		$validator = new Host_Validator();
		$host      = new Host_Resolver( $validator );
		$inspector = new Registry_Connection_Inspector();

		return array(
			new Provider_Registrar(),
			new Local_Host_Allowance( $host ),
			new Ai_Plugin_Credentials(),
			new Settings_Registrar( new Settings_Page( $host, $inspector ), $validator, $inspector, $this->plugin_file ),
			new Action_Links( $this->plugin_file ),
		);
	}
}
