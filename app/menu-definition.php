<?php

$menuDefinition = (object) [
	'groups' => [
		(object) [
			'items' => [
				(object) [
					'title' => 'Dashboard',
					'icon' => 'dashboard',
					'href' => '/dashboard',
					'activeRoute' => '/',
				],
			],
		],
		(object) [
			'title' => 'Sales',
			'icon' => 'attach_money',
			'roles' => ['ADMIN', 'SALES'],
			'items' => [
				(object) [
					'title' => 'Customers',
					'icon' => 'person',
					'href' => '/customers',
					'activeRoute' => ['/customers/*'],
				],
				(object) [
					'title' => 'Orders',
					'icon' => 'payment',
					'href' => '/orders',
					'activeRoute' => ['/orders/*'],
				],
			],
		],
		(object) [
			'title' => 'Administration',
			'icon' => 'build',
			'roles' => ['ADMIN', 'STOCK'],
			'items' => [
				(object) [
					'title' => 'Products',
					'icon' => 'settings',
					'href' => '/products',
					'activeRoute' => ['/products/*'],
					'roles' => ['ADMIN', 'STOCK'],
				],
				(object) [
					'title' => 'Categories',
					'icon' => 'local_offer',
					'href' => '/categories',
					'activeRoute' => ['/categories/*'],
					'roles' => ['ADMIN', 'STOCK'],
				],
				(object) [
					'title' => 'Employees',
					'icon' => 'face',
					'href' => '/employees',
					'activeRoute' => ['/employees/*'],
					'roles' => ['ADMIN'],
				],
			],
		],
	],
];
