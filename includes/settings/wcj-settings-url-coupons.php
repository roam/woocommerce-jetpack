<?php
/**
 * Booster for WooCommerce - Settings - URL Coupons
 *
 * @version 2.9.1
 * @since   2.9.1
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$settings = array(
	array(
		'title'    => __( 'Options', 'woocommerce-jetpack' ),
		'desc'     => sprintf( __( 'Additionally you can hide standard coupon field on cart page in Booster\'s <a href="%s">Cart Customization</a> module.', 'woocommerce-jetpack' ),
				admin_url( 'admin.php?page=wc-settings&tab=jetpack&wcj-cat=cart_and_checkout&section=cart_customization' ) ) . '<br>' .
			sprintf( __( 'Your users can apply shop\'s standard coupons, by visiting URL. E.g.: %s.', 'woocommerce-jetpack' ),
				'<code>' . site_url() . '/?' . get_option( 'wcj_url_coupons_key', 'wcj_apply_coupon' ) . '=couponcode' . '</code>' ),
		'type'     => 'title',
		'id'       => 'wcj_url_coupons_options',
	),
	array(
		'title'    => __( 'URL Coupons Key', 'woocommerce-jetpack' ),
		'desc_tip' => __( 'URL key. If you change this, make sure it\'s unique and is not used anywhere on your site (e.g. by another plugin).', 'woocommerce-jetpack' ),
		'id'       => 'wcj_url_coupons_key',
		'default'  => 'wcj_apply_coupon',
		'type'     => 'text',
	),
	array(
		'type'     => 'sectionend',
		'id'       => 'wcj_url_coupons_options',
	),
);
return $settings;