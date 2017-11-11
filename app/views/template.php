<?php include $this->views . '/parts/head.php'; ?>

	<?php if ($this->security->isAuthenticated()): ?>

		<?php include $this->views . '/parts/top.php'; ?>

		<?php include $this->views . '/parts/menu.php'; ?>

	<?php endif; ?>

	<?php include $this->views . '/parts/content.php'; ?>

<?php include $this->views . '/parts/foot.php'; ?>