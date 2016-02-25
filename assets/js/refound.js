jQuery(function() {
    var dialog, form,

    // From http://www.whatwg.org/specs/web-apps/current-work/multipage/states-of-the-type-attribute.html#e-mail-state-%28type=email%29
        emailRegex = /^[a-zA-Z0-9.!#jQuery%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/,
        bank_code = jQuery("#bank_code"),
        agencia = jQuery( "#agencia" ),
        conta = jQuery( "#conta" ),
        document_number = jQuery( "#document_number" ),
        legal_name = jQuery( "#legal_name" ),
        allFields = jQuery( [] ).add( bank_code ).add( agencia ).add( conta ).add( document_number ).add( legal_name ),
        tips = jQuery( ".validateTips" );

    function updateTips( t ) {
        tips
            .text( t )
            .addClass( "ui-state-highlight" );
        setTimeout(function() {
            tips.removeClass( "ui-state-highlight" );
            tips.text( "" );
        }, 3500 );
    }

    function checkLength( o, n, min, max ) {
        if ( o.val().length > max || o.val().length < min ) {
            o.addClass( "ui-state-error" );
            updateTips( "O tamanho " + n + " deve estar entre " +
                min + " e " + max + "." );
            return false;
        } else {
            return true;
        }
    }

    function checkRegexp( o, regexp, n ) {
        if ( !( regexp.test( o.val() ) ) ) {
            o.addClass( "ui-state-error" );
            updateTips( n );
            return false;
        } else {
            return true;
        }
    }

    function addUser() {
        var valid = true;
        allFields.removeClass( "ui-state-error" );

        valid = valid && checkLength( bank_code, "código do banco", 3, 3 );
        valid = valid && checkLength( agencia, "agência", 4, 4 );
        valid = valid && checkLength( conta, "conta", 5, 5 );
        valid = valid && checkLength( document_number, "CPF ou CNPJ", 10, 14 );
        valid = valid && checkLength( legal_name, "nome ou razão social", 5, 40);

        valid = valid && checkRegexp( legal_name, /^[a-z]([0-9a-z_\s])+$/i, "Formato de nome ou razão social inválido, verifique." );
        valid = valid && checkRegexp( bank_code, /^([0-9])+$/, "Formato de código do banco inválido, apenas números." );
        valid = valid && checkRegexp( agencia, /^([0-9])+$/, "Formato de agência inválido, apenas números." );
        valid = valid && checkRegexp( conta, /^([0-9])+$/, "Formato de conta inválido, apenas números." );
        valid = valid && checkRegexp( document_number, /^([0-9])+$/, "Formato de CPF ou CNPJ inválido, apenas números." );

        return valid;
    }

    dialog1 = jQuery( "#dialog-form-credit" ).dialog({
        autoOpen: false,
        height: 300,
        width: 350,
        modal: true,
        buttons: {
            'Cancelar': function() {
                dialog1.dialog("close");
            },
            'Receber': function() {
                jQuery( "#formRefund1" ).submit();
            }
        }
    });

    dialog2 = jQuery( "#dialog-form-boleto" ).dialog({
        autoOpen: false,
        height: 300,
        width: 350,
        modal: true,
        buttons: {
            'Cancelar': function() {
                dialog2.dialog("close");
            },
            'Receber': function() {
                jQuery( "#formRefund2" ).submit();
            }
        }
    });

    form = dialog2.find( "form" ).on( "submit", function( event ) {
        if(!addUser()){
            event.preventDefault();
            return false;
        }

    });

    jQuery( ".refound-button-credit" ).button().on( "click", function() {
        var wpHttpReferer = jQuery(this).data('wp_http_referer');
        jQuery('input[name="_wp_http_referer"]').val(wpHttpReferer);

        var orderId = jQuery(this).data('order_id');
        jQuery('input[name="order_id"]').val(orderId);
        dialog1.dialog( "open" );

    });

    jQuery( ".refound-button-boleto" ).button().on( "click", function() {

        var wpHttpReferer = jQuery(this).data('wp_http_referer');
        jQuery('input[name="_wp_http_referer"]').val(wpHttpReferer);

        var orderId = jQuery(this).data('order_id');
        jQuery('input[name="order_id"]').val(orderId);
        dialog2.dialog( "open" );

    });
});