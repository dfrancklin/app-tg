<?php

namespace PHC\Components\Form;

use PHC\Interfaces\IComponent;

class HiddenComponent implements IComponent {

	private $templates = [
		'input' => '<input type="hidden" name="%s" value="%s">',
	];

	private $value;

	private $name;

	public function render(bool $print = false) {
		$input = $this->formatInput();

		if ($print) {
			echo $input;
		} else {
			return $input;
		}
	}

	private function formatInput() {
		if (empty($this->name)) {
			throw new \Exception('The name of the input must be informed');
		}

		return sprintf($this->templates['input'],
						$this->name,
						$this->value);
	}

	public function __get(string $attr) {
		if (!property_exists(__CLASS__, $attr)) {
			throw new \Exception('The property "' . $attr . '" does not exists on the class "' . __CLASS__ . '"');
		}

		return $this->$attr;
	}

	public function __set(string $attr, $value) {
		if (!property_exists(__CLASS__, $attr)) {
			throw new \Exception('The property "' . $attr . '" does not exists on the class "' . __CLASS__ . '"');
		}

		$this->$attr = $value;
	}

	public function __toString() {
		return $this->render(false);
	}

}
