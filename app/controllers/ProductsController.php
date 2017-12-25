<?php

namespace App\Controllers;

use FW\Core\Router;
use FW\Core\FlashMessages;
use FW\View\IViewFactory;

use PHC\Components\Form;

use App\Models\Product;
use App\Interfaces\Services\IProductsService;

/**
 * @Controller
 * @Route /products
 * @Authenticate
 * @Roles [ADMIN, STOCK]
 */
class ProductsController
{

	private $factory;

	private $service;

	private $message;

	public function __construct(IViewFactory $factory, IProductsService $service)
	{
		$this->factory = $factory;
		$this->service = $service;
		$this->message = FlashMessages::getInstance();
	}

	public function products()
	{
		$quantity = 10;
		$page = $_GET['page'] ?? 1;
		$products = $this->service->page($page, $quantity);
		$totalPages = $this->service->totalPages($quantity);

		if (!empty($totalPages) && $page > $totalPages) {
			Router::redirect('/products');
		}

		$view = $this->factory::create();
		$view->pageTitle = 'Products';
		$view->products = $products;
		$view->page = (int) $page;
		$view->totalPages = $totalPages;

		return $view->render('products/home');
	}

	/**
	 * @RequestMap /form/{id}
	 */
	public function edit(int $id)
	{
		$product = $this->service->findById($id);

		if ($product) {
			return $this->form($product);
		} else {
			$this->message->error('No product with the ID ' . $id . ' was found!');
			Router::redirect('/products');
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
		$product = $this->createProduct();
		$product = $this->service->save($product);

		if ($product) {
			$this->message->info('Product saved!');
		} else {
			$this->message->error('A problem occurred while saving the product!');
		}

		Router::redirect('/products');
	}

	/**
	 * @RequestMap /delete/{id}
	 */
	public function delete($id)
	{
		if ($this->service->delete($id)) {
			$this->message->info('Product deleted!');
		} else {
			$this->message->error('A problem occurred while deleting the product!');
		}

		Router::redirect('/products');
	}

	private function form($product = null)
	{
		$view = $this->factory::create();

		$view->pageTitle = (is_null($product) ? 'New' : 'Update') . ' Product';
		$view->product = $product;
		$view->form = new Form;

		return $view->render('products/form');
	}

	private function createProduct() : Product
	{
		$properties = ['id', 'name', 'description', 'price', 'quantity'];
		$product = new Product;

		foreach ($properties as $property) {
			$value = $_POST[$property];

			if (!empty($value)) {
				if (is_numeric($value)) {
					if (is_int($value)) {
						$product->{$property} = (int) $value;
					} elseif (is_float($value)) {
						$product->{$property} = (float) $value;
					} else {
						$product->{$property} = $value + 0;
					}
				} else {
					$product->{$property} = $value;
				}
			}
		}

		if (isset($_FILES['picture']) && !$_FILES['picture']['error']) {
			$mime = $_FILES['picture']['type'];
			$file = file_get_contents($_FILES['picture']['tmp_name']);
			$picture = sprintf('data:%s;base64,%s', $mime, base64_encode($file));
			$product->picture = $picture;
		} else {
			if (!empty($product->id)) {
				$old = $this->service->findById($product->id);

				if (!empty($old)) {
					$product->picture = $old->picture;
				}
			}
		}

		if (!empty($_POST['categories'])) {
			$categories = [];

			foreach ($_POST['categories'] as $id) {
				$category = new \App\Models\Category;
				$category->id = $id;
				$categories[] = $category;
			}

			$product->categories = $categories;
		}

		return $product;
	}

}
