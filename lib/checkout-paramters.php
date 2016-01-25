<?php

function format_cep($cep){
    return preg_replace("/^(\d{5})(\d{3})$/", "\\1-\\2", $cep);
}

function format_phone($ddd, $number){
    if(strlen($number) == 8) {
        $number = preg_replace("/^(\d{4})(\d{4})$/", "\\1-\\2", $number);
    }else {
        $number = preg_replace("/^(\d{5})(\d{4})$/", "\\1-\\2", $number);
    }
    return "(".$ddd.") ".$number;
}

function format_cpf($cpf){
    return preg_replace("/^(\d{3})(\d{3})(\d{3})(\d{2})$/", "\\1.\\2.\\3-\\4", $cpf);
}

function format_cnpj($cnpj){
    return preg_replace("/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/", "\\1.\\2.\\3/\\4-\\5", $cnpj);
}

function format_first_name($name){
    return strtok($name, " ");
}

function format_last_name($name){
    $pieces = explode(' ', $name);
    return array_pop($pieces);
}
