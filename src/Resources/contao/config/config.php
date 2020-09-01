<?php

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

if (TL_MODE == 'BE')
{
    $GLOBALS['TL_CSS'][] = 'bundles/markocupiccontaocrm/css/markocupic_crm_be.css';
    $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/markocupiccontaocrm/js/markocupic_crm_be.js';
}

/**
 * Backend modules
 */
$GLOBALS['BE_MOD']['crm']['customer'] = [

    'tables' => ['tl_crm_customer'],
];

$GLOBALS['BE_MOD']['crm']['service'] = [
    'tables' => ['tl_crm_service'],
];

/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_crm_customer'] = \Markocupic\ContaoCrmBundle\Model\CrmCustomerModel::class;
$GLOBALS['TL_MODELS']['tl_crm_service'] = \Markocupic\ContaoCrmBundle\Model\CrmServiceModel::class;
