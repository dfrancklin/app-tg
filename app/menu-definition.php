<?php

$menuDefinition = (object) [
	'groups' => [
		(object) [
			'items' => [
				(object) [
					'title' => $this->lang('dashboard'),
					'icon' => 'dashboard',
					'href' => '/dashboard',
					'activeRoute' => '/',
				],
			],
		],
		(object) [
			'title' => $this->lang('sales'),
			'icon' => 'attach_money',
			'roles' => ['ADMIN', 'SALES'],
			'items' => [
				(object) [
					'title' => $this->lang('customers'),
					'icon' => 'person',
					'href' => '/customers',
					'activeRoute' => ['/customers/*'],
				],
				(object) [
					'title' => $this->lang('orders'),
					'icon' => 'payment',
					'href' => '/orders',
					'activeRoute' => ['/orders/*'],
				],
			],
		],
		(object) [
			'title' => $this->lang('administration'),
			'icon' => 'build',
			'roles' => ['ADMIN', 'STOCK'],
			'items' => [
				(object) [
					'title' => $this->lang('products'),
					'icon' => 'settings',
					'href' => '/products',
					'activeRoute' => ['/products/*'],
					'roles' => ['ADMIN', 'STOCK'],
				],
				(object) [
					'title' => $this->lang('categories'),
					'icon' => 'local_offer',
					'href' => '/categories',
					'activeRoute' => ['/categories/*'],
					'roles' => ['ADMIN', 'STOCK'],
				],
				(object) [
					'title' => $this->lang('employees'),
					'icon' => 'face',
					'href' => '/employees',
					'activeRoute' => ['/employees/*'],
					'roles' => ['ADMIN'],
				],
			],
		],
	],
];
