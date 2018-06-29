<h1><?php echo $this->lang($pageTitle); ?></h1>

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
		'title' => $this->lang('name'),
		'hideLabel' => true,
		'required' => true,
		'autofocus' => true,
		'value' => !is_null($this->category) ? $this->category->name : ''
	]);
	$this->form->button([
		'name' => 'save',
		'title' => $this->lang('save'),
		'style' => 'primary',
		'icon' => 'save',
		'type' => 'submit',
	]);

	if (!is_null($this->category)) {
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
		'action' => '/categories'
	]);

	$this->form->render();


	if ($this->category) {
		$category = $this->category;

		$modal = new \PHC\Components\Modal;
		$modal->name = 'confirm-modal';
		$modal->title = $this->lang('confirm-modal-title');
		$modal->body = $this->lang('confirm-modal-message');
		$modal->closeButtonLabel = $this->lang('close');
		$modal->actions = [
			(function () use ($category) {
				$delete = new \PHC\Components\Form\Button;

				$delete->name = 'delete';
				$delete->type = 'link';
				$delete->title = $this->lang('delete');
				$delete->icon = 'delete';
				$delete->style = 'danger';
				$delete->action = '/categories/delete/' . $category->id;

				return $delete;
			})()
		];
		$modal->render();
	}
?>
