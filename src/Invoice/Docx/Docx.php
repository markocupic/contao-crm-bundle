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

namespace Markocupic\ContaoCrmBundle\Invoice\Docx;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\File;
use Contao\StringUtil;
use Contao\System;
use Contao\Date;
use Markocupic\ContaoCrmBundle\Model\CrmCustomerModel;
use Markocupic\ContaoCrmBundle\Model\CrmServiceModel;
use Markocupic\PhpOffice\PhpWord\MsWordTemplateProcessor;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class Docx
 *
 * @package Markocupic\ContaoCrmBundle\Docx
 */
class Docx
{
    /** @var ContaoFramework */
    protected $framework;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var string */
    protected $projectDir;

    /**
     * Generator constructor.
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
     * Generate Invoice
     *
     * @param CrmServiceModel $objService
     * @param string $templSrc
     * @param string $destinationSrc
     * @return File|null
     * @throws \PhpOffice\PhpWord\Exception\CopyFileException
     * @throws \PhpOffice\PhpWord\Exception\CreateTemporaryFileException
     */

    /**
     * Generate Invoice
     *
     * @param CrmServiceModel $objService
     * @param CrmCustomerModel $objCustomer
     * @param string $templSrc
     * @param string $destinationSrc
     * @return File
     * @throws \PhpOffice\PhpWord\Exception\CopyFileException
     * @throws \PhpOffice\PhpWord\Exception\CreateTemporaryFileException
     */
    public function generate(CrmServiceModel $objService, CrmCustomerModel $objCustomer, string $templSrc, string $destinationSrc): File
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
        $templateProcessor = new MsWordTemplateProcessor($templSrc, $destinationSrc);
        $templateProcessor->replace('invoiceAddress', $objCustomer->invoiceAddress, ['multiline' => true]);
        $ustNumber = $objCustomer->ustId != '' ? 'Us-tID: ' . $objCustomer->ustId : '';
        $templateProcessor->replace('ustId', $ustNumber);
        $templateProcessor->replace('invoiceDate', $dateAdapter->parse('d.m.Y', $objService->invoiceDate));

        $projectId = sprintf(
            '%s: %s',
            $this->translator->trans('MSC.projectId', [], 'contao_default'),
            str_pad($objService->id, 7, '0', STR_PAD_LEFT)
        );
        $templateProcessor->replace('projectId', $projectId);

        $invoiceNumber = '';
        if ($objService->invoiceType == 'invoiceDelivered')
        {
            $invoiceNumber = sprintf(
                '%s: %s',
                $this->translator->trans('MSC.invoiceNumber', [], 'contao_default'),
                $objService->invoiceNumber
            );
        }

        // Invoice Number
        $templateProcessor->replace('invoiceNumber', $invoiceNumber);

        // Invoice type
        $templateProcessor->replace('invoiceType', strtoupper($GLOBALS['TL_LANG']['tl_crm_service']['invoiceTypeReference'][$objService->invoiceType][1]));

        // Customer ID
        $customerId = sprintf(
            '%s: %s',
            $this->translator->trans('MSC.customerId', [], 'contao_default'),
            str_pad($objCustomer->id, 7, '0', STR_PAD_LEFT)
        );
        $templateProcessor->replace('customerId', $customerId);

        // Invoice table
        $arrServices = $stringUtilAdapter->deserialize($objService->servicePositions, true);
        $quantityTotal = 0;
        foreach ($arrServices as $key => $arrService)
        {
            $i = $key + 1;
            $quantityTotal += $arrService['quantity'];
            $templateProcessor->createClone('a');
            $templateProcessor->addToClone('a', 'a', $this->prepareString((string) $i), ['multiline' => false]);
            $templateProcessor->addToClone('a', 'b', $this->prepareString($arrService['item']), ['multiline' => true]);
            $templateProcessor->addToClone('a', 'c', $arrService['quantity'], ['multiline' => false]);
            $templateProcessor->addToClone('a', 'd', $this->prepareString($objService->currency), ['multiline' => false]);
            $templateProcessor->addToClone('a', 'e', $this->prepareString($arrService['price']), ['multiline' => false]);
        }

        $templateProcessor->replace('f', $quantityTotal);
        $templateProcessor->replace('g', $objService->currency);
        $templateProcessor->replace('h', $objService->price);

        if ($objService->alternativeInvoiceText != '')
        {
            $templateProcessor->replace('INVOICE_TEXT', $objService->alternativeInvoiceText, ['multiline' => true]);
        }
        else
        {
            $templateProcessor->replace('INVOICE_TEXT', $objService->defaultInvoiceText, ['multiline' => true]);
        }
        if (file_exists($this->projectDir . '/' . $destinationSrc))
        {
            unlink($this->projectDir . '/' . $destinationSrc);
        }

        // Save file to system/tmp
        $templateProcessor->generateUncached(true)
            ->sendToBrowser(false)
            ->generate();

        if (file_exists($this->projectDir . '/' . $destinationSrc))
        {
            return new File($destinationSrc);
        }

        throw new \Exception('Failed generating Invoice from docx template.');
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
