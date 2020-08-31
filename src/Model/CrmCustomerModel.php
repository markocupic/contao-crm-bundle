<?php

/**
 * This file is part of a markocupic Contao Bundle.
 *
 * (c) Marko Cupic 2020 <m.cupic@gmx.ch>
 * @author     Marko Cupic
 * @package    Contao CRM Bundle
 * @license    MIT
 * @see        https://github.com/markocupic/contao-crm-bundle
 *
 */

declare(strict_types=1);

namespace Markocupic\ContaoCrmBundle\Model;

use Contao\Model;

/**
 * Class CrmCustomerModel
 *
 * @package Markocupic\ContaoCrmBundle\Model
 */
class CrmCustomerModel extends Model
{
    protected static $strTable = 'tl_crm_customer';

}
