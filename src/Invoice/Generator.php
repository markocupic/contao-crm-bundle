<?php

declare(strict_types=1);

/*
 * This file is part of Contao CRM Bundle.
 *
 * (c) Marko Cupic 2024 <m.cupic@gmx.ch>
 * @license GPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-crm-bundle
 */

namespace Markocupic\ContaoCrmBundle\Invoice;

use Contao\CoreBundle\Exception\ResponseException;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Slug\Slug;
use Contao\Date;
use Contao\FilesModel;
use Contao\System;
use Contao\Validator;
use Markocupic\CloudconvertBundle\Conversion\ConvertFile;
use Markocupic\ContaoCrmBundle\Invoice\Docx\Docx;
use Markocupic\ContaoCrmBundle\Model\CrmCustomerModel;
use Markocupic\ContaoCrmBundle\Model\CrmServiceModel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Contracts\Translation\TranslatorInterface;

class Generator
{
    public function __construct(
        protected readonly ContaoFramework $framework,
        protected readonly Slug $slug,
        protected readonly Docx $docx,
        protected readonly ConvertFile $convertFile,
        protected readonly TranslatorInterface $translator,
        protected string $docxInvoiceTemplate,
        protected readonly string $tempDir,
    ) {
    }

    /**
     * Generate the invoice from a docx template.
     *
     * @throws \Exception
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

        $options = [
            'locale' => 'en',
            'validChars' => 'a-zA-Z0-9_-',
            'delimiter' => '_',
        ];

        $filename = $this->slug->generate($filename, $options);

        $filename = preg_replace('/[_]{2,}/', '_', $filename).'.docx';

        $destinationSrc = $this->tempDir.'/'.$filename;

        $objSplFile = $this->docx->generate($objService, $objCustomer, $this->docxInvoiceTemplate, $destinationSrc);

        if ('pdf' === $format) {
            $objSplFile = $this->convertFile
                ->file($objSplFile->getRealPath())
                ->convertTo('pdf')
            ;
        }

        throw new ResponseException($this->sendToBrowser($objSplFile));
    }

    protected function prepareString(string $string = ''): string
    {
        if (empty($string)) {
            return '';
        }

        return htmlspecialchars(html_entity_decode($string));
    }

    protected function sendToBrowser(\SplFileInfo|string $file, string $fileName = null, string $disposition = ResponseHeaderBag::DISPOSITION_ATTACHMENT): BinaryFileResponse
    {
        $response = new BinaryFileResponse($file);
        $response->setContentDisposition($disposition, $fileName ?? $response->getFile()->getFilename());

        return $response;
    }
}
