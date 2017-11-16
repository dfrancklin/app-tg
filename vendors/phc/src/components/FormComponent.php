<?php

namespace PHC\Components;

use PHC\Interfaces\IComponent;

class FormComponent implements IComponent
{

	const METHODS = ['GET', 'POST'];

	const TEMPLATES = [
		'form' => '<form class="row" action="%s" method="%s" name="%s" id="%s"%s>%s%s</form>'
	];

	const COMPONENTS = [
		'input' => \PHC\Components\Form\InputComponent::class,
		'hidden' => \PHC\Components\Form\HiddenComponent::class,
		'checkbox' => \PHC\Components\Form\CheckboxComponent::class,
		'radio' => \PHC\Components\Form\RadioComponent::class,
		'select' => \PHC\Components\Form\SelectComponent::class,
		'text' => \PHC\Components\Form\TextAreaComponent::class,
		'uploader' => \PHC\Components\Form\UploaderComponent::class,
		'picklist' => \PHC\Components\Form\PicklistComponent::class,
		'button' => \PHC\Components\Form\ButtonComponent::class,
	];

	private $action;

	private $method;

	private $multipart;

	private $name;

	private $id;

	private $children = [];

	private $buttons = [];

	public function __call(String $method, Array $parameters)
	{
		if (array_key_exists($method, self::COMPONENTS)) {
			return $this->add($method, ...$parameters);
		} else {
			throw new \Exception('The method "' . $method . '" does not exists on class "' . self::class . '"');
		}
	}

	private function add(String $type, Array $config)
	{
		$component = self::COMPONENTS[$type];
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
