<?php
/**
 * WooCommerce Jetpack Product Bookings
 *
 * The WooCommerce Jetpack Product Bookings class.
 *
 * @version 2.4.9
 * @since   2.4.9
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WCJ_Product_Bookings' ) ) :

class WCJ_Product_Bookings extends WCJ_Module {

	/**
	 * Constructor.
	 *
	 * @version 2.4.9
	 * @since   2.4.9
	 */
	function __construct() {

		$this->id         = 'product_bookings';
		$this->short_desc = __( 'Bookings [BETA]', 'woocommerce-jetpack' );
		$this->desc       = __( 'Add bookings products to WooCommerce.', 'woocommerce-jetpack' );
		$this->link       = 'http://booster.io/features/woocommerce-bookings/';
		parent::__construct();

		if ( $this->is_enabled() ) {

			add_action( 'add_meta_boxes',    array( $this, 'add_meta_box' ) );
			add_action( 'save_post_product', array( $this, 'save_meta_box' ), PHP_INT_MAX, 2 );

			if ( ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
				// Prices
				add_filter( 'woocommerce_get_price',                      array( $this, 'change_price' ), PHP_INT_MAX - 100, 2 );
				add_filter( 'woocommerce_get_sale_price',                 array( $this, 'change_price' ), PHP_INT_MAX - 100, 2 );
				add_filter( 'woocommerce_get_regular_price',              array( $this, 'change_price' ), PHP_INT_MAX - 100, 2 );
				// Prices - Variations
				add_filter( 'woocommerce_variation_prices_price',         array( $this, 'change_price' ), PHP_INT_MAX - 100, 2 );
				add_filter( 'woocommerce_variation_prices_regular_price', array( $this, 'change_price' ), PHP_INT_MAX - 100, 2 );
				add_filter( 'woocommerce_variation_prices_sale_price',    array( $this, 'change_price' ), PHP_INT_MAX - 100, 2 );
				add_filter( 'woocommerce_get_variation_prices_hash',      array( $this, 'get_variation_prices_hash' ), PHP_INT_MAX - 100, 3 );
				// Single Page
				add_action( 'woocommerce_before_add_to_cart_button',      array( $this, 'add_input_fields_to_frontend' ), PHP_INT_MAX );
				// Add to cart
				add_filter( 'woocommerce_add_to_cart_validation',         array( $this, 'validate_bookings_on_add_to_cart' ), PHP_INT_MAX, 2 );
				add_filter( 'woocommerce_add_cart_item_data',             array( $this, 'add_bookings_price_to_cart_item_data' ), PHP_INT_MAX, 3 );
				add_filter( 'woocommerce_add_cart_item',                  array( $this, 'add_bookings_price_to_cart_item' ), PHP_INT_MAX, 2 );
				add_filter( 'woocommerce_get_cart_item_from_session',     array( $this, 'get_cart_item_bookings_price_from_session' ), PHP_INT_MAX, 3 );
				// Price html
				add_filter( 'woocommerce_get_price_html',                 array( $this, 'add_per_day_label' ), PHP_INT_MAX, 2 );
				add_filter( 'woocommerce_get_variation_price_html',       array( $this, 'add_per_day_label' ), PHP_INT_MAX, 2 );
				// Add to Cart button on archives
				add_filter( 'woocommerce_product_add_to_cart_url',        array( $this, 'add_to_cart_url' ), PHP_INT_MAX, 2 );
				add_filter( 'woocommerce_product_add_to_cart_text',       array( $this, 'add_to_cart_text' ), PHP_INT_MAX, 2 );
				// Show details at cart, order details, emails
				add_filter( 'woocommerce_cart_item_name',                 array( $this, 'add_info_to_cart_item_name' ), PHP_INT_MAX, 3 );
				add_filter( 'woocommerce_order_item_name',                array( $this, 'add_info_to_order_item_name' ), PHP_INT_MAX, 2 );
				add_action( 'woocommerce_add_order_item_meta',            array( $this, 'add_info_to_order_item_meta' ), PHP_INT_MAX, 3 );
			}

			add_filter( 'wcj_save_meta_box_value', array( $this, 'save_meta_box_value' ), PHP_INT_MAX, 3 );
			add_action( 'admin_notices',           array( $this, 'admin_notices' ) );
		}
	}

	/**
	 * add_to_cart_text.
	 *
	 * @version 2.4.9
	 * @since   2.4.9
	 */
	function add_to_cart_text( $text, $_product ) {
		return ( $this->is_bookings_product( $_product ) ) ? __( 'Read more', 'woocommerce' ) : $text;
	}

	/**
	 * add_to_cart_url.
	 *
	 * @version 2.4.9
	 * @since   2.4.9
	 */
	function add_to_cart_url( $url, $_product ) {
		return ( $this->is_bookings_product( $_product ) ) ? get_permalink( $_product->id ) : $url;
	}

	/**
	 * add_info_to_order_item_meta.
	 *
	 * @version 2.4.9
	 * @since   2.4.9
	 */
	function add_info_to_order_item_meta(  $item_id, $values, $cart_item_key  ) {
		if ( isset( $values['wcj_bookings_price'] ) ) {
			wc_add_order_item_meta( $item_id, '_' . 'wcj_bookings_price',     $values['wcj_bookings_price'] );
			wc_add_order_item_meta( $item_id, '_' . 'wcj_bookings_date_from', $values['wcj_bookings_date_from'] );
			wc_add_order_item_meta( $item_id, '_' . 'wcj_bookings_date_to',   $values['wcj_bookings_date_to'] );
		}
	}

	/**
	 * Adds info to order details (and emails).
	 *
	 * @version 2.4.9
	 * @since   2.4.9
	 */
	function add_info_to_order_item_name( $name, $item, $is_cart = false ) {
		if ( $is_cart ) {
			$name .= '<dl style="font-size:smaller;">';
		}
		if ( isset( $item['wcj_bookings_price'] ) ) {
			if ( $is_cart ) {
				$name .= '<dt>' . __( 'Period', 'woocommerce-jetpack' ) . '</dt>';
				$name .= '<dd>' . $item['wcj_bookings_date_from'] . ' - ' . $item['wcj_bookings_date_to'] . '</dd>';
			} else {
				$name .= ' | ' . $item['wcj_bookings_date_from'] . ' - ' . $item['wcj_bookings_date_to'];
			}
		}
		if ( $is_cart ) {
			$name .= '</dl>';
		}
		return $name;
	}

	/**
	 * Adds info to cart item details.
	 *
	 * @version 2.4.9
	 * @since   2.4.9
	 */
	function add_info_to_cart_item_name( $name, $cart_item, $cart_item_key  ) {
		return $this->add_info_to_order_item_name( $name, $cart_item, true );
	}

	/**
	 * validate_bookings_on_add_to_cart.
	 *
	 * @version 2.4.9
	 * @since   2.4.9
	 */
	function validate_bookings_on_add_to_cart( $passed, $product_id ) {
		$the_product = wc_get_product( $product_id );
		if ( $this->is_bookings_product( $the_product ) ) {
			if ( ! isset( $_POST['wcj_product_bookings_date_from'] ) || '' == $_POST['wcj_product_bookings_date_from'] ) {
				wc_add_notice( __( '"Date from" must be set', 'woocommerce-jetpack' ), 'error' );
				return false;
			}
			if ( ! isset( $_POST['wcj_product_bookings_date_to'] ) || '' == $_POST['wcj_product_bookings_date_to'] ) {
				wc_add_notice( __( '"Date to" must be set', 'woocommerce-jetpack' ), 'error' );
				return false;
			}
			$date_to   = strtotime( $_POST['wcj_product_bookings_date_to'] );
			$date_from = strtotime( $_POST['wcj_product_bookings_date_from'] );
			if ( $date_from >= $date_to ) {
				wc_add_notice( __( '"Date to" must be after "Date from"', 'woocommerce-jetpack' ), 'error' );
				return false;
			}
		}
		return $passed;
	}

	/**
	 * get_cart_item_bookings_price_from_session.
	 *
	 * @version 2.4.9
	 * @since   2.4.9
	 */
	function get_cart_item_bookings_price_from_session( $item, $values, $key ) {
		if ( array_key_exists( 'wcj_bookings_price', $values ) ) {
			$item['data']->wcj_bookings_price = $values['wcj_bookings_price'];
		}
		return $item;
	}

	/**
	 * add_bookings_price_to_cart_item_data.
	 *
	 * @version 2.4.9
	 * @since   2.4.9
	 */
	function add_bookings_price_to_cart_item_data( $cart_item_data, $product_id, $variation_id ) {
		if ( isset( $_POST['wcj_product_bookings_date_to'] ) && isset( $_POST['wcj_product_bookings_date_from'] ) ) {
			$the_product = wc_get_product( $product_id );
			if ( $this->is_bookings_product( $the_product ) ) {
				$date_to   = strtotime( $_POST['wcj_product_bookings_date_to'] );
				$date_from = strtotime( $_POST['wcj_product_bookings_date_from'] );
				$seconds_diff = $date_to - $date_from;
				$days_diff = ( $seconds_diff / 60 / 60 / 24 );
				if ( 0 != $variation_id ) {
					$the_product = wc_get_product( $variation_id );
				}
				$price_per_day = $the_product->get_price();
				$the_price = $days_diff * $price_per_day;
				$cart_item_data['wcj_bookings_price']     = $the_price;
				$cart_item_data['wcj_bookings_date_from'] = $_POST['wcj_product_bookings_date_from'];
				$cart_item_data['wcj_bookings_date_to']   = $_POST['wcj_product_bookings_date_to'];
				wc_add_notice( sprintf( __( 'Price for %d days: %s', 'woocommerce-jetpack' ), $days_diff, wc_price( $the_price ) ), 'notice' );
			}
		}
		return $cart_item_data;
	}

	/**
	 * add_bookings_price_to_cart_item.
	 *
	 * @version 2.4.9
	 * @since   2.4.9
	 */
	function add_bookings_price_to_cart_item( $cart_item_data, $cart_item_key ) {
		if ( isset( $cart_item_data['wcj_bookings_price'] ) ) {
			$cart_item_data['data']->wcj_bookings_price = $cart_item_data['wcj_bookings_price'];
		}
		return $cart_item_data;
	}

	/**
	 * add_input_fields_to_frontend.
	 *
	 * @version 2.4.9
	 * @since   2.4.9
	 */
	function add_input_fields_to_frontend() {
		if ( $this->is_bookings_product( wc_get_product() ) ) {
			$data_table = array();
			$date_from_value = ( isset( $_POST['wcj_product_bookings_date_from'] ) ) ? $_POST['wcj_product_bookings_date_from'] : '';
			$date_to_value   = ( isset( $_POST['wcj_product_bookings_date_to'] ) )   ? $_POST['wcj_product_bookings_date_to']   : '';
			$data_table[] = array(
				__( 'Date from' ),
				'<input firstday="0" dateformat="mm/dd/yy" mindate="0" type="datepicker" display="date" name="wcj_product_bookings_date_from" placeholder="" value="' . $date_from_value . '">',
			);
			$data_table[] = array(
				__( 'Date to' ),
				'<input firstday="0" dateformat="mm/dd/yy" mindate="0" type="datepicker" display="date" name="wcj_product_bookings_date_to" placeholder="" value="' . $date_to_value . '">',
			);
			echo wcj_get_table_html( $data_table, array( 'table_heading_type' => 'none', ) );
		}
	}

	/**
	 * add_per_day_label.
	 *
	 * @version 2.4.9
	 * @since   2.4.9
	 */
	function add_per_day_label( $price_html, $_product ) {
		return ( $this->is_bookings_product( $_product ) ) ? $price_html . ' / ' . __( 'day', 'woocommerce-jetpack' ) : $price_html;
	}

	/**
	 * change_price.
	 *
	 * @version 2.4.9
	 * @since   2.4.9
	 */
	function change_price( $price, $_product ) {
		return ( $this->is_bookings_product( $_product ) && isset( $_product->wcj_bookings_price ) ) ? $_product->wcj_bookings_price : $price;
	}

	/**
	 * get_variation_prices_hash.
	 *
	 * @version 2.4.9
	 * @since   2.4.9
	 * @todo    recheck if this really needed
	 */
	function get_variation_prices_hash( $price_hash, $_product, $display ) {
		return $price_hash;
	}

	/**
	 * save_meta_box_value.
	 *
	 * @version 2.4.9
	 * @since   2.4.9
	 */
	function save_meta_box_value( $option_value, $option_name, $module_id ) {
		if ( true === apply_filters( 'wcj_get_option_filter', false, true ) ) {
			return $option_value;
		}
		if ( 'no' === $option_value ) {
			return $option_value;
		}
		if ( $this->id === $module_id && 'wcj_product_bookings_enabled' === $option_name ) {
			$args = array(
				'post_type'      => 'product',
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'meta_key'       => '_' . 'wcj_product_bookings_enabled',
				'meta_value'     => 'yes',
				'post__not_in'   => array( get_the_ID() ),
			);
			$loop = new WP_Query( $args );
			$c = $loop->found_posts + 1;
			if ( $c >= 2 ) {
				add_filter( 'redirect_post_location', array( $this, 'add_notice_query_var' ), 99 );
				return 'no';
			}
		}
		return $option_value;
	}

	/**
	 * add_notice_query_var.
	 *
	 * @version 2.4.9
	 * @since   2.4.9
	 */
	function add_notice_query_var( $location ) {
		remove_filter( 'redirect_post_location', array( $this, 'add_notice_query_var' ), 99 );
		return add_query_arg( array( 'wcj_product_bookings_admin_notice' => true ), $location );
	}

	/**
	 * admin_notices.
	 *
	 * @version 2.4.9
	 * @since   2.4.9
	 */
	function admin_notices() {
		if ( ! isset( $_GET['wcj_product_bookings_admin_notice'] ) ) {
			return;
		}
		?><div class="error"><p><?php
			echo '<div class="message">'
				. __( 'Booster: Free plugin\'s version is limited to only one bookings product enabled at a time. You will need to get <a href="http://booster.io/plus/" target="_blank">Booster Plus</a> to add unlimited number of bookings products.', 'woocommerce-jetpack' )
				. '</div>';
		?></p></div><?php
	}

	/**
	 * is_bookings_product.
	 *
	 * @version 2.4.9
	 * @since   2.4.9
	 */
	function is_bookings_product( $_product ) {
		return ( 'yes' === get_post_meta( $_product->id, '_' . 'wcj_product_bookings_enabled', true ) ) ? true : false;
	}

	/**
	 * get_meta_box_options.
	 *
	 * @version 2.4.9
	 * @since   2.4.9
	 */
	function get_meta_box_options() {
		$options = array(
			array(
				'name'       => 'wcj_product_bookings_enabled',
				'default'    => 'no',
				'type'       => 'select',
				'options'    => array(
					'yes' => __( 'Yes', 'woocommerce-jetpack' ),
					'no'  => __( 'No', 'woocommerce-jetpack' ),
				),
				'title'      => __( 'Enabled', 'woocommerce-jetpack' ),
			),
		);
		return $options;
	}

	/**
	 * get_settings.
	 *
	 * @version 2.4.9
	 * @since   2.4.9
	 */
	function get_settings() {
		$settings = array();
		return $this->add_standard_settings( $settings );
	}
}

endif;

return new WCJ_Product_Bookings();