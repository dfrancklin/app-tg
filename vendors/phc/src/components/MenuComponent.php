<?php

namespace PHC;

class MenuComponent {

	private static $menu;

	private static $router;

	private static $security;

	private static $templates = [
		'menu' => '<aside class="menu js-menu"><div class="menu__wrapper js-menu-container bg-dark"><button class="menu__hide js-menu-hide">&times;</button>%s</div></aside>',
		'menu-group' => '<nav class="menu__group">%s%s</nav>',
		'menu-title' => '<h3 class="menu__title">%s%s</h3>',
		'menu-content' => '<ul class="menu__content nav flex-column nav-pills">%s</ul>',
		'menu-item' => '<li class="menu__item nav-item"><a href="%s" class="menu__link nav-link %s">%s%s</a></li>',
		'icon' => '<span class="material-icons mr-2">%s</span>',
	];

	private function __contruct() {

	}

	public static function render() {
		self::init();
		$output = '';

		foreach (self::$menu->groups as $group) {
			$output .= self::formatGroup($group);
		}

		echo sprintf(self::$templates['menu'], $output);
	}

	private static function init() {
		$dm = \FW\Core\DependenciesManager::getInstance();

		self::$menu = \FW\Core\Config::getInstance()->get('menu');
		self::$router = \FW\Core\Router::getInstance();
		self::$security = $dm->resolve(\FW\Security\SecurityService::class);
	}

	private static function formatGroup($group) {
		$show = !empty($group->roles) ? self::$security->hasAnyRoles($group->roles) : true;

		if (!$show) {
			return;
		}

		$title = '';

		if (!empty($group->icon) || !empty($group->title)) {
			$icon = $group->icon
					? sprintf(self::$templates['icon'], $group->icon)
					: '';

			$title .= sprintf(self::$templates['menu-title'], $icon, $group->title);
		}

		$content = self::formatContent($group->items);

		return sprintf(self::$templates['menu-group'], $title, $content);
	}

	private static function formatContent($items) {
		$content = '';

		foreach ($items as $item) {
			$content .= self::formatItem($item);
		}

		return sprintf(self::$templates['menu-content'], $content);
	}

	private static function formatItem($item) {
		$show = !empty($item->roles) ? self::$security->hasAnyRoles($item->roles) : true;
		$active = false;

		if (!$show) {
			return;
		}

		if (empty($item->activeRoute)) {
			$item->activeRoute = [];
		}

		if (!is_array($item->activeRoute)) {
			$item->activeRoute = [$item->activeRoute];
		}

		if (!in_array($item->href, $item->activeRoute)) {
			array_push($item->activeRoute, $item->href);
		}

		foreach ($item->activeRoute as $route) {
			$pattern = '/^' . preg_replace(['/[\/\/]+/i', '/\//i', '/([\*]+)/i'], ['/', '\/', '?.*'], $route) . '$/';

			if (preg_match($pattern, self::$router->getActiveRoute())) {
				$active = true;
				break;
			}
		}

		$icon = $item->icon
				? sprintf(self::$templates['icon'], $item->icon)
				: '';

		return sprintf(self::$templates['menu-item'], $item->href, $active ? 'active menu__link--active' : '', $icon, $item->title);
	}

}
