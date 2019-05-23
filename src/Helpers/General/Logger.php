<?php

/**
 * @Author: LogIN
 * @Date:   2017-04-02 10:52:34
 * @Last Modified by:   LogIN
 * @Last Modified time: 2017-04-02 12:49:20
 */

namespace SysLog\Helpers\General;

use \Bramus\Monolog\Formatter\ColoredLineFormatter;
use \Monolog;

class Logger {
	/**
	 * @param $environment
	 * @return \Monolog\Logger
	 */
	static public function write($environment = null) {
		static $log = null;
		if ((null === $log || null !== $environment)) {
			$log = new Monolog\Logger('fluprint');
			// Log file
			$dir = __DIR__ . '/../../../log';
			if (!is_dir($dir)) {
				mkdir($dir);
			}
			$log->pushHandler(new Monolog\Handler\StreamHandler($dir . '/' . date('Y-m-d'), Monolog\Logger::INFO));

			// Console
			if (php_sapi_name() == 'cli') {
				$handler = new Monolog\Handler\StreamHandler('php://stdout', Monolog\Logger::DEBUG);
				$handler->setFormatter(new ColoredLineFormatter());
				$log->pushHandler($handler);
			}
		}
		return $log;
	}
}
