<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

// Legends
$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] = $GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] . ';{cloudconvert_settings:hide},clodConvertApiKey;';

// Fields
$GLOBALS['TL_DCA']['tl_settings']['fields']['clodConvertApiKey'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_settings']['clodConvertApiKey'],
    'inputType' => 'text',
    'eval' => array('mandatory' => true)
);

