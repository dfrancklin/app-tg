<h1><?php echo $this->lang($pageTitle); ?></h1>

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
		'title' => $this->lang('name'),
		'hideLabel' => true,
		'required' => true,
		'autofocus' => true,
		'value' => !is_null($this->product) ? $this->product->name : ''
	]);
	$this->form->input([
		'name' => 'price',
		'type' => 'number',
		'title' => $this->lang('price'),
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
		'title' => $this->lang('quantity'),
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
		'title' => $this->lang('picture'),
		'fieldLabel' => $this->lang('picture-field', 'picture'),
		'buttonLabel' => $this->lang('picture-button'),
		'title' => $this->lang('picture'),
		'hideLabel' => true,
		'width' => '1/2',
		'accept' => 'images',
		'value' => !is_null($this->product) ? $this->product->picture : ''
	]);
	$this->form->picklist([
		'name' => 'categories',
		'title' => $this->lang('categories'),
		'actionLabel' => $this->lang('action'),
		'removeLabel' => $this->lang('remove'),
		'value' => 'id',
		'label' => 'name',
		'source' => '/categories/json',
		'placeholder' => $this->lang('picklist-placeholder', 'categories'),
		'hideLabel' => true,
		'width' => '1/2',
		'values' => !is_null($this->product) ? $this->product->categories : null
	]);
	$this->form->text([
		'name' => 'description',
		'title' => $this->lang('description'),
		'hideLabel' => true,
		'value' => !is_null($this->product) ? $this->product->description : ''
	]);
	$this->form->button([
		'name' => 'save',
		'title' => $this->lang('save'),
		'style' => 'primary',
		'icon' => 'save',
		'type' => 'submit',
	]);

	if (!is_null($this->product)) {
		$this->form->button([
			'name' => 'delete',
			'title' => $this->lang('delete'),
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
		'title' => $this->lang('cancel'),
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
		$modal->title = $this->lang('confirm-modal-title');
		$modal->body = $this->lang('confirm-modal-message');
		$modal->closeButtonLabel = $this->lang('close');
		$modal->actions = [
			(function () use ($product) {
				$delete = new \PHC\Components\Form\Button;

				$delete->name = 'delete';
				$delete->type = 'link';
				$delete->title = $this->lang('delete');
				$delete->icon = 'delete';
				$delete->style = 'danger';
				$delete->additional = '/products/delete/' . $product->id;

				return $delete;
			})()
		];
		$modal->render();
	}
?>
