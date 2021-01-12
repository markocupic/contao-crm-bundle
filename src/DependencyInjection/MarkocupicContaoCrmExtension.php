<?php

declare(strict_types=1);

/*
 * This file is part of Contao Crm Bundle.
 *
 * (c) Marko Cupic 2021 <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-crm-bundle
 */

namespace Markocupic\ContaoCrmBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Class MarkocupicContaoCrmExtension.
 */
class MarkocupicContaoCrmExtension extends Extension
{
    /**
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );

        $config = $this->processConfiguration($configuration, $configs);

        $loader->load('parameters.yml');
        $loader->load('services.yml');
        $loader->load('listener.yml');

        $container->setParameter('markocupic_contao_crm.temp_dir', $config['temp_dir']);
        $container->setParameter('markocupic_contao_crm.cloudconvert_api_key', $config['cloudconvert_api_key']);
        $container->setParameter('markocupic_contao_crm.docx_invoice_template', $config['docx_invoice_template']);
    }
}
