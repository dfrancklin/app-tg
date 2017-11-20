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
		'icon' => 'add_circle',
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
?>

<?php if (!is_null($this->customer)) : ?>
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

					<form method="POST" id="confirm-form" action="/customers/delete/<?=$this->customer->id?>">
						<button type="submit" class="btn btn-danger">
							Delete <span class="material-icons">delete</span>
						</button>
					</form>
				</div>
			</div>
		</div>
	</div>
<?php endif; ?>
