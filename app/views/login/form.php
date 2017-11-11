<div id="form">
	<h1 class="text-center"><?=$pageTitle?></h1>

	<?php
		$this->form->action = '/authenticate';
		$this->form->method = 'POST';

		$this->form
				->hidden([
					'name' => 'returns-to',
					'value' => $returnsTo,
				])
				->input([
					'type' => 'email',
					'name' => 'email',
					'title' => 'E-mail',
					'size' => 'l',
					'hideLabel' => true,
					'required' => true,
					'autofocus' => true,
					'icon' => 'account_circle',
				])
				->input([
					'type' => 'password',
					'name' => 'password',
					'size' => 'l',
					'hideLabel' => true,
					'required' => true,
					'icon' => 'lock',
				])
				->checkbox([
					'name' => 'remember-me',
					'value' => 'true'
				])
				->button([
					'name' => 'submit',
					'title' => 'Login',
					'type' => 'submit',
					'style' => 'dark',
					'icon' => 'keyboard_return',
				])
				->render();
	?>
</div>