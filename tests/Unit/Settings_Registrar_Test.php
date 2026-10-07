<?php
/**
 * Tests for the settings registrar.
 *
 * @package AiProviderForMagnitude
 */

declare( strict_types=1 );

namespace AiProviderForMagnitude\Tests\Unit;

use AiProviderForMagnitude\Connection\Connection_Status;
use AiProviderForMagnitude\Contracts\Connection_Inspector;
use AiProviderForMagnitude\Host\Host_Resolver;
use AiProviderForMagnitude\Settings\Host_Validator;
use AiProviderForMagnitude\Settings\Settings_Page;
use AiProviderForMagnitude\Settings\Settings_Registrar;
use PHPUnit\Framework\TestCase;

/**
 * Covers how the address from the settings form is sanitized.
 *
 * @covers \AiProviderForMagnitude\Settings\Settings_Registrar
 */
final class Settings_Registrar_Test extends TestCase {

	/**
	 * Creates the registrar with stand-in collaborators.
	 *
	 * @return Settings_Registrar The registrar.
	 */
	private function registrar(): Settings_Registrar {
		$inspector = new class() implements Connection_Inspector {
			/**
			 * Reports a connected server.
			 *
			 * @return Connection_Status The status.
			 */
			public function inspect(): Connection_Status {
				return new Connection_Status( true );
			}

			/**
			 * Does nothing.
			 */
			public function refresh(): void {}
		};

		return new Settings_Registrar( new Settings_Page( new Host_Resolver(), $inspector ), new Host_Validator(), $inspector, '/plugins/ai-provider-for-magnitude/ai-provider-for-magnitude.php' );
	}

	/**
	 * Resets the stand-ins after each test.
	 */
	protected function tearDown(): void {
		$GLOBALS['ai_magnitude_options']         = array();
		$GLOBALS['ai_magnitude_settings_errors'] = array();
	}

	public function test_valid_address_is_normalized(): void {
		$this->assertSame( 'http://192.168.1.20:10100/inference', $this->registrar()->sanitize_host( ' http://192.168.1.20:10100/inference/v1 ' ) );
		$this->assertSame( array(), get_settings_errors() );
	}

	public function test_empty_value_clears_the_setting_without_an_error(): void {
		$this->assertSame( '', $this->registrar()->sanitize_host( '   ' ) );
		$this->assertSame( '', $this->registrar()->sanitize_host( null ) );
		$this->assertSame( array(), get_settings_errors() );
	}

	public function test_invalid_address_keeps_the_saved_value_and_adds_one_error(): void {
		$GLOBALS['ai_magnitude_options'][ Host_Resolver::OPTION_NAME ] = 'http://saved.test:1/inference';

		$this->assertSame( 'http://saved.test:1/inference', $this->registrar()->sanitize_host( 'ftp://x.test' ) );
		$this->assertCount( 1, get_settings_errors( Host_Resolver::OPTION_NAME ) );
	}

	public function test_invalid_address_without_a_saved_value_returns_empty(): void {
		$this->assertSame( '', $this->registrar()->sanitize_host( 'javascript:alert(1)' ) );
	}
}
