<?php
/**
 * WooCommerce Jetpack Admin Tools
 *
 * The WooCommerce Jetpack Admin Tools class.
 *
 * @version 2.5.7
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WCJ_Admin_Tools' ) ) :

class WCJ_Admin_Tools extends WCJ_Module {

	/**
	 * Constructor.
	 *
	 * @version 2.5.7
	 */
	public function __construct() {

		$this->id         = 'admin_tools';
		$this->short_desc = __( 'Admin Tools', 'woocommerce-jetpack' );
		$this->desc       = __( 'Booster for WooCommerce debug and log tools.', 'woocommerce-jetpack' );
		$this->link       = 'http://booster.io/features/woocommerce-booster-admin-tools/';
		parent::__construct();

		$this->add_tools( array(
			'admin_tools' => array(
				'title'     => __( 'Admin Tools', 'woocommerce-jetpack' ),
				'desc'      => __( 'Log.', 'woocommerce-jetpack' ),
				'tab_title' => __( 'Log', 'woocommerce-jetpack' ),
			),
		) );

		$this->current_php_memory_limit = '';
		if ( $this->is_enabled() ) {
			if ( 0 != ( $php_memory_limit = get_option( 'wcj_admin_tools_php_memory_limit', 0 ) ) ) {
				ini_set( 'memory_limit', $php_memory_limit . 'M' );
			}
			$this->current_php_memory_limit = sprintf( ' Current PHP memory limit: %s.', ini_get( 'memory_limit' ) );
		}
	}

	/**
	 * create_tool.
	 *
	 * @version 2.5.7
	 */
	public function create_admin_tools_tool() {

		$the_notice = '';
		if ( isset( $_GET['wcj_delete_log'] ) && wcj_is_user_role( 'administrator' ) ) {
			update_option( 'wcj_log', '' );
			$the_notice .= __( 'Log deleted successfully.', 'woocommerce-jetpack' );
		}

		$the_tools = '';
		$the_tools .= $this->get_tool_header_html( 'admin_tools' );
		$the_tools .= '<p><a href="' . add_query_arg( 'wcj_delete_log', '1' ) . '">' . __( 'Delete Log', 'woocommerce-jetpack' ) . '</a></p>';

		$the_log = '';
		$the_log .= '<pre>' . get_option( 'wcj_log', '' ) . '</pre>';

		$html = '';
		$html .= '<p>' . $the_tools  . '</p>';
		$html .= '<p>' . $the_notice . '</p>';
		$html .= '<p>' . $the_log    . '</p>';
		echo $html;
	}

	/**
	 * get_settings.
	 *
	 * @version 2.5.7
	 */
	function get_settings() {

		$settings = array(

			array(
				'title'    => __( 'Admin Tools Options', 'woocommerce-jetpack' ),
				'type'     => 'title',
				'id'       => 'wcj_admin_tools_module_options',
			),

			array(
				'title'    => __( 'Logging', 'woocommerce-jetpack' ),
				'desc'     => __( 'Enable', 'woocommerce-jetpack' ),
				'id'       => 'wcj_logging_enabled',
				'default'  => 'no',
				'type'     => 'checkbox',
			),

			array(
				'title'    => __( 'Debug', 'woocommerce-jetpack' ),
				'desc'     => __( 'Enable', 'woocommerce-jetpack' ),
				'id'       => 'wcj_debuging_enabled',
				'default'  => 'no',
				'type'     => 'checkbox',
			),

			array(
				'title'    => __( 'PHP Memory Limit', 'woocommerce-jetpack' ),
				'desc'     => __( 'megabytes.', 'woocommerce-jetpack' ),
				'desc_tip' => __( 'Set zero to disable.', 'woocommerce-jetpack' ) . $this->current_php_memory_limit,
				'id'       => 'wcj_admin_tools_php_memory_limit',
				'default'  => 0,
				'type'     => 'number',
				'custom_attributes' => array( 'min' => 0 ),
			),

			/*
			array(
				'title'    => __( 'Custom Shortcode', 'woocommerce-jetpack' ),
				'id'       => 'wcj_custom_shortcode_1',
				'default'  => '',
				'type'     => 'textarea',
			),
			*/

			array(
				'type'     => 'sectionend',
				'id'       => 'wcj_admin_tools_module_options',
			),
		);

		return $this->add_standard_settings( $settings );
	}
}

endif;

return new WCJ_Admin_Tools();
