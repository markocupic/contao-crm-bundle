/**
 * This file is part of a markocupic Contao Bundle.
 *
 * (c) Marko Cupic 2020 <m.cupic@gmx.ch>
 *
 * @author     Marko Cupic
 * @package    Contao CRM Bundle
 * @license    MIT
 * @see        https://github.com/markocupic/contao-crm-bundle
 *
 */

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
