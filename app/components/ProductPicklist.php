<?php

namespace App\Components;

use PHC\Interfaces\IComponent;

use PHC\Components\Form\Hidden;
use PHC\Components\Form\Input;
use PHC\Components\Form\Button;

class ProductPicklist implements IComponent
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
		'component' => '<div class="component__product-picklist%s" data-name="%s" data-title="%s" data-source="%s">%s%s%s%s</div>',
		'loader' => '<spam class="material-icons loader">sync</spam>',
		'select-list' => '<div class="show-select-list"></div>',
		'selected-list' => '<div class="show-selected-list">%s</div>',
	];

	private $name;

	private $title;

	private $source;

	private $values;

	private $icon;

	private $size;

	private $width;

	private $hideLabel;

	private $placeholder;

	public function render(bool $print = true)
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
		$input = $this->formatInputs();
		$loader = self::TEMPLATES['loader'];
		$selectList = self::TEMPLATES['select-list'];
		$selectedList = $this->formatSelectedList();

		if (empty($this->width) || !array_key_exists($this->width, self::WIDTHS)) {
			$this->width = '1';
		}

		if (empty($this->source)) {
			throw new \Exception('The source of the component Picklist must be informed');
		}

		return sprintf(
			self::TEMPLATES['component'],
			self::WIDTHS[$this->width],
			$this->name,
			$this->title,
			$this->source,
			$input,
			$loader,
			$selectList,
			$selectedList
		);
	}

	private function formatInputs()
	{
		$row = '<div class="row">%s</div>';

		if (empty($this->name)) {
			throw new \Exception('The name of the component must be informed');
		}

		if (empty($this->title) && !empty($this->name)) {
			$this->title = ucfirst($this->name);
		}

		$search = new Input(false);

		$search->size = $this->size;
		$search->title = $this->title;
		$search->hideLabel = $this->hideLabel;
		$search->placeholder = $this->placeholder;
		$search->width = '1/2';

		$quantity = new Input(false);

		$quantity->type = 'number';
		$quantity->size = $this->size;
		$quantity->title = 'Quantity';
		$quantity->hideLabel = $this->hideLabel;
		$quantity->width = '1/4';
		$quantity->additional = ['min' => '1'];

		$inputs[] = $search;
		$inputs[] = $quantity;
		$inputs[] = $this->formatButtons();

		return sprintf(
			$row,
			implode($inputs)
		);
	}

	private function formatButtons()
	{
		$row = '<div class="form-group col"><div class="row">%s</div></div>';
		$wrap = '<div class="col-6">%s</div>';

		$add = new Button;

		$add->type = 'link';
		$add->title = 'Add';
		$add->icon = 'add_circle_outline';
		$add->style = 'primary';
		$add->block = true;
		$add->iconOnly = true;

		$cancel = new Button;

		$cancel->type = 'link';
		$cancel->title = 'Cancel';
		$cancel->icon = 'cancel';
		$cancel->style = 'warning';
		$cancel->block = true;
		$cancel->iconOnly = true;
		$cancel->additional = [
			'style' => 'margin-left: 0;'
		];

		$buttons[] = sprintf($wrap, $add);
		$buttons[] = sprintf($wrap, $cancel);

		return sprintf($row, implode($buttons));
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
		$table = '<table class="table table-bordered table-striped table-responsive table-hover">%s%s%s</table>';
		$head = '<thead class="thead-inverse">
			<tr>
				<th style="width: 5%%; text-align: right;">#</th>
				<th>%s</th>
				<th style="width: 10%%; text-align: right;">Quantity</th>
				<th style="width: 10%%; text-align: right;">Price</th>
				<th style="width: 10%%; text-align: right;">Subtotal</th>
				<th style="width: 5%%;">Action</th>
			</tr>
		</thead>';
		$body = '<tbody>%s</tbody>';
		$foot = '<tfoot class="text-white bg-dark">
			<tr class="font-weight-bold text-right">
				<td colspan="4">Total</td>
				<td colspan="2">$ %.2f</td>
			</tr>
		</tfoot>';
		$rowTemplate = '<tr
			data-id="%s"
			data-name="%s"
			data-quantity="%s"
			data-price="%s"
		>
			<td class="id" style="text-align: right;">%s</td>
			<td class="name">%s</td>
			<td class="quantity" style="text-align: right;">%s</td>
			<td class="price" style="text-align: right;">$ %.2f</td>
			<td style="text-align: right;">$ %.2f</td>
			<td>%s</td>
		</tr>';
		$rows = [];

		$total = 0;
		$inputId = new Hidden;
		$inputName = new Hidden;
		$inputQuantity = new Hidden;
		$inputPrice = new Hidden;
		$button = new Button;

		$button->type = 'link';
		$button->title = 'Remove';
		$button->icon = 'delete';
		$button->size = 's';
		$button->style = 'danger';
		$button->iconOnly = true;

		foreach ($this->values as $item) {
			$inputId->name = $this->name . '[' . $item->id . '][id]';
			$inputName->name = $this->name . '[' . $item->id . '][name]';
			$inputQuantity->name = $this->name . '[' . $item->id . '][quantity]';
			$inputPrice->name = $this->name . '[' . $item->id . '][price]';

			$inputId->value = $item->product->id;
			$inputName->value = $item->product->name;
			$inputQuantity->value = $item->quantity;
			$inputPrice->value = $item->price;

			$button->additional = ['data-id' => $item->product->id];

			$subtotal = $item->price * $item->quantity;

			$rows[] = sprintf(
				$rowTemplate,
				$item->product->id,
				$item->product->name,
				$item->quantity,
				$item->price,
				implode('', [$inputId, $inputName, $inputQuantity, $inputPrice, $item->product->id]),
				$item->product->name,
				$item->quantity,
				$item->price,
				$subtotal,
				$button
			);

			$total += $subtotal;
		}

		$head = sprintf($head, $this->title);
		$body = sprintf($body, implode('', $rows));
		$foot = sprintf($foot, $total);
		$table = sprintf($table, $head, $body, $foot);

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
