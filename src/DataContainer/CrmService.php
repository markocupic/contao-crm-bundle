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

namespace Markocupic\ContaoCrmBundle\DataContainer;

use Contao\Controller;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\Image;
use Contao\Input;
use Contao\Message;
use Contao\StringUtil;
use Markocupic\ContaoCrmBundle\Config\InvoiceType;
use Markocupic\ContaoCrmBundle\Invoice\Generator;
use Markocupic\ContaoCrmBundle\Model\CrmCustomerModel;
use Markocupic\ContaoCrmBundle\Model\CrmServiceModel;
use PhpOffice\PhpWord\Exception\CopyFileException;
use PhpOffice\PhpWord\Exception\CreateTemporaryFileException;
use Twig\Environment;

readonly class CrmService
{
    public function __construct(
        private Generator $generator,
        private Environment $twig,
        private string $cloudConvertApiKey,
    ) {
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

            if (null !== ($objInvoice = CrmServiceModel::findById(Input::get('id')))) {
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
        $classes = [];

        if (InvoiceType::INVOICE_DELIVERED === $arrRow['invoiceType']) {
            $classes[] = 'invoice-delivered';
        }

        if ($arrRow['paid']) {
            $classes[] = 'invoice-paid';
        }

        $key = empty($arrRow['invoiceType']) ? InvoiceType::CALCULATION : $arrRow['invoiceType'];

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

        $arrRow['id_padded'] = str_pad((string) $arrRow['id'], 7, '0', STR_PAD_LEFT);

        return $this->twig->render('@MarkocupicContaoCrm/Backend/list_label.html.twig', [
            'classes' => $classes,
            'title_attribute' => $titleAttr,
            'project' => $arrRow,
            'customer_company' => $arrRow['toCustomer'] ? CrmCustomerModel::findById($arrRow['toCustomer'])->company : '---',
            'customer_id' => $arrRow['toCustomer'] ? CrmCustomerModel::findById($arrRow['toCustomer'])->id : '---',
            'unit' => $unit,
            'quantity' => $quantity,
            'price' => $price,
        ]);
    }
}
