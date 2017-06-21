<?php
/**
 * Booster for WooCommerce - Settings - Offer Price
 *
 * @version 2.9.0
 * @since   2.9.0
 * @author  Algoritmika Ltd.
 * @todo    recheck if all button positions working properly
 * @todo    ! more info about position priority
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

return array(
	array(
		'title'    => __( 'Options', 'woocommerce-jetpack' ),
		'id'       => 'wcj_offer_price_options',
		'type'     => 'title',
	),
	array(
		'title'    => __( 'Enable for All Products', 'woocommerce-jetpack' ),
		'desc'     => __( 'Enable', 'woocommerce-jetpack' ),
		'id'       => 'wcj_offer_price_enabled_for_all_products',
		'type'     => 'checkbox',
		'default'  => 'no',
	),
	array(
		'title'    => __( 'Enable per Product', 'woocommerce-jetpack' ),
		'desc'     => __( 'Enable', 'woocommerce-jetpack' ),
		'desc_tip' => __( 'This will add new meta box to each product\'s edit page.', 'woocommerce-jetpack' ),
		'id'       => 'wcj_offer_price_enabled_per_product',
		'type'     => 'checkbox',
		'default'  => 'no',
	),
	array(
		'title'    => __( 'Price Step', 'woocommerce-jetpack' ),
		'desc_tip' => __( 'Number of decimals', 'woocommerce' ),
		'id'       => 'wcj_offer_price_price_step',
		'type'     => 'number',
		'default'  => get_option( 'woocommerce_price_num_decimals' ),
		'custom_attributes' => array( 'min' => 0 ),
	),
	array(
		'title'    => __( 'Minimal Price', 'woocommerce-jetpack' ),
		'id'       => 'wcj_offer_price_min_price',
		'type'     => 'number',
		'default'  => 0,
		'custom_attributes' => array( 'min' => 0 ),
	),
	array(
		'title'    => __( 'Maximal Price', 'woocommerce-jetpack' ),
		'desc_tip' => __( 'Set zero to disable.', 'woocommerce-jetpack' ),
		'id'       => 'wcj_offer_price_max_price',
		'type'     => 'number',
		'default'  => 0,
		'custom_attributes' => array( 'min' => 0 ),
	),
	array(
		'title'    => __( 'Default Price', 'woocommerce-jetpack' ),
		'desc_tip' => __( 'Set zero to disable.', 'woocommerce-jetpack' ),
		'id'       => 'wcj_offer_price_default_price',
		'type'     => 'number',
		'default'  => 0,
		'custom_attributes' => array( 'min' => 0 ),
	),
	array(
		'title'    => __( 'Button Label', 'woocommerce-jetpack' ),
		'id'       => 'wcj_offer_price_button_label',
		'type'     => 'text',
		'default'  => __( 'Make an offer', 'woocommerce-jetpack' ),
		'css'      => 'width:99%;',
	),
	array(
		'title'    => __( 'Button Position On Single Product Page', 'woocommerce-jetpack' ),
		'id'       => 'wcj_offer_price_button_position',
		'type'     => 'select',
		'default'  => 'woocommerce_single_product_summary',
		'options'  => array(
			'woocommerce_single_product_summary'        => __( 'Inside single product summary', 'woocommerce-jetpack' ),
			'woocommerce_before_single_product_summary' => __( 'Before single product summary', 'woocommerce-jetpack' ),
			'woocommerce_after_single_product_summary'  => __( 'After single product summary', 'woocommerce-jetpack' ),
		),
	),
	array(
		'desc'     => __( 'Position Priority (i.e. Order)', 'woocommerce-jetpack' ),
		'desc_tip' => __( 'Standard priorities for "Inside single product summary": title - 5, rating - 10, price - 10, excerpt - 20, add to cart - 30, meta - 40, sharing - 50', 'woocommerce-jetpack' ),
		'id'       => 'wcj_offer_price_button_position_priority',
		'type'     => 'number',
		'default'  => 31,
	),
	array(
		'title'    => __( 'Form Header', 'woocommerce-jetpack' ),
		'id'       => 'wcj_offer_price_form_header_template',
		'type'     => 'custom_textarea',
		'default'  => '<h3>' . sprintf( __( 'Suggest your price for %s', 'woocommerce-jetpack' ), '%product_title%' ) . '</h3>',
		'css'      => 'width:99%;',
	),
	array(
		'title'    => __( 'Form Button Label', 'woocommerce-jetpack' ),
		'id'       => 'wcj_offer_price_form_button_label',
		'type'     => 'text',
		'default'  => __( 'Send', 'woocommerce-jetpack' ),
		'css'      => 'width:99%;',
	),
	array(
		'title'    => __( 'Form Footer', 'woocommerce-jetpack' ),
		'id'       => 'wcj_offer_price_form_footer_template',
		'type'     => 'custom_textarea',
		'default'  => '',
		'css'      => 'width:99%;',
	),
	array(
		'title'    => __( 'Customer Notice', 'woocommerce-jetpack' ),
		'id'       => 'wcj_offer_price_customer_notice',
		'type'     => 'custom_textarea',
		'default'  => __( 'Your price offer has been sent.', 'woocommerce-jetpack' ),
		'css'      => 'width:99%;',
	),
	array(
		'title'    => __( 'Email Recipient', 'woocommerce-jetpack' ),
		'desc'     => sprintf( __( 'Leave blank to send to administrator email: %s', 'woocommerce-jetpack' ), '<code>' . get_option( 'admin_email' ) . '</code>' ),
		'id'       => 'wcj_offer_price_email_address',
		'type'     => 'text',
		'default'  => '',
		'css'      => 'width:99%;',
	),
	array(
		'title'    => __( 'Email Subject', 'woocommerce-jetpack' ),
		'id'       => 'wcj_offer_price_email_subject',
		'type'     => 'text',
		'default'  => __( 'Price Offer', 'woocommerce-jetpack' ),
		'css'      => 'width:99%;',
	),
	array(
		'title'    => __( 'Email Template', 'woocommerce-jetpack' ),
		'id'       => 'wcj_offer_price_email_template',
		'type'     => 'custom_textarea',
		'default'  => sprintf( __( 'Product: %s', 'woocommerce-jetpack' ),       '%product_title%' ) . '<br>' . PHP_EOL .
			sprintf( __( 'Offered price: %s', 'woocommerce-jetpack' ), '%offered_price%' ) . '<br>' . PHP_EOL .
			sprintf( __( 'From: %s %s', 'woocommerce-jetpack' ),       '%customer_name%', '%customer_email%' ) . '<br>' . PHP_EOL .
			sprintf( __( 'Message: %s', 'woocommerce-jetpack' ),       '%customer_message%' ),
		'css'      => 'width:99%;height:200px;',
	),
	array(
		'id'       => 'wcj_offer_price_options',
		'type'     => 'sectionend',
	),
);
