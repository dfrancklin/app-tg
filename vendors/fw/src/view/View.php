<?php

namespace FW\View;

use \FW\Core\Config;
use \FW\Core\FlashMessages;
use \FW\Core\DependenciesManager;
use \FW\Security\ISecurityService;

class View
{

	private $data;

	private $security;

	private $template;

	private $messages;

	private $views;

	public function __construct(
		ISecurityService $security,
		FlashMessages $messages,
		String $template,
		String $views)
	{
		$this->security = $security;
		$this->messages = $messages;
		$this->template = $template;
		$this->views = $views;
		$this->data = [];
	}

	public function __get($name)
	{
		if (!array_key_exists($name, $this->data)) {
			throw new \Exception('A variable "' . $name . '" was not defined on the view');
		}

		return $this->data[$name];
	}

	public function __set($name, $value)
	{
		$this->data[$name] = $value;
	}

	public function render($__view)
	{
		$__template = $this->views . '/' . $this->template . '.php';
		if (!file_exists($__template)) {
			throw new \Exception('Template file "' . $__template . '" does not exists!');
		}

		$__view = $this->views . '/' . $__view . '.php';
		if (!file_exists($__view)) {
			throw new \Exception('Page file "' . $__view . '" does not exists!');
		}

		if (count($this->data)) {
			extract($this->data);
		}

		ob_start();
		require $__view;
		$__content = ob_get_contents();
		ob_clean();

		ob_start();
		require $__template;
		$__fullPage = ob_get_contents();
		ob_clean();

		$__parts = preg_split('/<!--\s?content\s?-->/i', $__fullPage);
		$__head = $__foot = null;

		if (!empty($__parts)) {
			if (count($__parts) === 2) {
				list($__head, $__foot) = $__parts;
			} elseif (count($__parts) === 1) {
				list($__head) = $__parts;
			}
		}

		return $__head . $__content . $__foot;
	}

}
