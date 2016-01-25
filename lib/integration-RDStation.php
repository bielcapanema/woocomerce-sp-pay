<?php
/**
 * RD Station - Integrações
 * addLeadConversionToRdstationCrm()
 * Envio de dados para a API de leads do RD Station
 *
 * Parâmetros:
 *     ($rdstation_token) - token da sua conta RD Station ( encontrado em https://www.rdstation.com.br/docs/api )
 *     ($identifier) - identificador da página ou evento ( por exemplo, 'pagina-contato' )
 *     ($data_array) - um Array com campos do formulário ( por exemplo, array('email' => 'teste@rdstation.com.br', 'nome' =>'Fulano') )
 */
function addLeadConversionToRdstationCrm( $rdstation_token, $identifier, $data_array ) {
    $api_url = "https://www.rdstation.com.br/api/1.3/conversions";

    try {
        if (empty($data_array["token_rdstation"]) && !empty($rdstation_token)) { $data_array["token_rdstation"] = $rdstation_token; }
        if (empty($data_array["identificador"]) && !empty($identifier)) { $data_array["identificador"] = $identifier; }
        if (empty($data_array["c_utmz"])) { $data_array["c_utmz"] = $_COOKIE["__utmz"]; }
        unset($data_array["password"], $data_array["password_confirmation"], $data_array["senha"],
            $data_array["confirme_senha"], $data_array["captcha"], $data_array["_wpcf7"],
            $data_array["_wpcf7_version"], $data_array["_wpcf7_unit_tag"], $data_array["_wpnonce"],
            $data_array["_wpcf7_is_ajax_call"]);

        if ( !empty($data_array["token_rdstation"]) && !( empty($data_array["email"]) && empty($data_array["email_lead"]) ) ) {
            $data_query = http_build_query($data_array);
            if (in_array ('curl', get_loaded_extensions())) {
                $ch = curl_init($api_url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data_query);
                //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_exec($ch);
                curl_close($ch);
            } else {
                $params = array('http' => array('method' => 'POST', 'content' => $data_query, 'ignore_errors' => true));
                $ctx = stream_context_create($params);
                //$fp = @fopen($api_url, 'rb', false, $ctx);
            }
        }
    } catch (Exception $e) { }
}
?>