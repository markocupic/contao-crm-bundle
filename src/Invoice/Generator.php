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

declare(strict_types=1);

namespace Markocupic\ContaoCrmBundle\Invoice;

use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\File;
use Contao\System;
use Contao\Validator;
use Contao\FilesModel;
use Contao\Date;
use Markocupic\ContaoCrmBundle\Invoice\Pdf\Pdf;
use Markocupic\ContaoCrmBundle\Invoice\Docx\Docx;
use Markocupic\ContaoCrmBundle\Model\CrmCustomerModel;
use Markocupic\ContaoCrmBundle\Model\CrmServiceModel;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class Generator
 *
 * @package Markocupic\ContaoCrmBundle\Invoice
 */
class Generator
{
    /** @var string */
    protected static $tplSrc = 'vendor/markocupic/contao-crm-bundle/src/Resources/contao/templates/crm_invoice_template_default.docx';

    /** @var string */
    protected static $tempDir = 'system/tmp';

    /** @var ContaoFramework */
    protected $framework;

    /** @var Docx */
    protected $docx;

    /** @var Pdf */
    protected $pdf;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var string */
    protected $projectDir;

    /**
     * Generator constructor.
     *
     * @param ContaoFramework $framework
     * @param Docx $docx
     * @param Pdf $pdf
     * @param TranslatorInterface $translator
     * @param string $projectDir
     */
    public function __construct(ContaoFramework $framework, Docx $docx, Pdf $pdf, TranslatorInterface $translator, string $projectDir)
    {

        $this->framework = $framework;
        $this->translator = $translator;
        $this->projectDir = $projectDir;
        $this->docx = $docx;
        $this->pdf = $pdf;
    }



    /**
     * Generate the invoice from a docx template
     *
     * @param CrmServiceModel $objService
     * @param string $format
     * @throws \CloudConvert\Exceptions\ApiException
     * @throws \CloudConvert\Exceptions\InvalidParameterException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \PhpOffice\PhpWord\Exception\CopyFileException
     * @throws \PhpOffice\PhpWord\Exception\CreateTemporaryFileException
     */
    public function generateInvoice(CrmServiceModel $objService, string $format = 'docx')
    {

        /** @var CrmServiceModel $crmServiceModelAdapter */
        $crmServiceModelAdapter = $this->framework->getAdapter(CrmServiceModel::class);

        /** @var $crmCustomerModelAdapter */
        $crmCustomerModelAdapter = $this->framework->getAdapter(CrmCustomerModel::class);

        /** @var Validator $validatorAdapter */
        $validatorAdapter = $this->framework->getAdapter(Validator::class);

        /** @var FilesModel $filesModelAdapter */
        $filesModelAdapter = $this->framework->getAdapter(FilesModel::class);

        /** @var Date $dateAdapter */
        $dateAdapter = $this->framework->getAdapter(Date::class);

        /** @var System $systemAdapter */
        $systemAdapter = $this->framework->getAdapter(System::class);

        // Load language
        $systemAdapter->loadLanguageFile('tl_crm_service');

        // Get customer object
        $objCustomer = $crmCustomerModelAdapter->findByPk($objService->toCustomer);
        if (null === $objCustomer)
        {
            throw new \Exception(sprintf('Datarecord tl_crm_customer with ID %s is null.', $objService->toCustomer));
        }

        // Get the template path
        if ($objService->crmInvoiceTpl != '' || $validatorAdapter->isUuid($objService->crmInvoiceTpl))
        {
            $objTplFile = $filesModelAdapter->findByUuid($objService->crmInvoiceTpl);
            if ($objTplFile !== null)
            {
                static::$tplSrc = $objTplFile->path;
            }
        }

        // Generate filename
        $type = $this->translator->trans('tl_crm_service.invoiceTypeReference.' . $objService->invoiceType . '.1', [], 'contao_default');
        $filename = sprintf(
            '%s_%s_%s_%s.docx',
            $type,
            $dateAdapter->parse('Ymd', $objService->invoiceDate),
            str_pad($objService->id, 7, '0', STR_PAD_LEFT),
            str_replace(' ', '-', $objCustomer->company)
        );

        $destinationSrc = static::$tempDir . '/' . $filename;

        $objFile = $this->docx->generate($objService, $objCustomer, static::$tplSrc, $destinationSrc);
        if ($objFile instanceof File)
        {
            if ($format == 'pdf')
            {
                $this->sendPdfToBrowser($objFile);
            }
            else
            {
                $objFile->sendToBrowser();
            }
        }
    }

    /**
     * Convert docx to pdf
     *
     * @param File $objFile
     * @throws \CloudConvert\Exceptions\ApiException
     * @throws \CloudConvert\Exceptions\InvalidParameterException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function sendPdfToBrowser(File $objFile)
    {

        /** @var Config $configAdapter */
        $configAdapter = $this->framework->getAdapter(Config::class);

        if (empty($apiKey = $configAdapter->get('clodConvertApiKey')))
        {
            new \Exception('No API Key defined for the Cloud Convert Service. https://cloudconvert.com/api');
        }

        $objFile = $this->pdf->generate($objFile, $apiKey);
        if ($objFile instanceof File)
        {
            $objFile->sendToBrowser();
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
