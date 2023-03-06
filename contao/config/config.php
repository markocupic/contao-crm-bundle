<?php

declare(strict_types=1);

/*
 * This file is part of Contao CRM Bundle.
 *
 * (c) Marko Cupic 2023 <m.cupic@gmx.ch>
 * @license GPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-crm-bundle
 */

use Markocupic\ContaoCrmBundle\Model\CrmCustomerModel;
use Markocupic\ContaoCrmBundle\Model\CrmServiceModel;

/*
 * Backend modules
 */
$GLOBALS['BE_MOD']['crm']['customer'] = [
    'tables' => ['tl_crm_customer'],
];

$GLOBALS['BE_MOD']['crm']['service'] = [
    'tables' => ['tl_crm_service'],
];

/*
 * Models
 */
$GLOBALS['TL_MODELS']['tl_crm_customer'] = CrmCustomerModel::class;
$GLOBALS['TL_MODELS']['tl_crm_service'] = CrmServiceModel::class;
