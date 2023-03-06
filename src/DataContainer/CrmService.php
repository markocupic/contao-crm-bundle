<?php

declare(strict_types=1);

/*
 * This file is part of Contao CRM Bundle.
 *
 * (c) Marko Cupic 2023 <m.cupic@gmx.ch>
 * @license GPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-crm-bundle
 */

namespace Markocupic\ContaoCrmBundle\DataContainer;

use Contao\Controller;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\Image;
use Contao\Input;
use Contao\Message;
use Contao\StringUtil;
use Markocupic\ContaoCrmBundle\Invoice\Generator;
use Markocupic\ContaoCrmBundle\Model\CrmCustomerModel;
use Markocupic\ContaoCrmBundle\Model\CrmServiceModel;
use PhpOffice\PhpWord\Exception\CopyFileException;
use PhpOffice\PhpWord\Exception\CreateTemporaryFileException;

class CrmService
{
    private Generator $generator;
    private string $cloudConvertApiKey;

    public function __construct(Generator $generator, string $cloudConvertApiKey)
    {
        $this->generator = $generator;
        $this->cloudConvertApiKey = $cloudConvertApiKey;
    }

    /**
     * @throws CopyFileException
     * @throws CreateTemporaryFileException
     */
    #[AsCallback(table: 'tl_crm_service', target: 'list.operations.generateInvoiceDocx.button', priority: 100)]
    #[AsCallback(table: 'tl_crm_service', target: 'list.operations.generateInvoicePdf.button', priority: 100)]
    public function generateInvoice(array $row, string|null $href, string $label, string $title, string|null $icon, string $attributes): string
    {
        if ('generateInvoice' === Input::get('action') && Input::get('id') && Input::get('type')) {
            $type = Input::get('type');

            if (null !== ($objInvoice = CrmServiceModel::findByPk(Input::get('id')))) {
                $this->generator->generateInvoice($objInvoice, $type);
            }
        }

        return '<a href="'.Controller::addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ';
    }

    #[AsCallback(table: 'tl_crm_service', target: 'config.onload', priority: 100)]
    public function checkCloudConvertApiKey(): void
    {
        if (empty($this->cloudConvertApiKey)) {
            Message::addInfo('Please read the README.md in vendor/markocupic/contao-crm-bundle and add your CloudConvert API key in config/config.yml for downloading pdf invoices.');
        }
    }

    #[AsCallback(table: 'tl_crm_service', target: 'list.label.label', priority: 100)]
    public function listServices(array $arrRow): string
    {
        $strService = '
<div class="tl_content_left %s" title="%s">
    <div class="list-service-row-1">%s/Kd-Nr: %s</div>
    <div class="list-service-row-2">%s</div>
    <div class="list-service-row-3">%s: %s</div>
    <div class="list-service-row-4">%s: %s</div>
    <div class="list-service-row-5">%s: %s %s (%s %s)</div>
    <div class="list-service-row-6">%s: %s %s</div>
</div>';
        $class = '';

        if ('invoiceDelivered' === $arrRow['invoiceType']) {
            $class = ' invoiceDelivered';
        }

        if ($arrRow['paid']) {
            $class = ' invoicePaid';
        }

        $key = $arrRow['invoiceType'];
        $titleAttr = $arrRow['paid'] ? $GLOBALS['TL_LANG']['tl_crm_service']['paid'][0] : $GLOBALS['TL_LANG']['tl_crm_service']['invoiceTypeReference'][$key][0];

        // Service positions
        $servicePositions = StringUtil::deserialize($arrRow['servicePositions'], true);
        $quantity = 0;
        $unit = '';
        $price = 0;

        if (\count($servicePositions)) {
            foreach ($servicePositions as $service) {
                if (isset($service['quantity']) && !empty($service['quantity'])) {
                    $quantity += $service['quantity'];
                }

                if ('' === $unit && isset($service['unit']) && !empty($service['unit'])) {
                    $unit = $service['unit'];
                }

                if (isset($service['price']) && !empty($service['price'])) {
                    $price += (int) $service['price'];
                }
            }
        }

        return sprintf(
            $strService,
            $class,
            $titleAttr,
            // Row 1
            CrmCustomerModel::findByPk($arrRow['toCustomer'])->company,
            CrmCustomerModel::findByPk($arrRow['toCustomer'])->id,
            // Row 2
            $arrRow['title'],
            // Row 3
            $GLOBALS['TL_LANG']['MSC']['invoiceNumber'],
            $arrRow['invoiceNumber'],
            // Row 4
            $GLOBALS['TL_LANG']['MSC']['projectId'],
            str_pad((string) $arrRow['id'], 7, '0', STR_PAD_LEFT),
            // Row 5
            $GLOBALS['TL_LANG']['MSC']['projectPrice'],
            $arrRow['price'],
            $arrRow['currency'],
            $price,
            $arrRow['currency'],
            // Row 6
            $GLOBALS['TL_LANG']['MSC']['expense'],
            $quantity,
            $unit,
        );
    }
}
