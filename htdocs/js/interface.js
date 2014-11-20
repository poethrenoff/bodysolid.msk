function buyItem(id){
    $.get('/cart/add/' + id + '/',function (response){
        $("#cart").html(response);
        $(".in-cart").show('slow');
    });
    return false;
}

function incItem(incLink){
    return shiftItem(incLink, +1);
}

function decItem(decLink){
    return shiftItem(decLink, -1);
}

function shiftItem(shiftLink, shift){
    var $row = $(shiftLink).parents('tr:first');
    var $qntInput = $row.find('input[name^=quantity]');
    var $priceInput = $row.find('input[name^=price]');
    var qnt = parseInt($qntInput.val());
    var price = parseInt($priceInput.val());
    var $qntCell = $row.find('td').eq(2);
    var $costCell = $row.find('td').eq(3);
    
    qnt = qnt + shift;
    
    if (qnt > 0) {
        $qntInput.val(qnt);
        $qntCell.find('span').html(qnt);
        $costCell.html(qnt * price);
        
        updateCart();
    }
    
    return false;
}

function updateCart(){
    var totalQnt = 0; var totalSum = 0;
    $('form.cart').find('input[name^=quantity]').each(function(){
        var $qntInput = $(this);
        var $priceInput = $qntInput.parent().find('input[name^=price]');
        var qnt = parseInt($qntInput.val());
        var price = parseInt($priceInput.val());
        totalQnt += qnt;
        totalSum += qnt * price;
    });
    
    var $totalRow = $('form.cart').find('tr:last');
    var $totalQntCell = $totalRow.find('td').eq(2);
    var $totalSumCell = $totalRow.find('td').eq(3);
    $totalQntCell.html(totalQnt);
    $totalSumCell.html(totalSum);
    
    $('form.cart').ajaxSubmit(function(response){
        $("#cart").html(response);
    });
}

$(function() {
    $(document).bind('click', function(e) {
        var $target = $(e.target);
        if (!($target.is('.in-cart') || $target.parents('.in-cart').length)) {
            $('.in-cart').hide('slow');
        }
    });
    
    $('input[href]').bind('click', function(e) {
        if (!$(this).attr('confirm') || confirm($(this).attr('confirm'))) {
            location.href = $(this).attr('href');
        }
    });
});