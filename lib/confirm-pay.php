<?php
if (!defined('ABSPATH'))
    define('ABSPATH', dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/');

require_once ABSPATH . 'wp-config.php';
require_once ABSPATH. 'wp-content/plugins/sp-pay/assets/vendor/pagarme/Pagarme.php';
require_once dirname(__FILE__).'/integration-RDStation.php';
require_once dirname(__FILE__).'/checkout-paramters.php';

if(!$_POST['fingerprint'])
    return;

$pagarme_gateway = new WC_Pagarme_Gateway();
Pagarme::setApiKey($pagarme_gateway->api_key);

if (Pagarme::validateFingerprint($_POST['id'], $_POST['fingerprint'])) {

    if('transaction' == $_POST['object']) {
        $this_transaction = PagarMe_Transaction::findById($_POST['id']);
        $order = wc_get_order($this_transaction->metadata->order_id);

        if ($this_transaction['status'] == 'paid') {
            //TODO FOREACH PARA VARIOS PRODUTOS
            $order->update_status('completed');
            $product = get_order_products($this_transaction->metadata->order_id)[0];

            if(is_array($product)){
                $opm_level = get_post_meta($product['product_id'], 'opm_level')[0];
                $token_rdstation = get_post_meta($product['product_id'], 'token_rdstation')[0];
                $identifier = get_post_meta($product['product_id'], 'identifier')[0];
                $email = $this_transaction->customer->email;
                $_POST['opm_level'] = $opm_level;
                $_POST['fname'] = format_first_name($this_transaction->customer->name);
                $_POST['lname'] = format_last_name($this_transaction->customer->name);
                $_POST['email'] = $email;
                addUserToOpm(null);
                $data_array = array('email' => $email, 'name' => $this_transaction->customer->name);
                addLeadConversionToRdstationCrm($token_rdstation, $identifier, $data_array);
            }
       }elseif ($this_transaction['status'] == 'refused') {
            $order->cancel_order();
       }
    }
}


