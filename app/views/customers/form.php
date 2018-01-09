<h1><?=$pageTitle?></h1>

<hr>

<?php
	$this->form->action = '/customers';
	$this->form->method = 'POST';

	$this->form->hidden([
		'name' => 'id',
		'value' => !is_null($this->customer) ? $this->customer->id : ''
	]);
	$this->form->input([
		'name' => 'name',
		'hideLabel' => true,
		'required' => true,
		'autofocus' => true,
		'value' => !is_null($this->customer) ? $this->customer->name : ''
	]);
	$this->form->input([
		'name' => 'email',
		'type' => 'email',
		'hideLabel' => true,
		'value' => !is_null($this->customer) ? $this->customer->email : '',
		'width' => '1/2'
	]);
	$this->form->input([
		'name' => 'phone',
		'type' => 'tel',
		'hideLabel' => true,
		'required' => true,
		'value' => !is_null($this->customer) ? $this->customer->phone : '',
		'width' => '1/2',
		'help' => 'In the form (ddd) 9xxxx-xxxx or (ddd) xxxx-xxxx',
		'additional' => [
			'pattern' => '\([0-9]{3}\) 9?[0-9]{4}-[0-9]{4}'
		]
	]);

	$this->form->button([
		'name' => 'save',
		'style' => 'primary',
		'icon' => 'save',
		'type' => 'submit',
	]);

	if (!is_null($this->customer)) {
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
		'action' => '/customers'
	]);

	$this->form->render();

	if ($this->customer) {
		$customer = $this->customer;

		$modal = new \PHC\Components\Modal;
		$modal->name = 'confirm-modal';
		$modal->title = 'Are you sure?';
		$modal->body = '<p>Are you sure that you want to delete this item permanently?</p>';
		$modal->actions = [
			(function () use ($customer) {
				$delete = new \PHC\Components\Form\Button;

				$delete->name = 'Delete';
				$delete->type = 'link';
				$delete->icon = 'delete';
				$delete->style = 'danger';
				$delete->action = '/customers/delete/' . $customer->id;

				return $delete;
			})()
		];
		$modal->render();
	}
?>
