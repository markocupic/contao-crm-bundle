<?php

declare(strict_types=1);

/*
 * This file is part of Contao CRM Bundle.
 *
 * (c) Marko Cupic <m.cupic@gmx.ch>
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
use Contao\StringUtil;
use Contao\System;
use Markocupic\CloudconvertBundle\Conversion\ConvertFile;
use Markocupic\ContaoCrmBundle\Config\InvoiceType;
use Markocupic\ContaoCrmBundle\Invoice\Docx\Docx;
use Markocupic\ContaoCrmBundle\Model\CrmCompanyModel;
use Markocupic\ContaoCrmBundle\Model\CrmCustomerModel;
use Markocupic\ContaoCrmBundle\Model\CrmServiceModel;
use Markocupic\ContaoCrmBundle\QrCodeBill\Creditor;
use Markocupic\ContaoCrmBundle\QrCodeBill\Deptor;
use Markocupic\ContaoCrmBundle\QrCodeBill\PdfMerger;
use Markocupic\ContaoCrmBundle\QrCodeBill\QrInvoiceGenerator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Contracts\Translation\TranslatorInterface;

class Generator
{
    public function __construct(
        protected readonly ContaoFramework $framework,
        protected readonly ConvertFile $convertFile,
        protected readonly Docx $docx,
        protected readonly PdfMerger $pdfMerger,
        protected readonly QrInvoiceGenerator $qrInvoiceGenerator,
        protected readonly Slug $slug,
        protected readonly TranslatorInterface $translator,
        protected string $docxInvoiceTemplate,
        protected readonly string $projectDir,
        protected readonly string $tempDir,
    ) {
    }

    /**
     * Generate the invoice from a docx template.
     *
     * @throws \Exception
     */
    public function generateInvoice(CrmServiceModel $service, string $format = 'docx'): void
    {
        // Get customer object
        $customer = $this->framework->getAdapter(CrmCustomerModel::class)
            ->findById($service->toCustomer)
        ;

        if (null === $customer) {
            throw new \Exception(\sprintf('Data record tl_crm_customer with ID %s is null.', $service->toCustomer));
        }

        // Get the template path
        if (null !== ($docxTempl = $this->framework->getAdapter(FilesModel::class)->findByUuid($service->crmInvoiceTpl))) {
            if (null !== $docxTempl) {
                $this->docxInvoiceTemplate = $docxTempl->path;
            }
        }

        $outputPath = $this->tempDir.'/'.$this->buildFileName($service, $customer);

        $file = $this->docx->generate($service, $customer, $this->docxInvoiceTemplate, $outputPath);

        if ('pdf' === $format) {
            // Convert the docx- to a pdf-file
            $file = $this->convertFile
                ->file($file->getRealPath())
                ->convertTo('pdf')
            ;

            // Append the Swiss QR invoice
            if (InvoiceType::INVOICE_DELIVERED === $service->invoiceType) {
                $file = $this->appendSwissQrInvoice($file, $service);
            }
        }

        throw new ResponseException($this->sendToBrowser($file));
    }

    protected function appendSwissQrInvoice(\SplFileInfo $file, CrmServiceModel $service): \SplFileInfo
    {
        $qrInvoiceFile = $this->qrInvoiceGenerator->generate(
            $this->buildCreditor($service),
            $this->buildDeptor($service),
            $service->invoiceNumber,
            (float) $service->price,
            $service->currency,
            'de',
            $service->title,
        );

        $tempDir = Path::join($this->projectDir, '/system/tmp/qr_invoice');

        if (!is_dir($tempDir)) {
            (new Filesystem())->mkdir($tempDir);
        }

        $outputPath = Path::join($tempDir, $file->getFilename());

        return $this->pdfMerger->merge([$file->getRealPath(), $qrInvoiceFile->getRealPath()], $outputPath);
    }

    protected function buildCreditor(CrmServiceModel $service): Creditor
    {
        $company = CrmCompanyModel::findById($service->company);

        $stringUtil = $this->framework->getAdapter(StringUtil::class);

        return new Creditor(
            $stringUtil->revertInputEncoding($company->company),
            $stringUtil->revertInputEncoding($company->street),
            $stringUtil->revertInputEncoding($company->streetNumber),
            $stringUtil->revertInputEncoding($company->postal),
            $stringUtil->revertInputEncoding($company->city),
            $stringUtil->revertInputEncoding($company->country),
            $stringUtil->revertInputEncoding($company->iban),
        );
    }

    protected function buildDeptor(CrmServiceModel $service): Deptor
    {
        $customer = CrmCustomerModel::findById($service->toCustomer);

        $stringUtil = $this->framework->getAdapter(StringUtil::class);

        return new Deptor(
            $stringUtil->revertInputEncoding($customer->company),
            $stringUtil->revertInputEncoding($customer->street),
            '',
            $stringUtil->revertInputEncoding($customer->postal),
            $stringUtil->revertInputEncoding($customer->city),
            $stringUtil->revertInputEncoding($customer->country),
        );
    }

    protected function prepareString(string $string = ''): string
    {
        if (empty($string)) {
            return '';
        }

        return htmlspecialchars(html_entity_decode($string));
    }

    protected function buildFileName(CrmServiceModel $service, CrmCustomerModel $customer): string
    {
        // Load language
        $this->framework->getAdapter(System::class)
            ->loadLanguageFile('tl_crm_service')
        ;

        // Generate the filename
        $type = $this->translator->trans("tl_crm_service.invoiceTypeReference.$service->invoiceType.1", [], 'contao_default');

        $fileName = \sprintf(
            '%s_%s_%s_%s',
            $type,
            $this->framework->getAdapter(Date::class)->parse('Ymd', $service->invoiceDate),
            str_pad((string) $service->id, 7, '0', STR_PAD_LEFT),
            $customer->company,
        );

        $options = [
            'locale' => 'en',
            'validChars' => 'a-zA-Z0-9_-',
            'delimiter' => '_',
        ];

        $fileName = $this->slug->generate($fileName, $options);

        return preg_replace('/[_]{2,}/', '_', $fileName).'.docx';
    }

    protected function sendToBrowser(\SplFileInfo|string $file, string|null $fileName = null, string $disposition = ResponseHeaderBag::DISPOSITION_ATTACHMENT): BinaryFileResponse
    {
        $response = new BinaryFileResponse($file);
        $response->setContentDisposition($disposition, $fileName ?? $response->getFile()->getFilename());

        return $response;
    }
}
