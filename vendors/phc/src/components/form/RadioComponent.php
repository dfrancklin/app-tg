<?php

namespace PHC\Components\Form;

use PHC\Interfaces\IComponent;

class RadioComponent implements IComponent
{

	const WIDTHS = [
		'1' => ' col-12',
		'1/2' => ' col-md-6 col-12',
		'1/3' => ' col-md-4 col-12',
		'1/4' => ' col-md-3 col-12',
	];

	const TEMPLATES = [
		'form-check' => '<div class="form-check%s">%s</div>',
		'label' => '<label class="form-check-label">%s %s</label>',
		'input' => '<input type="radio" name="%s" id="%s" title="%s" value="%s" class="form-check-input"%s%s>',
	];

	private $name;

	private $title;

	private $value;

	private $checked;

	private $required;

	private $autofocus;

	private $width;

	public function render(bool $print = false)
	{
		$input = $this->formatFormCheck();

		if ($print) {
			echo $input;
		} else {
			return $input;
		}
	}

	private function formatFormCheck()
	{
		$label = $this->formatLabel();

		if (empty($this->width) || !array_key_exists($this->width, self::WIDTHS)) {
			$this->width = '1';
		}

		return sprintf(self::TEMPLATES['form-check'], self::WIDTHS[$this->width], $label);
	}

	private function formatLabel()
	{
		$input = $this->formatInput();

		return sprintf(self::TEMPLATES['label'], $input, $this->title);
	}

	private function formatInput()
	{
		if (empty($this->name)) {
			throw new \Exception('The name of the input must be informed');
		}

		if (empty($this->title)) {
			$this->title = ucfirst($this->name);
		}

		return sprintf(self::TEMPLATES['input'],
						$this->name,
						$this->name,
						$this->title,
						$this->value,
						($this->checked ? ' checked' : null),
						($this->required ? ' required' : null),
						($this->autofocus ? ' autofocus' : null));
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
