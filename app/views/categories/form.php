<h1><?=$pageTitle?></h1>

<hr>

<?php
	$this->form->action = '/categories';
	$this->form->method = 'POST';

	$this->form->hidden([
		'name' => 'id',
		'value' => !is_null($this->category) ? $this->category->id : ''
	]);
	$this->form->input([
		'name' => 'name',
		'hideLabel' => true,
		'required' => true,
		'value' => !is_null($this->category) ? $this->category->name : ''
	]);
	$this->form->button([
		'name' => 'save',
		'style' => 'primary',
		'icon' => 'add_circle',
		'type' => 'submit',
	]);

	if (!is_null($this->category)) {
		$this->form->button([
			'name' => 'delete',
			'style' => 'danger',
			'icon' => 'delete',
			'additional' => [
				'data-toggle' => 'modal',
				'data-target' => '#confirm-modal'
			]
		]);
	}

	$this->form->button([
		'name' => 'cancel',
		'style' => 'warning',
		'icon' => 'cancel',
		'type' => 'link',
		'action' => '/categories'
	]);

	$this->form->render();

	if (!is_null($this->category)) {
		$modal = new \PHC\Components\ModalComponent;

		$modal->name = 'confirm-modal';
		$modal->title = 'Are you sure?';
		$modal->body = '<p>Are you sure that you want to delete this item permanently?</p>';
		$modal->actions = [(function () {
			$delete = new \PHC\Components\Form\ButtonComponent;

			$delete->name = 'Delete';
			$delete->type = 'link';
			$delete->action = '/categories/delete/' . $this->category->id;
			$delete->icon = 'delete';
			$delete->style = 'danger';

			return $delete;
		})()];

		$modal->render();
	}
?>
