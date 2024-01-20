<?php

declare(strict_types=1);

/*
 * This file is part of Contao CRM Bundle.
 *
 * (c) Marko Cupic 2024 <m.cupic@gmx.ch>
 * @license GPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-crm-bundle
 */

namespace Markocupic\ContaoCrmBundle\Config;

class InvoiceType
{
    public const CALCULATION = 'calculation';
    public const INVOICE_NOT_DELIVERED = 'invoiceNotDelivered';
    public const INVOICE_DELIVERED = 'invoiceDelivered';
    public const ALL = [
        self::CALCULATION,
        self::INVOICE_NOT_DELIVERED,
        self::INVOICE_DELIVERED,
    ];
}
