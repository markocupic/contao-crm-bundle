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

namespace Markocupic\ContaoCrmBundle\Invoice;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Date;
use Contao\File;
use Contao\FilesModel;
use Contao\System;
use Contao\Validator;
use Markocupic\CloudconvertBundle\Conversion\ConvertFile;
use Markocupic\ContaoCrmBundle\Invoice\Docx\Docx;
use Markocupic\ContaoCrmBundle\Model\CrmCustomerModel;
use Markocupic\ContaoCrmBundle\Model\CrmServiceModel;
use PhpOffice\PhpWord\Exception\CopyFileException;
use PhpOffice\PhpWord\Exception\CreateTemporaryFileException;
use function Safe\preg_replace;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class Generator.
 */
class Generator
{
    protected ContaoFramework $framework;
    protected Docx $docx;
    protected ConvertFile $convertFile;
    protected TranslatorInterface $translator;
    protected string $projectDir;
    protected string $docxInvoiceTemplate;
    protected string $tempDir;

    /**
     * Generator constructor.
     */
    public function __construct(ContaoFramework $framework, Docx $docx, ConvertFile $convertFile, TranslatorInterface $translator, string $projectDir, string $docxInvoiceTemplate, string $tempDir)
    {
        $this->framework = $framework;
        $this->docx = $docx;
        $this->convertFile = $convertFile;
        $this->translator = $translator;
        $this->projectDir = $projectDir;
        $this->docxInvoiceTemplate = $docxInvoiceTemplate;
        $this->tempDir = $tempDir;
    }

    /**
     * Generate the invoice from a docx template.
     *
     * @throws CopyFileException
     * @throws CreateTemporaryFileException
     */
    public function generateInvoice(CrmServiceModel $objService, string $format = 'docx'): void
    {
        /** @var CrmCustomerModel $crmCustomerModelAdapter */
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
            throw new \Exception(sprintf('Data record tl_crm_customer with ID %s is null.', $objService->toCustomer));
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
            '%s_%s_%s_%s',
            $type,
            $dateAdapter->parse('Ymd', $objService->invoiceDate),
            str_pad((string) $objService->id, 7, '0', STR_PAD_LEFT),
            $objCustomer->company
        );

        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename);
        $filename = preg_replace('/[_]{2,}/', '_', $filename).'.docx';

        $destinationSrc = $this->tempDir.'/'.$filename;

        $objFile = $this->docx->generate($objService, $objCustomer, $this->docxInvoiceTemplate, $destinationSrc);

        if ('pdf' === $format) {
            $this->sendPdfToBrowser($objFile);
        } else {
            $objFile->sendToBrowser();
        }
    }

    /**
     * Convert docx to pdf.
     *
     * @throws \Exception
     */
    protected function sendPdfToBrowser(File $objFile): void
    {
        $this->convertFile
            ->file($this->projectDir.'/'.$objFile->path)
            ->sendToBrowser(true, true)
            ->convertTo('pdf')
        ;
    }

    protected function prepareString(string $string = ''): string
    {
        if (empty($string)) {
            return '';
        }

        return htmlspecialchars(html_entity_decode($string));
    }
}
