<?php
/**
 * @category    AssetUnicodeName Plugin
 * @package     Pimcore
 * @subpackage  AssetUnicodeName
 * @author      Łukasz Marszałek <lmarszalek@divante.pl>
 * @copyright   Copyright (c) 2017 Divante Ltd. (https://divante.co)
 */
namespace AssetUnicodeName;

use Pimcore\API\Plugin as PluginLib;
use Pimcore\Controller\Action\Frontend;
use Pimcore\Model\Element\Service;

/**
 * Class Plugin
 * @package AssetUnicodeName
 */
class Plugin extends PluginLib\AbstractPlugin implements PluginLib\PluginInterface
{
    const METADATA_NAME = 'unicode-name';

    /**
     * Init plugin
     */
    public function init()
    {
        parent::init();
        \Pimcore::getEventManager()->attach("asset.preAdd", [$this, "setMetadata"]);
        \Pimcore::getEventManager()->attach("asset.preUpdate", [$this, "setMetadata"]);
    }

    /**
     * Set unicode-name metadata.
     * @param Zend_EventManager_Event $event
     */
    public function setMetadata($event)
    {
        /** @var \Pimcore\Model\Asset $asset */
        $asset = $event->getTarget();
        $request = \Zend_Controller_Front::getInstance()->getRequest();
        $unicodeName = $request->getParam('filename', null);
        $metadata = $request->getParam('metadata', null);

        if ($unicodeName && $asset->getType() != 'folder') {
            $asset->addMetadata(self::METADATA_NAME, "input", $unicodeName);
        } elseif (!is_null($metadata)) {
            $postMetadata = json_decode($metadata);

            if (!is_array($postMetadata)) {
                return;
            }

            foreach ($postMetadata as $changedMetadata) {
                if (isset($changedMetadata->name) && ($changedMetadata->name == self::METADATA_NAME)
                    && !is_null($changedMetadata->data)
                ) {
                    $asset->setFilename(Service::getValidKey($changedMetadata->data, 'asset'));
                    break;
                }
            }
        }
    }

    /**
     * @return bool
     */
    public static function install()
    {
        return 'AssetUnicodeName plugin has been installed.';
    }

    /**
     * @return string
     */
    public static function uninstall()
    {
        return 'AssetUnicodeName plugin has been uninstalled.';
    }

    /**
     * @return bool
     */
    public static function isInstalled()
    {
        return true;
    }
}
