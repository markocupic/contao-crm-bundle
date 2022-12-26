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

namespace Markocupic\ContaoCrmBundle\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\Image;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

class CrmCustomer
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    #[AsCallback(table: 'tl_crm_customer', target: 'list.label.label', priority: 100)]
    public function addIcon(array $row, string $label, DataContainer $dc, array $labels): array
    {
        $image = 'member';
        $disabled = false;

        if ($row['disable']) {
            $image .= '_';
            $disabled = true;
        }

        $labels[0] = sprintf(
            '<div class="list_icon_new" style="background-image:url(\'%s\')" data-icon="%s" data-icon-disabled="%s">&nbsp;</div>',
            Image::getPath($image),
            Image::getPath($disabled ? $image : rtrim($image, '_')),
            Image::getPath(rtrim($image, '_').'_')
        );

        return $labels;
    }

    /**
     * @throws Exception
     */
    #[AsCallback(table: 'tl_crm_customer', target: 'config.onsubmit', priority: 100)]
    public function storeDateAdded(DataContainer $dc): void
    {
        // Return if there is no active record (override all)
        if (!$dc->activeRecord || $dc->activeRecord->dateAdded > 0) {
            return;
        }

        $this->connection->update('tl_crm_customer', ['dateAdded' => time()], ['id' => $dc->id]);
    }
}
