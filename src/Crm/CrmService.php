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

namespace Markocupic\ContaoCrmBundle\Crm;

use CloudConvert\Api;
use PhpOffice\PhpWord\TemplateProcessorExtended;
use Contao\Controller;
use Contao\Validator;
use Contao\FilesModel;
use Contao\Date;
use Contao\Database;
use Markocupic\PhpOffice\PhpWord\MsWordTemplateProcessor;

/**
 * Class CrmService
 *
 * @package Markocupic\ContaoCrmBundle\Crm
 */
class CrmService
{

    /**
     * Generate the invoice from a docx template
     *
     * @param $id
     * @param string $format
     */
    public static function generateInvoice($id, $format = 'docx')
    {

        // Load the invoice and customer data
        $objInvoice = Database::getInstance()->prepare('SELECT * FROM tl_crm_service WHERE id=?')->execute($id);
        $objCustomer = Database::getInstance()->prepare('SELECT * FROM tl_crm_customer WHERE id=?')->execute($objInvoice->toCustomer);

        // Get the template path
        $tplSRC = 'vendor/markocupic/contao-crm-bundle/src/Resources/contao/templates/crm_invoice_template_default.docx';
        if ($objInvoice->crmInvoiceTpl != '' || Validator::isUuid($objInvoice->crmInvoiceTpl))
        {
            $objTplFile = FilesModel::findByUuid($objInvoice->crmInvoiceTpl);
            if ($objTplFile !== null)
            {
                $tplSRC = $objTplFile->path;
            }
        }


        $type = $GLOBALS['TL_LANG']['tl_crm_service']['invoiceTypeReference'][$objInvoice->invoiceType][1];
        $filename = $type . '_' . Date::parse('Ymd', $objInvoice->invoiceDate) . '_' . '_' . str_pad($objInvoice->id, 7, 0, STR_PAD_LEFT) . '_' . str_replace(' ', '-', $objCustomer->company) . '.docx';
        $target = 'system/tmp/' . $filename;

        // Instantiate the Template processor
        $templateProcessor = new MsWordTemplateProcessor($tplSRC, $target);

        $templateProcessor->replace('invoiceAddress', $objCustomer->invoiceAddress, array('multiline' => true));
        $ustNumber = $objCustomer->ustId != '' ? 'Us-tID: ' . $objCustomer->ustId : '';
        $templateProcessor->replace('ustId', $ustNumber);
        $templateProcessor->replace('invoiceDate', Date::parse('d.m.Y', $objInvoice->invoiceDate));
        $templateProcessor->replace('projectId', $GLOBALS["TL_LANG"]["MSC"]["projectId"] . ': ' . str_pad($objInvoice->id, 7, 0, STR_PAD_LEFT));

        if ($objInvoice->invoiceType == 'invoiceDelivered')
        {
            $invoiceNumber = $GLOBALS["TL_LANG"]["MSC"]["invoiceNumber"] . ': ' . $objInvoice->invoiceNumber;
        }
        else
        {
            $invoiceNumber = '';
        }
        // Invoice Number
        $templateProcessor->replace('invoiceNumber', $invoiceNumber);

        // Invoice type
        $templateProcessor->replace('invoiceType', strtoupper($GLOBALS['TL_LANG']['tl_crm_service']['invoiceTypeReference'][$objInvoice->invoiceType][1]));

        // Customer ID
        $customerId = $GLOBALS["TL_LANG"]["MSC"]["customerId"] . ': ' . str_pad($objCustomer->id, 7, 0, STR_PAD_LEFT);
        $templateProcessor->replace('customerId', $customerId);

        // Invoice table
        $arrServices = deserialize($objInvoice->servicePositions, true);
        $templateProcessor->cloneRow('a', count($arrServices));
        $quantityTotal = 0;
        foreach ($arrServices as $key => $arrService)
        {
            $i = $key + 1;
            $quantityTotal += $arrService['quantity'];
            $templateProcessor->createClone('a');
            $templateProcessor->addToClone('a', 'a', static::prepareString($i), array('multiline' => false));
            $templateProcessor->addToClone('a', 'b', static::prepareString($arrService['item']), array('multiline' => true));
            $templateProcessor->addToClone('a', 'c', $arrService['quantity'], array('multiline' => false));
            $templateProcessor->addToClone('a', 'd', static::prepareString($objInvoice->currency), array('multiline' => false));
            $templateProcessor->addToClone('a', 'e', static::prepareString($arrService['price']), array('multiline' => false));

        }
        $templateProcessor->replace('f', $quantityTotal);
        $templateProcessor->replace('g', $objInvoice->currency);
        $templateProcessor->replace('h', $objInvoice->price);
        // End table

        if ($objInvoice->alternativeInvoiceText != '')
        {
            $templateProcessor->replace('INVOICE_TEXT', static::formatMultilineText($objInvoice->alternativeInvoiceText));
        }
        else
        {
            $templateProcessor->replace('INVOICE_TEXT', static::formatMultilineText($objInvoice->defaultInvoiceText));
        }

        $type = $GLOBALS['TL_LANG']['tl_crm_service']['invoiceTypeReference'][$objInvoice->invoiceType][1];
        $filename = $type . '_' . Date::parse('Ymd', $objInvoice->invoiceDate) . '_' . '_' . str_pad($objInvoice->id, 7, 0, STR_PAD_LEFT) . '_' . str_replace(' ', '-', $objCustomer->company) . '.docx';
        $templateProcessor->saveAs(TL_ROOT . '/' . $target);
        sleep(2);
        if ($format == 'pdf')
        {
            static::sendPdfToBrowser('files/Rechnungen/' . $filename);
        }
        else
        {
            Controller::sendFileToBrowser('files/Rechnungen/' . $filename);
        }
    }


    /**
     * @param string $string
     * @return string
     */
    protected static function prepareString(string $string = ''): string
    {
        if (empty($string))
        {
            return '';
        }

        return utf8_decode_entities(htmlspecialchars(html_entity_decode((string) $string)));
    }

    /**
     * Convert docx to pdf
     *
     * @param $docxSRC
     */
    protected static function sendPdfToBrowser($docxSRC)
    {

        if (!isset($GLOBALS['TL_CONFIG']['clodConvertApiKey']))
        {
            new Exception('No API Key defined for the Cloud Convert Service. https://cloudconvert.com/api');
        }

        $key = $GLOBALS['TL_CONFIG']['clodConvertApiKey'];

        $path_parts = pathinfo($docxSRC);
        $dirname = $path_parts['dirname'];
        $filename = $path_parts['filename'];
        $pdfSRC = $dirname . '/' . $filename . '.pdf';

        $api = new Api($key);
        try
        {
            $api->convert([
                'inputformat'  => 'docx',
                'outputformat' => 'pdf',
                'input'        => 'upload',
                'file'         => fopen(TL_ROOT . '/' . $docxSRC, 'r'),
            ])->wait()->download(TL_ROOT . '/' . $pdfSRC);
            Controller::sendFileToBrowser($pdfSRC);
        } catch (\CloudConvert\Exceptions\ApiBadRequestException $e)
        {
            echo "Something with your request is wrong: " . $e->getMessage();
        } catch (\CloudConvert\Exceptions\ApiConversionFailedException $e)
        {
            echo "Conversion failed, maybe because of a broken input file: " . $e->getMessage();
        } catch (\CloudConvert\Exceptions\ApiTemporaryUnavailableException $e)
        {
            echo "API temporary unavailable: " . $e->getMessage() . "\n";
            echo "We should retry the conversion in " . $e->retryAfter . " seconds";
        } catch (Exception $e)
        {
            // network problems, etc..
            echo "Something else went wrong: " . $e->getMessage() . "\n";
        }
    }

}
