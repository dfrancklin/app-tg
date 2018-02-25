<h1><?=$pageTitle?></h1>

<hr>

<?php
	$this->form->action = '/profile/save';
	$this->form->method = 'POST';

	$this->form->hidden([
		'name' => 'id',
		'value' => $this->employee->id
	]);
	$this->form->hidden([
		'name' => 'admission-date',
		'value' => $this->employee->admissionDate->format('Y-m-d') // !empty($this->employee->admissionDate) ? $this->employee->admissionDate->format('Y-m-d') : date('Y-m-d')
	]);
	$this->form->input([
		'name' => 'name',
		'hideLabel' => true,
		'required' => true,
		'autofocus' => true,
		'value' => $this->employee->name
	]);
	$this->form->input([
		'name' => 'email',
		'type' => 'email',
		'hideLabel' => true,
		'required' => true,
		'value' => $this->employee->email
	]);
	$this->form->input([
		'name' => 'password',
		'type' => 'password',
		'hideLabel' => true,
		'required' => true,
		'width' => '1/3',
	]);

	$this->form->input([
		'name' => 'new-password',
		'title' => 'New Password',
		'type' => 'password',
		'hideLabel' => true,
		'width' => '1/3',
	]);
	$this->form->input([
		'name' => 'confirm-password',
		'title' => 'Confirm Password',
		'type' => 'password',
		'hideLabel' => true,
		'width' => '1/3',
	]);
	$this->form->button([
		'name' => 'save',
		'style' => 'primary',
		'icon' => 'save',
		'type' => 'submit',
	]);
	$this->form->button([
		'name' => 'cancel',
		'style' => 'warning',
		'icon' => 'cancel',
		'type' => 'link',
		'action' => '/',
	]);

	$this->form->render();
?>
