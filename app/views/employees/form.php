<h1><?=$pageTitle?></h1>

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
		'hideLabel' => true,
		'required' => true,
		'value' => !is_null($this->employee) ? $this->employee->name : ''
	]);
	$this->form->input([
		'name' => 'email',
		'type' => 'email',
		'hideLabel' => true,
		'required' => true,
		'value' => !is_null($this->employee) ? $this->employee->email : ''
	]);
	$this->form->input([
		'name' => 'admission-date',
		'title' => 'Admission Date',
		'type' => 'datetime-local',
		'hideLabel' => true,
		'required' => true,
		'readOnly' => true,
		'value' => !is_null($this->employee) ? (!empty($this->employee->admissionDate) ? $this->employee->admissionDate->format('Y-m-d\TH:i:s') : date('Y-m-d\TH:i:s')) : date('Y-m-d\TH:i:s'),
		'width' => '1/2'
	]);
	$this->form->select([
		'name' => 'supervisor',
		'selected' => !is_null($this->employee) ? (!empty($this->employee->supervisor) ? $this->employee->supervisor->id : '') : '',
		'options' => $supervisors,
		'hideLabel' => true,
		'width' => '1/2',
	]);

	if (!is_null($this->employee) && !is_null($this->employee->id)) {
		$this->form->input([
			'name' => 'password',
			'type' => 'password',
			'hideLabel' => true,
			'width' => '1/3',
		]);
	}

	$this->form->input([
		'name' => 'new-password',
		'title' => (!is_null($this->employee) ? 'New ' : '') . 'Password',
		'type' => 'password',
		'hideLabel' => true,
		'required' => is_null($this->employee),
		'width' => !is_null($this->employee) ? '1/3' : '1/2',
	]);
	$this->form->input([
		'name' => 'confirm-password',
		'title' => 'Confirm Password',
		'type' => 'password',
		'hideLabel' => true,
		'required' => is_null($this->employee),
		'width' => !is_null($this->employee) ? '1/3' : '1/2',
	]);
	$this->form->picklist([
		'name' => 'roles',
		'value' => 'id',
		'label' => 'name',
		'source' => '/roles/json',
		'placeholder' => 'Start typing to search roles...',
		'hideLabel' => true,
		'values' => !is_null($this->employee) ? $this->employee->roles : null
	]);
	$this->form->button([
		'name' => 'save',
		'style' => 'primary',
		'icon' => 'add_circle',
		'type' => 'submit',
	]);

	if (!is_null($this->employee) && !is_null($this->employee->id)) {
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
		'action' => '/employees'
	]);

	$this->form->render();
?>

<?php if (!is_null($this->employee)) : ?>
	<div class="modal fade" id="confirm-modal" tabindex="-1" role="dialog" aria-labelledby="confirm-modal-label" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="confirm-modal-label">Are you sure?</h5>

					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>

				<div class="modal-body">
					<p>Are you sure that you want to delete this item permanently?</p>
				</div>

				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>

					<form method="POST" id="confirm-form" action="/employees/delete/<?=$this->employee->id?>">
						<button type="submit" class="btn btn-danger">
							Delete <span class="material-icons">delete</span>
						</button>
					</form>
				</div>
			</div>
		</div>
	</div>
<?php endif; ?>
