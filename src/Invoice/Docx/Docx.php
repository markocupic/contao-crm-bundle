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

namespace Markocupic\ContaoCrmBundle\Invoice\Docx;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Date;
use Contao\StringUtil;
use Contao\System;
use Markocupic\ContaoCrmBundle\Config\InvoiceType;
use Markocupic\ContaoCrmBundle\Model\CrmCustomerModel;
use Markocupic\ContaoCrmBundle\Model\CrmServiceModel;
use Markocupic\PhpOffice\PhpWord\MsWordTemplateProcessor;
use Symfony\Component\Filesystem\Path;
use Symfony\Contracts\Translation\TranslatorInterface;

class Docx
{
    protected array $tags = [];

    public function __construct(
        protected readonly ContaoFramework $framework,
        protected readonly TranslatorInterface $translator,
        protected readonly string $projectDir,
    ) {
        // Prepare the tags array
        $this->tags['clones'] = [];
        $this->tags['tags'] = [];
    }

    /**
     * Generate the invoice.
     *
     * @throws \Exception
     */
    public function generate(CrmServiceModel $objService, CrmCustomerModel $objCustomer, string $templateSrc, string $destinationSrc): \SplFileObject
    {
        /** @var System $systemAdapter */
        $systemAdapter = $this->framework->getAdapter(System::class);

        $docxTemplateSrc = Path::makeAbsolute($templateSrc, $this->projectDir);
        $destinationSrc = Path::makeAbsolute($destinationSrc, $this->projectDir);

        // Load language
        $systemAdapter->loadLanguageFile('tl_crm_service');

        $this->setTags($objService, $objCustomer);

        // Instantiate the template processor
        $templateProcessor = new MsWordTemplateProcessor($docxTemplateSrc, $destinationSrc);

        // Replace tags
        if (isset($this->tags['tags']) && \is_array($this->tags['tags'])) {
            foreach ($this->tags['tags'] as $key => $arrValue) {
                $templateProcessor->replace($key, (string) $arrValue[0], $arrValue[1]);
            }
        }

        // Replace clones
        if (isset($this->tags['clones']) && \is_array($this->tags['clones'])) {
            foreach (array_keys($this->tags['clones']) as $k) {
                foreach ($this->tags['clones'][$k] as $vv) {
                    $templateProcessor->createClone($k);

                    foreach ($vv as $kkk => $vvv) {
                        // $k (clone key)
                        // $kkk search string
                        // replace string
                        // array options e.g. ["multiline" => true]
                        $templateProcessor->addToClone($k, (string) $kkk, (string) $vvv[0], $vvv[1]);
                    }
                }
            }
        }

        // Remove old file
        if (file_exists($destinationSrc)) {
            unlink($destinationSrc);
        }

        // Save file to system/tmp
        $objSplFile = $templateProcessor->generate();

        if (file_exists($objSplFile->getRealPath())) {
            return $objSplFile;
        }

        throw new \Exception('Failed generating Invoice from docx template.');
    }

    protected function setTags(CrmServiceModel $objService, CrmCustomerModel $objCustomer): void
    {
        /** @var Date $dateAdapter */
        $dateAdapter = $this->framework->getAdapter(Date::class);

        /** @var StringUtil $stringUtilAdapter */
        $stringUtilAdapter = $this->framework->getAdapter(StringUtil::class);

        /** @var System $systemAdapter */
        $systemAdapter = $this->framework->getAdapter(System::class);

        // Load language
        $systemAdapter->loadLanguageFile('tl_crm_service');

        // Instantiate the Template processor
        $this->tags['tags']['invoiceAddress'] = [$objCustomer->invoiceAddress, ['multiline' => true]];

        $ustNumber = '' !== $objCustomer->ustId ? 'Us-tID: '.$objCustomer->ustId : '';
        $this->tags['tags']['ustId'] = [$ustNumber, ['multiline' => false]];

        $this->tags['tags']['invoiceDate'] = [$dateAdapter->parse('d.m.Y', $objService->invoiceDate), ['multiline' => false]];

        $projectId = str_pad((string) $objService->id, 7, '0', STR_PAD_LEFT);
        $this->tags['tags']['projectId'] = [$projectId, ['multiline' => false]];

        // Invoice Number
        $invoiceNumber = '';

        if (InvoiceType::CALCULATION === $objService->invoiceType) {
            $invoiceNumber = '---';
        }

        if (InvoiceType::INVOICE_DELIVERED === $objService->invoiceType) {
            $invoiceNumber = $objService->invoiceNumber;
        }

        $this->tags['tags']['invoiceNumber'] = [$invoiceNumber, ['multiline' => false]];

        // Invoice type
        $this->tags['tags']['invoiceType'] = [strtoupper($GLOBALS['TL_LANG']['tl_crm_service']['invoiceTypeReference'][$objService->invoiceType][1]), ['multiline' => false]];

        // Customer ID
        $customerId = str_pad((string) $objCustomer->id, 7, '0', STR_PAD_LEFT);
        $this->tags['tags']['customerId'] = [$customerId, ['multiline' => false]];

        // Invoice table
        $arrServices = $stringUtilAdapter->deserialize($objService->servicePositions, true);
        $quantityTotal = 0;

        foreach ($arrServices as $key => $arrService) {
            $i = $key + 1;
            $quantityTotal += $arrService['quantity'];
            $this->tags['clones']['a'][] = [
                'a' => [$this->prepareString((string) $i), ['multiline' => false]],
                'b' => [$this->prepareString($arrService['item']), ['multiline' => true]],
                'c' => [$arrService['quantity'], ['multiline' => false]],
                'd' => [$this->prepareString($objService->currency), ['multiline' => false]],
                'e' => [$this->prepareString($arrService['price']), ['multiline' => false]],
            ];
        }

        $this->tags['tags']['f'] = [$quantityTotal, ['multiline' => false]];
        $this->tags['tags']['g'] = [$objService->currency, ['multiline' => false]];
        $this->tags['tags']['h'] = [$objService->price, ['multiline' => false]];

        // Invoice text
        if (!empty($objService->alternativeInvoiceText)) {
            $this->tags['tags']['invoiceText'] = [$objService->alternativeInvoiceText, ['multiline' => true]];
        } else {
            $this->tags['tags']['invoiceText'] = [$objService->defaultInvoiceText, ['multiline' => true]];
        }
    }

    protected function prepareString(string $string = ''): string
    {
        if (empty($string)) {
            return '';
        }

        return htmlspecialchars(html_entity_decode($string));
    }
}
