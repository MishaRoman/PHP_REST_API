<?php 

namespace App\controllers;

use App\models\Category;

class CategoriesController extends Controller
{
	public function read()
	{
		$category = new Category();

		$categories = $category->read();

		if($categories) {
			echo json_encode($categories);
		} else {
			echo json_encode([]);
		}
	}
}