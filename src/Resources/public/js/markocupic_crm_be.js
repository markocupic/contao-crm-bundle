// Dollar Safe Mode
(function ($) {
    window.addEvent('domready', function () {


        $$('.tl_listing .invoicePaid').each(function (el) {
            var cells = el.getParent('tr').addClass('invoice-paid');
        });
        $$('.tl_listing .invoiceDelivered').each(function (el) {
            var cells = el.getParent('tr').addClass('invoice-delivered');
        });


    });
})(document.id);