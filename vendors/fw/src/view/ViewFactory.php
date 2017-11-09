<?php

namespace FW\View;

use \FW\Core\Config;
use \FW\Core\FlashMessages;
use \FW\Core\DependenciesManager;
use \FW\Security\ISecurityService;

/**
 * @Factory
 */
class ViewFactory implements IViewFactory {

	private static $dm;

	private static $config;

	public function __construct() {
		self::$dm = DependenciesManager::getInstance();
		self::$config = Config::getInstance();
	}

	public static function create($template = null) : View {
		$security = self::$dm->resolve(ISecurityService::class);
		$views = self::$config->get('views-folder');

		if (!$template) {
			$template = self::$config->get('template');
		}

		return new View($security,FlashMessages::getInstance(), $template, $views);
	}

}
