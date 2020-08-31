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

        // Instantiate the Template processor
        $templateProcessor = new TemplateProcessorExtended(TL_ROOT . '/' . $tplSRC);

        $templateProcessor->setValue('invoiceAddress', static::formatMultilineText($objCustomer->invoiceAddress));
        $ustNumber = $objCustomer->ustId != '' ? 'Us-tID: ' . $objCustomer->ustId : '';
        $templateProcessor->setValue('ustId', $ustNumber);
        $templateProcessor->setValue('invoiceDate', Date::parse('d.m.Y', $objInvoice->invoiceDate));
        $templateProcessor->setValue('projectId', $GLOBALS["TL_LANG"]["MSC"]["projectId"] . ': ' . str_pad($objInvoice->id, 7, 0, STR_PAD_LEFT));

        if ($objInvoice->invoiceType == 'invoiceDelivered')
        {
            $invoiceNumber = $GLOBALS["TL_LANG"]["MSC"]["invoiceNumber"] . ': ' . $objInvoice->invoiceNumber;
        }
        else
        {
            $invoiceNumber = '';
        }
        // Invoice Number
        $templateProcessor->setValue('invoiceNumber', $invoiceNumber);

        // Invoice type
        $templateProcessor->setValue('invoiceType', strtoupper($GLOBALS['TL_LANG']['tl_crm_service']['invoiceTypeReference'][$objInvoice->invoiceType][1]));

        // Customer ID
        $customerId = $GLOBALS["TL_LANG"]["MSC"]["customerId"] . ': ' . str_pad($objCustomer->id, 7, 0, STR_PAD_LEFT);
        $templateProcessor->setValue('customerId', $customerId);

        // Invoice table
        $arrServices = deserialize($objInvoice->servicePositions, true);
        $templateProcessor->cloneRow('a', count($arrServices));
        $quantityTotal = 0;
        foreach ($arrServices as $key => $arrService)
        {
            $i = $key + 1;
            $quantityTotal += $arrService['quantity'];
            $templateProcessor->setValue('a#' . $i, htmlspecialchars(utf8_decode_entities($i)));
            $templateProcessor->setValue('b#' . $i, static::formatMultilineText($arrService['item']));
            $templateProcessor->setValue('c#' . $i, $arrService['quantity']);
            $templateProcessor->setValue('d#' . $i, htmlspecialchars(utf8_decode_entities($objInvoice->currency)));
            $templateProcessor->setValue('e#' . $i, htmlspecialchars(utf8_decode_entities($arrService['price'])));
        }
        $templateProcessor->setValue('f', $quantityTotal);
        $templateProcessor->setValue('g', $objInvoice->currency);
        $templateProcessor->setValue('h', $objInvoice->price);
        // End table

        if ($objInvoice->alternativeInvoiceText != '')
        {
            $templateProcessor->setValue('INVOICE_TEXT', static::formatMultilineText($objInvoice->alternativeInvoiceText));
        }
        else
        {
            $templateProcessor->setValue('INVOICE_TEXT', static::formatMultilineText($objInvoice->defaultInvoiceText));
        }

        $type = $GLOBALS['TL_LANG']['tl_crm_service']['invoiceTypeReference'][$objInvoice->invoiceType][1];
        $filename = $type . '_' . Date::parse('Ymd', $objInvoice->invoiceDate) . '_' . '_' . str_pad($objInvoice->id, 7, 0, STR_PAD_LEFT) . '_' . str_replace(' ', '-', $objCustomer->company) . '.docx';
        $templateProcessor->saveAs(TL_ROOT . '/files/Rechnungen/' . $filename);
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
     * @param $text
     * @return mixed|string
     */
    protected static function formatMultilineText($text)
    {

        $text = htmlspecialchars(utf8_decode_entities($text));
        $text = preg_replace('~\R~u', '</w:t><w:br/><w:t>', $text);
        return $text;
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
