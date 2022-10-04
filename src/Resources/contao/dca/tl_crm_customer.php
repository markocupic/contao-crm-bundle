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

use Contao\Backend;
use Contao\DataContainer;
use Contao\Image;
use Contao\System;

$GLOBALS['TL_DCA']['tl_crm_customer'] = [
    'config'      => [
        'dataContainer'     => 'Table',
        'enableVersioning'  => true,
        'onsubmit_callback' => [
            ['tl_crm_customer', 'storeDateAdded'],
        ],
        'sql'               => [
            'keys' => [
                'id'    => 'primary',
                'email' => 'index',
            ],
        ],
    ],
    'list'        => [
        'sorting'           => [
            'mode'        => DataContainer::MODE_SORTABLE,
            'fields'      => ['dateAdded'],
            'panelLayout' => 'filter;sort,search,limit',
        ],
        'label'             => [
            'fields'         => ['icon', 'firstname', 'lastname', 'username', 'dateAdded'],
            'showColumns'    => true,
            'label_callback' => ['tl_crm_customer', 'addIcon'],
        ],
        'global_operations' => [
            'all' => [
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"',
            ],
        ],
        'operations'        => [
            'edit'   => [
                'href' => 'act=edit',
                'icon' => 'edit.gif',
            ],
            'copy'   => [
                'href' => 'act=copy',
                'icon' => 'copy.svg',
            ],
            'delete' => [
                'href'       => 'act=delete',
                'icon'       => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null).'\'))return false;Backend.getScrollOffset()"',
            ],
            'toggle' => [
                'href'    => 'act=toggle&amp;field=disable',
                'icon'    => 'visible.svg',
                'reverse' => true,
            ],
            'show'   => [
                'href' => 'act=show',
                'icon' => 'show.svg',
            ],
        ],
    ],
    'palettes'    => [
        '__selector__' => ['assignDir'],
        'default'      => '{personal_legend},firstname,lastname,gender;{address_legend:hide},company,street,postal,city,state,country;{contact_legend},phone,mobile,fax,email,website;{invoice_legend},ustId,invoiceAddress;{homedir_legend:hide},assignDir;{account_legend},disable',
    ],
    'subpalettes' => [
        'assignDir' => 'homeDir',
    ],
    'fields'      => [
        'id'             => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp'         => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'firstname'      => [
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'flag'      => 1,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'lastname'       => [
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'flag'      => 1,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'gender'         => [
            'exclude'   => true,
            'inputType' => 'select',
            'options'   => ['male', 'female', 'other'],
            'reference' => &$GLOBALS['TL_LANG']['MSC'],
            'eval'      => ['includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(32) NOT NULL default ''",
        ],
        'company'        => [
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'flag'      => 1,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'street'         => [
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'postal'         => [
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 32, 'tl_class' => 'w50'],
            'sql'       => "varchar(32) NOT NULL default ''",
        ],
        'city'           => [
            'exclude'   => true,
            'filter'    => true,
            'search'    => true,
            'sorting'   => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'state'          => [
            'exclude'   => true,
            'sorting'   => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 64, 'tl_class' => 'w50'],
            'sql'       => "varchar(64) NOT NULL default ''",
        ],
        'country'        => [
            'exclude'          => true,
            'filter'           => true,
            'sorting'          => true,
            'inputType'        => 'select',
            'eval'             => ['includeBlankOption' => true, 'chosen' => true, 'feEditable' => true, 'feViewable' => true, 'feGroup' => 'address', 'tl_class' => 'w50'],
            'options_callback' => static function () {
                $countries = System::getContainer()->get('contao.intl.countries')->getCountries();

                // Convert to lower case for backwards compatibility, to be changed in Contao 5.0
                return array_combine(array_map('strtolower', array_keys($countries)), $countries);
            },
            'sql'              => "varchar(2) NOT NULL default ''",
        ],
        'phone'          => [
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 64, 'rgxp' => 'phone', 'decodeEntities' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(64) NOT NULL default ''",
        ],
        'mobile'         => [
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 64, 'rgxp' => 'phone', 'decodeEntities' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(64) NOT NULL default ''",
        ],
        'fax'            => [
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 64, 'rgxp' => 'phone', 'decodeEntities' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(64) NOT NULL default ''",
        ],
        'email'          => [
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'rgxp' => 'email', 'unique' => true, 'decodeEntities' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'website'        => [
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'url', 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'ustId'          => [
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'invoiceAddress' => [
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'textarea',
            'eval'      => ['decodeEntities' => false, 'tl_class' => 'clr'],
            'sql'       => 'mediumtext NULL',
        ],
        'assignDir'      => [
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'disable'        => [
            'exclude'   => true,
            'toggle'    => true,
            'filter'    => true,
            'flag'      => DataContainer::SORT_INITIAL_LETTER_DESC,
            'inputType' => 'checkbox',
            'eval'      => ['doNotCopy' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'dateAdded'      => [
            'default'   => time(),
            'sorting'   => true,
            'flag'      => 6,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'date', 'datepicker' => true, 'tl_class' => 'clr wizard'],
            'sql'       => "int(10) unsigned NOT NULL default '0'",
        ],
    ],
];

/**
 * Class tl_crm_customer.
 */
class tl_crm_customer extends Backend
{
    /**
     * Add an image to each record.
     *
     * @param array $row
     * @param string $label
     * @param array $args
     *
     * @return array
     */
    public function addIcon($row, $label, DataContainer $dc, $args)
    {
        $image = 'member';
        $disabled = false;

        if ($row['disable']) {
            $image .= '_';
            $disabled = true;
        }

        $args[0] = sprintf(
            '<div class="list_icon_new" style="background-image:url(\'%s\')" data-icon="%s" data-icon-disabled="%s">&nbsp;</div>',
            Image::getPath($image),
            Image::getPath($disabled ? $image : rtrim($image, '_')),
            Image::getPath(rtrim($image, '_').'_')
        );

        return $args;
    }

    /**
     * Store the date when the account has been added.
     *
     * @param DataContainer $dc
     */
    public function storeDateAdded($dc): void
    {
        // Front end call
        if (!$dc instanceof DataContainer) {
            return;
        }

        // Return if there is no active record (override all)
        if (!$dc->activeRecord || $dc->activeRecord->dateAdded > 0) {
            return;
        }

        $this->Database
            ->prepare('UPDATE tl_crm_customer SET dateAdded = ? WHERE id = ?')
            ->execute(time(), $dc->id);
    }

}
