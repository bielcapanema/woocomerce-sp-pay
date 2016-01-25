$(document).ready(function(e){
    $('.btn-pay').click(function(){
        var productId = $(this).data('data-product-id');
        var url = $(this).attr('href');
        var link = $(this).attr('data-link');
        $.post(url, {product_id : productId}, function(data){
            window.location(link);
        });

        e.preventDefault();
        return false;
    });
});
