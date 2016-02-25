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
 * [sp-pay-button product_id = "3484" methods = "credit_card,boleto" max_installments = "6" text="Comprar" free_installments="1" interest_rate="5"]
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
        'free_installments' => '',
        'interest_rate' => ''

    ), $attrs ) );

    $product = wc_get_product($product_id);
    $amount = $product->price*100;
    $pagarme_gateway = new WC_Pagarme_Gateway();
    $encryption_key = $pagarme_gateway->encryption_key;
    if(!$interest_rate){
        $interest_rate = 0;
    }

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
                        data-button-class ='button-pagarme'
                        data-free-installments = ".$free_installments."
                        data-interest-rate =".$interest_rate.">
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

function sp_pay_shortcode_refound($attrs){
    wp_get_image_editor( plugins_url( 'assets/images/ui-icons_444444_256x240.png', __FILE__) );
    wp_get_image_editor( plugins_url( 'assets/images/ui-icons_555555_256x240.png', __FILE__) );
    wp_get_image_editor( plugins_url( 'assets/images/ui-icons_777620_256x240.png', __FILE__) );
    wp_get_image_editor( plugins_url( 'assets/images/ui-icons_777777_256x240.png', __FILE__) );
    wp_enqueue_style('sp-pay-jqueryui-css', plugins_url( 'assets/css/jquery-ui.min.css', __FILE__), array(), '0.1.0');
    wp_enqueue_script('sp-pay-jquery-ui-js', plugins_url( 'assets/js/jquery-ui.min.js', __FILE__), array(), '0.1.0');
    wp_enqueue_script('sp-pay-refound-js', plugins_url( 'assets/js/refound.js', __FILE__), array(), '0.1.0');

    extract( shortcode_atts( array(
        'product_id' => '',
        'methods' => '',
        'max_installments' => '',
        'text' => '',
        'free_installments' => '',
        'interest_rate' => ''

    ), $attrs ) );
    $current_user = wp_get_current_user();
    $customer_orders = get_posts( array(
        'numberposts' => -1,
        'meta_key'    => 'customer_user_id',
        'meta_value'  => $current_user->ID,
        'post_type'   => wc_get_order_types(),
        'post_status' => array_keys( wc_get_order_statuses() ),
    ) );
    $refound_action = site_url()."/wp-content/plugins/sp-pay/lib/refound.php";
    $content = '<div id="dialog-form-boleto" title="Reembolso">
                  <form action='.$refound_action.' method="post" id="formRefund2">
                    <p class="validateTips"></p>
                    <fieldset>
                      <p>Complete os dados abaixo para receber estorno do pagamento do produto. Pedindo o estorno seu acesso ao respectivo produto será bloqueado e você receberá
                      seu dinheiro de volta.</p>
                      <label for="bank_code">Código do banco</label>
                      <input type="text" name="bank_code" id="bank_code" value="" class="text ui-widget-content ui-corner-all">
                      <label for="agencia">Agência</label>
                      <input type="text" name="agencia" id="agencia" value="" class="text ui-widget-content ui-corner-all">
                      <label for="agencia_dv">Dígito verificador da agência (caso o banco utilize)</label>
                      <input type="text" name="agencia_dv" id="agencia_dv" value="" class="text ui-widget-content ui-corner-all">
                      <label for="conta">Conta</label>
                      <input type="text" name="conta" id="conta" value="" class="text ui-widget-content ui-corner-all">
                      <label for="conta_dv">Dígito verificador da conta (caso o banco utilize)</label>
                      <input type="text" name="conta_dv" id="conta_dv" value="" class="text ui-widget-content ui-corner-all">
                      <label for="document_number">CPF ou CNPJ</label>
                      <input type="text" name="document_number" id="document_number" value="" class="text ui-widget-content ui-corner-all">
                      <label for="legal_name">Nome ou Razão social do favorecido</label>
                      <input type="text" name="legal_name" id="legal_name" value="" class="text ui-widget-content ui-corner-all">
                      <input type="hidden" name="order_id" id="order_id" />
                      <input type="hidden" name="_wp_http_referer" id="_wp_http_referer" />
                      <!-- Allow form submission with keyboard without duplicating the dialog button -->
                      <input type="submit" id="submit_form2" tabindex="-1" style="position:absolute; top:-1000px">
                    </fieldset>
                  </form>
                </div>
                <div><table>

                <div id="dialog-form-credit" title="Reembolso">
                  <form action='.$refound_action.' method="post" id="formRefund1" >
                  <p class="validateTips"></p>
                    <fieldset>
                      <p>Deseja realmente receber o estorno do pagamento do produto?</p>
                      <p>Ao pedir o estorno seu acesso ao respectivo produto será bloqueado e você receberá
                      seu dinheiro de volta.</p>
                      <input type="hidden" name="order_id" id="order_id" />
                      <input type="hidden" name="_wp_http_referer" id="_wp_http_referer" />
                      <input type="submit" id="submit_form1" tabindex="-1" style="position:absolute; top:-1000px">
                    </fieldset>
                  </form>
                </div>'.do_shortcode( '[shop_messages]' ).'
                <div><table class="refound_products_list">';

    $url_refererer = get_permalink();
    foreach($customer_orders as $order) {
        if ($order->post_status == 'wc-completed') {
            $products = get_order_products($order->ID);
            $product_list = '';
            foreach ($products as $product) {
                $product_name = $product['name'];
                $product_list .= $product_name . ';';
            }
            $product_list = substr($product_list, 0, -1);
            $payment_method = get_post_meta($order->ID, 'metodo_pagamento', true);

            add_post_meta($order->ID, 'order_payment_date', date('d/m/Y'));
            $order_payment_date = DateTime::createFromFormat('d/m/Y', get_post_meta($order->ID, 'order_payment_date', true));

            $data_atual = new DateTime();
            $diferenca = $order_payment_date->diff($data_atual)->days;

            if ($payment_method == 'credit_card') {
                $content .= '<tr><td><p>' . $product_list . '</p></td>';
                if($diferenca > 30) {
                    $content .= '<td></td>';
                }else{
                    $content .= '<td><button class="refound-button-credit uf_epicoepico_author-button" data-order_id=' . $order->ID . ' data-wp_http_referer=' . $url_refererer . '>REEMBOLSO</button></td>';
                }
            } elseif($payment_method == 'boleto') {
                $content .= '<tr><td><p>' . $product_list . '</p></td>';
                if($diferenca > 30) {
                    $content .= '<td></td>';
                }else{
                    $content .= '<td><button class="refound-button-boleto uf_epicoepico_author-button" data-order_id=' . $order->ID . ' data-wp_http_referer=' . $url_refererer . '>REEMBOLSO</button></td>';
                }
            }
            $content .= '</tr>';
        }
    }

    $content .= '</table></div>';

    return $content;
}

add_shortcode('sp-pay-refound', 'sp_pay_shortcode_refound');


