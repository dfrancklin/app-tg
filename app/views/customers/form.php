<h1><?php echo $this->lang($pageTitle); ?></h1>

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
		'title' => $this->lang('name'),
		'hideLabel' => true,
		'required' => true,
		'autofocus' => true,
		'value' => !is_null($this->customer) ? $this->customer->name : ''
	]);
	$this->form->input([
		'name' => 'email',
		'type' => 'email',
		'title' => $this->lang('email'),
		'hideLabel' => true,
		'value' => !is_null($this->customer) ? $this->customer->email : '',
		'width' => '1/2'
	]);
	$this->form->input([
		'name' => 'phone',
		'type' => 'tel',
		'title' => $this->lang('phone'),
		'hideLabel' => true,
		'required' => true,
		'value' => !is_null($this->customer) ? $this->customer->phone : '',
		'width' => '1/2',
		'help' => $this->lang('phone-help'),
		'additional' => [
			'pattern' => '\([0-9]{3}\) [0-9]{7}'
		]
	]);

	$this->form->button([
		'name' => 'save',
		'title' => $this->lang('save'),
		'style' => 'primary',
		'icon' => 'save',
		'type' => 'submit',
	]);

	if (!is_null($this->customer)) {
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
		'action' => '/customers'
	]);

	$this->form->render();

	if ($this->customer) {
		$customer = $this->customer;

		$modal = new \PHC\Components\Modal;
		$modal->name = 'confirm-modal';
		$modal->title = $this->lang('confirm-modal-title');
		$modal->body = $this->lang('confirm-modal-message');
		$modal->closeButtonLabel = $this->lang('close');
		$modal->actions = [
			(function () use ($customer) {
				$delete = new \PHC\Components\Form\Button;

				$delete->name = 'delete';
				$delete->type = 'link';
				$delete->title = $this->lang('delete');
				$delete->icon = 'delete';
				$delete->style = 'danger';
				$delete->action = '/customers/delete/' . $customer->id;

				return $delete;
			})()
		];
		$modal->render();
	}
?>
