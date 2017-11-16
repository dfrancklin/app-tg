<?php

namespace PHC\Components\Form;

use PHC\Interfaces\IComponent;

class ButtonComponent implements IComponent
{

	const STYLES = [
		'primary' => ' btn-primary',
		'secondary' => ' btn-secondary',
		'success' => ' btn-success',
		'danger' => ' btn-danger',
		'warning' => ' btn-warning',
		'info' => ' btn-info',
		'light' => ' btn-light',
		'dark' => ' btn-dark',
		'link' => ' btn-link'
	];

	const SIZES = [
		's' => ' btn-sm',
		'n' => '',
		'l' => ' btn-lg'
	];

	const TYPES = ['button', 'reset', 'submit', 'link'];

	private $templates = [
		'button' => '<button type="%s" name="%s" title="%s" class="component__button btn%s%s%s"%s>%s %s</button>',
		'link' => '<a href="%s" name="%s" title="%s" class="component__button btn%s%s%s"%s>%s %s</a>',
		'icon' => '<span class="material-icons">%s</span>'
	];

	private $name;

	private $title;

	private $type;

	private $action;

	private $size;

	private $block;

	private $style;

	private $icon;

	private $iconOnly;

	private $additional;

	public function render(bool $print = false)
	{
		$button = $this->formatButton();

		if ($print) {
			echo $button;
		} else {
			return $button;
		}
	}

	private function formatButton()
	{
		$icon = '';

		if ($this->type !== 'link' && empty($this->name)) {
			throw new \Exception('The name of the button must be informed');
		}

		if (empty($this->type) || !in_array($this->type, self::TYPES)) {
			$this->type = 'button';
		}

		if (empty($this->style) || !array_key_exists($this->style, self::STYLES)) {
			$this->style = 'secondary';
		}

		if (empty($this->size) || !array_key_exists($this->size, self::SIZES)) {
			$this->size = 'n';
		}

		if (empty($this->title)) {
			$this->title = ucfirst($this->name);
		}

		if (!empty($this->icon)) {
			$icon = sprintf($this->templates['icon'], $this->icon);
		}

		$text = $this->title;

		if ($icon && $this->iconOnly) {
			$text = sprintf('<span class="sr-only">%s</span>', $this->title);
		}

		$additional = '';

		if (!empty($this->additional) && is_array($this->additional)) {
			foreach ($this->additional as $property => $value) {
				$additional .= sprintf(' %s="%s"', $property, $value);
			}
		}

		if ($this->type === 'link' && empty($this->action)) {
			$this->action = '#';
		}

		if ($this->type === 'link') {
			return sprintf($this->templates['link'],
							$this->action,
							$this->name,
							$this->title,
							self::STYLES[$this->style],
							self::SIZES[$this->size],
							($this->block ? ' btn-block' : ''),
							$additional,
							$text,
							$icon);
		} else {
			return sprintf($this->templates['button'],
							$this->type,
							$this->name,
							$this->title,
							self::STYLES[$this->style],
							self::SIZES[$this->size],
							($this->block ? ' btn-block' : ''),
							$additional,
							$text,
							$icon);
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
