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

use Contao\DataContainer;
use Contao\DC_Table;

$GLOBALS['TL_DCA']['tl_crm_service'] = [
	'config'      => [
		'dataContainer'    => DC_Table::class,
		'enableVersioning' => true,
		'sql'              => [
			'keys' => [
				'id' => 'primary',
			],
		],
	],
	'list'        => [
		'sorting'           => [
			'mode'               => DataContainer::MODE_SORTED,
			'fields'             => ['projectDateStart'],
			'flag'               => DataContainer::SORT_MONTH_DESC,
			'panelLayout'        => 'filter;sort,search,limit',
			'child_record_class' => 'no_padding',
		],
		'label'             => [
			'fields' => ['invoiceNumber', 'toCustomer', 'title'],
		],
		'global_operations' => [
			'all' => [
				'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'       => 'act=select',
				'class'      => 'header_edit_all',
				'attributes' => 'onclick="Backend.getScrollOffset();"',
			],
		],
		'operations'        => [
			'edit'                => [
				'href' => 'act=edit',
				'icon' => 'edit.gif',
			],
			'copy'                => [
				'href' => 'act=copy',
				'icon' => 'copy.gif',
			],
			'delete'              => [
				'href'       => 'act=delete',
				'icon'       => 'delete.gif',
				'attributes' => 'onclick="if(!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null).'\'))return false;Backend.getScrollOffset()"',
			],
			'show'                => [
				'href' => 'act=show',
				'icon' => 'show.gif',
			],
			'generateInvoiceDocx' => [
				'label' => &$GLOBALS['TL_LANG']['tl_crm_service']['generateInvoiceDocx'],
				'href'  => 'action=generateInvoice&type=docx',
				'icon'  => 'bundles/markocupiccontaocrm/images/docx.svg',
			],
			'generateInvoicePdf'  => [
				'label' => &$GLOBALS['TL_LANG']['tl_crm_service']['generateInvoicePdf'],
				'href'  => 'action=generateInvoice&type=pdf',
				'icon'  => 'bundles/markocupiccontaocrm/images/pdf.svg',
			],
		],
	],
	'palettes'    => [
		'__selector__' => ['paid'],
		'default'      => '
        {service_legend},title,projectDateStart,toCustomer,description,servicePositions;
        {invoice_legend},invoiceType,invoiceNumber,invoiceDate,price,currency,defaultInvoiceText,alternativeInvoiceText,crmInvoiceTpl;
        {state_legend},paid
        ',
	],
	'subpalettes' => [
		'paid' => 'amountReceivedDate',
	],
	'fields'      => [
		'id'                     => [
			'sql' => 'int(10) unsigned NOT NULL auto_increment',
		],
		'tstamp'                 => [
			'sql' => "int(10) unsigned NOT NULL default '0'",
		],
		'title'                  => [
			'inputType' => 'text',
			'exclude'   => true,
			'eval'      => ['mandatory' => true, 'maxlength' => 250, 'tl_class' => 'clr'],
			'sql'       => "varchar(255) NOT NULL default ''",
		],
		'projectDateStart'       => [
			'default'   => time(),
			'exclude'   => true,
			'inputType' => 'text',
			'eval'      => ['mandatory' => true, 'rgxp' => 'date', 'datepicker' => true, 'tl_class' => 'clr wizard'],
			'sql'       => "varchar(10) NOT NULL default ''",
		],
		'toCustomer'             => [
			'inputType'  => 'select',
			'sorting'    => true,
			'filter'     => true,
			'search'     => true,
			'exclude'    => true,
			'foreignKey' => 'tl_crm_customer.company',
			'eval'       => ['multiple' => false, 'tl_class' => 'clr'],
			'sql'        => 'blob NULL',
			'relation'   => ['type' => 'belongsTo', 'load' => 'lazy'],
		],
		'description'            => [
			'inputType' => 'textarea',
			'exclude'   => true,
			'eval'      => ['decodeEntities' => false, 'tl_class' => 'clr', 'rte' => false],
			'sql'       => 'mediumtext NULL',
		],
		'servicePositions'       => [
			'inputType' => 'multiColumnWizard',
			'exclude'   => true,
			'eval'      => [
				'columnFields' => [
					'item'     => [
						'label'     => &$GLOBALS['TL_LANG']['tl_crm_service']['position_item'],
						'exclude'   => true,
						'inputType' => 'textarea',
						'eval'      => ['rte' => false, 'style' => 'width:95%;'],
					],
					'quantity' => [
						'label'     => &$GLOBALS['TL_LANG']['tl_crm_service']['position_quantity'],
						'exclude'   => true,
						'inputType' => 'select',
						'options'   => range(0.25, 50, 0.25),
						'eval'      => ['style' => 'width:100px;', 'chosen' => true],
					],
					'unit'     => [
						'label'     => &$GLOBALS['TL_LANG']['tl_crm_service']['position_unit'],
						'exclude'   => true,
						'inputType' => 'select',
						'options'   => ['h', 'Mt.', 'Stk.'],
						'eval'      => ['style' => 'width:100px', 'chosen' => true],
					],
					'price'    => [
						'label'     => &$GLOBALS['TL_LANG']['tl_crm_service']['position_price'],
						'exclude'   => true,
						'inputType' => 'text',
						'eval'      => ['rgxp' => 'natural', 'style' => 'width:50px;text-align:center;'],
					],
				],
			],
			'sql'       => 'blob NULL',
		],
		'price'                  => [
			'inputType' => 'text',
			'exclude'   => true,
			'eval'      => ['mandatory' => true, 'maxlength' => 12, 'tl_class' => 'clr', 'rgxp' => 'natural', 'alwaysSave' => true],
			'sql'       => "double NOT NULL default '0'",
		],
		'currency'               => [
			'inputType' => 'select',
			'exclude'   => true,
			'options'   => ['EUR', 'CHF'],
			'eval'      => ['mandatory' => true, 'chosen' => true, 'tl_class' => 'clr'],
			'sql'       => "varchar(3) NOT NULL default ''",
		],
		'invoiceType'            => [
			'sorting'   => true,
			'filter'    => true,
			'search'    => true,
			'exclude'   => true,
			'reference' => &$GLOBALS['TL_LANG']['tl_crm_service']['invoiceTypeReference'],
			'options'   => ['calculation', 'invoiceNotDelivered', 'invoiceDelivered'],
			'inputType' => 'select',
			'eval'      => ['tl_class' => 'w50 wizard'],
			'sql'       => "varchar(128) NOT NULL default ''",
		],
		'invoiceDate'            => [
			'default'   => time(),
			'exclude'   => true,
			'inputType' => 'text',
			'eval'      => ['rgxp' => 'date', 'datepicker' => true, 'tl_class' => 'clr wizard'],
			'sql'       => "varchar(10) NOT NULL default ''",
		],
		'invoiceNumber'          => [
			'exclude'   => true,
			'inputType' => 'text',
			'default'   => 'XXXX-'.date('m/Y'),
			'eval'      => ['unique' => true, 'tl_class' => 'clr'],
			'sql'       => "varchar(128) NOT NULL default ''",
		],
		'defaultInvoiceText'     => [
			'inputType' => 'textarea',
			'exclude'   => true,
			'default'   => 'Vielen Dank für Ihren sehr geschätzten Auftrag. Für Rückfragen stehe ich Ihnen gerne zur Verfügung.'.chr(10).chr(10).'Mit besten Grüßen'.chr(10).chr(10).'Marko Cupic',
			'eval'      => ['decodeEntities' => false, 'tl_class' => 'clr', 'rte' => false],
			'sql'       => 'mediumtext NULL',
		],
		'alternativeInvoiceText' => [
			'inputType' => 'textarea',
			'exclude'   => true,
			'eval'      => ['decodeEntities' => false, 'tl_class' => 'clr', 'rte' => false],
			'sql'       => 'mediumtext NULL',
		],
		'crmInvoiceTpl'          => [
			'exclude'   => true,
			'inputType' => 'fileTree',
			'eval'      => ['filesOnly' => true, 'extensions' => 'docx', 'fieldType' => 'radio', 'mandatory' => false, 'tl_class' => 'clr'],
			'sql'       => 'binary(16) NULL',
		],
		'paid'                   => [
			'inputType' => 'checkbox',
			'exclude'   => true,
			'filter'    => true,
			'eval'      => ['submitOnChange' => true, 'tl_class' => 'clr'],
			'sql'       => "char(1) NOT NULL default ''",
		],
		'amountReceivedDate'     => [
			'exclude'   => true,
			'inputType' => 'text',
			'eval'      => ['mandatory' => true, 'rgxp' => 'date', 'datepicker' => true, 'tl_class' => 'clr wizard'],
			'sql'       => "varchar(10) NOT NULL default ''",
		],
	],
];
