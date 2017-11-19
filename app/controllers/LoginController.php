<?php

namespace App\Controllers;

use FW\Core\Router;
use FW\Core\FlashMessages;

use FW\View\IViewFactory;

use FW\Security\ISecurityService;
use FW\Security\IAuthentication;
use FW\Security\UserProfile;

use App\Interfaces\Services\IEmployeesService;

/**
 * @Controller
 */
class LoginController implements IAuthentication
{

	private $service;

	private $security;

	private $factory;

	public function __construct(
		IEmployeesService $service,
		ISecurityService $security,
		IViewFactory $factory
	)
	{
		$this->service = $service;
		$this->security = $security;
		$this->factory = $factory;
		$this->message = FlashMessages::getInstance();
	}

	/**
	 * @RequestMap /login
	 */
	public function login($returnsTo = '')
	{
		$view = $this->factory::create();

		$view->pageTitle = 'Login';
		$view->styles = ['/resources/css/login.css'];
		$view->returnsTo = $returnsTo;
		$view->form = new \PHC\Components\FormComponent;

		return $view->render('login/form');
	}

	/**
	 * @RequestMap /authenticate
	 * @RequestMethod POST
	 */
	public function authenticate()
	{
		try {
			$user = $this->service->authenticate($_POST['email'], $_POST['password']);

			if (!$user) {
				$this->message->warning('User does not exists or invalid password');
			} else {
				$user = new UserProfile($user->email, $user->name, $user->roles);
				$remember = !empty($_POST['remember-me']) && $_POST['remember-me'] === 'true';

				$this->security->authenticate($user, $remember);

				if ($this->security->isAuthenticated()) {
					$this->message->success('You are now logged in!');
				}
			}
		} catch(\Throwable $e) {
			$this->message->error('A problem occurred during authentication: ' . $e->getMessage(), 'Authentication error!');
		} finally {
			$this->redirect();
		}
	}

	/**
	 * @RequestMap /forbidden
	 */
	public function forbidden($route)
	{
		return $this->factory::create()->render('forbidden');
	}

	/**
	 * @RequestMap /logout
	 */
	public function logout()
	{
		$this->security->logout();
		$this->message->info('You are now logged out!');

		Router::redirect('/');
	}

	private function redirect()
	{
		if (isset($_POST['returns-to']) && !empty(trim($_POST['returns-to']))) {
			Router::redirect($_POST['returns-to']);
		} else {
			Router::redirect('/dashboard');
		}
	}

}
