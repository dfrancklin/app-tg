<?php

namespace PHC\Components;

use PHC\Interfaces\IComponent;

class Modal implements IComponent
{

	const TEMPLATES = [
		'modal' => '<div class="modal fade" id="%s" tabindex="-1" role="dialog" aria-labelledby="%s-label" aria-hidden="true"><div class="modal-dialog" role="document">%s</div></div>',
		'content' => '<div class="modal-content">%s%s%s</div>',
		'header' => '<div class="modal-header"><h5 class="modal-title" id="%s-label">%s</h5>%s</div>',
		'body' => '<div class="modal-body">%s</div>',
		'footer' => '<div class="modal-footer">%s</div>',
		'close-icon' => '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>',
		'close-button' => '<button type="button" class="btn btn-secondary" data-dismiss="modal">%s</button>',
	];

	private $name;

	private $title;

	private $body;

	private $actions;

	private $closeIcon;

	private $closeButton;

	private $closeButtonLabel = 'Close';

	public function __construct()
	{
		$this->actions = [];
		$this->closeIcon = true;
		$this->closeButton = true;
	}

	public function render(bool $print = true)
	{
		$modal = $this->formatModal();

		if ($print) {
			echo $modal;
		} else {
			return $modal;
		}
	}

	private function formatModal()
	{
		if (empty($this->name)) {
			throw new \Exception('The name of the select must be informed');
		}

		$content = $this->formatContent();

		return sprintf(
			self::TEMPLATES['modal'],
			$this->name,
			$this->name,
			$content
		);
	}

	private function formatContent()
	{
		$header = $this->formatHeader();
		$body = $this->formatBody();
		$footer = $this->formatFooter();

		return sprintf(
			self::TEMPLATES['content'],
			$header,
			$body,
			$footer
		);
	}

	private function formatHeader()
	{
		if (empty($this->title)) {
			$this->title = $this->name;
		}

		return sprintf(
			self::TEMPLATES['header'],
			$this->name,
			$this->title,
			$this->closeIcon ? self::TEMPLATES['close-icon'] : ''
		);
	}

	private function formatBody()
	{
		if (empty($this->body)) {
			return '';
		}

		return sprintf(
			self::TEMPLATES['body'],
			$this->body
		);
	}

	private function formatFooter()
	{
		if ($this->closeButton) {
			array_unshift(
				$this->actions,
				sprintf(
					self::TEMPLATES['close-button'],
					$this->closeButtonLabel
				)
			);
		}

		$actions = '';

		if (!empty($this->actions)) {
			foreach ($this->actions as $action) {
				$actions .= $action;
			}
		}

		if (empty($actions)) {
			return '';
		}

		return sprintf(
			self::TEMPLATES['footer'],
			$actions
		);
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
