<?php

include_once __DIR__ . '/../../menu-definition.php';

if (isset($menuDefinition)) {
	$dm = \FW\Core\DependenciesManager::getInstance();
	$security = $dm->resolve(\FW\Security\SecurityService::class);

	$menuComponent = new \PHC\Components\MenuComponent;

	$menuComponent->definition = $menuDefinition;
	$menuComponent->activeRoute = \FW\Core\Router::getInstance()->getActiveRoute();

	if (!empty($security)) {
		$menuComponent->userRoles = $security->getUserProfile()->getRoles();
	}

	echo $menuComponent;
}


// private function init()
// {

// 	self::$menu = \FW\Core\Config::getInstance()->get('menu');
// }
?>
