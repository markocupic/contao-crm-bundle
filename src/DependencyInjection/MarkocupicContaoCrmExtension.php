<?php

/**
 * This file is part of a markocupic Contao Bundle.
 *
 * (c) Marko Cupic 2020 <m.cupic@gmx.ch>
 *
 * @author     Marko Cupic
 * @package    Contao CRM Bundle
 * @license    MIT
 * @see        https://github.com/markocupic/contao-crm-bundle
 *
 */

declare(strict_types=1);

namespace Markocupic\ContaoCrmBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Class MarkocupicContaoCrmExtension
 *
 * @package Markocupic\ContaoCrmBundle\DependencyInjection
 */
class MarkocupicContaoCrmExtension extends Extension
{

    /**
     * @param array $configs
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {

        $configuration = new Configuration();

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
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
