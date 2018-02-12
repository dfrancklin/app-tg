<?php

namespace PHC\Components;

use PHC\Interfaces\IComponent;

class Pagination implements IComponent
{

	const TEMPLATES = [
		'pagination' => '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">%s</ul></nav>',
		'item' => '<li class="page-item%s"><a class="page-link" href="%s?page=%s" tabindex="-1">%s</a></li>'
	];

	private $route;

	private $active;

	private $total;

	private $quantity;

	private $showPageNumbers;

	public function __construct()
	{
		$this->quantity = 3;
		$this->showPageNumbers = true;
	}

	public function render(bool $print = true)
	{
		$pagination = $this->formatPagination();

		if ($print) {
			echo $pagination;
		} else {
			return $pagination;
		}
	}

	private function formatPagination()
	{
		if ($this->total < 2) {
			return '';
		}

		$pages = $this->formatPages();

		return sprintf(
			self::TEMPLATES['pagination'],
			$pages
		);
	}

	private function formatPages()
	{
		$first = sprintf(
			self::TEMPLATES['item'],
			$this->active === 1 ? ' disabled' : '',
			$this->route,
			1,
			'First'
		);

		$previous = sprintf(
			self::TEMPLATES['item'],
			$this->active === 1 ? ' disabled' : '',
			$this->route,
			$this->active === 1 ? 1 : $this->active - 1,
			'Previous'
		);

		$next = sprintf(
			self::TEMPLATES['item'],
			$this->active === $this->total ? ' disabled' : '',
			$this->route,
			$this->active === $this->total ? $this->total : $this->active + 1,
			'Next'
		);

		$last = sprintf(
			self::TEMPLATES['item'],
			$this->active === $this->total ? ' disabled' : '',
			$this->route,
			$this->total,
			'Last'
		);

		$pages = [];

		$start = $this->active - $this->quantity;
		$end = $this->active + $this->quantity;

		$start = $start < 1 ? 1 : $start;
		$end = $end > $this->total ? $this->total : $end;

		if ($this->showPageNumbers) {
			foreach (range($start, $end) as $page) {
				$pages[] = sprintf(
					self::TEMPLATES['item'],
					$page === $this->active ? ' active' : '',
					$this->route,
					$page,
					$page
				);
			}
		}

		if ($this->active > 1) {
			array_unshift($pages, $previous);
		}

		if ($this->active - $this->quantity > 1) {
			array_unshift($pages, $first);
		}

		if ($this->active < $this->total) {
			array_push($pages, $next);
		}

		if ($this->active + $this->quantity < $this->total)
		array_push($pages, $last);

		return implode('', $pages);
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
