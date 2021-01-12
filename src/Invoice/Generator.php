<?php

declare(strict_types=1);

/*
 * This file is part of Contao Crm Bundle.
 *
 * (c) Marko Cupic 2021 <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-crm-bundle
 */

namespace Markocupic\ContaoCrmBundle\Invoice;

use CloudConvert\Exceptions\ApiException;
use CloudConvert\Exceptions\InvalidParameterException;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Date;
use Contao\File;
use Contao\FilesModel;
use Contao\System;
use Contao\Validator;
use GuzzleHttp\Exception\GuzzleException;
use Markocupic\ContaoCrmBundle\Invoice\Docx\Docx;
use Markocupic\ContaoCrmBundle\Invoice\Pdf\Pdf;
use Markocupic\ContaoCrmBundle\Model\CrmCustomerModel;
use Markocupic\ContaoCrmBundle\Model\CrmServiceModel;
use PhpOffice\PhpWord\Exception\CopyFileException;
use PhpOffice\PhpWord\Exception\CreateTemporaryFileException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class Generator.
 */
class Generator
{
    /**
     * @var ContaoFramework
     */
    protected $framework;

    /**
     * @var Docx
     */
    protected $docx;

    /**
     * @var Pdf
     */
    protected $pdf;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var string
     */
    protected $projectDir;

    /**
     * @var string
     */
    protected $docxInvoiceTemplate;

    /**
     * @var string
     */
    protected $tempDir;

    /**
     * Generator constructor.
     */
    public function __construct(ContaoFramework $framework, Docx $docx, Pdf $pdf, TranslatorInterface $translator, string $projectDir, string $docxInvoiceTemplate, string $tempDir)
    {
        $this->framework = $framework;
        $this->docx = $docx;
        $this->pdf = $pdf;
        $this->translator = $translator;
        $this->projectDir = $projectDir;
        $this->docxInvoiceTemplate = $docxInvoiceTemplate;
        $this->tempDir = $tempDir;
    }

    /**
     * Generate the invoice from a docx template.
     *
     * @throws ApiException
     * @throws InvalidParameterException
     * @throws GuzzleException
     * @throws CopyFileException
     * @throws CreateTemporaryFileException
     */
    public function generateInvoice(CrmServiceModel $objService, string $format = 'docx'): void
    {
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

        if (null === $objCustomer) {
            throw new \Exception(sprintf('Datarecord tl_crm_customer with ID %s is null.', $objService->toCustomer));
        }

        // Get the template path
        if ('' !== $objService->crmInvoiceTpl || $validatorAdapter->isUuid($objService->crmInvoiceTpl)) {
            $objTplFile = $filesModelAdapter->findByUuid($objService->crmInvoiceTpl);

            if (null !== $objTplFile) {
                $this->docxInvoiceTemplate = $objTplFile->path;
            }
        }

        // Generate filename
        $type = $this->translator->trans('tl_crm_service.invoiceTypeReference.'.$objService->invoiceType.'.1', [], 'contao_default');
        $filename = sprintf(
            '%s_%s_%s_%s.docx',
            $type,
            $dateAdapter->parse('Ymd', $objService->invoiceDate),
            str_pad($objService->id, 7, '0', STR_PAD_LEFT),
            str_replace(' ', '-', $objCustomer->company)
        );

        $destinationSrc = $this->tempDir.'/'.$filename;

        $objFile = $this->docx->generate($objService, $objCustomer, $this->docxInvoiceTemplate, $destinationSrc);

        if ($objFile instanceof File) {
            if ('pdf' === $format) {
                $this->sendPdfToBrowser($objFile);
            } else {
                $objFile->sendToBrowser();
            }
        }
    }

    /**
     * Convert docx to pdf.
     *
     * @throws ApiException
     * @throws InvalidParameterException
     * @throws GuzzleException
     */
    protected function sendPdfToBrowser(File $objFile): void
    {
        $objFile = $this->pdf->generate($objFile);

        if ($objFile instanceof File) {
            $objFile->sendToBrowser();
        }
    }

    protected function prepareString(string $string = ''): string
    {
        if (empty($string)) {
            return '';
        }

        return htmlspecialchars(html_entity_decode((string) $string));
    }
}
