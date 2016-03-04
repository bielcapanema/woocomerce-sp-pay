<?php
if (!defined('ABSPATH'))
    define('ABSPATH', dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/');
require_once ABSPATH. 'wp-content/plugins/sp-pay/assets/vendor/pagarme/Pagarme.php';
require_once ABSPATH. 'wp-content/plugins/sp-pay/assets/vendor/pagarme/lib/Pagarme/Bank_Account.php';
require_once ABSPATH . 'wp-config.php';

try {
$pagarme_gateway = new WC_Pagarme_Gateway();
Pagarme::setApiKey($pagarme_gateway->api_key);

$order = wc_get_order($_POST['order_id']);
$payment_method = get_post_meta($order->id, 'metodo_pagamento', true);
$pagarme_id = get_post_meta($order->id, '_wc_pagarme_transaction_id')[0];

$transaction = PagarMe_Transaction::findById($pagarme_id);


    if ($transaction->status == 'paid') {
        if($payment_method == 'credit_card') {
            $transaction->refund();
        }elseif($payment_method == 'boleto'){
            if(!$_POST['bank_code'] || !$_POST['agencia'] || !$_POST['conta'] || !$_POST['document_number'] || !$_POST['legal_name']){
                throw new Exception('Dados incompletos para o estorno. Preencha todo o formulário antes de pedir o estorno.');
            }

            $transaction->refund(['bank_account' => array(
                "bank_code" => trim($_POST['bank_code']),
                "agencia" => trim($_POST['agencia']),
                "agencia_dv" => trim($_POST['agencia_dv']),
                "conta" => trim($_POST['conta']),
                "conta_dv" => trim($_POST['conta_dv']),
                "document_number" => trim($_POST['document_number']),
                "legal_name" => trim($_POST['legal_name'])
            )]);
        }else{
            throw new Exception('Tente novamente, se o erro persistir entre em contato.');
        }
        wc_add_notice('Pedido de estorno feito com sucesso.', 'success' );
        wc_order_fully_refunded($_POST['order_id']);
        $order->update_status('refunded');
    } else {
        throw new Exception('Tente novamente, se o erro persistir entre em contato.');
    }

}catch ( Exception $e ) {
    if ( ! empty( $e ) ) {
        wc_add_notice( $e->getMessage(), 'error' );
    }
}

wp_redirect($_POST['_wp_http_referer']);
exit;

