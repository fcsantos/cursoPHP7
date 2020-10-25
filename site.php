<?php

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;

//página index do ecommerce
$app->get('/', function() {
    
	$page = new Page();

	$page->setTpl("index");
});

//listar categorias no site
$app->get("/categories/:idcategory", function($idcategory){

	$category = new Category();

	$category->getById((int)$idcategory);

	$page = new Page();

	$page->setTpl("category", [
		"category"=>$category->getValues()
	]);
});

?>