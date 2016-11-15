<?php

namespace Bug19149\ReproducingBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    const DEFAULT_ZIP_LIB = 'ZipArchive';
    const DEFAULT_WORKING_DIR = '/tmp';

    const YAML_CONFIG_ID_SRC = "source";
    const YAML_CONFIG_ID_DEST = "destination";
    const YAML_CONFIG_ID_WORKING_DIR = "working_dir";
    const YAML_CONFIG_ID_USE = "use";
    const YAML_CONFIG_ID_LIB = "lib";
    const YAML_CONFIG_ID_COMMAND = "command";
    const YAML_CONFIG_ID_STANDARD = "standard";
    
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('bug19149_reproducing');
        $this->addArchiverNode($rootNode);
        return $treeBuilder;
    }

    /**
     * similar to the following:
     *  archiver:
     *      <key>:
     *          target: destination.local
     *          destination: local
     *          working_directory: ~
     *          use: command.standard # refers to lib.command.standard
     *          lib:
     *              command:
     *              # 1st %s: zip file name including path, 2nd %s: files(space-separated if multiple)/ folder to be archived.
     *              # e.g. zip -r foo.zip pdf1.pdf pdf2.pdf folder/ | zip -r foo.zip folder/ | zip -r path/to/foo.zip folder/
     *              standard: 'zip -r %s %s'
     * @param ArrayNodeDefinition $rootNode
     */
    private function addArchiverNode(ArrayNodeDefinition $rootNode)
    {
        // create all fields
        $fields = [
            ["name" => self::YAML_CONFIG_ID_SRC, "type" => "scalar", "isRequired" => true, "cannotBeEmpty" => true],
            ["name" => self::YAML_CONFIG_ID_DEST, "type" => "scalar", "default" => "local"],
            ["name" => self::YAML_CONFIG_ID_WORKING_DIR, "type" => "scalar", "default" => self::DEFAULT_WORKING_DIR],
            ["name" => self::YAML_CONFIG_ID_USE, "type" => "scalar", "default" => self::DEFAULT_ZIP_LIB],
            ["name" => self::YAML_CONFIG_ID_STANDARD, "type" => "scalar"]
        ];
        $nodes = $this->createNodes($fields, false, false);

        /**
         * build command field
         * command:
         *      standard: ~
         **/
        $commandNode = $this->createArrayNode(self::YAML_CONFIG_ID_COMMAND, [$nodes[self::YAML_CONFIG_ID_STANDARD]]);

        /**
         * more could be added in for lib node
         *
         * build lib field
         * lib:
         *      command:
         *          <...>
         **/
        $libNode = $this->createArrayNode(self::YAML_CONFIG_ID_LIB, [$commandNode]);

        /**
         * more could be added in for lib node
         *
         * build archiver field
         * archiver:
         *      target: ~
         *      destination: ~
         *      working_dir:
         *      use: ~
         *      lib:
         *          <...>
         **/
        $archiverNode = $this->createArrayNode("archiver", [
            $nodes[self::YAML_CONFIG_ID_SRC],
            $nodes[self::YAML_CONFIG_ID_DEST],
            $nodes[self::YAML_CONFIG_ID_WORKING_DIR],
            $nodes[self::YAML_CONFIG_ID_USE], $libNode
        ], [
            "prototype" => "array", "requiresAtLeastOneElement" => true, "useAttributeAsKey" => "id", "addDefaultsIfNotSet" => true
        ]);

        $rootNode->append($archiverNode);
    }

    /**
     * @param $name
     * @param array $children
     * @param array $options
     * @return ArrayNodeDefinition
     */
    private function createArrayNode($name, $children = [], $options = [])
    {
        $builder = new NodeBuilder();
        $arrayNode = $builder->arrayNode($name);
        if (empty($children) && empty($options)) {
            return $arrayNode;
        }

        // append all children to normal array node
        if (!empty($children) && empty($options)) {
            foreach ($children as $child) {
                $arrayNode->append($child);
            }
            return $arrayNode;
        }

        if (isset($options["requiresAtLeastOneElement"])) {
            $arrayNode->requiresAtLeastOneElement();
        }
        if (isset($options["useAttributeAsKey"])) {
            $arrayNode->useAttributeAsKey($options["useAttributeAsKey"]);
        }

        // create prototype for $arrayNode
        if (isset($options["prototype"])) {
            $node = $arrayNode->prototype($options["prototype"]);
            if (!$node instanceof ArrayNodeDefinition) {
                throw new \InvalidArgumentException("currently only support array prototype.");
            }
            if ($node instanceof ArrayNodeDefinition && empty($children)) {
                throw new \InvalidArgumentException('$children is required when creating an array prototype.');
            }
            if (isset($options["addDefaultsIfNotSet"])) {
                $node->addDefaultsIfNotSet();
            }

            foreach ($children as $child) {
                $node->append($child);
            }
        } else {
            if (isset($options["addDefaultsIfNotSet"])) {
                $arrayNode->addDefaultsIfNotSet();
            }
        }

        return $arrayNode;
    }

    /**
     * @param $nodes
     * @param bool $returnNodeIfSingle directly return NodeDefinition if true
     * @param bool $returnOnlyValues
     * @return array <NodeDefinition>|NodeDefinition
     */
    private function createNodes($nodes, $returnNodeIfSingle = false, $returnOnlyValues = true)
    {
        $nodeBuilder = new NodeBuilder();
        $list = [];
        foreach ($nodes as $node) {
            $newNode = $nodeBuilder->node($node["name"], $node["type"]);
            if (array_key_exists("default", $node)) {
                $defaultValue = $node["default"];
                $newNode->defaultValue($defaultValue);
                $newNode->validate()->ifNull()->then(function ($v) use ($defaultValue) {
                    return $defaultValue;
                });
            }

            if (array_key_exists("isRequired", $node) && $node["isRequired"]) {
                $newNode->isRequired();
            }
            if (array_key_exists("cannotBeEmpty", $node) && $node["cannotBeEmpty"]) {
                $newNode->cannotBeEmpty();
            }

            $list[$node["name"]] = clone $newNode;
            $newNode = null;
        }
        if (count($list) == 1 && $returnNodeIfSingle) {
            return array_shift($list);
        }
        $values = array_values($list);

        return $returnOnlyValues ? $values : $list;
    }
}
