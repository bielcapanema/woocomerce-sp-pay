<?php
/*
Plugin Name: $ocialPRÓ Pay
Plugin URI:  http://wordpress.org/extend/plugins/sp-pay/
Description: Plugin SocialPró de pagamentos para WordPress.
Version:     0.1-alpha
Author:      Gabriel Capanema
Author URI:  http://wordpress.org/extend/plugins/sp-pay/
Text Domain: sp-pay
Domain Path: /languages
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

if( ! defined( 'ABSPATH')){
    die('Get away !');
}


function sp_pay_load_textdomain() {
    load_plugin_textdomain( 'sp-pay', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

add_action( 'plugins_loaded', 'sp_pay_load_textdomain' );

/**
 * Shortcode Button.
 * Exemplo de uso:
 * [sp-pay-button product_id = "3484" methods = "credit_card,boleto" max_installments = "6" text="Comprar"]
 *
 * @param  string $link
 * @param  string $label
 * @param  string $data-product_id
 *
 * @return string
 */
function sp_pay_shortcode_button($attrs){

    extract( shortcode_atts( array(
        'product_id' => '',
        'methods' => '',
        'max_installments' => '',
        'text' => '',
    ), $attrs ) );

    $product = wc_get_product($product_id);
    $amount = $product->price*100;
    $pagarme_gateway = new WC_Pagarme_Gateway();
    $encryption_key = $pagarme_gateway->encryption_key;

    if(!$text) {
        $text = 'R$' . number_format($product->price, 2, ',', '.');
    }

    $token = 'false';
    $color = "#3A5795";
    $action =  plugins_url( 'lib/transaction.php', __FILE__);
    $page_link = get_permalink();
    return do_shortcode( '[shop_messages]' )."
            <div style='align:center'>
            <form method='POST' action='".$action."'>
                <script type='text/javascript'
                        src='https://assets.pagar.me/checkout/checkout.js'
                        data-button-text=" . $text . "
                        data-max-installments=" . $max_installments . "
                        data-default-installment = '1'
                        data-ui-color = " . $color . "
                        data-payment-methods= " . $methods . "
                        data-encryption-key= ".$encryption_key."
                        data-amount=" . $amount . "
                        data-create-token=" . $token . "
                        data-button-class ='button-pagarme' >
                </script>
                <input name='product_id' value=".$product_id." type='hidden'>
                <input name='_wp_http_referer' value=".$page_link." type='hidden'>
            </form>
            </div>";
}

add_shortcode('sp-pay-button', 'sp_pay_shortcode_button');

function sp_pay_register_style_scripts() {
    wp_enqueue_style('sp-pay-stylesheet', plugins_url( 'assets/css/stylesheet.css', __FILE__), array(), '0.1.0');
    //wp_enqueue_script( 'sp-pay-product', plugins_url( 'assets/js/product.js', __FILE__ ), array( 'jquery' ), '0.1.0', true );
}

add_action( 'wp_enqueue_scripts', 'sp_pay_register_style_scripts', 999);

function add_product_to_cart($product_id) {
    if ( ! is_admin() ) {
        WC()->cart->empty_cart();
        $found = false;
        //check if product already in cart
        if ( sizeof( WC()->cart->get_cart() ) > 0 ) {
            foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
                $_product = $values['data'];
                if ( $_product->id == $product_id )
                    $found = true;
            }
            // if product not found, add it
            if ( ! $found )
                WC()->cart->add_to_cart( $product_id );
        } else {
            // if no products in cart, add it
            WC()->cart->add_to_cart( $product_id );
        }
    }
}

function get_order_products($order_id){
    $order = new WC_Order( $order_id );
    $items = $order->get_items();
    $products = array();
    foreach ( $items as $item ) {
        $product = array("type" => 'product',
                         "name" => $item['name'],
                         "product_id" => $item['product_id']
        );
        $products[] = $product;
    }
    return $products;
}


//add_action( 'template_redirect', 'add_product_to_cart' );