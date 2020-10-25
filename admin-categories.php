<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;
use \Hcode\Model\Product;

//página da listagem de categorias
$app->get("/admin/categories", function(){

	$categories = Category::listAll();

	$page = new PageAdmin();

	$page->setTpl("categories", [
		'categories'=>$categories
	]);
});

//página para criação da categoria
$app->get("/admin/categories/create", function(){

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("categories-create");
});

////criação da categoria e redirect para página da lista de categorias
$app->post("/admin/categories/create", function () {

	User::verifyLogin();

    $category = new Category();

	$category->setData($_POST);

    $category->save();

    header("Location: /admin/categories");
	exit;
});

//deleção do usuário e redirect para página da lista de usuários
$app->get("/admin/categories/:idcategory/delete", function ($idcategory) {

	User::verifyLogin();

	$category = new Category();

	$category->getById((int)$idcategory);

	$category->delete();

	header("Location: /admin/categories");
	exit;
});

//página para alteração da categoria
$app->get("/admin/categories/:idcategory", function($idcategory){

	User::verifyLogin();

	$category = new Category();

	$category->getById((int)$idcategory);

	$page = new PageAdmin();

	$page->setTpl("categories-update", array(
		"category"=>$category->getValues()
	));
});

//alteração do usuário e redirect para página da lista de usuários
$app->post("/admin/categories/:idcategory", function ($idcategory) {

	User::verifyLogin();

	$category = new Category();

	$category->getById((int)$idcategory);

	$category->setData($_POST);

	$category->save();

	header("Location: /admin/categories");
	exit;
});

$app->get("/admin/categories/:idcategory/products", function($idcategory){

	User::verifyLogin();

	$category = new Category();

	$category->getById((int)$idcategory);

	$page = new PageAdmin();

	$page->setTpl("categories-products", [
		'category'=>$category->getValues(),
		'productsRelated'=>$category->getProducts(),
		'productsNotRelated'=>$category->getProducts(false)
	]);
});

$app->get("/admin/categories/:idcategory/products/:idproduct/add", function($idcategory, $idproduct){

	User::verifyLogin();

	$category = new Category();

	$category->getById((int)$idcategory);

	$product = new Product();

	$product->getById((int)$idproduct);

	$category->addProduct($product);

	header("Location: /admin/categories/".$idcategory."/products");
	exit;

});

$app->get("/admin/categories/:idcategory/products/:idproduct/remove", function($idcategory, $idproduct){

	User::verifyLogin();

	$category = new Category();

	$category->getById((int)$idcategory);

	$product = new Product();

	$product->getById((int)$idproduct);

	$category->removeProduct($product);

	header("Location: /admin/categories/".$idcategory."/products");
	exit;

});

?>