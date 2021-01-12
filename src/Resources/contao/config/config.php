<?php

/*
 * This file is part of Contao Crm Bundle.
 *
 * (c) Marko Cupic 2021 <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-crm-bundle
 */

use Markocupic\ContaoCrmBundle\Model\CrmCustomerModel;
use Markocupic\ContaoCrmBundle\Model\CrmServiceModel;

if (TL_MODE == 'BE')
{
	$GLOBALS['TL_CSS'][] = 'bundles/markocupiccontaocrm/css/markocupic_crm_be.css';
	$GLOBALS['TL_JAVASCRIPT'][] = 'bundles/markocupiccontaocrm/js/markocupic_crm_be.js';
}

/**
 * Backend modules
 */
$GLOBALS['BE_MOD']['crm']['customer'] = array(
	'tables' => array('tl_crm_customer'),
);

$GLOBALS['BE_MOD']['crm']['service'] = array(
	'tables' => array('tl_crm_service'),
);

/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_crm_customer'] = CrmCustomerModel::class;
$GLOBALS['TL_MODELS']['tl_crm_service'] = CrmServiceModel::class;
