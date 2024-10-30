<?php

/**
 * WooCommerce.
 */
class CM_IDIN_WooCommerce {
	/**
	 * Construct.
	 */
	public function __construct( $plugin ) {
		$this->plugin  = $plugin;
		$this->service = $plugin->service;
	}

	/**
	 * Setup.
	 */
	public function setup() {
		add_action( 'wp_loaded', array( $this, 'loaded' ) );

		add_filter( 'cm_din_default_role', array( $this, 'filter_default_role' ) );

		add_action( 'cm_idin_transaction_update', array( $this, 'idin_transaction_update' ) );
		add_action( 'cm_idin_user_register', array( $this, 'idin_user_register' ), 10, 2 );
	}

	/**
	 * Loaded.
	 */
	public function loaded() {
		add_action( 'woocommerce_account_navigation', array( $this->plugin, 'maybe_show_message' ) );
		add_action( 'woocommerce_before_checkout_form', array( $this->plugin, 'maybe_show_message' ) );
		add_action( 'woocommerce_before_customer_login_form', array( $this->plugin, 'maybe_show_message' ) );

		if ( $this->plugin->show_register_login() ) {
			// @see https://github.com/woocommerce/woocommerce/search?utf8=%E2%9C%93&q=woocommerce_before_checkout_form
			add_action( 'woocommerce_before_checkout_form', array( $this->plugin, 'register_login_form' ) );
			// @see https://github.com/woocommerce/woocommerce/search?utf8=%E2%9C%93&q=woocommerce-form-login
			add_action( 'woocommerce_before_customer_login_form', array( $this->plugin, 'register_login_form' ) );
		}

		if ( $this->plugin->is_age_verification_active() ) {
			if ( is_admin() && 'only_18plus_products' === get_option( 'cm_idin_age_verification_required' ) ) {
				add_action( 'woocommerce_product_options_general_product_data', array( $this, 'product_options_idin' ) );
				add_action( 'woocommerce_process_product_meta', array( $this, 'process_product_meta' ), 10, 2 );
			}

			if ( get_option( 'cm_idin_age_show_product_notice' ) ) {
				add_action( 'woocommerce_single_product_summary', array( $this, 'product_notice_18' ), 25 );
			}

			if ( get_option( 'cm_idin_age_show_cart_notice' ) ) {
				add_action( 'woocommerce_before_cart_table', array( $this, 'cart_notice_18' ) );
			}

			if ( $this->cart_needs_age_verification() && $this->is_age_verification_required() ) {
				// We could also use `woocommerce_review_order_before_submit` and `woocommerce_order_button_html`.
				add_action( 'woocommerce_before_checkout_form', array( $this->plugin, 'age_verification_form' ) );

				add_action( 'woocommerce_checkout_process', array( $this, 'checkout_process' ) );
			}
		}

		if ( is_admin() ) {
			add_action( 'woocommerce_admin_order_data_after_shipping_address', array( $this, 'admin_order_idin_data' ) );
		}

		// @see https://github.com/woocommerce/woocommerce/blob/3.0.8/includes/data-stores/class-wc-order-data-store-cpt.php#L79
		add_action( 'woocommerce_new_order', array( $this, 'new_order' ) );
	}

	/**
	 * Admin order iDIN data.
	 *
	 * @param $order
	 */
	public function admin_order_idin_data( $order ) {
		$id    = $order->get_meta( '_cm_idin_transaction_id' );
		$trxid = $order->get_meta( '_cm_idin_transaction_trxid' );

		if ( empty( $id ) || empty( $trxid ) ) {
			return;
		}

		?>
		<h3><?php esc_html_e( 'CM iDIN', 'cm-idin' ); ?></h3>

		<div class="address">
			<p>
				<strong><?php esc_html_e( 'Transaction:', 'cm-idin' ); ?></strong>

				<?php

				$url = add_query_arg( array(
					'page' => 'cm-idin-transactions',
					'id'   => $id,
				), admin_url( 'admin.php' ) );

				printf(
					'<a href="%s">%s</a>',
					esc_url( $url ),
					esc_html( $trxid )
				);

				?>
			</p>
		</div>
		<?php
	}

	/**
	 * WooCommerce new order.
	 *
	 * @see https://github.com/woocommerce/woocommerce/blob/3.0.8/includes/data-stores/class-wc-order-data-store-cpt.php#L79
	 * @param string $order_id
	 */
	public function new_order( $order_id ) {
		$order = wc_get_order( $order_id );

		$session = WC()->session;

		if ( isset( $session ) ) {
			$transaction = $session->get( 'cm_idin_transaction' );

			if ( is_object( $transaction ) ) {
				$order->update_meta_data( '_cm_idin_transaction_id', $transaction->id );
				$order->update_meta_data( '_cm_idin_transaction_trxid', $transaction->transaction_id );

				$order->save();
			}
		}
	}

	/**
	 * Is age verification required.
	 */
	public function is_age_verification_required() {
		if ( $this->plugin->is_current_user_18y_or_older() ) {
			return false;
		}

		$session = WC()->session;

		if ( is_object( $session ) ) {
			$transaction = WC()->session->get( 'cm_idin_transaction' );

			if ( isset( $transaction, $transaction->age, $transaction->age->{'18y_or_older'} ) && $transaction->age->{'18y_or_older'} ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Check if cart needs age verification.
	 *
	 * @return boolean true if age verification is required, false otherwise.
	 */
	private function cart_needs_age_verification() {
		if ( 'always' === get_option( 'cm_idin_age_verification_required' ) ) {
			return true;
		}

		if ( ! function_exists( 'WC' ) ) {
			return false;
		}

		if ( empty( WC()->cart ) ) {
			return false;
		}

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$product_id = $cart_item['product_id'];

			$age_verification_required = ( 'yes' === get_post_meta( $product_id, '_cm_idin_age_verification_required', true ) );

			if ( $age_verification_required ) {
				return true;
			}
		}

		return false;
	}

	private function product_needs_age_verification( $post = null ) {
		if ( 'always' === get_option( 'cm_idin_age_verification_required' ) ) {
			return true;
		}

		$post = get_post( $post );

		return ( 'yes' === get_post_meta( $post->ID, '_cm_idin_age_verification_required', true ) );
	}

	/**
	 * Product options iDIN.
	 *
	 * @see http://www.remicorson.com/mastering-woocommerce-products-custom-fields/
	 * @see https://github.com/woocommerce/woocommerce/blob/3.0.7/includes/admin/meta-boxes/views/html-product-data-general.php#L154
	 * @see https://docs.woocommerce.com/wc-apidocs/function-wc_get_product.html
	 */
	public function product_options_idin() {
		$product = wc_get_product();

		if ( is_null( $product ) ) {
			return;
		}

		wp_nonce_field( 'cm-din-product-options-' . $product->get_id(), 'cm-idin-nonce' );

		woocommerce_wp_checkbox( array(
			'id'          => '_cm_idin_age_verification_required',
			'label'       => __( '18+ Age Verification', 'cm-idin' ),
			'description' => __( '18+ Age Verification with iDIN required to order this product.', 'cm-idin' ),
		) );
	}

	/**
	 * Process product meta.
	 *
	 * @param int $post_id
	 * @param WP_Post $post
	 */
	public function process_product_meta( $post_id, $post ) {
		// Verify nonce.
		if ( ! filter_has_var( INPUT_POST, 'cm-idin-nonce' ) ) {
			return;
		}

		check_admin_referer( 'cm-din-product-options-' . $post_id, 'cm-idin-nonce' );

		// Nonce ok.
		$age_verification_required = filter_has_var( INPUT_POST, '_cm_idin_age_verification_required' ) ? 'yes' : 'no';

		update_post_meta( $post_id, '_cm_idin_age_verification_required', $age_verification_required );
	}

	/**
	 * Product notice 18+.
	 *
	 * @see https://docs.woocommerce.com/wc-apidocs/source-function-woocommerce_template_single_add_to_cart.html#951
	 */
	public function product_notice_18() {
		if ( $this->product_needs_age_verification() ) {
			include plugin_dir_path( $this->plugin->file ) . 'templates/product-notice-18.php';
		}
	}

	/**
	 * Cart notice 18+.
	 *
	 * @see https://github.com/woocommerce/woocommerce/search?utf8=%E2%9C%93&q=woocommerce_before_cart
	 */
	public function cart_notice_18() {
		if ( $this->cart_needs_age_verification() ) {
			include plugin_dir_path( $this->plugin->file ) . 'templates/cart-notice-18.php';
		}
	}

	/**
	 * Checkout process.
	 */
	public function checkout_process() {
		$transaction = WC()->session->get( 'cm_idin_transaction' );

		if ( isset( $transaction ) && $transaction->status->age->{'18y_or_older'} ) {
			return;
		}

		 wc_add_notice( __( 'Age verification via iDIN is required.', 'cm-idin' ), 'error' );
	}

	/**
	 * Update customer with iDIN data.
	 *
	 * @param WC_Customer $customer
	 * @param stdClass $transaction
	 */
	private function update_customer_from_idin_transaction( $customer, $transaction ) {
		// @see https://docs.woocommerce.com/wc-apidocs/source-class-WC_Customer.html#784-800

		// Email
		if ( isset( $transaction->email_address ) && ! empty( $transaction->email_address ) ) {
			$customer->set_email( $transaction->email_address );
			$customer->set_billing_email( $transaction->email_address );
		}

		// Name
		if ( isset( $transaction->name ) ) {
			if ( isset( $transaction->name->first_name ) ) {
				$first_name = $transaction->name->first_name;

				if ( ! empty( $first_name ) ) {
					$customer->set_first_name( $first_name );
					$customer->set_billing_first_name( $first_name );
					$customer->set_shipping_first_name( $first_name );
				}
			}

			if ( isset( $transaction->name->last_name ) ) {
				$last_name = $transaction->name->last_name;

				if ( isset( $transaction->name->last_name_prefix ) ) {
					$last_name = trim( $transaction->name->last_name_prefix . ' ' . $last_name );
				}

				if ( ! empty( $last_name ) ) {
					$customer->set_last_name( $last_name );
					$customer->set_billing_last_name( $last_name );
					$customer->set_shipping_last_name( $last_name );
				}
			}
		}

		// Address
		if ( isset( $transaction->address ) ) {
			$address_parts = array();

			if ( isset( $transaction->address->street ) ) {
				$address_parts[] = $transaction->address->street;
			}

			if ( isset( $transaction->address->house_number ) ) {
				$address_parts[] = $transaction->address->house_number;
			}

			if ( isset( $transaction->address->house_number_suffix ) ) {
				$address_parts[] = $transaction->address->house_number_suffix;
			}

			$address = trim( implode( ' ', array_filter( $address_parts ) ) );

			if ( ! empty( $address ) ) {
				$customer->set_billing_address( $address );
				$customer->set_shipping_address( $address );
			}

			if ( isset( $transaction->address->postal_code ) && ! empty( $transaction->address->postal_code ) ) {
				$customer->set_billing_postcode( $transaction->address->postal_code );
				$customer->set_shipping_postcode( $transaction->address->postal_code );
			}

			if ( isset( $transaction->address->city ) && ! empty( $transaction->address->city ) ) {
				$customer->set_billing_city( $transaction->address->city );
				$customer->set_shipping_city( $transaction->address->city );
			}

			if ( isset( $transaction->address->country ) && ! empty( $transaction->address->country ) ) {
				$customer->set_billing_country( $transaction->address->country );
				$customer->set_shipping_country( $transaction->address->country );
			}
		}

		// Telephone Number
		if ( isset( $transaction->telephone_number ) && ! empty( $transaction->telephone_number ) ) {
			$customer->set_billing_phone( $transaction->telephone_number );
		}

		$customer->save();
	}

	/**
	 * Transaction update.
	 *
	 * @see https://docs.woocommerce.com/wc-apidocs/class-WC_Customer.html
	 * @param stdClass $transaction
	 */
	public function idin_transaction_update( $transaction ) {
		// Only update if iDIN transaction has status success.
		if ( 'success' !== $transaction->status ) {
			return;
		}

		// @see https://github.com/woocommerce/woocommerce/blob/3.0.8/woocommerce.php#L439
		$customer = WC()->customer;

		if ( is_object( $customer ) ) {
			// @see https://docs.woocommerce.com/wc-apidocs/class-WC_Customer.html
			$this->update_customer_from_idin_transaction( $customer, $transaction );
		}

		// @see https://github.com/woocommerce/woocommerce/blob/master/woocommerce.php#L435
		$session = WC()->session;

		if ( is_object( $session ) ) {
			$session->set( 'cm_idin_transaction', $transaction );
		}
	}

	/**
	 * User register via iDIN.
	 *
	 * @param int $user_id
	 * @param stdClass $transaction
	 */
	public function idin_user_register( $user_id, $transaction ) {
		// @see https://docs.woocommerce.com/wc-apidocs/source-class-WC_Customer.html#784-800
		$customer = new WC_Customer( $user_id );

		$this->update_customer_from_idin_transaction( $customer, $transaction );
	}

	/**
	 * Default role filter.
	 *
	 * @param string $role
	 * @return  string
	 */
	public function filter_default_role( $role ) {
		return 'customer';
	}
}
