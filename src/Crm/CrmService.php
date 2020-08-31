<?php

/**
 * This file is part of a markocupic Contao Bundle.
 *
 * (c) Marko Cupic 2020 <m.cupic@gmx.ch>
 * @author     Marko Cupic
 * @package    Contao CRM Bundle
 * @license    MIT
 * @see        https://github.com/markocupic/contao-crm-bundle
 *
 */

declare(strict_types=1);

namespace Markocupic\ContaoCrmBundle\Crm;

use CloudConvert\Api;
use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\StringUtil;
use Contao\Controller;
use Contao\System;
use Contao\Validator;
use Contao\FilesModel;
use Contao\Date;
use Contao\Database;
use Markocupic\PhpOffice\PhpWord\MsWordTemplateProcessor;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class CrmService
 *
 * @package Markocupic\ContaoCrmBundle\Crm
 */
class CrmService
{
    /** @var string */
    protected static $tplSrc = 'vendor/markocupic/contao-crm-bundle/src/Resources/contao/templates/crm_invoice_template_default.docx';

    /** @var string */
    protected static $tempDir = 'system/tmp';

    /** @var ContaoFramework */
    protected $framework;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var string */
    protected $projectDir;

    /**
     * CrmService constructor.
     *
     * @param ContaoFramework $framework
     * @param TranslatorInterface $translator
     * @param string $projectDir
     */
    public function __construct(ContaoFramework $framework, TranslatorInterface $translator, string $projectDir)
    {

        $this->framework = $framework;
        $this->translator = $translator;
        $this->projectDir = $projectDir;
    }

    /**
     * Generate the invoice from a docx template
     *
     * @param $id
     * @param string $format
     * @throws \CloudConvert\Exceptions\InvalidParameterException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \PhpOffice\PhpWord\Exception\CopyFileException
     * @throws \PhpOffice\PhpWord\Exception\CreateTemporaryFileException
     * @throws \PhpOffice\PhpWord\Exception\Exception
     */
    public function generateInvoice($id, string $format = 'docx')
    {

        /** @var Database $databaseAdapter */
        $databaseAdapter = $this->framework->getAdapter(Database::class);

        /** @var Validator $validatorAdapter */
        $validatorAdapter = $this->framework->getAdapter(Validator::class);

        /** @var FilesModel $filesModelAdapter */
        $filesModelAdapter = $this->framework->getAdapter(FilesModel::class);

        /** @var Date $dateAdapter */
        $dateAdapter = $this->framework->getAdapter(Date::class);

        /** @var StringUtil $stringUtilAdapter */
        $stringUtilAdapter = $this->framework->getAdapter(StringUtil::class);

        /** @var System $systemAdapter */
        $systemAdapter = $this->framework->getAdapter(System::class);

        /** @var Controller $controllerAdapter */
        $controllerAdapter = $this->framework->getAdapter(Controller::class);

        // Load language
        $systemAdapter->loadLanguageFile('tl_crm_service');

        // Load the invoice and customer data
        $objInvoice = $databaseAdapter->getInstance()
            ->prepare('SELECT * FROM tl_crm_service WHERE id=?')
            ->execute($id);

        $objCustomer = $databaseAdapter->getInstance()
            ->prepare('SELECT * FROM tl_crm_customer WHERE id=?')
            ->execute($objInvoice->toCustomer);

        // Get the template path
        if ($objInvoice->crmInvoiceTpl != '' || $validatorAdapter->isUuid($objInvoice->crmInvoiceTpl))
        {
            $objTplFile = $filesModelAdapter->findByUuid($objInvoice->crmInvoiceTpl);
            if ($objTplFile !== null)
            {
                static::$tplSrc = $objTplFile->path;
            }
        }

        // Generate filename
        $type = $this->translator->trans('tl_crm_service.invoiceTypeReference.' . $objInvoice->invoiceType . '.1', [], 'contao_default');
        $filename = sprintf(
            '%s_%s_%s_%s.docx',
            $type,
            $dateAdapter->parse('Ymd', $objInvoice->invoiceDate),
            str_pad($objInvoice->id, 7, 0, STR_PAD_LEFT),
            str_replace(' ', '-', $objCustomer->company)
        );

        $target = static::$tempDir . '/' . $filename;

        // Instantiate the Template processor
        $templateProcessor = new MsWordTemplateProcessor(static::$tplSrc, $target);

        $templateProcessor->replace('invoiceAddress', $objCustomer->invoiceAddress, ['multiline' => true]);
        $ustNumber = $objCustomer->ustId != '' ? 'Us-tID: ' . $objCustomer->ustId : '';
        $templateProcessor->replace('ustId', $ustNumber);
        $templateProcessor->replace('invoiceDate', $dateAdapter->parse('d.m.Y', $objInvoice->invoiceDate));
        $templateProcessor->replace('projectId', $this->translator->trans('MSC.projectId', [], 'contao_default') . ': ' . str_pad($objInvoice->id, 7, 0, STR_PAD_LEFT));

        if ($objInvoice->invoiceType == 'invoiceDelivered')
        {
            $invoiceNumber = $this->translator->trans('MSC.invoiceNumber', [], 'contao_default') . ': ' . $objInvoice->invoiceNumber;
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
        $customerId = $this->translator->trans('MSC.customerId', [], 'contao_default') . ': ' . str_pad($objCustomer->id, 7, 0, STR_PAD_LEFT);
        $templateProcessor->replace('customerId', $customerId);

        // Invoice table
        $arrServices = $stringUtilAdapter->deserialize($objInvoice->servicePositions, true);
        $templateProcessor->cloneRow('a', count($arrServices));
        $quantityTotal = 0;
        foreach ($arrServices as $key => $arrService)
        {
            $i = $key + 1;
            $quantityTotal += $arrService['quantity'];
            $templateProcessor->createClone('a');
            $templateProcessor->addToClone('a', 'a', $this->prepareString($i), ['multiline' => false]);
            $templateProcessor->addToClone('a', 'b', $this->prepareString($arrService['item']), ['multiline' => true]);
            $templateProcessor->addToClone('a', 'c', $arrService['quantity'], ['multiline' => false]);
            $templateProcessor->addToClone('a', 'd', $this->prepareString($objInvoice->currency), ['multiline' => false]);
            $templateProcessor->addToClone('a', 'e', $this->prepareString($arrService['price']), ['multiline' => false]);
        }

        $templateProcessor->replace('f', $quantityTotal);
        $templateProcessor->replace('g', $objInvoice->currency);
        $templateProcessor->replace('h', $objInvoice->price);

        if ($objInvoice->alternativeInvoiceText != '')
        {
            $templateProcessor->replace('INVOICE_TEXT', $objInvoice->alternativeInvoiceText, ['multiline' => true]);
        }
        else
        {
            $templateProcessor->replace('INVOICE_TEXT', $objInvoice->defaultInvoiceText, ['multiline' => true]);
        }

        $templateProcessor->saveAs($this->projectDir . '/' . $target);
        sleep(2);

        if ($format == 'pdf')
        {
            $this->sendPdfToBrowser('files/Rechnungen/' . $filename);
        }
        else
        {
            $controllerAdapter->sendFileToBrowser('files/Rechnungen/' . $filename);
        }
    }

    /**
     * Convert docx to pdf
     *
     * @param string $docxSRC
     * @throws \CloudConvert\Exceptions\InvalidParameterException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function sendPdfToBrowser(string $docxSRC)
    {

        /** @var Controller $controllerAdapter */
        $controllerAdapter = $this->framework->getAdapter(Controller::class);

        /** @var Config $configAdapter */
        $configAdapter = $this->framework->getAdapter(Config::class);

        if (!$configAdapter->get('clodConvertApiKey'))
        {
            new \Exception('No API Key defined for the Cloud Convert Service. https://cloudconvert.com/api');
        }

        $key = $configAdapter->get('clodConvertApiKey');

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
                'file'         => fopen($this->projectDir . '/' . $docxSRC, 'r'),
            ])
                ->wait()
                ->download($this->projectDir . '/' . $pdfSRC);

            $controllerAdapter->sendFileToBrowser($pdfSRC);
        } catch (\Exception $e)
        {
            // network problems, etc..
            echo "Something else went wrong: " . $e->getMessage() . "\n";
        }
    }

    /**
     * @param string $string
     * @return string
     */
    protected function prepareString(string $string = ''): string
    {

        if (empty($string))
        {
            return '';
        }

        return htmlspecialchars(html_entity_decode((string) $string));
    }

}
