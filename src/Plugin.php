<?php
/**
 * Main plugin class.
 *
 * @since 0.1
 */

namespace CiviCRM_WP_REST;

use CiviCRM_WP_REST\Civi\Mailing_Hooks;

class Plugin {

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 */
	public function __construct() {

		$this->register_hooks();

		$this->setup_objects();

	}

	/**
	 * Register hooks.
	 *
	 * @since 1.0
	 */
	protected function register_hooks() {

		add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );

		add_filter( 'rest_pre_dispatch', [ $this, 'bootstrap_civi' ], 10, 3 );

		add_filter( 'rest_post_dispatch',  [ $this, 'maybe_reset_wp_timezone' ], 10, 3);

	}

	/**
	 * Bootstrap CiviCRM when hitting a the 'civicrm' namespace.
	 *
	 * @since 0.1
	 * @param mixed $result
	 * @param WP_REST_Server $server REST server instance
	 * @param WP_REST_Request $request The request
	 * @return mixed $result
	 */
	public function bootstrap_civi( $result, $server, $request ) {

		if ( false !== strpos( $request->get_route(), 'civicrm' ) ) {

			$this->maybe_set_user_timezone( $request );

			civi_wp()->initialize();

		}

		return $result;

	}

	/**
	 * Setup objects.
	 *
	 * @since 0.1
	 */
	private function setup_objects() {

		if ( CIVICRM_WP_REST_REPLACE_MAILING_TRACKING ) {

			// register mailing hooks
			$mailing_hooks = ( new Mailing_Hooks )->register_hooks();

		}

	}

	/**
	 * Registers Rest API routes.
	 *
	 * @since 0.1
	 */
	public function register_rest_routes() {

		// rest endpoint
		$rest_controller = new Controller\Rest;
		$rest_controller->register_routes();

		// url controller
		$url_controller = new Controller\Url;
		$url_controller->register_routes();

		// open controller
		$open_controller = new Controller\Open;
		$open_controller->register_routes();

		// authorizenet controller
		$authorizeIPN_controller = new Controller\AuthorizeIPN;
		$authorizeIPN_controller->register_routes();

		// paypal controller
		$paypalIPN_controller = new Controller\PayPalIPN;
		$paypalIPN_controller->register_routes();

		// pxpay controller
		$paypalIPN_controller = new Controller\PxIPN;
		$paypalIPN_controller->register_routes();

		// civiconnect controller
		$cxn_controller = new Controller\Cxn;
		$cxn_controller->register_routes();

		// widget controller
		$widget_controller = new Controller\Widget;
		$widget_controller->register_routes();

		// soap controller
		$soap_controller = new Controller\Soap;
		$soap_controller->register_routes();

		/**
		 * Opportunity to add more rest routes.
		 *
		 * @since 0.1
		 */
		do_action( 'civi_wp_rest/plugin/rest_routes_registered' );

	}

	/**
	 * Sets the timezone to the users timezone when
	 * calling the civicrm/v3/rest endpoint.
	 *
	 * @since 0.1
	 * @param WP_REST_Request $request The request
	 */
	private function maybe_set_user_timezone( $request ) {

		if ( $request->get_route() != '/civicrm/v3/rest' ) return;

		$timezones = [
			'wp_timezone' => date_default_timezone_get(),
			'user_timezone' => get_option( 'timezone_string', false )
		];

		// filter timezones
		add_filter( 'civi_wp_rest/plugin/timezones', function() use ( $timezones ) {

			return $timezones;

		} );

		if ( empty( $timezones['user_timezone'] ) ) return;

		/**
		 * CRM-12523
		 * CRM-18062
		 * CRM-19115
		 */
		date_default_timezone_set( $timezones['user_timezone'] );
		\CRM_Core_Config::singleton()->userSystem->setMySQLTimeZone();

	}

	/**
	 * Resets the timezone to the original WP
	 * timezone after calling the civicrm/v3/rest endpoint.
	 *
	 * @since 0.1
	 * @param mixed $result
	 * @param WP_REST_Server $server REST server instance
	 * @param WP_REST_Request $request The request
	 * @return mixed $result
	 */
	public function maybe_reset_wp_timezone( $result, $server, $request ) {

		if ( $request->get_route() != '/civicrm/v3/rest' ) return $result;

		$timezones = apply_filters( 'civi_wp_rest/plugin/timezones', null );

		if ( empty( $timezones['wp_timezone'] ) ) return $result;

		// reset wp timezone
		date_default_timezone_set( $timezones['wp_timezone'] );

		return $result;

	}

}
