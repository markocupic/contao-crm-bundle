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

/**
 * Table tl_crm_service
 */
$GLOBALS['TL_DCA']['tl_crm_service'] = array(
    // Config
    'config'      => array(
        'dataContainer'    => 'Table',
        'enableVersioning' => true,
        'onload_callback'  => array(
            array('tl_crm_service', 'checkCloudConvertApiKey'),
        ),
        'sql'              => array(
            'keys' => array(
                'id' => 'primary',
            ),
        ),
    ),
    // List
    'list'        => array(
        'sorting'           => array(
            'mode'               => 1,
            'fields'             => array('projectDateStart'),
            'flag'               => 8,
            'panelLayout'        => 'filter;sort,search,limit',
            'child_record_class' => 'no_padding',
        ),
        'label'             => array(
            'fields'         => array('invoiceNumber', 'toCustomer', 'title'),
            'label_callback' => array('tl_crm_service', 'listServices'),
        ),
        'global_operations' => array(
            'all' => array(
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"',
            ),
        ),
        'operations'        => array(
            'edit'                => array(
                'label' => &$GLOBALS['TL_LANG']['tl_crm_service']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif',
            ),
            'copy'                => array(
                'label' => &$GLOBALS['TL_LANG']['tl_crm_service']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif',
            ),
            'delete'              => array(
                'label'      => &$GLOBALS['TL_LANG']['tl_crm_service']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if (!confirm(\''.$GLOBALS['TL_LANG']['MSC']['deleteConfirm'].'\')) return false; Backend.getScrollOffset();"',
            ),
            'show'                => array(
                'label' => &$GLOBALS['TL_LANG']['tl_crm_service']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif',
            ),
            'generateInvoiceDocx' => array(
                'label'           => &$GLOBALS['TL_LANG']['tl_crm_service']['generateInvoiceDocx'],
                'href'            => 'action=generateInvoice&type=docx',
                'button_callback' => array('tl_crm_service', 'generateInvoice'),
                'icon'            => 'bundles/markocupiccontaocrm/images/docx.svg',
            ),
            'generateInvoicePdf'  => array(
                'label'           => &$GLOBALS['TL_LANG']['tl_crm_service']['generateInvoicePdf'],
                'href'            => 'action=generateInvoice&type=pdf',
                'button_callback' => array('tl_crm_service', 'generateInvoice'),
                'icon'            => 'bundles/markocupiccontaocrm/images/pdf.svg',
            ),
        ),
    ),
    // Palettes
    'palettes'    => array(
        '__selector__' => array('paid'),
        'default'      => '{service_legend},title,projectDateStart,toCustomer,description,servicePositions;
                        {invoice_legend},invoiceType,invoiceNumber,invoiceDate,price,currency,defaultInvoiceText,alternativeInvoiceText,crmInvoiceTpl;
                        {state_legend},paid',
    ),
    // Subpalettes
    'subpalettes' => array('paid' => 'amountReceivedDate'),
    // Fields
    'fields'      => array(
        'id'                     => array(
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ),
        'tstamp'                 => array(
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ),
        'title'                  => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_crm_service']['title'],
            'inputType' => 'text',
            'exclude'   => true,
            'eval'      => array('mandatory' => true, 'maxlength' => 250, 'tl_class' => 'clr'),
            'sql'       => "varchar(255) NOT NULL default ''",
        ),
        'projectDateStart'       => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_crm_service']['projectDateStart'],
            'default'   => time(),
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => array('mandatory' => true, 'rgxp' => 'date', 'datepicker' => true, 'tl_class' => 'clr wizard'),
            'sql'       => "varchar(10) NOT NULL default ''",
        ),
        'toCustomer'             => array(
            'label'      => &$GLOBALS['TL_LANG']['tl_crm_service']['toCustomer'],
            'inputType'  => 'select',
            'sorting'    => true,
            'filter'     => true,
            'search'     => true,
            'exclude'    => true,
            'foreignKey' => 'tl_crm_customer.company',
            'eval'       => array('multiple' => false, 'tl_class' => 'clr'),
            'sql'        => "blob NULL",
            'relation'   => array('type' => 'belongsTo', 'load' => 'lazy'),
        ),
        'description'            => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_crm_service']['description'],
            'inputType' => 'textarea',
            'exclude'   => true,
            'eval'      => array('decodeEntities' => false, 'tl_class' => 'clr'),
            'sql'       => "mediumtext NULL",
        ),
        'servicePositions'       => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_crm_service']['servicePositions'],
            'inputType' => 'multiColumnWizard',
            'exclude'   => true,
            'eval'      => array(
                'columnFields' => array(
                    'item'     => array(
                        'label'     => &$GLOBALS['TL_LANG']['tl_crm_service']['position_item'],
                        'exclude'   => true,
                        'inputType' => 'textarea',
                        'eval'      => array('style' => 'width:95%;'),
                    ),
                    'quantity' => array(
                        'label'     => &$GLOBALS['TL_LANG']['tl_crm_service']['position_quantity'],
                        'exclude'   => true,
                        'inputType' => 'select',
                        'options'   => range(0.25, 50, 0.25),
                        'eval'      => array('style' => 'width:50px;', 'chosen' => true),
                    ),
                    'unit'     => array(
                        'label'     => &$GLOBALS['TL_LANG']['tl_crm_service']['position_unit'],
                        'exclude'   => true,
                        'inputType' => 'select',
                        'options'   => array('h', 'Mt.', 'Stk.'),
                        'eval'      => array('style' => 'width:50px;', 'chosen' => true),
                    ),
                    'price'    => array(
                        'label'     => &$GLOBALS['TL_LANG']['tl_crm_service']['position_price'],
                        'exclude'   => true,
                        'inputType' => 'text',
                        'eval'      => array('rgxp' => 'natural', 'style' => 'width:50px;text-align:center;'),
                    ),
                ),
            ),
            'sql'       => "blob NULL",
        ),
        'price'                  => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_crm_service']['price'],
            'inputType' => 'text',
            'exclude'   => true,
            'eval'      => array('mandatory' => true, 'maxlength' => 12, 'tl_class' => 'clr', 'rgxp' => 'natural', 'alwaysSave' => true),
            'sql'       => "double NOT NULL default '0'",
        ),
        'currency'               => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_crm_service']['currency'],
            'inputType' => 'select',
            'exclude'   => true,
            'options'   => array('EUR', 'CHF'),
            'eval'      => array('mandatory' => true, 'chosen' => true, 'tl_class' => 'clr'),
            'sql'       => "varchar(3) NOT NULL default ''",
        ),
        'invoiceType'            => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_crm_service']['invoiceType'],
            'sorting'   => true,
            'filter'    => true,
            'search'    => true,
            'exclude'   => true,
            'reference' => &$GLOBALS['TL_LANG']['tl_crm_service']['invoiceTypeReference'],
            'options'   => array('calculation', 'invoiceNotDelivered', 'invoiceDelivered'),
            'inputType' => 'select',
            'eval'      => array('tl_class' => 'w50 wizard'),
            'sql'       => "varchar(128) NOT NULL default ''",
        ),
        'invoiceDate'            => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_crm_service']['invoiceDate'],
            'default'   => time(),
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => array('rgxp' => 'date', 'datepicker' => true, 'tl_class' => 'clr wizard'),
            'sql'       => "varchar(10) NOT NULL default ''",
        ),
        'invoiceNumber'          => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_crm_service']['invoiceNumber'],
            'default'   => time(),
            'exclude'   => true,
            'inputType' => 'text',
            'default'   => 'XXXX-'.Date::parse('m/Y'),
            'eval'      => array('tl_class' => 'clr'),
            'sql'       => "varchar(128) NOT NULL default ''",
        ),
        'defaultInvoiceText'     => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_crm_service']['defaultInvoiceText'],
            'inputType' => 'textarea',
            'exclude'   => true,
            'default'   => 'Vielen Dank für Ihren sehr geschätzten Auftrag. Für Rückfragen stehe ich Ihnen gerne zur Verfügung.'.chr(10).chr(10).'Mit besten Grüßen'.chr(10).chr(10).'Marko Cupic',
            'eval'      => array('decodeEntities' => false, 'tl_class' => 'clr'),
            'sql'       => "mediumtext NULL",
        ),
        'alternativeInvoiceText' => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_crm_service']['alternativeInvoiceText'],
            'inputType' => 'textarea',
            'exclude'   => true,
            'eval'      => array('decodeEntities' => false, 'tl_class' => 'clr'),
            'sql'       => "mediumtext NULL",
        ),
        'crmInvoiceTpl'          => array(
            'label'            => &$GLOBALS['TL_LANG']['tl_crm_service']['crmInvoiceTpl'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => array('tl_crm_service', 'getInvoiceTemplates'),
            'eval'             => array('includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'clr'),
            'sql'              => "varchar(64) NOT NULL default ''",
        ),
        'crmInvoiceTpl'          => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_crm_service']['crmInvoiceTpl'],
            'exclude'   => true,
            'inputType' => 'fileTree',
            'eval'      => array('filesOnly' => true, 'extensions' => 'docx', 'fieldType' => 'radio', 'mandatory' => false, 'tl_class' => 'clr'),
            'sql'       => "binary(16) NULL",
        ),
        'paid'                   => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_crm_service']['paid'],
            'inputType' => 'checkbox',
            'exclude'   => true,
            'filter'    => true,
            'eval'      => array('submitOnChange' => true, 'tl_class' => 'clr'),
            'sql'       => "char(1) NOT NULL default ''",
        ),
        'amountReceivedDate'     => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_crm_service']['amountReceivedDate'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => array('mandatory' => true, 'rgxp' => 'date', 'datepicker' => true, 'tl_class' => 'clr wizard'),
            'sql'       => "varchar(10) NOT NULL default ''",
        ),
    ),
);

/**
 * Class tl_crm_service
 */
class tl_crm_service extends Backend
{
    /**
     * Return the print invoice button
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
        if (Input::get('action') === 'generateInvoice' && Input::get('id') && Input::get('type')) {
            $type = Input::get('type');

            if (null !== ($objInvoice = CrmServiceModel::findByPk(Input::get('id')))) {
                $objInvoiceGenerator = System::getContainer()->get(Generator::class);
                $objInvoiceGenerator->generateInvoice($objInvoice, $type);
            }
        }

        return '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ';
    }

    /**
     * Check Cloudconvert API key
     */
    public function checkCloudConvertApiKey()
    {
        if (empty(System::getContainer()->getParameter('markocupic_contao_crm.cloudconvert_api_key'))) {
            Message::addInfo('Please read the README.md in vendor/markocupic/contao-crm-bundle and add your Cloudconvert API key in config/config.yml for downloading pdf invoices.');
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
    <div class="list-service-row-5">%s: %s %s (%s %s)</div>
    <div class="list-service-row-6">%s: %s %s</div>
</div>';

        $class = '';

        if ($arrRow['invoiceType'] == 'invoiceDelivered') {
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

                if ($unit === '' && isset($service['unit']) && !empty($service['unit'])) {
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
            $GLOBALS["TL_LANG"]["MSC"]["invoiceNumber"],
            $arrRow['invoiceNumber'],
            // Row 4
            $GLOBALS["TL_LANG"]["MSC"]["projectId"],
            str_pad($arrRow['id'], 7, 0, STR_PAD_LEFT),
            // Row 5
            $GLOBALS["TL_LANG"]["MSC"]["projectPrice"],
            $arrRow['price'],
            $arrRow['currency'],
            $price,
            $arrRow['currency'],
            // Row 6
            $GLOBALS["TL_LANG"]["MSC"]["expense"],
            $quantity,
            $unit,
        );
    }
}
