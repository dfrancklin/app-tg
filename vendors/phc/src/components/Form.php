<?php

namespace PHC\Components;

use PHC\Interfaces\IComponent;

class Form implements IComponent
{

	const METHODS = ['GET', 'POST'];

	const TEMPLATES = [
		'form' => '<form class="row" action="%s" method="%s" name="%s" id="%s"%s>%s%s</form>'
	];

	const COMPONENTS = [
		'input' => \PHC\Components\Form\Input::class,
		'hidden' => \PHC\Components\Form\Hidden::class,
		'checkbox' => \PHC\Components\Form\Checkbox::class,
		'radio' => \PHC\Components\Form\Radio::class,
		'select' => \PHC\Components\Form\Select::class,
		'text' => \PHC\Components\Form\TextArea::class,
		'uploader' => \PHC\Components\Form\Uploader::class,
		'picklist' => \PHC\Components\Form\Picklist::class,
		'button' => \PHC\Components\Form\Button::class,
	];

	private static $custom = [];

	private $action;

	private $method;

	private $multipart;

	private $name;

	private $id;

	private $children = [];

	private $buttons = [];

	public static function use(String $name, String $class)
	{
		self::$custom[$name] = $class;
	}

	public function __call(String $method, Array $parameters)
	{
		if (array_key_exists($method, array_merge(self::$custom, self::COMPONENTS))) {
			return $this->add($method, ...$parameters);
		} else {
			throw new \Exception('The method "' . $method . '" does not exists on class "' . self::class . '"');
		}
	}

	private function add(String $type, Array $config)
	{
		if (array_key_exists($type, self::$custom)) {
			$component = self::$custom[$type];
		} else {
			$component = self::COMPONENTS[$type];
		}

		$component = new $component;

		foreach ($config as $attr => $value) {
			$component->{$attr} = $value;
		}

		if (get_class($component) === self::COMPONENTS['uploader']) {
			$this->multipart = true;
		}

		if (get_class($component) === self::COMPONENTS['button']) {
			$this->buttons[] = $component;
		} else {
			$this->children[] = $component;
		}

		return $this;
	}

	public function render(bool $print = true)
	{
		if (empty($this->method)) {
			$this->method = 'GET';
		}

		if (!in_array($this->method, self::METHODS)) {
			throw new \Exception('The HTTP Method "' . $this->method . '" is invalid');
		}

		if (!empty($this->buttons)) {
			array_unshift($this->buttons, '<div class="col component__group-buttons">');
			array_push($this->buttons, '</div>');
		}

		$multipart = '';

		if ($this->multipart) {
			$multipart = ' enctype="multipart/form-data"';
		}

		$form = sprintf(self::TEMPLATES['form'],
						$this->action,
						$this->method,
						$this->name,
						$this->id,
						$multipart,
						implode('', $this->children),
						implode('', $this->buttons));

		if ($print) {
			echo $form;
		} else {
			return $form;
		}
	}

	public function __get(String $attr)
	{
		if (!property_exists(__CLASS__, $attr)) {
			throw new \Exception('The property "' . $attr . '" does not exists on the class "' . __CLASS__ . '"');
		}

		return $this->$attr;
	}

	public function __set(String $attr, $value)
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
