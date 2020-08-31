<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

/**
 * Table tl_crm_customer
 */
$GLOBALS['TL_DCA']['tl_crm_customer'] = [

    // Config
    'config'      => [
        'dataContainer'     => 'Table',
        'enableVersioning'  => true,
        'onsubmit_callback' => [
            ['tl_crm_customer', 'storeDateAdded'],
        ],
        'sql'               => [
            'keys' => [
                'id'    => 'primary',
                'email' => 'index'
            ]
        ]
    ],

    // List
    'list'        => [
        'sorting'           => [
            'mode'        => 2,
            'fields'      => ['dateAdded DESC'],
            'flag'        => 1,
            'panelLayout' => 'filter;sort,search,limit'
        ],
        'label'             => [
            'fields'         => ['icon', 'firstname', 'lastname', 'dateAdded'],
            'showColumns'    => true,
            'label_callback' => ['tl_crm_customer', 'addIcon']
        ],
        'global_operations' => [
            'all' => [
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            ]
        ],
        'operations'        => [
            'edit'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_crm_customer']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif'
            ],
            'copy'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_crm_customer']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif'
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_crm_customer']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
            ],
            'toggle' => [
                'label'           => &$GLOBALS['TL_LANG']['tl_crm_customer']['toggle'],
                'icon'            => 'visible.gif',
                'attributes'      => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback' => ['tl_crm_customer', 'toggleIcon']
            ],
            'show'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_crm_customer']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif'
            ]
        ]
    ],

    // Palettes
    'palettes'    => [
        '__selector__' => ['assignDir'],
        'default'      => '{personal_legend},firstname,lastname,gender;{address_legend:hide},company,street,postal,city,state,country;{contact_legend},phone,mobile,fax,email,website;{invoice_legend},ustId,invoiceAddress;{homedir_legend:hide},assignDir;{account_legend},disable',
    ],

    // Subpalettes
    'subpalettes' => [
        'assignDir' => 'homeDir'
    ],

    // Fields
    'fields'      => [
        'id'        => [
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ],
        'tstamp'    => [
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],
        'firstname' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_crm_customer']['firstname'],
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'flag'      => 1,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'lastname'  => [
            'label'     => &$GLOBALS['TL_LANG']['tl_crm_customer']['lastname'],
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'flag'      => 1,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'gender'    => [
            'label'     => &$GLOBALS['TL_LANG']['tl_crm_customer']['gender'],
            'exclude'   => true,
            'inputType' => 'select',
            'options'   => ['male', 'female'],
            'reference' => &$GLOBALS['TL_LANG']['MSC'],
            'eval'      => ['includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(32) NOT NULL default ''"
        ],
        'company'   => [
            'label'     => &$GLOBALS['TL_LANG']['tl_crm_customer']['company'],
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'flag'      => 1,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'street'    => [
            'label'     => &$GLOBALS['TL_LANG']['tl_crm_customer']['street'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],

        'postal'         => [
            'label'     => &$GLOBALS['TL_LANG']['tl_crm_customer']['postal'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 32, 'tl_class' => 'w50'],
            'sql'       => "varchar(32) NOT NULL default ''"
        ],
        'city'           => [
            'label'     => &$GLOBALS['TL_LANG']['tl_crm_customer']['city'],
            'exclude'   => true,
            'filter'    => true,
            'search'    => true,
            'sorting'   => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'state'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_crm_customer']['state'],
            'exclude'   => true,
            'sorting'   => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 64, 'tl_class' => 'w50'],
            'sql'       => "varchar(64) NOT NULL default ''"
        ],
        'country'        => [
            'label'     => &$GLOBALS['TL_LANG']['tl_crm_customer']['country'],
            'exclude'   => true,
            'filter'    => true,
            'sorting'   => true,
            'inputType' => 'select',
            'options'   => \Contao\System::getCountries(),
            'eval'      => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(2) NOT NULL default ''"
        ],
        'phone'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_crm_customer']['phone'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 64, 'rgxp' => 'phone', 'decodeEntities' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(64) NOT NULL default ''"
        ],
        'mobile'         => [
            'label'     => &$GLOBALS['TL_LANG']['tl_crm_customer']['mobile'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 64, 'rgxp' => 'phone', 'decodeEntities' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(64) NOT NULL default ''"
        ],
        'fax'            => [
            'label'     => &$GLOBALS['TL_LANG']['tl_crm_customer']['fax'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 64, 'rgxp' => 'phone', 'decodeEntities' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(64) NOT NULL default ''"
        ],
        'email'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_crm_customer']['email'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'rgxp' => 'email', 'unique' => true, 'decodeEntities' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'website'        => [
            'label'     => &$GLOBALS['TL_LANG']['tl_crm_customer']['website'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'url', 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'ustId'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_crm_customer']['ustId'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'invoiceAddress' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_crm_customer']['invoiceAddress'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'textarea',
            'eval'      => ['decodeEntities' => false, 'tl_class' => 'clr'],
            'sql'       => "mediumtext NULL"
        ],
        'assignDir'      => [
            'label'     => &$GLOBALS['TL_LANG']['tl_crm_customer']['assignDir'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'disable'        => [
            'label'     => &$GLOBALS['TL_LANG']['tl_crm_customer']['disable'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'checkbox',
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'dateAdded'      => [
            'label'     => &$GLOBALS['TL_LANG']['MSC']['dateAdded'],
            'default'   => time(),
            'sorting'   => true,
            'flag'      => 6,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'date', 'datepicker' => true, 'tl_class' => 'clr wizard'],
            'sql'       => "int(10) unsigned NOT NULL default '0'"
        ]
    ]
];

/**
 * Class tl_crm_customer
 */
class tl_crm_customer extends \Contao\Backend
{

    /**
     * tl_crm_customer constructor.
     */
    public function __construct()
    {

        parent::__construct();
        $this->import('BackendUser', 'User');
    }

    /**
     * Add an image to each record
     *
     * @param array $row
     * @param string $label
     * @param \Contao\DataContainer $dc
     * @param array $args
     *
     * @return array
     */
    public function addIcon($row, $label, \Contao\DataContainer $dc, $args)
    {

        $image = 'member';

        if ($row['disable'] || $disabled)
        {
            $image .= '_';
        }

        $args[0] = sprintf('<div class="list_icon_new" style="background-image:url(\'%ssystem/themes/%s/images/%s.gif\')" data-icon="%s.gif" data-icon-disabled="%s.gif">&nbsp;</div>', TL_ASSETS_URL, Backend::getTheme(), $image, $disabled ? $image : rtrim($image, '_'), rtrim($image, '_') . '_');

        return $args;
    }

    /**
     * Store the date when the account has been added
     *
     * @param \Contao\DataContainer $dc
     */
    public function storeDateAdded($dc)
    {

        // Front end call
        if (!$dc instanceof \Contao\DataContainer)
        {
            return;
        }

        // Return if there is no active record (override all)
        if (!$dc->activeRecord || $dc->activeRecord->dateAdded > 0)
        {
            return;
        }

        $this->Database->prepare("UPDATE tl_crm_customer SET dateAdded=? WHERE id=?")
            ->execute(time(), $dc->id);
    }

    /**
     * Return the "toggle visibility" button
     *
     * @param array $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {

        if (strlen(\Contao\Input::get('tid')))
        {
            $this->toggleVisibility(\Contao\Input::get('tid'), (\Contao\Input::get('state') == 1), (@func_get_arg(12) ?: null));
            $this->redirect($this->getReferer());
        }

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$this->User->hasAccess('tl_crm_customer::disable', 'alexf'))
        {
            return '';
        }

        $href .= '&amp;tid=' . $row['id'] . '&amp;state=' . $row['disable'];

        if ($row['disable'])
        {
            $icon = 'invisible.gif';
        }

        return '<a href="' . $this->addToUrl($href) . '" title="' . specialchars($title) . '"' . $attributes . '>' . \Contao\Image::getHtml($icon, $label, 'data-state="' . ($row['disable'] ? 0 : 1) . '"') . '</a> ';
    }

    /**
     * Disable/enable a user group
     *
     * @param integer $intId
     * @param boolean $blnVisible
     * @param \Contao\DataContainer $dc
     */
    public function toggleVisibility($intId, $blnVisible, \Contao\DataContainer $dc = null)
    {

        // Set the ID and action
        \Contao\Input::setGet('id', $intId);
        \Contao\Input::setGet('act', 'toggle');

        if ($dc)
        {
            $dc->id = $intId; // see #8043
        }

        // Check the field access
        if (!$this->User->hasAccess('tl_crm_customer::disable', 'alexf'))
        {
            $this->log('Not enough permissions to activate/deactivate member ID "' . $intId . '"', __METHOD__, TL_ERROR);
            $this->redirect('contao/main.php?act=error');
        }

        $objVersions = new \Contao\Versions('tl_crm_customer', $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA']['tl_crm_customer']['fields']['disable']['save_callback']))
        {
            foreach ($GLOBALS['TL_DCA']['tl_crm_customer']['fields']['disable']['save_callback'] as $callback)
            {
                if (is_array($callback))
                {
                    $this->import($callback[0]);
                    $blnVisible = $this->{$callback[0]}->{$callback[1]}($blnVisible, ($dc ?: $this));
                }
                elseif (is_callable($callback))
                {
                    $blnVisible = $callback($blnVisible, ($dc ?: $this));
                }
            }
        }

        $time = time();

        // Update the database
        $this->Database->prepare("UPDATE tl_crm_customer SET tstamp=$time, disable='" . ($blnVisible ? '' : 1) . "' WHERE id=?")
            ->execute($intId);

        $objVersions->create();

        // Remove the session if the user is disabled (see #5353)
        if (!$blnVisible)
        {
            $this->Database->prepare("DELETE FROM tl_session WHERE name='FE_USER_AUTH' AND pid=?")
                ->execute($intId);
        }

        // HOOK: update newsletter subscriptions
        if (in_array('newsletter', ModuleLoader::getActive()))
        {
            $objUser = $this->Database->prepare("SELECT email FROM tl_crm_customer WHERE id=?")
                ->limit(1)
                ->execute($intId);

            if ($objUser->numRows)
            {
                $this->Database->prepare("UPDATE tl_newsletter_recipients SET tstamp=$time, active=? WHERE email=?")
                    ->execute(($blnVisible ? 1 : ''), $objUser->email);
            }
        }
    }
}