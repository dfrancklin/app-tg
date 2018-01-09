	<?php
		if ($this->messages->hasMessages()) {
			?>
				<div id="messages-wrapper">
					<div id="messages-container">
						<?php $this->messages->display(); ?>
					</div>
				</div>
			<?php
		}
	?>

	<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js"></script>

	<script src="/public/js/detabinator.js"></script>
	<script src="/public/js/Menu.js"></script>
	<script src="/public/js/UploaderComponent.js"></script>
	<script src="/public/js/PicklistComponent.js"></script>
	<script src="/public/js/app.js"></script>

	<?php
		if(isset($scripts)) {
			foreach ($scripts as $script) {
				?><script src="<?=$script?>"></script><?php
			}
		}
	?>
</body>
</html>
