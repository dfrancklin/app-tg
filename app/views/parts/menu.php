<?php

include_once __DIR__ . '/../../menu-definition.php';

if (isset($menuDefinition)) {
	$dm = \FW\Core\DependenciesManager::getInstance();
	$security = $dm->resolve(\FW\Security\SecurityService::class);

	$menu = new \PHC\Components\Menu;

	$menu->definition = $menuDefinition;
	$menu->activeRoute = \FW\Core\Router::getInstance()->getActiveRoute();

	if (!empty($security)) {
		$menu->userRoles = $security->getUserProfile()->getRoles();
	}

	echo $menu;
}


// private function init()
// {

// 	self::$menu = \FW\Core\Config::getInstance()->get('menu');
// }
?>
