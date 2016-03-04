>>Passo a passo integração Pagar.me no Wordpress

>>Instalação dos plugins WooCommerce e WooCommerce Extra Checkout Fields for Brazil.
>>Baixar e instalar o plugin WooCommerce Pagar.me EDITED, $ocialPRÓ Pay.
>>Forçar a tradução do site para atualizar a tradução dos plugins.
  dentro da pasta do seu tema no wordpress encontre o arquivo functions.php
	ex: public ->wp-content ->themes>epico ->functions.php
	adicione o seguinte trecho de código:

    add_action( 'template_redirect', 'wc_custom_redirect_after_purchase' );
    function wc_custom_redirect_after_purchase() {
      global $wp;
      $product = get_order_products($wp->query_vars['order-received'])[0];
      $url_redirect = get_post_meta($product['product_id'], 'url_thankyou_page')[0];
      $transaction_data = get_post_meta( $wp->query_vars['order-received'], '_wc_pagarme_transaction_data');
      $boleto_url = $transaction_data[0]['boleto_url'];

      if ( is_checkout() && ! empty( $wp->query_vars['order-received'] ) && $url_redirect) {
         if($boleto_url){
            wp_redirect($url_redirect.'?url_boleto='.$boleto_url);
         }else {
            wp_redirect($url_redirect);
         }
         exit;
      }
    }

>> WooCommerce > Configurações > Finalizar Compra > Pagar.me: Ative o Pagar.me selecionando o checkbox e copie suas ApiKey e EncryptionKey para os respectivos campos.
   Verifique as demais configurações do WooCommerce e adeque ao seu caso.

>> Produtos > Adicionar Produto: Preencha os campos listados e no final da página em “Campos Personalizados” adicione os campos:
    1. identifier (identificador que será enviado ao RD Station).
    2. opm_level (nível que será disponibilizado para quem comprar o produto).
    3. token_rdstation (token público do RD Station).
    4. url_thankyou_page (caso possua uma página de obrigado para o produto).

>>Para adicionar o botão para chamar o checkout do Pagar.me, basta inserir o seginte shortcode em sua página: [sp-pay-button product_id = "3611" methods = "credit_card,boleto" max_installments = "12" text = "Comprar" free_installments = "1" interest_rate = "5"]
    1. product_id: id do produto criado anteriormente.
    2. methods: metodos de pagamento(“credit_card,boleto”, “credit_card” e “boleto”.
    3. max_installments: máximo de parcelamentos da venda.
    4. text: texto que sera mostrado no botão.
    5. free_installments: quantas parcelas não receberão juros no parcelamento.
    6. interest_rate: porcentagem de juros.

>>Para editar e-mails basta baixar e instalar o plugin SB Welcome Email Editor na versão traduzida que sera disponibilizada junto com os plugins anteriores.
    > Configurações > SB Welcome Email para editar os e-mails.

>>para realizar um estorno manual de um pagamento bastar entrar em sua conta Pagar.me e acessar: > Menu > Transações: no final da pagina identificar a transação e com um click abrir os dados da transação, no lado superior direito clicar no botão “ Estornar” e todo o processo de estorno será realizado, tirando o acesso do comprador do produto e enviando um post para o RD Station com o identificador do produto concatenado com “_ESTORNADO”.

>>se você possuir uma página de obrigado após a compra diferente é necessário adicionar o seguinte js nela para exibição de um botão para pegar o boleto:
    <script>
    function getParameterByName(name, url) {
        if (!url) url = window.location.href;
        name = name.replace(/[\[\]]/g, "\\$&");
        var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
            results = regex.exec(url);
        if (!results) return null;
        if (!results[2]) return '';
        return decodeURIComponent(results[2].replace(/\+/g, " "));
    }

    if(getParameterByName('url_boleto')){
      var btn = document.createElement("BUTTON");
      var t = document.createTextNode("CLICK ME");
      btn.appendChild(t);
      document.getElementById("le_body_row_2_col_1_el_1").appendChild(btn);
    }
    </script>

>>O sistema de estorno funciona adicionando o shortcode [sp-pay-refound] que exbirá uma tabela com os cursos do usuário e um botão de reembolso para cada curso se o curso tiver sido comprado dentro de um período de 30 dias.

>>qualquer dúvida enviar um e-mail com assunto “duvida integração pagar.me wordpress” para capanema.ggfc@gmail.com.

