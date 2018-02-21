<?php

@set_time_limit(0);

loadClasses();

$quantity = 1000;
$page = $_GET['page'] ?? 1;
$initial = ($page - 1) * $quantity;

$em = getEM();
$total = getTotal($em);
$id = ++$initial;

if ($id > $total) {
	die('Finished<br><a href="' . $_SERVER['PHP_SELF'] . '">Again</a>');
}

$list = getItems($em, $page, $quantity);

$file = fopen('Orders.sql', 'a');

foreach ($list as $item) {
	$insert = formatItem($id++, $item);
	fwrite($file, $insert);
	fflush($file);
}

fclose($file);

header('Location: ' . $_SERVER['PHP_SELF'] . '?page=' . ++$page);

function loadClasses()
{
	include __DIR__ . '/../vendors/orm/load.php';
	include __DIR__ . '/../app/models/Customer.php';
	include __DIR__ . '/../app/models/Role.php';
	include __DIR__ . '/../app/models/Employee.php';
	include __DIR__ . '/../app/models/Order.php';
	include __DIR__ . '/../app/models/Category.php';
	include __DIR__ . '/../app/models/Product.php';
	include __DIR__ . '/../app/models/ItemOrder.php';
}

function getEM()
{
	$orm = \ORM\Orm::getInstance();

	$orm->setConnectionsFile(__DIR__ . '/../app/connections.php');
	$orm->setConnection('sqlite');

	return $orm->createEntityManager();
}

function getTotal($em) : int
{
	$query = $em->createQuery();
	$query->from(\App\Models\Order::class, 'o');
	$count = $query->count('o.id', 'total')->one();

	return (int) $count['total'] ?? 0;
}

function getItems($em, $page, $quantity) : array
{
	$query = $em->createQuery();
	$query->from(\App\Models\Order::class, 'o');
	$query->orderBy('o.date');
	$list = $query->page($page, $quantity);

	return $query->list();
}

function formatItem($id, $item) : string
{
	$template = "insert into orders (id, date, finished, customer_id, salesman_id) values (%d, '%s', %d, %d, %d);\n";

	return @sprintf(
		$template,
		$id,
		$item->date->format('Y-m-d'),
		$item->finished,
		$item->customer->id,
		$item->salesman->id
	);
}
