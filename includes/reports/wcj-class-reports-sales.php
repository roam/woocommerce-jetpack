<?php
/**
 * WooCommerce Jetpack Sales Reports
 *
 * The WooCommerce Jetpack Sales Reports class.
 *
 * @version 2.6.0
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WCJ_Reports_Sales' ) ) :

class WCJ_Reports_Sales {

	/**
	 * Constructor.
	 *
	 * @version 2.6.0
	 * @since   2.3.0
	 */
	public function __construct( $args = null ) {
		return true;
	}

	/**
	 * get_report.
	 *
	 * @version 2.3.9
	 * @since   2.3.0
	 */
	public function get_report() {

		$html = '';

		$this->year          = isset( $_GET['year'] )          ? $_GET['year']          : date( 'Y' );
		$this->product_title = isset( $_GET['product_title'] ) ? $_GET['product_title'] : '';

		$html .= $this->get_products_sales();

		return $html;
	}

	/*
	 * sort_by_total_sales.
	 *
	 * @version    2.3.0
	 * @since      2.3.0
	 * @deprecated 2.5.8
	 */
	/*
	function sort_by_total_sales( $a, $b ) {
		if ( $a['sales'] == $b['sales'] ) {
			return 0;
		}
		return ( $a['sales'] < $b['sales'] ) ? 1 : -1;
	}
	*/

	/*
	 * sort_by_title.
	 *
	 * @version 2.5.7
	 * @since   2.5.7
	 */
	function sort_by_title( $a, $b ) {
		return strcmp( strip_tags( $a['title'] ), strip_tags( $b['title'] ) );
	}

	/*
	 * get_products_sales.
	 *
	 * @version 2.6.0
	 * @since   2.3.0
	 */
	function get_products_sales() {

		// Get report data
		$products_data = array();
		$totals_data = array();
		$years = array();
		$total_orders = 0;
		$offset = 0;
		$block_size = 512;
		while( true ) {
			$args_orders = array(
				'post_type'      => 'shop_order',
				'post_status'    => 'wc-completed',
				'posts_per_page' => $block_size,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'offset'         => $offset,
				'date_query' => array(
					array(
						'year'  => $this->year,
					),
				),
				'fields'         => 'ids',
			);
			$loop_orders = new WP_Query( $args_orders );
			if ( ! $loop_orders->have_posts() ) {
				break;
			}
			foreach ( $loop_orders->posts as $order_id ) {
				$order = wc_get_order( $order_id );
				$items = $order->get_items();
				foreach ( $items as $item ) {
					$product_ids = array( $item['product_id'] );
					if ( 0 != $item['variation_id'] && 'yes' === get_option( 'wcj_reports_products_sales_count_variations', 'no' ) ) {
						$product_ids[] = $item['variation_id'];
					}
					foreach ( $product_ids as $product_id ) {
						// Title
						if ( ! isset( $products_data[ $product_id ][ 'title' ] ) ) {
							$products_data[ $product_id ][ 'title' ] = '';
							$_product = wc_get_product( $product_id );
							if ( is_object( $_product ) ) {
								$products_data[ $product_id ][ 'title' ] .= $_product->get_title(); // get_the_title( $product_id );
								if ( 'WC_Product_Variation' === get_class( $_product ) && is_object( $_product->parent ) ) {
									$products_data[ $product_id ][ 'title' ] .= '<br><em>' . $_product->get_formatted_variation_attributes( true ) . '</em>';
								} elseif ( 'WC_Product_Variation' === get_class( $_product ) ) {
//									$products_data[ $product_id ][ 'title' ] .= ' [PARENT PRODUCT DELETED]'; // todo
									$products_data[ $product_id ][ 'title' ] .= $item['name'] . '<br><em>' . __( 'Variation', 'woocommerce-jetpack' ) . '</em>'; // get_the_title( $product_id );
									$products_data[ $product_id ][ 'title' ] = '<del>' . $products_data[ $product_id ][ 'title' ] . '</del>';
								}
							} else {
//								$products_data[ $product_id ][ 'title' ] .= $item['name'] . ' [PRODUCT DELETED]'; // todo
								$products_data[ $product_id ][ 'title' ] .= $item['name'];
								$products_data[ $product_id ][ 'title' ] = '<del>' . $products_data[ $product_id ][ 'title' ] . '</del>';
							}
//							$products_data[ $product_id ][ 'title' ] .= ' [ID: ' . $product_id . ']';
						}
						if ( ! ( '' == $this->product_title || false !== stripos( $products_data[ $product_id ][ 'title' ], $this->product_title ) ) ) {
							unset( $products_data[ $product_id ] );
							continue;
						}
						// Total Sales
						if ( ! isset( $products_data[ $product_id ][ 'sales' ] ) ) {
							$products_data[ $product_id ][ 'sales' ] = 0;
						}
						$products_data[ $product_id ][ 'sales' ] += $item['qty'];
						// Total Sum
						if ( ! isset( $products_data[ $product_id ][ 'total_sum' ] ) ) {
							$products_data[ $product_id ][ 'total_sum' ] = 0;
						}
						$products_data[ $product_id ][ 'total_sum' ] += ( $item['line_total'] + ( 'yes' === get_option( 'wcj_reports_products_sales_include_taxes', 'no' ) ? $item['line_tax'] : 0 ) );
						// Sales by Month
						$month = date( 'n', get_the_time( 'U', $order_id ) );
						$year  = date( 'Y', get_the_time( 'U', $order_id ) );
						$years[ $year ] = true;
						if ( ! isset( $products_data[ $product_id ][ 'sales_by_month' ][ $year ][ $month ] ) ) {
							$products_data[ $product_id ][ 'sales_by_month' ][ $year ][ $month ] = 0;
						}
						$products_data[ $product_id ][ 'sales_by_month' ][ $year ][ $month ] += $item['qty'];
						// Sales by Month - Sum
						if ( ! isset( $products_data[ $product_id ][ 'sales_by_month_sum' ][ $year ][ $month ] ) ) {
							$products_data[ $product_id ][ 'sales_by_month_sum' ][ $year ][ $month ] = 0;
						}
						$products_data[ $product_id ][ 'sales_by_month_sum' ][ $year ][ $month ] += $item['line_total'] + ( 'yes' === get_option( 'wcj_reports_products_sales_include_taxes', 'no' ) ? $item['line_tax'] : 0 );
						// Sales by Month (Totals)
						if ( ! isset( $totals_data[ 'sales_by_month' ][ $year ][ $month ] ) ) {
							$totals_data[ 'sales_by_month' ][ $year ][ $month ] = 0;
						}
						$totals_data[ 'sales_by_month' ][ $year ][ $month ] += $item['qty'];
						// Sales by Month - Sum (Totals)
						if ( ! isset( $totals_data[ 'sales_by_month_sum' ][ $year ][ $month ] ) ) {
							$totals_data[ 'sales_by_month_sum' ][ $year ][ $month ] = 0;
						}
						$totals_data[ 'sales_by_month_sum' ][ $year ][ $month ] += $item['line_total'] + ( 'yes' === get_option( 'wcj_reports_products_sales_include_taxes', 'no' ) ? $item['line_tax'] : 0 );
						// Last Sale Time
						if ( ! isset( $products_data[ $product_id ][ 'last_sale' ] ) ) {
							$products_data[ $product_id ][ 'last_sale' ] = date( 'Y-m-d H:i:s', get_the_time( 'U', $order_id ) );
						}
						// Product ID
						if ( ! isset( $products_data[ $product_id ][ 'product_id' ] ) ) {
							$products_data[ $product_id ][ 'product_id' ] = $product_id;
						}
					}
				}
				$total_orders++;
			}
			$offset += $block_size;
		}
//		usort( $products_data, array( $this, 'sort_by_total_sales' ) );
		usort( $products_data, array( $this, 'sort_by_title' ) );

		// Output report table
		$table_data = array();
		$the_header = array(
			__( 'ID', 'woocommerce-jetpack' ),
			__( 'Product', 'woocommerce-jetpack' ),
			__( 'Last Sale', 'woocommerce-jetpack' ),
			__( 'Total', 'woocommerce-jetpack' ),
//			__( 'Purchase Price', 'woocommerce-jetpack' ),
		);
		foreach ( $years as $year => $value ) {
			if ( $year != $this->year ) continue;
			for ( $i = 12; $i >= 1; $i-- ) {
				$the_header[] = sprintf( '%04d.%02d', $year, $i );
			}
		}
		$total_profit = 0;
		$table_data[] = $the_header;
		foreach ( $products_data as /* $product_id => */ $the_data ) {
//			if ( '' == $this->product_title || false !== stripos( $the_data['title'], $this->product_title ) ) {
				$product_purchase_price = wc_get_product_purchase_price( $the_data['product_id'] );
				// todo
				/* if ( 0 == $product_purchase_price ) {
					if ( 0 != $the_data['sales'] ) {
						$product_purchase_price = ( $the_data['total_sum'] / $the_data['sales'] ) * 0.80;
					}
					*//* $_product = wc_get_product( $the_data['product_id'] );
					if ( is_object( $_product ) ) {
						$product_purchase_price = $_product->get_price();
					} *//*
				} */
				$profit = $the_data['total_sum'] - $product_purchase_price * $the_data['sales'];
				$total_profit += $profit;
				$the_row = array(
					$the_data['product_id'],
					$the_data['title'],
					$the_data['last_sale'],
					'<strong>' . $the_data['sales'] . '</strong>',
//					wc_price( wc_get_product_purchase_price( $the_data['product_id'] ) ),
				);
				$the_row2 = array(
					$the_data['product_id'],
					$the_data['title'],
					$the_data['last_sale'],
					'<strong>' . wc_price( $the_data['total_sum'] ) . '</strong>',
//					wc_price( wc_get_product_purchase_price( $the_data['product_id'] ) ),
				);
				$the_row3 = array(
					$the_data['product_id'],
					$the_data['title'],
					$the_data['last_sale'],
					'<strong>' . wc_price( $profit ) . '</strong>',
//					wc_price( wc_get_product_purchase_price( $the_data['product_id'] ) ),
				);
				foreach ( $years as $year => $value ) {
					if ( $year != $this->year ) continue;
					for ( $i = 12; $i >= 1; $i-- ) {
						if ( isset( $the_data['sales_by_month'][ $year ][ $i ] ) ) {
							// Sales
							if ( $i > 1 ) {
								$prev_month_data = ( isset( $the_data['sales_by_month'][ $year ][ $i - 1 ] ) ) ?
									$the_data['sales_by_month'][ $year ][ $i - 1 ] :
									0;
								$color = ( $prev_month_data >= $the_data['sales_by_month'][ $year ][ $i ] ) ? 'red' : 'green';
							} else {
								$color = 'green';
							}
							$the_row[] = '<span style="color:' . $color . ';">' . $the_data['sales_by_month'][ $year ][ $i ] . '</span>';
							// Sum
							if ( $i > 1 ) {
								$prev_month_data = ( isset( $the_data['sales_by_month_sum'][ $year ][ $i - 1 ] ) ) ?
									$the_data['sales_by_month_sum'][ $year ][ $i - 1 ] :
									0;
								$color = ( $prev_month_data >= $the_data['sales_by_month_sum'][ $year ][ $i ] ) ? 'red' : 'green';
							} else {
								$color = 'green';
							}
							$the_row2[] = '<span style="color:' . $color . ';">' . wc_price( $the_data['sales_by_month_sum'][ $year ][ $i ] ) . '</span>';
							// Profit
							if ( ! isset( $totals_data['profit_by_month'][ $year ][ $i ] ) ) {
								$totals_data['profit_by_month'][ $year ][ $i ] = 0;
							}
							$profit_by_month_for_product = $the_data['sales_by_month_sum'][ $year ][ $i ] - $product_purchase_price * $the_data['sales_by_month'][ $year ][ $i ];
							$totals_data['profit_by_month'][ $year ][ $i ] += $profit_by_month_for_product;
							if ( $i > 1 ) {
								$prev_month_data = ( isset( $the_data['sales_by_month_sum'][ $year ][ $i - 1 ] ) ) ?
									$the_data['sales_by_month_sum'][ $year ][ $i - 1 ] - $product_purchase_price * $the_data['sales_by_month'][ $year ][ $i - 1 ] :
									0;
								$color = ( $prev_month_data >= $profit_by_month_for_product ) ? 'red' : 'green';
							} else {
								$color = 'green';
							}
							$the_row3[] = '<span style="color:' . $color . ';">' . wc_price( $profit_by_month_for_product ) . '</span>';
						} else {
							$the_row[] = '';
							$the_row2[] = '';
							$the_row3[] = '';
						}
					}
				}
				if ( 'yes' === get_option( 'wcj_reports_products_sales_display_sales', 'yes' ) ) {
					$table_data[] = $the_row;
				}
				if ( 'yes' === get_option( 'wcj_reports_products_sales_display_sales_sum', 'yes' ) ) {
					$table_data[] = $the_row2;
				}
				if ( 'yes' === get_option( 'wcj_reports_products_sales_display_profit', 'no' ) ) {
					$table_data[] = $the_row3;
				}
//			}
		}

		// Totals
		$totals_row = array();
		$totals_row2 = array();
		$totals_row3 = array();
		$totals_sales_by_month = 0;
		$totals_sales_by_month_sum = 0;
		foreach ( $years as $year => $value ) {
			if ( $year != $this->year ) continue;
			for ( $i = 12; $i >= 1; $i-- ) {
				if ( isset( $totals_data['sales_by_month'][ $year ][ $i ] ) ) {
					$totals_row[]  = '<strong>' . $totals_data['sales_by_month'][ $year ][ $i ] . '</strong>';
					$totals_row2[] = '<strong>' . wc_price( $totals_data['sales_by_month_sum'][ $year ][ $i ] ) . '</strong>';
					$totals_row3[] = '<strong>' . wc_price( $totals_data['profit_by_month'][ $year ][ $i ] ) . '</strong>';
					$totals_sales_by_month     += $totals_data['sales_by_month'][ $year ][ $i ];
					$totals_sales_by_month_sum += $totals_data['sales_by_month_sum'][ $year ][ $i ];
				} else {
					$totals_row[] = '';
					$totals_row2[] = '';
					$totals_row3[] = '';
				}
			}
		}
		if ( 'yes' === get_option( 'wcj_reports_products_sales_display_sales', 'yes' ) ) {
			$table_data[] = array_merge( array( '', '', '<strong>' . __( 'Total Items', 'woocommerce-jetpack' ) . '</strong>', '<strong>' . $totals_sales_by_month . '</strong>' ), $totals_row );
		}
		if ( 'yes' === get_option( 'wcj_reports_products_sales_display_sales_sum', 'yes' ) ) {
			$table_data[] = array_merge( array( '', '', '<strong>' . __( 'Total Sum', 'woocommerce-jetpack' ) . '</strong>', '<strong>' . wc_price( $totals_sales_by_month_sum . '</strong>' ) ), $totals_row2 );
		}
		if ( 'yes' === get_option( 'wcj_reports_products_sales_display_profit', 'no' ) ) {
			$table_data[] = array_merge( array( '', '', '<strong>' . __( 'Total Profit', 'woocommerce-jetpack' ) . '</strong>', '<strong>' . wc_price( $total_profit . '</strong>' ) ), $totals_row3 );
		}

		$settings_link = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=jetpack&wcj-cat=emails_and_misc&section=reports' ) . '">' . '<< ' . __( 'Reports Settings', 'woocommerce-jetpack' ) . '</a>';

		$menu = '';
		$menu .= '<ul class="subsubsub">';
		$menu .= '<li><a href="' . add_query_arg( 'year', date( 'Y' ) )         . '" class="' . ( ( $this->year == date( 'Y' ) ) ? 'current' : '' ) . '">' . date( 'Y' ) . '</a> | </li>';
		$menu .= '<li><a href="' . add_query_arg( 'year', ( date( 'Y' ) - 1 ) ) . '" class="' . ( ( $this->year == ( date( 'Y' ) - 1 ) ) ? 'current' : '' ) . '">' . ( date( 'Y' ) - 1 ) . '</a> | </li>';
		$menu .= '<li><a href="' . add_query_arg( 'year', ( date( 'Y' ) - 2 ) ) . '" class="' . ( ( $this->year == ( date( 'Y' ) - 2 ) ) ? 'current' : '' ) . '">' . ( date( 'Y' ) - 2 ) . '</a></li>';
		$menu .= '</ul>';
		$menu .= '<br class="clear">';

		$filter_form = '';
		$filter_form .= '<form method="get" action="">';
		$filter_form .= '<input type="hidden" name="page" value="' . $_GET['page'] . '" />';
		$filter_form .= '<input type="hidden" name="tab" value="' . $_GET['tab'] . '" />';
		$filter_form .= '<input type="hidden" name="report" value="' . $_GET['report'] . '" />';
		$filter_form .= '<input type="text" name="product_title" title="" value="' . $this->product_title . '" /><input type="submit" value="' . __( 'Filter products', 'woocommerce-jetpack' ) . '" />';
		$filter_form .= '</form>';

		$the_results = ( ! empty( $products_data ) ) ?
			wcj_get_table_html( $table_data, array( 'table_class' => 'widefat striped' ) ) :
			'<p><em>' . __( 'No sales data for current period.' ) . '</em></p>';

		return '<p>' . $settings_link . '</p>' . '<p>' . $menu . '</p>' . '<p>' . $filter_form . '</p>' . $the_results;
	}
}

endif;
