<?php
/**
 * Main class
 *
 * @author Your Inspiration Themes
 * @package YITH WooCommerce Zoom Magnifier
 * @version 1.1.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly


if ( ! class_exists( 'YITH_WooCommerce_Zoom_Magnifier' ) ) {
	/**
	 * YITH WooCommerce Zoom Magnifier
	 *
	 * @since 1.0.0
	 */
	class YITH_WooCommerce_Zoom_Magnifier {

		/**
		 * Plugin object
		 *
		 * @var string
		 * @since 1.0.0
		 */
		public $obj = null;

		/**
		 * Constructor
		 *
		 * @return mixed|YITH_WCMG_Admin|YITH_WCMG_Frontend
		 * @since 1.0.0
		 */
		public function __construct() {
			/** Stop the plugin on mobile devices */
			if ( ( 'yes' != get_option( 'yith_wcmg_enable_mobile' ) ) && wp_is_mobile() ) {
				return;
			}

            add_action( 'wp_ajax_nopriv_yith_wc_zoom_magnifier_get_main_image', array(
                $this,
                'yith_wc_zoom_magnifier_get_main_image_call_back'
            ), 10 );

            add_action( 'wp_ajax_yith_wc_zoom_magnifier_get_main_image', array(
                $this,
                'yith_wc_zoom_magnifier_get_main_image_call_back'
            ), 10 );

			// actions
			add_action( 'init', array( $this, 'init' ) );

			if ( is_admin() && ( ! isset( $_REQUEST['action'] ) || ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] != 'yith_load_product_quick_view' ) ) ) {
				$this->obj = new YITH_WCMG_Admin(  );
			} else {
				$this->obj = new YITH_WCMG_Frontend(  );
			}

			return $this->obj;
		}

        /**
         * Ajax method to retrieve the product main imavge
         *
         * @access public
         * @author Daniel Sanchez Saez
         * @since  1.3.3
         */
        public function yith_wc_zoom_magnifier_get_main_image_call_back(){

		    $product_id = ( isset( $_POST[ 'product_id' ] ) ? $_POST[ 'product_id' ] : 0 );

            $url	= wp_get_attachment_image_src ( get_post_thumbnail_id ( $product_id ), "full" );

            $response = array(
                "url" => $url[ 0 ],
            );
            wp_send_json( $response );

        }

		/**
		 * Init method:
		 *  - default options
		 *
		 * @access public
		 * @since 1.0.0
		 */
		public function init() {
			$this->_image_sizes();
		}


		/**
		 * Add image sizes
		 *
		 * Init images
		 *
		 * @access protected
		 * @return void
		 * @since 1.0.0
		 */
		protected function _image_sizes() {
			$size   = get_option( 'woocommerce_magnifier_image' );
			$width  = $size['width'];
			$height = $size['height'];
			$crop   = isset( $size['crop'] ) ? true : false;

			add_image_size( 'shop_magnifier', $width, $height, $crop );
		}
	}
}