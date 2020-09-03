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

    /** @var array */
    protected $tags = [];

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

        // Prepare the tags array
        $this->tags['clones'] = [];
        $this->tags['tags'] = [];
    }

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

        /** @var System $systemAdapter */
        $systemAdapter = $this->framework->getAdapter(System::class);

        // Load language
        $systemAdapter->loadLanguageFile('tl_crm_service');

        $this->setTags($objService, $objCustomer);

        // Instantiate the Template processor
        $templateProcessor = new MsWordTemplateProcessor($templSrc, $destinationSrc);

        // Replace tags
        if (isset($this->tags['tags']) && is_array($this->tags['tags']))
        {
            foreach ($this->tags['tags'] as $key => $arrValue)
            {
                $templateProcessor->replace($key, (string) $arrValue[0], $arrValue[1]);
            }
        }

        // Replace clones
        if (isset($this->tags['clones']) && is_array($this->tags['clones']))
        {
            foreach ($this->tags['clones'] as $k => $v)
            {
                $templateProcessor->createClone($k);
                foreach ($v as $vv)
                {
                    foreach ($vv as $kkk => $vvv)
                    {
                        $templateProcessor->addToClone($k, $kkk, (string) $vvv[0], $vvv[1]);
                    }
                }
            }
        }

        // Remove old file
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
     * @param CrmServiceModel $objService
     * @param CrmCustomerModel $objCustomer
     */
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

        $ustNumber = $objCustomer->ustId != '' ? 'Us-tID: ' . $objCustomer->ustId : '';
        $this->tags['tags']['ustId'] = [$ustNumber, ['multiline' => false]];

        $this->tags['tags']['invoiceDate'] = [$dateAdapter->parse('d.m.Y', $objService->invoiceDate), ['multiline' => false]];

        $projectId = sprintf(
            '%s: %s',
            $this->translator->trans('MSC.projectId', [], 'contao_default'),
            str_pad($objService->id, 7, '0', STR_PAD_LEFT)
        );
        $this->tags['tags']['projectId'] = [$projectId, ['multiline' => false]];

        // Invoice Number
        $invoiceNumber = '';
        if ($objService->invoiceType == 'invoiceDelivered')
        {
            $invoiceNumber = sprintf(
                '%s: %s',
                $this->translator->trans('MSC.invoiceNumber', [], 'contao_default'),
                $objService->invoiceNumber
            );
        }
        $this->tags['tags']['invoiceNumber'] = [$invoiceNumber, ['multiline' => false]];

        // Invoice type
        $this->tags['tags']['invoiceType'] = [strtoupper($GLOBALS['TL_LANG']['tl_crm_service']['invoiceTypeReference'][$objService->invoiceType][1]), ['multiline' => false]];

        // Customer ID
        $customerId = sprintf(
            '%s: %s',
            $this->translator->trans('MSC.customerId', [], 'contao_default'),
            str_pad($objCustomer->id, 7, '0', STR_PAD_LEFT)
        );
        $this->tags['tags']['customerId'] = [$customerId, ['multiline' => false]];

        // Invoice table
        $arrServices = $stringUtilAdapter->deserialize($objService->servicePositions, true);
        $quantityTotal = 0;
        foreach ($arrServices as $key => $arrService)
        {
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
        if ($objService->alternativeInvoiceText != '')
        {
            $this->tags['tags']['invoiceText'] = [$objService->alternativeInvoiceText, ['multiline' => true]];
        }
        else
        {
            $this->tags['tags']['invoiceText'] = [$objService->defautInvoiceText, ['multiline' => true]];
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
