<?php
/**
 * Image Max Size plugin for Craft CMS 3.x
 *
 * Prevent images being uploaded that are bigger than a certain size
 *
 * @link      http://moldedjelly.com
 * @copyright Copyright (c) 2019 MoldedJelly
 */

namespace moldedjelly\imagemaxsize;

use moldedjelly\imagemaxsize\fields\AssetsImgMax as AssetsImgMaxField;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\services\Fields;
use craft\events\RegisterComponentTypesEvent;

use yii\base\Event;

/**
 * Class ImageMaxSize
 *
 * @author    MoldedJelly
 * @package   ImageMaxSize
 * @since     1.0.0
 *
 */
class ImageMaxSize extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var ImageMaxSize
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = AssetsImgMaxField::class;
            }
        );

        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                }
            }
        );

        Craft::info(
            Craft::t(
                'imagemaxsize',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================

}
