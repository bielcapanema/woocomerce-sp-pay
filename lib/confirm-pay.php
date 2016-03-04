<?php
if (!defined('ABSPATH'))
    define('ABSPATH', dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/');

require_once ABSPATH . 'wp-config.php';
require_once ABSPATH. 'wp-content/plugins/sp-pay/assets/vendor/pagarme/Pagarme.php';
require_once dirname(__FILE__).'/integration-RDStation.php';
require_once dirname(__FILE__).'/checkout-paramters.php';

if(!$_POST['fingerprint'])
    return;

//$transaction_id = get_post_meta($order->id, '_wc_pagarme_transaction_id')[0];
$pagarme_gateway = new WC_Pagarme_Gateway();
Pagarme::setApiKey($pagarme_gateway->api_key);

if (Pagarme::validateFingerprint($_POST['id'], $_POST['fingerprint'])) {

    if('transaction' == $_POST['object']) {
        $this_transaction = PagarMe_Transaction::findById($_POST['id']);
        $order = wc_get_order($this_transaction->metadata->order_id);
        $product = get_order_products($this_transaction->metadata->order_id)[0];
        $email = $this_transaction->customer->email;
        //RD-SATATION
        $token_rdstation = get_post_meta($product['product_id'], 'token_rdstation')[0];
        $identifier = get_post_meta($product['product_id'], 'identifier')[0];
        $phone = format_phone($this_transaction->phone->ddd, $this_transaction->phone->number);
        $data_array = array('email' => $email, 'name' => $this_transaction->customer->name, 'telefone' => $phone, 'estado' => $this_transaction->address->state, 'cidade' => $this_transaction->address->city);
        //NIVEIS DE ACESSO
        $capabilities = list_capabilities(get_post_meta($product['product_id'], 'package')[0]);
        $opm_level = get_post_meta($product['product_id'], 'opm_level')[0];

        //PROCESSOS
        if ($this_transaction['status'] == 'paid') {
            //TODO FOREACH PARA VARIOS PRODUTOS
            $order->update_status('completed');
            if(is_array($product)){
                $user = get_user_by('email', $email);
                if($user){
                    if($opm_level) {
                        $user->add_cap('optimizemember_level'.$opm_level);
                    }
                }else {
                    $_POST['opm_level'] = $opm_level;
                    $_POST['fname'] = format_first_name($this_transaction->customer->name);
                    $_POST['lname'] = format_last_name($this_transaction->customer->name);
                    $_POST['email'] = $email;
                    addUserToOpm(null);
                }
                foreach($capabilities as $capability){
                    $user = get_user_by('email', $email);
                    $user->add_cap('access_optimizemember_ccap_'.trim($capability));
                }
                addLeadConversionToRdstationCrm($token_rdstation, $identifier.'_INSCRICAO', $data_array);
                add_post_meta($this_transaction->metadata->order_id, 'metodo_pagamento', $this_transaction->payment_method);
                add_post_meta($this_transaction->metadata->order_id, 'customer_user_id', $user->ID);
                add_post_meta($this_transaction->metadata->order_id, 'order_payment_date', date('d/m/Y'));
            }
       }elseif ($this_transaction['status'] == 'refused') {
            $order->cancel_order();
            addLeadConversionToRdstationCrm($token_rdstation, $identifier."_ERRO", $data_array);
       }elseif ($this_transaction['status'] == 'refunded') {
            $user = get_user_by('email', $email);

            foreach($capabilities as $capability) {
                $user->remove_cap('access_optimizemember_ccap_'.trim($capability));
            }
            if($opm_level) {
                $level = (int)$opm_level-1;
                $user->remove_cap('optimizemember_level'.$opm_level);
            }

            wc_order_fully_refunded($this_transaction->metadata->order_id);
            $order->update_status('refunded');
            $data_array = array('email' => $email, 'name' => $this_transaction->customer->name);
            addLeadConversionToRdstationCrm($token_rdstation, $identifier."_ESTORNADO", $data_array);
       }
    }
}


