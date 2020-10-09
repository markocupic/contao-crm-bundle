<?php

declare(strict_types=1);

/*
 * This file is part of markocupic/contao-crm-bundle.
 *
 * (c) Marko Cupic
 *
 * @license MIT
 */

namespace Markocupic\ContaoCrmBundle\ContaoManager;

use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;

/**
 * Class Plugin.
 */
class Plugin implements BundlePluginInterface
{
    /**
     * @return array
     */
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create('Markocupic\ContaoCrmBundle\MarkocupicContaoCrmBundle')
                ->setLoadAfter(['Contao\CoreBundle\ContaoCoreBundle']),
        ];
    }
}
