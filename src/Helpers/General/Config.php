<?php

/**
 * @Author: LogIN
 * @Date:   2017-04-02 10:54:58
 * @Last Modified by:   LogIN
 * @Last Modified time: 2017-04-02 12:56:28
 */

namespace SysLog\Helpers\General;

use \Configula\Config as ConfigulaConf;

/**
 * Configuration helper class used for Configuration values and Translation variables
 *
 * @package SysLog\Helpers
 */
class Config
{

    private function __construct()
    {
    }

    private static $language = array();

    /** @var ConfigulaConf */
    private static $configuration;

    private static $initialized = false;

    /**
     * Initialize Configuration values and Language Translations
     */
    private static function initialize()
    {
        if (self::$initialized) {
            return;
        }
        self::$initialized = true;
        self::$configuration = new ConfigulaConf(ROOT_DIR . 'config');
    }

    /**
     * Returns configuration values
     *
     * @return array
     */
    public static function conf()
    {
        self::initialize();
        return self::$configuration;
    }
}
