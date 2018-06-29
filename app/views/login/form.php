<div id="form">
	<h1 class="text-center"><?php echo $this->lang($pageTitle); ?></h1>

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
					'title' => $this->lang('email'),
					'size' => 'l',
					'hideLabel' => true,
					'required' => true,
					'autofocus' => true,
					'icon' => 'account_circle',
				])
				->input([
					'type' => 'password',
					'name' => 'password',
					'title' => $this->lang('password'),
					'size' => 'l',
					'hideLabel' => true,
					'required' => true,
					'icon' => 'lock',
				])
				->checkbox([
					'name' => 'remember-me',
					'title' => $this->lang('remember-me'),
					'value' => 'true'
				])
				->button([
					'name' => 'submit',
					'title' => $this->lang('login'),
					'type' => 'submit',
					'style' => 'dark',
					'icon' => 'keyboard_return',
				])
				->render();
	?>
</div>
