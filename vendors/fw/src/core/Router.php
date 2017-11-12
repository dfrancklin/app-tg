<?php

namespace FW\Core;

use FW\View\IViewFactory;
use FW\Security\ISecurityService;
use FW\Security\IAuthentication;

class Router
{

	private static $instance;

	private $routes;

	private $validMethods;

	private $dm;

	private $activeRoute;

	protected function __construct()
	{
		$this->routes = [];
		$this->validMethods = ['CONNECT', 'COPY', 'DELETE', 'GET', 'HEAD', 'LOCK', 'OPTIONS', 'PATCH', 'POST', 'PROPFIND', 'PUT', 'TRACE', 'UNLOCK'];
		$this->dm = DependenciesManager::getInstance();
		$this->page404 = Config::getInstance()->get('page-404');
	}

	public static function getInstance() : self
	{
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function register($class)
	{
		$reflection = new \ReflectionClass($class);

		if ($reflection->isAbstract()) {
			throw new \Exception('The class ' . $class . ' cannot be abstract');
		}

		$root = '/';
		$allRequiresAuth = false;
		$roles = [];

		if (preg_match('/@Route\s([^\n]+)/i', $reflection->getDocComment(), $matches)) {
			$root = trim($matches[1]);
		}

		if (strlen($root) && $root[0] !== '/') {
			$root = '/' . $root;
		}

		if (preg_match('/@Authenticate/i', $reflection->getDocComment())) {
			$allRequiresAuth = true;
		}

		if (preg_match("/@Roles\s([^" . PHP_EOL . "]+)/i", $reflection->getDocComment(), $matches)) {
			$roles = trim($matches[1], "[]");
			$roles = preg_split("/(,\s?)/i", $roles);
			$roles = array_filter($roles);
		}

		foreach($reflection->getMethods() as $method) {
			if (!$method->isConstructor() && !$method->isStatic() && $method->isPublic()) {
				$map = $this->resolveMethod($method);

				$route = $root;

				if (strlen($map->path) && $map->path[0] !== '/') {
					$route .= '/';
				}

				$route .= $map->path;
				$pattern = '/^' . preg_replace(['/[\/\/]+/i', '/\//i', '/{(.*?)}/i'], ['/', '\/', '([^\/]+)'], $route) . '\/?$/i';
				$route = preg_replace(['/[\/\/]+/i'], ['/'], $route);

				$parameters = array_map(function($parameter) {
					return $parameter->getName();
				}, $method->getParameters());

				if (array_key_exists($route, $this->routes) && $this->existsRoutesMethods($this->routes[$route], $map->requestMethods)) {
					throw new \Exception('The route "' . $route . '" already exists with the following HTTP Methods "[' . implode(', ', $map->requestMethods) . ']"');
				}

				$this->routes[$route][] = (object) [
					'class' => $class,
					'method' => $method->getName(),
					'parameters' => $parameters,
					'requestMethods' => $map->requestMethods,
					'pattern' => $pattern,
					'requiresAuthentication' => $allRequiresAuth ? true : $map->requiresAuthentication,
					'roles' => count($roles) ? $roles : $map->roles,
				];
			}
		}
	}

	private function resolveMethod($method)
	{
		$path = '';
		$methods = ['GET'];
		$requiresAuthentication = false;
		$roles = [];

// 		$doc = preg_replace("/[ \t]*(?:\/\*\*|\*\/|\*)?[ ]?(.*)?/i", "$1", $method->getDocComment());

		if (preg_match("/@RequestMap\s([^" . PHP_EOL . "]+)/i", $method->getDocComment(), $matches))
		{
			$path = trim($matches[1]);
		}

		if (preg_match("/@RequestMethod\s([^" . PHP_EOL . "]+)/i", $method->getDocComment(), $matches))
		{
			$methods = trim($matches[1], "[]");
			$methods = preg_split("/(,\s?)/i", $methods);
			$methods = array_filter($methods);

			if (in_array('ALL', $methods)) {
				$methods = $this->validMethods;
			} else {
				try {
					$this->validateMethods($methods);
				} catch (\Exception $e) {
					throw new \Exception($e->getMessage() . ' on the method "' . $method->getName() . '"
						of the controller "' . $method->getDeclaringClass()->getName() . '"');
				}
			}
		}

		if (preg_match('/@Authenticate/i', $method->getDocComment())) {
			$requiresAuthentication = true;
		}

		if (preg_match("/@Roles\s([^" . PHP_EOL . "]+)/i", $method->getDocComment(), $matches)) {
			$roles = trim($matches[1], "[]");
			$roles = preg_split("/(,\s?)/i", $roles);
			$roles = array_filter($roles);
		}

		return (object) [
			'path' => $path,
			'requestMethods' => $methods,
			'requiresAuthentication' => $requiresAuthentication,
			'roles' => $roles,
		];
	}

	private function validateMethods(array $methods)
	{
		foreach ($methods as $method) {
			if (!in_array($method, $this->validMethods)) {
				throw new \Exception('Invalid HTTP Method "' . $method . '"');
			}
		}
	}

	private function existsRoutesMethods($routes, $requestMethods)
	{
		foreach($routes as $route) {
			foreach ($requestMethods as $method) {
				if (in_array($method, $route->requestMethods)) {
					return true;
				}
			}
		}

		return false;
	}

	public function handle($route, $requestMethod)
	{
		$map = $this->findRoute($route, $requestMethod);

		if (!$map) {
			echo $this->notFoundHandler($route, $requestMethod);
			return;
		}

		$security = $this->dm->resolve(ISecurityService::class);

		if ($map->requiresAuthentication && !$security->isAuthenticated()) {
			$login = $this->dm->resolve(IAuthentication::class);

			if ($login) {
				echo $login->login($route);
			}

			return;
		}

		if (count($map->roles) && !$security->hasAnyRoles($map->roles)) {
			$login = $this->dm->resolve(IAuthentication::class);

			if ($login) {
				echo $login->forbidden($route);
			}

			return;
		}

		if (!in_array($requestMethod, $map->requestMethods)) {
			throw new \Exception('HTTP Method "' . $requestMethod . '" not allowed on route "' . $route . '"');
		}

		$controller = $this->dm->resolve($map->class);
		preg_match($map->pattern, $route, $matches);
		array_shift($matches);

		$this->activeRoute = $route;

		echo $controller->{$map->method}(...$matches);
	}

	private function findRoute($address, $requestMethod)
	{
		$routes = [];

		foreach ($this->routes as $items) {
			foreach ($items as $item) {
				if (preg_match($item->pattern, $address)) {
					$routes[] = $item;
				}
			}
		}

		if (empty($routes)) {
			return;
		}

		foreach ($routes as $route) {
			if (in_array($requestMethod, $route->requestMethods)) {
				return $route;
			}
		}

		throw new \Exception('HTTP Method "' . $requestMethod . '" not allowed on route "' . $address . '"');
	}

	private function notFoundHandler($route, $requestMethod)
	{
		$factory = $this->dm->resolve(IViewFactory::class);
		$view = $factory::create();

		$view->pageTitle = '404 - Not Found';
		$view->route = $route;

		return $view->render($this->page404);
	}

	public static function redirect($route)
	{
		header('location: ' . $route);
		exit;
	}

	public function getActiveRoute()
	{
		return $this->activeRoute;
	}

}
