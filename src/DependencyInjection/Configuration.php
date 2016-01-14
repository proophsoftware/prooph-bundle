<?php
/**
 * prooph (http://getprooph.org/)
 *
 * @see       https://github.com/proophsoftware/prooph-bundle for the canonical source repository
 * @copyright Copyright (c) 2016 prooph software GmbH (http://prooph-software.com/)
 * @license   https://github.com/proophsoftware/prooph-bundle/blob/master/LICENSE.md New BSD License
 */
namespace Prooph\Bundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Simple configuration for prooph components
 *
 * To learn more about the prooph component configuration see {@link http://getprooph.org/}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('prooph');

        // our factories handles validation and we are flexible with keys
        // Please take a look at the docs or specific prooph component factory for the configuration options
        $rootNode->ignoreExtraKeys(false);

        return $treeBuilder;
    }
}
