<h1><?php echo $this->lang($pageTitle); ?></h1>

<hr>

<?php
	$this->form->action = '/employees';
	$this->form->method = 'POST';

	$this->form->hidden([
		'name' => 'id',
		'value' => !is_null($this->employee) ? $this->employee->id : ''
	]);
	$this->form->input([
		'name' => 'name',
		'title' => $this->lang('name'),
		'hideLabel' => true,
		'required' => true,
		'autofocus' => true,
		'value' => !is_null($this->employee) ? $this->employee->name : ''
	]);
	$this->form->input([
		'name' => 'email',
		'type' => 'email',
		'title' => $this->lang('email'),
		'hideLabel' => true,
		'required' => true,
		'value' => !is_null($this->employee) ? $this->employee->email : ''
	]);
	$this->form->input([
		'name' => 'admission-date',
		'title' => $this->lang('admission-date'),
		'type' => 'date',
		'hideLabel' => true,
		'required' => true,
		// 'readOnly' => true,
		'value' => !is_null($this->employee) ? (!empty($this->employee->admissionDate) ? $this->employee->admissionDate->format('Y-m-d') : date('Y-m-d')) : date('Y-m-d'),
		'width' => '1/2'
	]);
	$this->form->select([
		'name' => 'supervisor',
		'title' => $this->lang('supervisor'),
		'selected' => !is_null($this->employee) ? (!empty($this->employee->supervisor) ? $this->employee->supervisor->id : '') : '',
		'options' => $this->supervisors,
		'hideLabel' => true,
		'width' => '1/2',
	]);

	if (!is_null($this->employee) && !is_null($this->employee->id)) {
		$this->form->input([
			'name' => 'password',
			'type' => 'password',
			'title' => $this->lang('password'),
			'hideLabel' => true,
			'width' => '1/3',
		]);
	}

	$this->form->input([
		'name' => 'new-password',
		'title' => $this->lang(
			(
				!is_null($this->employee) && !is_null($this->employee->id) ?
					'new-' :
					''
			) . 'password'
		),
		'type' => 'password',
		'hideLabel' => true,
		'required' => is_null($this->employee) || is_null($this->employee->id),
		'width' => !is_null($this->employee) && !is_null($this->employee->id) ? '1/3' : '1/2',
	]);
	$this->form->input([
		'name' => 'confirm-password',
		'title' => $this->lang('confirm-password'),
		'type' => 'password',
		'hideLabel' => true,
		'required' => is_null($this->employee) || is_null($this->employee->id),
		'width' => !is_null($this->employee) && !is_null($this->employee->id) ? '1/3' : '1/2',
	]);
	$this->form->picklist([
		'name' => 'roles',
		'title' => $this->lang('roles'),
		'actionLabel' => $this->lang('action'),
		'removeLabel' => $this->lang('remove'),
		'value' => 'id',
		'label' => 'name',
		'source' => '/roles/json',
		'placeholder' => $this->lang('picklist-placeholder', 'roles'),
		'hideLabel' => true,
		'values' => !is_null($this->employee) ? $this->employee->roles : null
	]);
	$this->form->button([
		'name' => 'save',
		'title' => $this->lang('save'),
		'style' => 'primary',
		'icon' => 'save',
		'type' => 'submit',
	]);

	if (!is_null($this->employee) && !is_null($this->employee->id)) {
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
		'action' => '/employees'
	]);

	$this->form->render();

	if ($this->employee) {
		$employee = $this->employee;

		$modal = new \PHC\Components\Modal;
		$modal->name = 'confirm-modal';
		$modal->title = $this->lang('confirm-modal-title');
		$modal->body = $this->lang('confirm-modal-message');
		$modal->closeButtonLabel = $this->lang('close');
		$modal->actions = [
			(function () use ($employee) {
				$delete = new \PHC\Components\Form\Button;

				$delete->name = 'delete';
				$delete->title = $this->lang('delete');
				$delete->type = 'link';
				$delete->icon = 'delete';
				$delete->style = 'danger';
				$delete->action = '/employees/delete/' . $employee->id;

				return $delete;
			})()
		];
		$modal->render();
	}
?>
