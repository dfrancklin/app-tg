<h1><?php echo $this->lang($pageTitle); ?></h1>

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
		'title' => $this->lang('customer'),
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
		'title' => $this->lang('product'),
		'pictureLabel' => $this->lang('picture'),
		'addLabel' => $this->lang('add'),
		'cancelLabel' => $this->lang('cancel'),
		'removeLabel' => $this->lang('remove'),
		'pictureLabel' => $this->lang('picture'),
		'quantityLabel' => $this->lang('quantity'),
		'priceLabel' => $this->lang('price'),
		'actionLabel' => $this->lang('action'),
		'source' => '/products/json',
		'placeholder' => $this->lang('picklist-placeholder', 'products'),
		'hideLabel' => true,
		'values' => !is_null($this->order) ? $this->order->items : null
	]);

	if (!is_null($this->order)) {
		$this->form->button([
			'name' => 'finish',
			'title' => $this->lang('finish'),
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
		'title' => $this->lang('save'),
		'style' => $this->order ? 'secondary' : 'primary',
		'icon' => 'save',
		'type' => 'submit',
	]);

	if (!is_null($this->order)) {
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
		'action' => '/orders'
	]);

	$this->form->render();

	if ($this->order) {
		$order = $this->order;

		$confirm = new \PHC\Components\Modal;
		$confirm->name = 'confirm-modal';
		$confirm->title = $this->lang('confirm-modal-title');
		$confirm->body = $this->lang('confirm-modal-message');
		$confirm->closeButtonLabel = $this->lang('close');
		$confirm->actions = [
			(function () use ($order) {
				$delete = new \PHC\Components\Form\Button;

				$delete->name = 'delete';
				$delete->type = 'link';
				$delete->title = $this->lang('delete');
				$delete->icon = 'delete';
				$delete->style = 'danger';
				$delete->action = '/orders/delete/' . $order->id;

				return $delete;
			})()
		];
		$confirm->render();

		$finish = new \PHC\Components\Modal;
		$finish->name = 'finish-modal';
		$finish->title = $this->lang('finish-modal-title');
		$finish->body = $this->lang('finish-modal-message');
		$finish->closeButtonLabel = $this->lang('close');
		$finish->actions = [
			(function () use ($order) {
				$finish = new \PHC\Components\Form\Button;

				$finish->name = 'finish';
				$finish->title = $this->lang('finish');
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
