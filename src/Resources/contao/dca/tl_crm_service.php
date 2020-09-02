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

/**
 * Table tl_crm_service
 */
$GLOBALS['TL_DCA']['tl_crm_service'] = [
    // Config
    'config'      => [
        'dataContainer'    => 'Table',
        'enableVersioning' => true,
        'onload_callback'  => [
            ['tl_crm_service', 'checkCloudConvertApiKey']
        ],
        'sql'              => [
            'keys' => [
                'id' => 'primary'
            ]
        ]
    ],

    // List
    'list'        => [
        'sorting'           => [
            'mode'               => 1,
            'fields'             => ['projectDateStart'],
            'flag'               => 8,
            'panelLayout'        => 'filter;sort,search,limit',
            //'disableGrouping' => true,
            //'headerFields' => array('invoiceNumber', 'toCustomer', 'title'),
            'child_record_class' => 'no_padding',
        ],
        'label'             => [
            'fields'         => ['invoiceNumber', 'toCustomer', 'title'],
            //'format' => '%s %s %s',
            'label_callback' => ['tl_crm_service', 'listServices'],

        ],
        'global_operations' => [
            'all' => [
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"'
            ],

        ],
        'operations'        => [
            'edit'                => [
                'label' => &$GLOBALS['TL_LANG']['tl_crm_service']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif'
            ],
            'copy'                => [
                'label' => &$GLOBALS['TL_LANG']['tl_crm_service']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif'
            ],
            'delete'              => [
                'label'      => &$GLOBALS['TL_LANG']['tl_crm_service']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
            ],
            'show'                => [
                'label' => &$GLOBALS['TL_LANG']['tl_crm_service']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif'
            ],
            'generateInvoiceDocx' => [
                'label' => &$GLOBALS['TL_LANG']['tl_crm_service']['generateInvoiceDocx'],
                'href'  => 'action=generateInvoice&type=docx',
                'icon'  => 'bundles/markocupiccontaocrm/images/page_white_word.png'
            ],
            'generateInvoicePdf'  => [
                'label' => &$GLOBALS['TL_LANG']['tl_crm_service']['generateInvoicePdf'],
                'href'  => 'action=generateInvoice&type=pdf',
                'icon'  => 'bundles/markocupiccontaocrm/images/page_white_acrobat.png'
            ],
        ]
    ],

    // Palettes
    'palettes'    => [
        '__selector__' => ['paid'],
        'default'      => '{service_legend},title,projectDateStart,toCustomer,description,servicePositions;
                        {invoice_legend},invoiceType,invoiceNumber,invoiceDate,price,currency,defaultInvoiceText,alternativeInvoiceText,crmInvoiceTpl;
                        {state_legend},paid'
    ],

    // Subpalettes
    'subpalettes' => ['paid' => 'amountReceivedDate'],

    // Fields
    'fields'      => [
        'id'               => [
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ],
        'tstamp'           => [
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],
        'title'            => [
            'label'     => &$GLOBALS['TL_LANG']['tl_crm_service']['title'],
            'inputType' => 'text',
            'exclude'   => true,
            'eval'      => ['mandatory' => true, 'maxlength' => 250, 'tl_class' => 'clr'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'projectDateStart' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_crm_service']['projectDateStart'],
            'default'   => time(),
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'rgxp' => 'date', 'datepicker' => true, 'tl_class' => 'clr wizard'],
            'sql'       => "varchar(10) NOT NULL default ''"
        ],
        'toCustomer'       => [
            'label'      => &$GLOBALS['TL_LANG']['tl_crm_service']['toCustomer'],
            'inputType'  => 'select',
            'sorting'    => true,
            'filter'     => true,
            'search'     => true,
            'exclude'    => true,
            'foreignKey' => 'tl_crm_customer.company',
            'eval'       => ['multiple' => false, 'tl_class' => 'clr'],
            'sql'        => "blob NULL",
            'relation'   => ['type' => 'belongsTo', 'load' => 'lazy']
        ],
        'description'      => [
            'label'     => &$GLOBALS['TL_LANG']['tl_crm_service']['description'],
            'inputType' => 'textarea',
            'exclude'   => true,
            'eval'      => ['decodeEntities' => false, 'tl_class' => 'clr'],
            'sql'       => "mediumtext NULL"
        ],
        'servicePositions' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_crm_service']['servicePositions'],
            'inputType' => 'multiColumnWizard',
            'exclude'   => true,
            'eval'      => [
                'columnFields' => [
                    'item'     => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_crm_service']['position_item'],
                        'exclude'   => true,
                        'inputType' => 'textarea',
                        'eval'      => ['style' => 'width:95%;']
                    ],
                    'quantity' => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_crm_service']['position_quantity'],
                        'exclude'   => true,
                        'inputType' => 'select',
                        'options'   => range(0.25, 50, 0.25),
                        'eval'      => ['style' => 'width:50px;', 'chosen' => true]
                    ],
                    'unit'     => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_crm_service']['position_unit'],
                        'exclude'   => true,
                        'inputType' => 'select',
                        'options'   => ['h', 'Mt.', 'Stk.'],
                        'eval'      => ['style' => 'width:50px;', 'chosen' => true]
                    ],
                    'price'    => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_crm_service']['position_price'],
                        'exclude'   => true,
                        'inputType' => 'text',
                        'eval'      => ['rgxp' => 'natural', 'style' => 'width:50px;text-align:center;']
                    ],
                ]
            ],
            'sql'       => "blob NULL"
        ],
        'price'            => [
            'label'     => &$GLOBALS['TL_LANG']['tl_crm_service']['price'],
            'inputType' => 'text',
            'exclude'   => true,
            'eval'      => ['mandatory' => true, 'maxlength' => 12, 'tl_class' => 'clr', 'rgxp' => 'natural', 'alwaysSave' => true],
            'sql'       => "double NOT NULL default '0'"
        ],

        'currency'               => [
            'label'     => &$GLOBALS['TL_LANG']['tl_crm_service']['currency'],
            'inputType' => 'select',
            'exclude'   => true,
            'options'   => ['EUR', 'CHF'],
            'eval'      => ['mandatory' => true, 'chosen' => true, 'tl_class' => 'clr'],
            'sql'       => "varchar(3) NOT NULL default ''"
        ],
        'invoiceType'            => [
            'label'     => &$GLOBALS['TL_LANG']['tl_crm_service']['invoiceType'],
            'sorting'   => true,
            'filter'    => true,
            'search'    => true,
            'exclude'   => true,
            'reference' => &$GLOBALS['TL_LANG']['tl_crm_service']['invoiceTypeReference'],
            'options'   => ['calculation', 'invoiceNotDelivered', 'invoiceDelivered'],
            'inputType' => 'select',
            'eval'      => ['tl_class' => 'w50 wizard'],
            'sql'       => "varchar(128) NOT NULL default ''"
        ],
        'invoiceDate'            => [
            'label'     => &$GLOBALS['TL_LANG']['tl_crm_service']['invoiceDate'],
            'default'   => time(),
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'date', 'datepicker' => true, 'tl_class' => 'clr wizard'],
            'sql'       => "varchar(10) NOT NULL default ''"
        ],
        'invoiceNumber'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_crm_service']['invoiceNumber'],
            'default'   => time(),
            'exclude'   => true,
            'inputType' => 'text',
            'default'   => 'XXXX-' . \Contao\Date::parse('m/Y'),
            'eval'      => ['tl_class' => 'clr'],
            'sql'       => "varchar(128) NOT NULL default ''"
        ],
        'defaultInvoiceText'     => [
            'label'     => &$GLOBALS['TL_LANG']['tl_crm_service']['defaultInvoiceText'],
            'inputType' => 'textarea',
            'exclude'   => true,
            'default'   => 'Vielen Dank für Ihren sehr geschätzten Auftrag. Für Rückfragen stehe ich Ihnen gerne zur Verfügung.' . chr(10) . chr(10) . 'Mit besten Grüßen' . chr(10) . chr(10) . 'Marko Cupic',
            'eval'      => ['decodeEntities' => false, 'tl_class' => 'clr'],
            'sql'       => "mediumtext NULL"
        ],
        'alternativeInvoiceText' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_crm_service']['alternativeInvoiceText'],
            'inputType' => 'textarea',
            'exclude'   => true,
            'eval'      => ['decodeEntities' => false, 'tl_class' => 'clr'],
            'sql'       => "mediumtext NULL"
        ],
        'crmInvoiceTpl'          => [
            'label'            => &$GLOBALS['TL_LANG']['tl_crm_service']['crmInvoiceTpl'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => ['tl_crm_service', 'getInvoiceTemplates'],
            'eval'             => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'clr'],
            'sql'              => "varchar(64) NOT NULL default ''"
        ],
        'crmInvoiceTpl'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_crm_service']['crmInvoiceTpl'],
            'exclude'   => true,
            'inputType' => 'fileTree',
            'eval'      => ['filesOnly' => true, 'extensions' => 'docx', 'fieldType' => 'radio', 'mandatory' => false, 'tl_class' => 'clr'],
            'sql'       => "binary(16) NULL"
        ],
        'paid'                   => [
            'label'     => &$GLOBALS['TL_LANG']['tl_crm_service']['paid'],
            'inputType' => 'checkbox',
            'exclude'   => true,
            'filter'    => true,
            'eval'      => ['submitOnChange' => true, 'tl_class' => 'clr'],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'amountReceivedDate'     => [
            'label'     => &$GLOBALS['TL_LANG']['tl_crm_service']['amountReceivedDate'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'rgxp' => 'date', 'datepicker' => true, 'tl_class' => 'clr wizard'],
            'sql'       => "varchar(10) NOT NULL default ''"
        ]
    ]
];

/**
 * Class tl_crm_service
 */
class tl_crm_service extends \Contao\Backend
{

    /**
     * tl_crm_service constructor.
     */
    public function __construct()
    {

        parent::__construct();

        if (\Contao\Input::get('action') == 'generateInvoice')
        {
            $objInvoice = \Markocupic\ContaoCrmBundle\Model\CrmServiceModel::findByPk(\Contao\Input::get('id'));
            $objInvoiceGenerator = \Contao\System::getContainer()->get(Markocupic\ContaoCrmBundle\Invoice\Generator::class);
            $objInvoiceGenerator->generateInvoice($objInvoice, \Contao\Input::get('type'));
        }
    }

    /**
     * Check Cloudconvert API key
     */
    public function checkCloudConvertApiKey()
    {

        if (!\Contao\System::getContainer()->getParameter('markocupic_contao_crm.cloudconvert_api_key'))
        {
            \Contao\Message::addInfo('Please read the README.md in vendor/markocupic/contao-crm-bundle and add your Cloudconvert API key in config/config.yml for downloading pdf invoices.');
        }
    }

    /**
     * Add the type of input field
     *
     * @param array $arrRow
     *
     * @return string
     */
    public function listServices($arrRow)
    {

        $strService = '
<div class="tl_content_left %s" title="%s">
    <div class="list-service-row-1">%s</div>
    <div class="list-service-row-2">%s</div>
    <div class="list-service-row-3">%s: %s</div>
    <div class="list-service-row-4">%s: %s</div>
    <div class="list-service-row-5">%s: %s</div>
</div>';

        if ($arrRow['invoiceType'] == 'invoiceDelivered')
        {
            $class = ' invoiceDelivered';
        }

        if ($arrRow['paid'])
        {
            $class = ' invoicePaid';
        }

        $key = $arrRow['invoiceType'];
        $titleAttr = $arrRow['paid'] ? $GLOBALS['TL_LANG']['tl_crm_service']['paid'][0] : $GLOBALS['TL_LANG']['tl_crm_service']['invoiceTypeReference'][$key][0];

        return sprintf($strService, $class, $titleAttr, \Markocupic\ContaoCrmBundle\Model\CrmCustomerModel::findByPk($arrRow['toCustomer'])->company, $arrRow['title'], $GLOBALS["TL_LANG"]["MSC"]["invoiceNumber"], $arrRow['invoiceNumber'], $GLOBALS["TL_LANG"]["MSC"]["projectId"], str_pad($arrRow['id'], 7, 0, STR_PAD_LEFT), $GLOBALS["TL_LANG"]["MSC"]["projectPrice"], $arrRow['price'] . ' ' . $arrRow['currency']);
    }

}

