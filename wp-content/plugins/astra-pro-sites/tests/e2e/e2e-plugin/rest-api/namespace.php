<?php
/**
 * Rest routes used for e2e tests.
 *
 * @package Astra.
 */

namespace Astra\E2E\REST;

use WP_Rest_Server;
use WP_Rest_Request;

const REST_NAMESPACE = 'astra-sites/v1';
const REST_BASE      = 'e2e-utils';

/**
 * Bootstrap the plugin.
 *
 * @return void
 */
function bootstrap() : void {
	add_action( 'rest_api_init', __NAMESPACE__ . '\\rest_route' );
}

/**
 * Register rest routes.
 *
 * @return void
 */
function rest_route() : void {
	register_rest_route(
		REST_NAMESPACE,
		REST_BASE . '/reset-site',
		array(
			array(
				'methods'             => WP_Rest_Server::DELETABLE,
				'callback'            => function () {
					delete_option( 'astra_sites_settings' );
					delete_option( 'astra-sites-favorites' );
					return rest_ensure_response(
						array(
							'success' => true,
						)
					);
				},
				'permission_callback' => '__return_true',
			),
		)
	);

}
