<h1><?=$pageTitle?></h1>

<hr>

<?php
	$this->form->action = '/orders';
	$this->form->method = 'POST';
	$this->form->id = 'form-order';

	$this->form->hidden([
		'name' => 'id',
		'value' => ($this->order ? $this->order->id : '')
	]);

	$this->form->select([
		'name' => 'customer',
		'selected' => (
			!is_null($this->order) ?
			(
				!empty($this->order->customer) ?
				$this->order->customer->id :
				''
			) :
			''
		),
		'options' => $this->customers,
		'hideLabel' => true,
		'required' => true,
		'autofocus' => true,
	]);

	$this->form->products([
		'name' => 'products',
		'source' => '/products/json',
		'placeholder' => 'Start typing to search products...',
		'hideLabel' => true,
		'values' => !is_null($this->order) ? $this->order->items : null
	]);

	if (!is_null($this->order)) {
		$this->form->button([
			'name' => 'finish',
			'type' => 'link',
			'style' => 'primary',
			'icon' => 'credit_card',
			'additional' => [
				'data-toggle' => 'modal',
				'data-target' => '#finish-modal'
			]
		]);
	}

	$this->form->button([
		'name' => 'save',
		'style' => $this->order ? 'secondary' : 'primary',
		'icon' => 'save',
		'type' => 'submit',
	]);

	if (!is_null($this->order)) {
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
		'action' => '/orders'
	]);

	$this->form->render();

	if ($this->order) {
		$order = $this->order;

		$confirm = new \PHC\Components\Modal;
		$confirm->name = 'confirm-modal';
		$confirm->title = 'Are you sure?';
		$confirm->body = '<p>Are you sure that you want to delete this item permanently?</p>';
		$confirm->actions = [
			(function () use ($order) {
				$delete = new \PHC\Components\Form\Button;

				$delete->name = 'Delete';
				$delete->type = 'link';
				$delete->icon = 'delete';
				$delete->style = 'danger';
				$delete->action = '/orders/delete/' . $order->id;

				return $delete;
			})()
		];
		$confirm->render();

		$finish = new \PHC\Components\Modal;
		$finish->name = 'finish-modal';
		$finish->title = 'Are you sure?';
		$finish->body = '<p>Are you sure that you want to finish this order?<br>Make sure that all information was saved.</p>';
		$finish->actions = [
			(function () use ($order) {
				$finish = new \PHC\Components\Form\Button;

				$finish->name = 'Finish';
				$finish->type = 'link';
				$finish->icon = 'credit_card';
				$finish->style = 'primary';
				// $finish->action = '/orders/finish/' . $order->id;
				$finish->additional = [
					'onclick' => '(function(e){
						e.preventDefault();
						const $form = $(\'#form-order\');

						$form.attr(\'action\', \'/orders/finish/' . $order->id . '\');
						$form.submit();
					})(event)'
				];

				return $finish;
			})()
		];
		$finish->render();
	}
?>
