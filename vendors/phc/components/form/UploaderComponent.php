<?php

namespace PHC\Form;

use PHC\Interfaces\IComponent;

class UploaderComponent implements IComponent {

	const ACCEPTS = [
		// images
		'images' => 'image/*',
		'jpeg' => 'image/jpeg,image/jpg',
		'png' => 'image/png',
		'gif' => 'image/gif',
		'bmp' => 'image/bmp',
		'webp' => 'image/webp',
		'svg' => 'image/svg+xml',
		// audios
		'audios' => 'audio/*',
		'midi' => 'audio/midi',
		'mpeg' => 'audio/mpeg',
		'webm' => 'audio/webm',
		'ogg' => 'audio/ogg',
		'wave' => 'audio/wave,audio/wav,audio/x-wav,audio/x-pn-wav',
		// videos
		'videos' => 'video/*',
		'webm' => 'video/webm',
		'ogg' => 'video/ogg',
	];

	const WIDTHS = [
		'1' => ' col-12',
		'1/2' => ' col-md-6 col-12',
		'1/3' => ' col-md-4 col-12',
		'1/4' => ' col-md-3 col-12',
	];

	const TEMPLATES = [
		'form-group' => '<div class="form-group component__uploader%s">%s%s%s%s</div>',
		'label' => '<label%s for="%s">%s:</label>',
		'previewer' => '<div class="previewer"%s>%s</div>',
		'progress-bar' => '<div class="progress mb-2"><div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div></div>',
		'component' => '<div class="input-group"><label class="custom-file">%s<span class="custom-file-control"></span></label></div>',
		'input' => '<input type="file" name="%s" id="%s" title="%s" class="custom-file-input" accept="%s"%s%s>',
	];

	private $value;

	private $name;

	private $title;

	private $hideLabel;

	private $required;

	private $width;

	private $accept;

	private $additional;

	public function render(bool $print = false) {
		$input = $this->formatFormGroup();

		if ($print) {
			echo $input;
		} else {
			return $input;
		}
	}

	private function formatFormGroup() {
		$label = $this->formatLabel();
		$previewer = $this->formatPreviewer();
		$component = $this->formatComponent();

		if (empty($this->width) || !array_key_exists($this->width, self::WIDTHS)) {
			$this->width = '1';
		}

		return sprintf(self::TEMPLATES['form-group'],
						self::WIDTHS[$this->width],
						$label,
						$previewer,
						self::TEMPLATES['progress-bar'],
						$component);
	}

	private function formatLabel() {
		if (empty($this->title)) {
			$this->title = ucfirst($this->name);
		}

		return sprintf(self::TEMPLATES['label'],
						($this->hideLabel ? ' class="sr-only"' : ''),
						$this->name,
						$this->title);
	}

	private function formatPreviewer() {
		$value = '';
		$display = '';

		if (!empty($this->value)) {
			$display = ' style="display: block;"';
			$value = sprintf('<img src="%s">', $this->value);
		}

		return sprintf(self::TEMPLATES['previewer'], $display, $value);
	}

	private function formatComponent() {
		return sprintf(self::TEMPLATES['component'], $this->formatInput());
	}


	private function formatInput() {
		if (empty($this->name)) {
			throw new \Exception('The name of the input must be informed');
		}

		if (empty($this->title)) {
			$this->title = ucfirst($this->name);
		}

		$accept = '';

		if (array_key_exists($this->accept, self::ACCEPTS)) {
			$accept = self::ACCEPTS[$this->accept];
		}

		$additional = '';

		if (!empty($this->additional) && is_array($this->additional)) {
			foreach ($this->additional as $property => $value) {
				$additional .= sprintf(' %s="%s"', $property, $value);
			}
		}

		return sprintf(self::TEMPLATES['input'],
						$this->name,
						$this->name,
						$this->title,
						$accept,
						($this->required ? ' required' : null),
						$additional);
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
