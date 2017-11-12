<?php

namespace PHC\Components;

use PHC\Interfaces\IComponent;

class MenuComponent implements IComponent
{

	const TEMPLATES = [
		'menu' => '<aside class="menu js-menu"><div class="menu__wrapper js-menu-container bg-dark"><button class="menu__hide js-menu-hide">&times;</button>%s</div></aside>',
		'menu-group' => '<nav class="menu__group">%s%s</nav>',
		'menu-title' => '<h3 class="menu__title">%s%s</h3>',
		'menu-content' => '<ul class="menu__content nav flex-column nav-pills">%s</ul>',
		'menu-item' => '<li class="menu__item nav-item"><a href="%s" class="menu__link nav-link %s">%s%s</a></li>',
		'icon' => '<span class="material-icons mr-2">%s</span>',
	];

	private $definition;

	private $activeRoute;

	private $userRoles;

	public function render(bool $print = false)
	{
		$output = '';

		foreach ($this->definition->groups as $group) {
			$output .= self::formatGroup($group);
		}

		$menu = sprintf(self::TEMPLATES['menu'], $output);

		if ($print) {
			echo $menu;
		} else {
			return $menu;
		}
	}

	private function formatGroup($group)
	{
		$show = !empty($group->roles) ? $this->hasAnyRoles($group->roles) : true;

		if (!$show) {
			return;
		}

		$title = '';

		if (!empty($group->icon) || !empty($group->title)) {
			$icon = $group->icon
					? sprintf(self::TEMPLATES['icon'], $group->icon)
					: '';

			$title .= sprintf(self::TEMPLATES['menu-title'], $icon, $group->title);
		}

		$content = self::formatContent($group->items);

		return sprintf(self::TEMPLATES['menu-group'], $title, $content);
	}

	private function formatContent($items)
	{
		$content = '';

		foreach ($items as $item) {
			$content .= self::formatItem($item);
		}

		return sprintf(self::TEMPLATES['menu-content'], $content);
	}

	private function formatItem($item)
	{
		$show = !empty($item->roles) ? $this->hasAnyRoles($item->roles) : true;
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

			if (preg_match($pattern, $this->activeRoute)) {
				$active = true;
				break;
			}
		}

		$icon = $item->icon
				? sprintf(self::TEMPLATES['icon'], $item->icon)
				: '';

		return sprintf(self::TEMPLATES['menu-item'], $item->href, $active ? 'active menu__link--active' : '', $icon, $item->title);
	}

	private function hasAnyRoles(array $roles)
	{
		if (is_null($this->userRoles) || !is_array($this->userRoles)) {
			return true;
		}

		foreach ($roles as $role) {
			if (in_array($role, $this->userRoles)) {
				return true;
			}
		}

		return false;
	}

	public function __get(string $attr)
	{
		if (!property_exists(__CLASS__, $attr)) {
			throw new \Exception('The property "' . $attr . '" does not exists on the class "' . __CLASS__ . '"');
		}

		return $this->$attr;
	}

	public function __set(string $attr, $value)
	{
		if (!property_exists(__CLASS__, $attr)) {
			throw new \Exception('The property "' . $attr . '" does not exists on the class "' . __CLASS__ . '"');
		}

		$this->$attr = $value;
	}

	public function __toString()
	{
		return $this->render(false);
	}

}
