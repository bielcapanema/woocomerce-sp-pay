<?php
if (!defined('ABSPATH'))
    define('ABSPATH', dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/');

require_once ABSPATH . 'wp-config.php';
require_once ABSPATH. 'wp-content/plugins/sp-pay/assets/vendor/pagarme/Pagarme.php';
require_once dirname(__FILE__).'/checkout-paramters.php';

$teste - WC()->api_request_url( 'WC_Pagarme_Gateway' ).'postbacks';
// vars: $installments, $amount, $payment_method, $card_hash, $costumer[]
extract($_POST['pagarme']);

if($payment_method == "credit_card") {
    $_POST['pagarme_payment_method'] = "credit-card";
    $_POST['pagarme_card_hash'] = $card_hash;
}elseif($payment_method == "boleto"){
    $_POST['pagarme_payment_method'] = "banking-ticket";
}
$_POST['pagarme_installments'] = $installments;

// vars: $name, $email, $document_number, $phone[], $address[]
extract($customer);
$_POST['billing_email'] = $email;
$_POST['billing_first_name'] = format_first_name($name);
$_POST['billing_last_name'] = format_last_name($name);
if(strlen($document_number) == 11){
    $_POST['billing_persontype'] = "1";
    $_POST['billing_cpf'] = format_cpf($document_number);
    $_POST['billing_cnpj'] = "";
    $_POST['billing_company'] = "";
}elseif(strlen($document_number) == 14){
    $_POST['billing_persontype'] = "2";
    $_POST['billing_cnpj'] = format_cnpj($document_number);;
    $_POST['billing_cpf'] = "";
    $_POST['billing_company'] = "Company";
}

// vars: $ddd, $number
extract($phone);
$_POST['billing_phone'] = format_phone($ddd, $number);
$_POST['billing_cellphone'] = "";

// vars: $zipcode, $street, $street_number, $complementary, $neighborhood, $city, $state
extract($address);
$_POST['billing_country'] = "BR";
$_POST['billing_postcode'] = format_cep($zipcode);
$_POST['billing_address_1'] = $street;
$_POST['billing_address_2'] = $complementary;
$_POST['billing_number'] = $street_number;
$_POST['billing_neighborhood'] = $neighborhood;
$_POST['billing_city'] = $city;
$_POST['billing_state'] = $state;

add_product_to_cart($_POST['product_id']);
//TODO CHECK AMOUNT OF PAGARME AND ORDER



$_POST['order_comments'] = "";
$_POST['payment_method'] = "pagarme";
$_POST['_wpnonce'] = wp_create_nonce('woocommerce-process_checkout');
try {
    $product = wc_get_product($_POST['product_id']);
    $product_price = $product->price*100;
    if($amount == $product_price) {
        $checkout = new WC_Checkout();
        $result = $checkout->process_checkout();
        // Abort if errors are present
    }else{
        throw new Exception( 'Invalid purchase!');
    }
    if ( wc_notice_count( 'error' ) > 0 )
        throw new Exception();

}catch ( Exception $e ) {
    if ( ! empty( $e ) ) {

        wp_safe_redirect(
            apply_filters( 'woocommerce_checkout_no_payment_needed_redirect', $_POST['_wp_http_referer'])
        );

    }
}
exit;