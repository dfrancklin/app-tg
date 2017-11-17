<?php

namespace PHC\Components\Form;

use PHC\Interfaces\IComponent;

class PicklistComponent implements IComponent
{

	const SIZES = [
		's' => ' input-group-sm',
		'n' => '',
		'l' => ' input-group-lg'
	];

	const WIDTHS = [
		'1' => ' col-12',
		'1/2' => ' col-md-6 col-12',
		'1/3' => ' col-md-4 col-12',
		'1/4' => ' col-md-3 col-12',
	];

	const TEMPLATES = [
		'component' => '<div class="component__picklist%s" data-name="%s" data-title="%s" data-value="%s" data-label="%s" data-source="%s">%s%s%s%s</div>',
		'loader' => '<spam class="material-icons loader">sync</spam>',
		'select-list' => '<div class="show-select-list"></div>',
		'selected-list' => '<div class="show-selected-list">%s</div>',
	];

	private $name;

	private $title;

	private $value;

	private $label;

	private $source;

	private $values;

	private $icon;

	private $size;

	private $width;

	private $hideLabel;

	private $placeholder;

	public function render(bool $print = false)
	{
		$component = $this->formatComponent();

		if ($print) {
			echo $component;
		} else {
			return $component;
		}
	}

	private function formatComponent()
	{
		$input = $this->formatInput();
		$loader = self::TEMPLATES['loader'];
		$selectList = self::TEMPLATES['select-list'];
		$selectedList = $this->formatSelectedList();

		if (empty($this->width) || !array_key_exists($this->width, self::WIDTHS)) {
			$this->width = '1';
		}

		if (empty($this->value)) {
			throw new \Exception('The value of the component Picklist must be informed');
		}

		if (empty($this->label)) {
			throw new \Exception('The label of the component Picklist must be informed');
		}

		if (empty($this->source)) {
			throw new \Exception('The source of the component Picklist must be informed');
		}

		return sprintf(
			self::TEMPLATES['component'],
			self::WIDTHS[$this->width],
			$this->name,
			$this->title,
			$this->value,
			$this->label,
			$this->source,
			$input,
			$loader,
			$selectList,
			$selectedList
		);
	}

	private function formatInput()
	{
		if (empty($this->name)) {
			throw new \Exception('The name of the component must be informed');
		}

		if (empty($this->title) && !empty($this->name)) {
			$this->title = ucfirst($this->name);
		}

		$input = new InputComponent(false, true);

		$input->icon = $this->icon;
		$input->size = $this->size;
		$input->title = $this->title;
		$input->hideLabel = $this->hideLabel;
		$input->placeholder = $this->placeholder;

		return $input;
	}

	private function formatSelectedList()
	{
		$table = null;

		if (!empty($this->values)) {
			$table = $this->formatTable();
		}

		return sprintf(self::TEMPLATES['selected-list'], $table);
	}

	private function formatTable()
	{
		$head = '<thead class="thead-inverse">';
		$head .= '<tr>';
		$head .= '<th>#</th>';
		$head .= '<th>' . $this->title . '</th>';
		$head .= '<th>Action</th>';
		$head .= '</tr>';
		$head .= '</thead>';

		$body = '<tbody>';

		$row = '<tr>
			<td class="value">%s%s</td>
			<td class="label">%s</td>
			<td>%s</td>
		</tr>';

		$hidden = new HiddenComponent;
		$hidden->name = $this->name . '[]';

		$button = new ButtonComponent;
		$button->type = 'link';
		$button->title = 'Remove';
		$button->icon = 'delete';
		$button->size = 's';
		$button->style = 'danger';
		$button->iconOnly = true;

		foreach ($this->values as $item) {
			if (is_array($item)) {
				$value = $item[$this->value];
				$label = $item[$this->label];
			} else {
				$value = $item->{$this->value};
				$label = $item->{$this->label};
			}

			$button->additional = ['data-value' => $value];
			$hidden->value = $value;

			$body .= sprintf($row, $hidden, $value, $label, $button);
		}

		$body .= '';
		$body .= '</tbody>';

		$table = '<table class="table table-bordered table-striped table-responsive table-hover">';
		$table .= $head . $body;
		$table .= '</table>';

		return $table;
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
