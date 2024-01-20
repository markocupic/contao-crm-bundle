/*
 * This file is part of Contao CRM Bundle.
 *
 * (c) Marko Cupic 2024 <m.cupic@gmx.ch>
 * @license GPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-crm-bundle
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
