<h1><?=$pageTitle?></h1>

<hr>

<dl class="row">
	<dt class="col-lg-2 col-md-2 col-sm-3 col-4">ID:</dt>
	<dd class="col-lg-10 col-md-10 col-sm-9 col-8"><?php echo $this->product->id; ?></dd>
	<dt class="col-lg-2 col-md-2 col-sm-3 col-4">Name:</dt>
	<dd class="col-lg-10 col-md-10 col-sm-9 col-8"><?php echo $this->product->name; ?></dd>
	<dt class="col-lg-2 col-md-2 col-sm-3 col-4">Price:</dt>
	<dd class="col-lg-10 col-md-10 col-sm-9 col-8"><?php echo $this->product->price; ?></dd>
	<dt class="col-lg-2 col-md-2 col-sm-3 col-4">Quantity:</dt>
	<dd class="col-lg-10 col-md-10 col-sm-9 col-8"><?php echo $this->product->quantity; ?></dd>
	<dt class="col-lg-2 col-md-2 col-sm-3 col-4">Categories:</dt>
	<dd class="col-lg-10 col-md-10 col-sm-9 col-8">
		<?php
			echo (function($categories) {
				$names = array_map(function($item) {
					return $item->name;
				}, $categories);

				return implode(', ', $names);
			})($this->product->categories);
		?>
	</dd>
</dl>

<p class="clearfix">
	<?php
		if ($this->product->picture) {
			?><img src="<?=$this->product->picture?>" class="img-fluid rounded float-left mr-3 mb-3"><?php
		}

		echo $this->product->description;
	?>
</p>

<div class="text-right">
	<?php
		$back = new \PHC\Components\Form\Button;

		$back->name = 'back';
		$back->type = 'link';
		$back->icon = 'arrow_back';
		$back->additional = ['onclick' => '(function(e) { e.preventDefault(); window.history.back(); })(event)'];

		$back->render();
	?>
</div>
