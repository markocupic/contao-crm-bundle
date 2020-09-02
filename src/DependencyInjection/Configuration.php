<?php

declare(strict_types=1);

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

namespace Markocupic\ContaoCrmBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('markocupic_contao_crm');
        $treeBuilder
            ->getRootNode()
            ->children()
                ->scalarNode('temp_dir')
                    ->cannotBeEmpty()
                    ->info('Set the temporary file directory, where Contao will store the generated invoices.')
                    ->defaultValue('system/tmp')
                ->end()
                ->scalarNode('cloudconvert_api_key')
                    ->info('Set the cloudconvert api key for downloading the invoice as a pdf document.')
                    ->defaultValue('')
                ->end()
                ->scalarNode('docx_invoice_template')
                    ->cannotBeEmpty()
                    ->info('Set the docx template path.')
                    ->defaultValue('vendor/markocupic/contao-crm-bundle/src/Resources/contao/templates/crm_invoice_template_default.docx')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }


}