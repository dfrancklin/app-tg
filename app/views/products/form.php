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
	$this->form->text([
		'name' => 'description',
		'hideLabel' => true,
		'width' => '1/2',
		'value' => !is_null($this->product) ? $this->product->description : ''
	]);
	$this->form->button([
		'name' => 'save',
		'style' => 'primary',
		'icon' => 'add_circle',
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
?>

<?php if (!is_null($this->product)) : ?>
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

					<form method="POST" action="/products/delete/<?=$this->product->id?>">
						<button type="submit" class="btn btn-danger">
							Delete <span class="material-icons">delete</span>
						</button>
					</form>
				</div>
			</div>
		</div>
	</div>
<?php endif; ?>
