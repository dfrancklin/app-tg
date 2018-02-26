<h1><?=$pageTitle?></h1>

<hr>

<?php
	$this->form->action = '/products';
	$this->form->method = 'POST';

	$this->form->hidden([
		'name' => 'id',
		'value' => !is_null($this->product) ? $this->product->id : ''
	]);
	$this->form->input([
		'name' => 'name',
		'hideLabel' => true,
		'required' => true,
		'autofocus' => true,
		'value' => !is_null($this->product) ? $this->product->name : ''
	]);
	$this->form->input([
		'name' => 'price',
		'type' => 'number',
		'hideLabel' => true,
		'required' => true,
		'value' => !is_null($this->product) ? $this->product->price : '',
		'width' => '1/2',
		'additional' => [
			'min' => 0,
			'step' => 0.01
		]
	]);
	$this->form->input([
		'name' => 'quantity',
		'type' => 'number',
		'hideLabel' => true,
		'required' => true,
		'value' => !is_null($this->product) ? $this->product->quantity : '',
		'width' => '1/2',
		'additional' => [
			'min' => 0
		]
	]);
	$this->form->uploader([
		'name' => 'picture',
		'hideLabel' => true,
		'width' => '1/2',
		'accept' => 'images',
		'value' => !is_null($this->product) ? $this->product->picture : ''
	]);
	$this->form->picklist([
		'name' => 'categories',
		'value' => 'id',
		'label' => 'name',
		'source' => '/categories/json',
		'placeholder' => 'Start typing to search categories...',
		'hideLabel' => true,
		'width' => '1/2',
		'values' => !is_null($this->product) ? $this->product->categories : null
	]);
	$this->form->text([
		'name' => 'description',
		'hideLabel' => true,
		'value' => !is_null($this->product) ? $this->product->description : ''
	]);
	$this->form->button([
		'name' => 'save',
		'style' => 'primary',
		'icon' => 'save',
		'type' => 'submit',
	]);

	if (!is_null($this->product)) {
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
		'action' => '/products'
	]);

	$this->form->render();

	if ($this->product) {
		$product = $this->product;

		$modal = new \PHC\Components\Modal;
		$modal->name = 'confirm-modal';
		$modal->title = 'Are you sure?';
		$modal->body = '<p>Are you sure that you want to delete this item permanently?</p>';
		$modal->actions = [
			(function () use ($product) {
				$delete = new \PHC\Components\Form\Button;

				$delete->name = 'Delete';
				$delete->type = 'link';
				$delete->icon = 'delete';
				$delete->style = 'danger';
				$delete->additional = '/products/delete/' . $product->id;

				return $delete;
			})()
		];
		$modal->render();
	}
?>
