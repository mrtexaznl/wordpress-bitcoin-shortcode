(function ($) {
    $(document).ready(function() {
	$('.mediterraneancoin-button').each(function () {
	    var url = $(this).attr('href');
	    var address = $(this).data('address');
	    var div = $(this).wrap('<div class="mediterraneancoin-div"/>').parent();
	    var show_info = $(this).data('info');

	    div.append('<a href="'+url+'" class="mediterraneancoin-counter" style="display:none;">&nbsp;</a>');
	    div.append('<div class="mediterraneancoin-bubble" style="display:none;"><div><strong>Mediterraneancoin address</strong></div><a href="'+url+'"><img src="http://chart.googleapis.com/chart?chs=200x200&cht=qr&chld=H|0&chl='+encodeURIComponent(url)+'" width="200" height="200" alt="QR code"/></a><div><a href="'+url+'">'+address+'</a></div></div>');

	    div.hover(function () {
		$(this).find('.mediterraneancoin-bubble').fadeIn(150);
	    }, function () {
		$(this).find('.mediterraneancoin-bubble').fadeOut(150);
	    });

	    if (show_info && show_info != 'none') {
		$.get(mediterraneancoin_button_ajax.url, { action: 'mediterraneancoin-address-info', address: address }, function (data) {
		    var counter = div.find('.mediterraneancoin-counter');
		    if (show_info == 'transaction' && data.transactions !== undefined) {
			counter.text(data.transactions+" tx");
			counter.show();
		    } else if (show_info == 'balance' && data.balance !== undefined) {
			counter.text((data.balance/100000000).toFixed(3)+" ฿");
			counter.show();
		    } else if (show_info == 'received' && data.received !== undefined) {
			counter.text((data.received/100000000).toFixed(3)+" ฿")
			counter.show();
		    }
		}, 'json');
	    }
	});
    });
})(jQuery);
