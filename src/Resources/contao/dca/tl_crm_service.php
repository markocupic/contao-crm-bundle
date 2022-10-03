<?php

declare(strict_types=1);

/*
 * This file is part of Contao CRM Bundle.
 *
 * (c) Marko Cupic 2022 <m.cupic@gmx.ch>
 * @license GPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-crm-bundle
 */

use Contao\Backend;
use Contao\Date;
use Contao\Image;
use Contao\Input;
use Contao\Message;
use Contao\StringUtil;
use Contao\System;
use Markocupic\ContaoCrmBundle\Invoice\Generator;
use Markocupic\ContaoCrmBundle\Model\CrmCustomerModel;
use Markocupic\ContaoCrmBundle\Model\CrmServiceModel;

/*
 * Table tl_crm_service
 */
$GLOBALS['TL_DCA']['tl_crm_service'] = [
    // Config
    'config'      => [
        'dataContainer'    => 'Table',
        'enableVersioning' => true,
        'onload_callback'  => [
            [
                'tl_crm_service',
                'checkCloudConvertApiKey',
            ],
        ],
        'sql'              => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],
    // List
    'list'        => [
        'sorting'           => [
            'mode'               => 1,
            'fields'             => ['projectDateStart'],
            'flag'               => 8,
            'panelLayout'        => 'filter;sort,search,limit',
            'child_record_class' => 'no_padding',
        ],
        'label'             => [
            'fields'         => [
                'invoiceNumber',
                'toCustomer',
                'title',
            ],
            'label_callback' => [
                'tl_crm_service',
                'listServices',
            ],
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
                'label' => &$GLOBALS['TL_LANG']['tl_crm_service']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif',
            ],
            'copy'                => [
                'label' => &$GLOBALS['TL_LANG']['tl_crm_service']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif',
            ],
            'delete'              => [
                'label'      => &$GLOBALS['TL_LANG']['tl_crm_service']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null).'\'))return false;Backend.getScrollOffset()"',
            ],
            'show'                => [
                'label' => &$GLOBALS['TL_LANG']['tl_crm_service']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif',
            ],
            'generateInvoiceDocx' => [
                'label'           => &$GLOBALS['TL_LANG']['tl_crm_service']['generateInvoiceDocx'],
                'href'            => 'action=generateInvoice&type=docx',
                'button_callback' => [
                    'tl_crm_service',
                    'generateInvoice',
                ],
                'icon'            => 'bundles/markocupiccontaocrm/images/docx.svg',
            ],
            'generateInvoicePdf'  => [
                'label'           => &$GLOBALS['TL_LANG']['tl_crm_service']['generateInvoicePdf'],
                'href'            => 'action=generateInvoice&type=pdf',
                'button_callback' => [
                    'tl_crm_service',
                    'generateInvoice',
                ],
                'icon'            => 'bundles/markocupiccontaocrm/images/pdf.svg',
            ],
        ],
    ],
    // Palettes
    'palettes'    => [
        '__selector__' => ['paid'],
        'default'      => '{service_legend},title,projectDateStart,toCustomer,description,servicePositions;{invoice_legend},invoiceType,invoiceNumber,invoiceDate,price,currency,defaultInvoiceText,alternativeInvoiceText,crmInvoiceTpl;{state_legend},paid',
    ],
    // Subpalettes
    'subpalettes' => ['paid' => 'amountReceivedDate'],
    // Fields
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
            'eval'      => [
                'mandatory' => true,
                'maxlength' => 250,
                'tl_class'  => 'clr',
            ],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'projectDateStart'       => [
            'default'   => time(),
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => [
                'mandatory'  => true,
                'rgxp'       => 'date',
                'datepicker' => true,
                'tl_class'   => 'clr wizard',
            ],
            'sql'       => "varchar(10) NOT NULL default ''",
        ],
        'toCustomer'             => [
            'inputType'  => 'select',
            'sorting'    => true,
            'filter'     => true,
            'search'     => true,
            'exclude'    => true,
            'foreignKey' => 'tl_crm_customer.company',
            'eval'       => [
                'multiple' => false,
                'tl_class' => 'clr',
            ],
            'sql'        => 'blob NULL',
            'relation'   => [
                'type' => 'belongsTo',
                'load' => 'lazy',
            ],
        ],
        'description'            => [
            'inputType' => 'textarea',
            'exclude'   => true,
            'eval'      => [
                'decodeEntities' => false,
                'tl_class'       => 'clr',
            ],
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
                        'eval'      => ['style' => 'width:95%;'],
                    ],
                    'quantity' => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_crm_service']['position_quantity'],
                        'exclude'   => true,
                        'inputType' => 'select',
                        'options'   => range(0.25, 50, 0.25),
                        'eval'      => [
                            'style'  => 'width:50px;',
                            'chosen' => true,
                        ],
                    ],
                    'unit'     => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_crm_service']['position_unit'],
                        'exclude'   => true,
                        'inputType' => 'select',
                        'options'   => [
                            'h',
                            'Mt.',
                            'Stk.',
                        ],
                        'eval'      => [
                            'style'  => 'width:50px;',
                            'chosen' => true,
                        ],
                    ],
                    'price'    => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_crm_service']['position_price'],
                        'exclude'   => true,
                        'inputType' => 'text',
                        'eval'      => [
                            'rgxp'  => 'natural',
                            'style' => 'width:50px;text-align:center;',
                        ],
                    ],
                ],
            ],
            'sql'       => 'blob NULL',
        ],
        'price'                  => [
            'inputType' => 'text',
            'exclude'   => true,
            'eval'      => [
                'mandatory'  => true,
                'maxlength'  => 12,
                'tl_class'   => 'clr',
                'rgxp'       => 'natural',
                'alwaysSave' => true,
            ],
            'sql'       => "double NOT NULL default '0'",
        ],
        'currency'               => [
            'inputType' => 'select',
            'exclude'   => true,
            'options'   => [
                'EUR',
                'CHF',
            ],
            'eval'      => [
                'mandatory' => true,
                'chosen'    => true,
                'tl_class'  => 'clr',
            ],
            'sql'       => "varchar(3) NOT NULL default ''",
        ],
        'invoiceType'            => [
            'sorting'   => true,
            'filter'    => true,
            'search'    => true,
            'exclude'   => true,
            'reference' => &$GLOBALS['TL_LANG']['tl_crm_service']['invoiceTypeReference'],
            'options'   => [
                'calculation',
                'invoiceNotDelivered',
                'invoiceDelivered',
            ],
            'inputType' => 'select',
            'eval'      => ['tl_class' => 'w50 wizard'],
            'sql'       => "varchar(128) NOT NULL default ''",
        ],
        'invoiceDate'            => [
            'default'   => time(),
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => [
                'rgxp'       => 'date',
                'datepicker' => true,
                'tl_class'   => 'clr wizard',
            ],
            'sql'       => "varchar(10) NOT NULL default ''",
        ],
        'invoiceNumber'          => [
            'exclude'   => true,
            'inputType' => 'text',
            'default'   => 'XXXX-'.Date::parse('m/Y'),
            'eval'      => ['tl_class' => 'clr'],
            'sql'       => "varchar(128) NOT NULL default ''",
        ],
        'defaultInvoiceText'     => [
            'inputType' => 'textarea',
            'exclude'   => true,
            'default'   => 'Vielen Dank für Ihren sehr geschätzten Auftrag. Für Rückfragen stehe ich Ihnen gerne zur Verfügung.'.chr(10).chr(10).'Mit besten Grüßen'.chr(10).chr(10).'Marko Cupic',
            'eval'      => [
                'decodeEntities' => false,
                'tl_class'       => 'clr',
            ],
            'sql'       => 'mediumtext NULL',
        ],
        'alternativeInvoiceText' => [
            'inputType' => 'textarea',
            'exclude'   => true,
            'eval'      => [
                'decodeEntities' => false,
                'tl_class'       => 'clr',
            ],
            'sql'       => 'mediumtext NULL',
        ],
        'crmInvoiceTpl'          => [
            'exclude'   => true,
            'inputType' => 'fileTree',
            'eval'      => [
                'filesOnly'  => true,
                'extensions' => 'docx',
                'fieldType'  => 'radio',
                'mandatory'  => false,
                'tl_class'   => 'clr',
            ],
            'sql'       => 'binary(16) NULL',
        ],
        'paid'                   => [
            'inputType' => 'checkbox',
            'exclude'   => true,
            'filter'    => true,
            'eval'      => [
                'submitOnChange' => true,
                'tl_class'       => 'clr',
            ],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'amountReceivedDate'     => [
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => [
                'mandatory'  => true,
                'rgxp'       => 'date',
                'datepicker' => true,
                'tl_class'   => 'clr wizard',
            ],
            'sql'       => "varchar(10) NOT NULL default ''",
        ],
    ],
];

/**
 * Class tl_crm_service.
 */
class tl_crm_service extends Backend
{
    /**
     * Return the print invoice button.
     *
     * @param array $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function generateInvoice($row, $href, $label, $title, $icon, $attributes)
    {
        if ('generateInvoice' === Input::get('action') && Input::get('id') && Input::get('type')) {
            $type = Input::get('type');

            if (null !== ($objInvoice = CrmServiceModel::findByPk(Input::get('id')))) {
                $objInvoiceGenerator = System::getContainer()->get(Generator::class);
                $objInvoiceGenerator->generateInvoice($objInvoice, $type);
            }
        }

        return '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ';
    }

    /**
     * Check Cloudconvert API key.
     */
    public function checkCloudConvertApiKey(): void
    {
        if (empty(System::getContainer()->getParameter('markocupic_contao_crm.cloudconvert_api_key'))) {
            Message::addInfo('Please read the README.md in vendor/markocupic/contao-crm-bundle and add your Cloudconvert API key in config/config.yml for downloading pdf invoices.');
        }
    }

    /**
     * Add the type of input field.
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
    <div class="list-service-row-5">%s: %s %s (%s %s)</div>
    <div class="list-service-row-6">%s: %s %s</div>
</div>';
        $class = '';

        if ('invoiceDelivered' === $arrRow['invoiceType']) {
            $class = ' invoiceDelivered';
        }

        if ($arrRow['paid']) {
            $class = ' invoicePaid';
        }

        $key = $arrRow['invoiceType'];
        $titleAttr = $arrRow['paid'] ? $GLOBALS['TL_LANG']['tl_crm_service']['paid'][0] : $GLOBALS['TL_LANG']['tl_crm_service']['invoiceTypeReference'][$key][0];

        // Service positions
        $servicePositions = StringUtil::deserialize($arrRow['servicePositions'], true);
        $quantity = 0;
        $unit = '';
        $price = 0;

        if (count($servicePositions)) {
            foreach ($servicePositions as $service) {
                if (isset($service['quantity']) && !empty($service['quantity'])) {
                    $quantity += $service['quantity'];
                }

                if ('' === $unit && isset($service['unit']) && !empty($service['unit'])) {
                    $unit = $service['unit'];
                }

                if (isset($service['price']) && !empty($service['price'])) {
                    $price += (int)$service['price'];
                }
            }
        }

        return sprintf(
            $strService,
            $class,
            $titleAttr,
            // Row 1
            CrmCustomerModel::findByPk($arrRow['toCustomer'])->company,
            // Row 2
            $arrRow['title'],
            // Row 3
            $GLOBALS['TL_LANG']['MSC']['invoiceNumber'],
            $arrRow['invoiceNumber'],
            // Row 4
            $GLOBALS['TL_LANG']['MSC']['projectId'],
            str_pad((string)$arrRow['id'], 7, '0', STR_PAD_LEFT),
            // Row 5
            $GLOBALS['TL_LANG']['MSC']['projectPrice'],
            $arrRow['price'],
            $arrRow['currency'],
            $price,
            $arrRow['currency'],
            // Row 6
            $GLOBALS['TL_LANG']['MSC']['expense'],
            $quantity,
            $unit,
        );
    }
}
