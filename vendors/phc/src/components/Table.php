<?php

namespace PHC\Components;

use PHC\Interfaces\IComponent;

class Table implements IComponent
{

	const TEMPLATES = [
		'table' => '<table class="component__table table table-bordered table-striped table-responsive table-hover"><thead class="thead-inverse">%s</thead><tbody>%s</tbody></table>',
		'row' => '<tr>%s</tr>',
		'head-cell' => '<th>%s</th>',
		'body-cell' => '<td>%s</td>',
	];

	private $pattern = '/\{row->([a-zA-Z0-9_]*)\}/i';

	private $resource;

	private $columns;

	private $actions;

	public function render(bool $print = true)
	{
		$table = $this->formatTable();

		if ($print) {
			echo $table;
		} else {
			return $table;
		}
	}

	private function formatTable()
	{
		$head = $this->formatHead();
		$body = $this->formatBody();

		return sprintf(
			self::TEMPLATES['table'],
			$head,
			$body
		);
	}

	private function formatHead()
	{
		if (empty($this->columns)) {
			throw new \Exception('The columns of the table must be informed');
		}

		$columns = [];

		foreach ($this->columns as $key => $value) {
			$columns[] = sprintf(self::TEMPLATES['head-cell'], $key);
		}

		if (!empty($this->actions)) {
			$columns[] = sprintf(self::TEMPLATES['head-cell'], 'Actions');
		}

		return sprintf(
			self::TEMPLATES['row'],
			implode('', $columns)
		);
	}

	private function formatBody()
	{
		$rows = [];

		if (empty($this->resource)) {
			$span = count($this->columns);

			if (!empty($this->actions)) {
				$span++;
			}

			return '<tr><td colspan="' . $span . '" class="text-center">No lines to be shown</td></tr>';
		}

		foreach ($this->resource as $row) {
			$columns = [];

			foreach ($this->columns as $column) {
				$value = $this->getValue($row, $column);

				$columns[] = sprintf(
					self::TEMPLATES['body-cell'],
					$value
				);
			}

			if (!empty($this->actions)) {
				$actions = $this->formatActions($row);
				$columns[] = sprintf(
					self::TEMPLATES['body-cell'],
					$actions
				);
			}

			$rows[] = sprintf(
				self::TEMPLATES['row'],
				implode('', $columns)
			);
		}

		return implode('', $rows);
	}

	private function formatActions($row)
	{
		$actions = [];

		foreach ($this->actions as $action) {
			preg_match_all($this->pattern, $action, $matches);
			$matches = array_unique($matches[1]);
			$placeholders = [];

			foreach ($matches as $match) {
				$value = $this->getValue($row, $match);
				$placeholders['{row->' . $match . '}'] = $value;
			}

			$actions[] = str_replace(
				array_keys($placeholders),
				array_values($placeholders),
				$action
			);
		}

		return implode('', $actions);
	}

	private function getValue($row, $column)
	{
		if (is_null($row)) {
			return;
		}

		$value = null;

		if (is_callable($column)) {
			$value = $column($row);
		} elseif(is_array($column)) {
			if (isset($column['function'])) {
				$function = $column['function'];
				$args = $column['args'];

				foreach ($args as $key => $arg) {
					if (preg_match($this->pattern, $arg, $matches)) {
						$args[$key] = $this->_getValue($row, $matches[1]);
					}
				}

				$value = $function(...$args);
			} elseif (isset($column['method'])) {
				$method = $column['method'];
				$args = $column['args'];

				foreach ($args as $key => $arg) {
					if (preg_match($this->pattern, $arg, $matches)) {
						$args[$key] = $this->_getValue($row, $matches[1]);
					}
				}

				$value = $row->{$method}(...$args);
			} else {
				$newColumn = array_shift($column);

				if (count($column) === 1) {
					$column = array_shift($column);
				}

				$row = $this->_getValue($row, $newColumn);
				$value = $this->getValue($row, $column);
			}
		} elseif (is_string($column)) {
			$value = $this->_getValue($row, $column);
		}

		return $value;
	}

	private function _getValue($row, $column)
	{
		$value = null;

		if (is_array($row)) {
			$value = $row[$column];
		} else {
			$value = $row->{$column};
		}

		return $value;
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
