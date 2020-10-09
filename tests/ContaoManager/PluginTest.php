<?php

declare(strict_types=1);

/*
 * This file is part of markocupic/contao-crm-bundle.
 *
 * (c) Marko Cupic
 *
 * @license MIT
 */

namespace Markocupic\ContaoCrmBundle\Tests\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\DelegatingParser;
use Contao\TestCase\ContaoTestCase;
use Markocupic\ContaoCrmBundle\ContaoManager\Plugin;
use Markocupic\ContaoCrmBundle\MarkocupicContaoCrmBundle;

/**
 * Class PluginTest.
 */
class PluginTest extends ContaoTestCase
{
    /**
     * Test Contao manager plugin class instantiation.
     */
    public function testInstantiation(): void
    {
        $this->assertInstanceOf(Plugin::class, new Plugin());
    }

    /**
     * Test returns the bundles.
     */
    public function testGetBundles(): void
    {
        $plugin = new Plugin();

        /** @var array $bundles */
        $bundles = $plugin->getBundles(new DelegatingParser());

        $this->assertCount(1, $bundles);
        $this->assertInstanceOf(BundleConfig::class, $bundles[0]);
        $this->assertSame(MarkocupicContaoCrmBundle::class, $bundles[0]->getName());
        $this->assertSame([ContaoCoreBundle::class], $bundles[0]->getLoadAfter());
    }
}
