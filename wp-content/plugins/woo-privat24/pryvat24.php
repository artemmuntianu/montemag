<?php

/*
Plugin Name: Woocommerce - Шлюз оплати Приват24
Plugin URI: http://moomoo.com.ua
Description: Додає можливість оплати через сервіс privat24.ua у плагін woocommerce.
Author: Vitaliy 'mr.psiho' Kiyko
Version: 1.1.3
Author URI: http://moomoo.com.ua
*/

if ( ! defined( 'WOO_PRYVAT24_DOMAIN' ) )
    define( 'WOO_PRYVAT24_DOMAIN', 'woo-pryvat24' );

if( !load_plugin_textdomain( WOO_PRYVAT24_DOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ) )
    load_plugin_textdomain( WOO_PRYVAT24_DOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

add_action('plugins_loaded', 'Privat24WoocommerceGateway', 0 );

function Privat24WoocommerceGateway() {

add_action( 'init', 'woo_pyvat24_check_complete' );

if (!class_exists('WC_Payment_Gateway')) return;

    class WC_Gateway_Privat24 extends WC_Payment_Gateway {

	var $notify_url;

    /**
     * Constructor for the gateway.
     *
     * @access public
     * @return void
     */
	public function __construct() {
		global $woocommerce;

        $this->id           = 'privat24';
        $this->icon         = 'https://privat24.privatbank.ua/p24/img/buttons/api_logo_2.jpg';
        $this->has_fields   = false;
        $this->method_title = __( 'Приват24', WOO_PRYVAT24_DOMAIN );
        $this->liveurl 		= 'https://api.privatbank.ua/p24api/ishop';
        $this->interactionurl	= add_query_arg( array('wc-api' => 'privat24_process_payment'), get_bloginfo('url') );

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables
		$this->title 			= $this->get_option( 'title' );
		$this->description 		= $this->get_option( 'description' );
		$this->pryvatid		    = $this->get_option( 'pryvatid' );
        $this->pryvatpass		= $this->get_option( 'pryvatpass' );
		$this->redirecturl	    = $this->get_option( 'redirecturl' );

		// Actions
        //add_action( 'init', array($this, 'check_complete') );
        add_action( 'woocommerce_receipt_privat24', array($this, 'receipt_page'));
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

    }

	/**
	 * Admin Panel Options
	 * - Options for bits like 'title' and availability on a country-by-country basis
	 *
	 * @since 1.0.0
	 */
	public function admin_options() {

		?>
		<h3><?php _e( 'Privat24', 'woocommerce' ); ?></h3>
		<p><?php _e( 'Privat24 надсилає користувача до http://privat24.ua для вказання платіжної інформації.', WOO_PRYVAT24_DOMAIN ); ?></p>

        <?php if ( $this->is_valid_for_use() ) { ?>

			<table class="form-table">
			<?php
    			// Generate the HTML For the settings form.
    			$this->generate_settings_html();
			?>
			</table><!--/.form-table-->

        <?php } else { ?>
            <p><?php _e('Вибачте, цей платіжний шлюз не підтримує валюту вашого онлайн магазину.', WOO_PRYVAT24_DOMAIN); ?></p>
        <?php } ?>

		<?php
	}

    public function is_valid_for_use() {

            if (!in_array(get_option('woocommerce_currency'), array('USD', 'EUR', 'UAH'))) {
                return false;
            }

            return true;
    }

    /**
     * Initialise Gateway Settings Form Fields
     *
     * @access public
     * @return void
     */
    function init_form_fields() {

    	$this->form_fields = array(
			'enabled' => array(
							'title' => __( 'Enable/Disable', 'woocommerce' ),
							'type' => 'checkbox',
							'label' => __( 'Увімкнути Privat24', WOO_PRYVAT24_DOMAIN ),
							'default' => 'yes'
						),
			'title' => array(
							'title' => __( 'Title', 'woocommerce' ),
							'type' => 'text',
							'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
							'default' => __( 'Приват24', WOO_PRYVAT24_DOMAIN ),
							'desc_tip'      => true,
						),
			'description' => array(
							'title' => __( 'Description', 'woocommerce' ),
							'type' => 'textarea',
							'description' => __( 'This controls the description which the user sees during checkout.', 'woocommerce' ),
							'default' => __( 'Платіть з допомогою Privat24 (метод підходить лише якщо у вас є відкритий рахунок у "Приватбанку")', WOO_PRYVAT24_DOMAIN )
						),
			'pryvatid' => array(
							'title' => __( 'Privat24 мерчант ID', WOO_PRYVAT24_DOMAIN ),
							'type' 			=> 'text',
							'description' => __( 'Будь ласка, вкажіть ваш Privat24 merchant ID.', WOO_PRYVAT24_DOMAIN ),
							'default' => '',
							'desc_tip'      => true,
							'placeholder'	=> ''
						),
			'pryvatpass' => array(
							'title' => __( 'Privat24 пароль мерчанта', WOO_PRYVAT24_DOMAIN ),
							'type' 			=> 'text',
							'description' => __( 'Будь ласка, вкажіть ваш Privat24 пароль мерчанта.', WOO_PRYVAT24_DOMAIN ),
							'default' => '',
							'desc_tip'      => true,
							'placeholder'	=> ''
						),
			'redirecturl' => array(
							'title' => __( 'Url повернення покупця', WOO_PRYVAT24_DOMAIN ),
							'type' 			=> 'text',
							'description' => __( 'Будь ласка, вкажіть URL на який потрібно повертати покупця з сайту Privat24.', WOO_PRYVAT24_DOMAIN ),
							'default' => '',
							'desc_tip'      => true,
							'placeholder'	=> ''
						)
			);

    }

		/**
		 * Generate the pryvat button link
		 **/
		public function generate_pryvat_form( $order_id ) {
            global $woocommerce;

			$order = new WC_Order( $order_id );

		    // Remove cart
		    $woocommerce->cart->empty_cart();

			$pryvat_adr         = $this->liveurl;
            $redirect_url       = $this->redirecturl;
            $interaction_url    = $this->interactionurl;
            $sCurrency          = get_option('woocommerce_currency');

			return '<form action="'.$pryvat_adr.'" method="post" id="pryvat_payment_form">
					<input type="hidden" name="amt" value="'.$order->order_total.'" />
					<input type="hidden" name="ccy" value="'.$sCurrency.'" />
					<input type="hidden" name="merchant" value="'.$this->pryvatid.'" />
					<input type="hidden" name="order" value="'.$order_id.'" />
					<input type="hidden" name="details" value="'.sprintf(__('Оплата за замовлення № %s', WOO_PRYVAT24_DOMAIN), $order_id).'" />
					<input type="hidden" name="ext_details" value="" />
					<input type="hidden" name="pay_way" value="privat24" />
					<input type="hidden" name="return_url" value="'.$redirect_url.'" />
					<input type="hidden" name="server_url" value="'.$interaction_url.'" />
					<input type="submit" class="button-alt" id="submit_pryvat_payment_form" value="'.__('Pay for order', 'woocommerce').'" /> <a class="button cancel" href="'.esc_url($order->get_cancel_order_url()).'">'.__('Cancel order &amp; restore cart', 'woocommerce').'</a>
				</form>';

		}

		/**
		 * Process the payment and return the result
		 **/
		function process_payment( $order_id ) {

			$order = wc_get_order( $order_id );

		    // Remove cart
		    //$woocommerce->cart->empty_cart();

			return array(
				'result' 	=> 'success',
                'redirect'	=> $order->get_checkout_payment_url( true )
			);

		}

		/**
		 * receipt_page
		 **/
		function receipt_page( $order ) {

			echo '<p>' . __( 'Дякуємо! Ваше замовлення очікує оплати. Ви можете натиснути кнопку "Оплатити" і потім ви будете направлені на сторінку оплати Приват24.', WOO_PRYVAT24_DOMAIN ) . '</p>';

			echo $this->generate_pryvat_form( $order );

		}

    }

		//complete order
		function woo_pyvat24_check_complete() {
			if ( isset($_POST['payment']) ) {

                $WC_Gateway_Privat24 = new WC_Gateway_Privat24;

			    $sMerchantId         = $WC_Gateway_Privat24->pryvatid;
			    $sMerchantPass       = $WC_Gateway_Privat24->pryvatpass;

				//create array from POST data
                $aPayment = $_POST['payment'];
				$str_arr = explode('&', $_POST['payment']);
				$i = 0;
				foreach ( $str_arr as $value ) {
					$data = explode('=', $value);
					$post_arr[$i]['key'] = $data[0];
					$post_arr[$i]['value'] = $data[1];
						if ( $post_arr[$i]['key'] == 'state' ) { $sStatus = $post_arr[$i]['value']; }
						if ( $post_arr[$i]['key'] == 'order' ) { $sOrderId = $post_arr[$i]['value']; }
					$i++;
				}

                $sSignature     = $_POST['signature'];
                $sMySignature   = sha1(md5($aPayment.$sMerchantPass));

				if ( $sStatus == 'ok' && $sSignature == $sMySignature ) {
					$order = new WC_Order( $sOrderId );

					if ( $order->status !== 'completed' ) {
					    $order->payment_complete();
						$order->add_order_note('processing', __( 'Оплачено з Privat24', WOO_PRYVAT24_DOMAIN ));
					}
				} elseif ( $sStatus == 'fail' && $sSignature == $sMySignature ) {
					$order = new WC_Order( $sOrderId );

					if ( $order->status !== 'completed' ) {
						$order->update_status('failed', __( 'Невдала оплата!', WOO_PRYVAT24_DOMAIN ));
                        wc_add_notice( __( 'Невдала оплата!', WOO_PRYVAT24_DOMAIN ), 'error' );
					}
				} elseif ( $sStatus == 'test' && $sSignature == $sMySignature ) {
					$order = new WC_Order( $sOrderId );

					if ( $order->status !== 'completed' ) {
					    $order->payment_complete();
						$order->add_order_note('completed', __( 'Тест: оплачено Privat24!', WOO_PRYVAT24_DOMAIN ));
					}
                }

			}
		}

	/**
    * Add the Gateway to WooCommerce
    **/
    function woocommerce_add_gateway_privat24_gateway($methods) {
        $methods[] = 'WC_Gateway_Privat24';
        return $methods;
    }
    add_filter('woocommerce_payment_gateways', 'woocommerce_add_gateway_privat24_gateway' );

}

//
class privat24_process_payment {

		/**
		 *  check response from payment service
		*/
	    public function __construct( ) {

                $WC_Gateway_Privat24 = new WC_Gateway_Privat24;

			    $sMerchantId         = $WC_Gateway_Privat24->pryvatid;
			    $sMerchantPass       = $WC_Gateway_Privat24->pryvatpass;

				//create array from POST data
                $aPayment = $_POST['payment'];
				$str_arr = explode('&', $_POST['payment']);
				$i = 0;
				foreach ( $str_arr as $value ) {
					$data = explode('=', $value);
					$post_arr[$i]['key'] = $data[0];
					$post_arr[$i]['value'] = $data[1];
						if ( $post_arr[$i]['key'] == 'state' ) { $sStatus = $post_arr[$i]['value']; }
						if ( $post_arr[$i]['key'] == 'order' ) { $sOrderId = $post_arr[$i]['value']; }
					$i++;
				}

                $sSignature     = $_POST['signature'];
                $sMySignature   = sha1(md5($aPayment.$sMerchantPass));

				if ( $sStatus == 'ok' && $sSignature == $sMySignature ) {
					$order = new WC_Order( $sOrderId );

					if ( $order->status !== 'completed' ) {
					    $order->payment_complete();
						$order->add_order_note('processing', __( 'Оплачено з Privat24', WOO_PRYVAT24_DOMAIN ));
					}
				} elseif ( $sStatus == 'fail' && $sSignature == $sMySignature ) {
					$order = new WC_Order( $sOrderId );

					if ( $order->status !== 'completed' ) {
						$order->update_status('failed', __( 'Невдала оплата!', WOO_PRYVAT24_DOMAIN ));
                        wc_add_notice( __( 'Невдала оплата!', WOO_PRYVAT24_DOMAIN ), 'error' );
					}
				} elseif ( $sStatus == 'test' && $sSignature == $sMySignature ) {
					$order = new WC_Order( $sOrderId );

					if ( $order->status !== 'completed' ) {
					    $order->payment_complete();
						$order->add_order_note('completed', __( 'Тест: оплачено Privat24!', WOO_PRYVAT24_DOMAIN ));
					}
                }
        }

}