<?php
/**
 * Base controller class.
 *
 * @since 0.1
 */

namespace CiviCRM_WP_REST\Controller;

use CiviCRM_WP_REST\Endpoint\Endpoint_Interface;

abstract class Base extends \WP_REST_Controller implements Endpoint_Interface {

	/**
	 * Gets the endpoint namespace.
	 *
	 * @since 0.1
	 * @return string $namespace
	 */
	public function get_namespace() {

		return self::NAMESPACE;

	}

	/**
	 * Gets the rest base route.
	 *
	 * @since 0.1
	 * @return string $rest_base
	 */
	public function get_rest_base() {

		return '/' . $this->rest_base;

	}

	/**
	 * Retrieves the endpoint ie. '/civicrm/v3/rest'.
	 *
	 * @since 0.1
	 * @return string $rest_base
	 */
	public function get_endpoint() {

		return '/' . $this->get_namespace() . $this->get_rest_base();

	}

	/**
	 * Checks whether the requested route is equal to this endpoint.
	 *
	 * @since 0.1
	 * @param WP_REST_Request $request
	 * @return bool $is_current_endpoint True if it's equal, false otherwise
	 */
	public function is_current_endpoint( $request ) {

		return $this->get_endpoint() == $request->get_route();

	}

	/**
	 * Authorization status code.
	 *
	 * @since 0.1
	 * @return int $status
	 */
	protected function authorization_status_code() {

		$status = 401;

		if ( is_user_logged_in() ) $status = 403;

		return $status;

	}

}
