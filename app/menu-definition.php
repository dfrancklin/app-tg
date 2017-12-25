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
			'items' => [
				(object) [
					'title' => 'Customers',
					'icon' => 'person',
					'href' => '/customers',
					'activeRoute' => ['/customers/*'],
					'roles' => ['ADMIN', 'SALES'],
				],
				(object) [
					'title' => 'Orders',
					'icon' => 'payment',
					'href' => '/orders',
					'activeRoute' => ['/orders/*'],
					'roles' => ['ADMIN', 'SALES'],
				],
			],
		],
		(object) [
			'title' => 'Administration',
			'icon' => 'build',
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
					'roles' => ['ADMIN'],
				],
			],
		],
	],
];
