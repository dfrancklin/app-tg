<header class="header fixed-top">
	<nav class="header__navbar navbar navbar-expand-lg navbar-dark bg-dark">
		<button class="navbar-toggler js-menu-show mr-3 header__menu-toggler" aria-label="Toggle navigation">
			<span class="material-icons">menu</span>
		</button>

		<a class="navbar-brand mr-auto d-none d-md-block" href="/">TG Book Store</a>
		<a class="navbar-brand mr-auto d-block d-md-none" href="/">TBS</a>

		<div class="dropdown">
			<button class="btn btn-dark dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				<spam class="material-icons">account_circle</spam>
				<span class="d-none d-md-inline-block">
					<?php echo $this->security->getUserProfile()->getName(); ?>
				</span>
			</button>
			<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
				<a class="dropdown-item" href="/profile">Profile</a>
				<div class="dropdown-divider"></div>
				<a class="dropdown-item" href="/logout">Log out</a>
			</div>
		</div>
	</nav>
</header>
