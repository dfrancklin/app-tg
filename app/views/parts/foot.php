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

	<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>

	<script src="/vendors/phc/resources/js/detabinator.js"></script>
	<script src="/vendors/phc/resources/js/Menu.js"></script>
	<script src="/vendors/phc/resources/js/UploaderComponent.js"></script>
	<script src="/resources/js/app.js"></script>

	<?php
		if(isset($scripts)) {
			foreach ($scripts as $script) {
				?><script src="<?=$script?>"></script><?php
			}
		}
	?>
</body>
</html>