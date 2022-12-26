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
                ->scalarNode('docx_invoice_template')
                    ->cannotBeEmpty()
                    ->info('Set the docx template path.')
                    ->defaultValue('vendor/markocupic/contao-crm-bundle/contao/templates/crm_invoice_template_default.docx')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
