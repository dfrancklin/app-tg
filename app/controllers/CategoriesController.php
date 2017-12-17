<?php

namespace App\Controllers;

use FW\Core\Router;
use FW\Core\FlashMessages;

use FW\View\IViewFactory;

use PHC\Components\Form;

use App\Models\Category;

use App\Interfaces\Services\ICategoriesService;

/**
 * @Controller
 * @Route /categories
 * @Authenticate
 */
class CategoriesController
{

	private $factory;

	private $service;

	private $message;

	public function __construct(IViewFactory $factory, ICategoriesService $service)
	{
		$this->factory = $factory;
		$this->service = $service;
		$this->message = FlashMessages::getInstance();
	}

	public function categories()
	{
		$quantity = 10;
		$page = $_GET['page'] ?? 1;
		$categories = $this->service->page($page, $quantity);
		$totalPages = $this->service->totalPages($quantity);

		if (!empty($totalPages) && $page > $totalPages) {
			Router::redirect('/categories');
		}

		$view = $this->factory::create();
		$view->pageTitle = 'Categories';
		$view->categories = $categories;
		$view->page = (int) $page;
		$view->totalPages = $totalPages;

		return $view->render('categories/home');
	}

	/**
	 * @RequestMap /form/{id}
	 */
	public function edit(int $id)
	{
		$category = $this->service->findById($id);

		if ($category) {
			return $this->form($category);
		} else {
			$this->message->error('No category with the ID ' . $id . ' was found!');
			Router::redirect('/categories');
		}
	}

	/**
	 * @RequestMap /form
	 */
	public function create()
	{
		return $this->form();
	}

	/**
	 * @RequestMethod POST
	 */
	public function save()
	{
		$category = $this->createCategory();
		$category = $this->service->save($category);

		if ($category) {
			$this->message->info('Category saved!');
		} else {
			$this->message->error('A problem occurred while saving the category!');
		}

		Router::redirect('/categories');
	}

	/**
	 * @RequestMap /delete/{id}
	 */
	public function delete($id)
	{
		if ($this->service->delete((int) $id)) {
			$this->message->info('Category deleted!');
		} else {
			$this->message->error('A problem occurred while deleting the category!');
		}

		Router::redirect('/categories');
	}

	/**
	 * @RequestMap /json
	 * @RequestMethod POST
	 * @Roles ADMIN
	 */
	public function json()
	{
		$list = $this->service->searchByName($_POST['search']);

		header('Content-type:application/json; charset=UTF-8');

		return json_encode($list);
	}

	private function form($category = null)
	{
		$view = $this->factory::create();

		$view->pageTitle = (is_null($category) ? 'New' : 'Update') . ' Category';
		$view->category = $category;
		$view->form = new Form;

		return $view->render('categories/form');
	}

	private function createCategory() : Category
	{
		$properties = ['id', 'name'];
		$category = new Category;

		foreach ($properties as $property) {
			$value = $_POST[$property];

			if (!empty($value)) {
				if (is_numeric($value)) {
					if (is_int($value)) {
						$category->{$property} = (int) $value;
					} else {
						$category->{$property} = $value + 0;
					}
				} else {
					$category->{$property} = $value;
				}
			}
		}

		return $category;
	}

}
