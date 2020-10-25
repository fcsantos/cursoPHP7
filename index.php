<?php 

session_start();
require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;

$app = new Slim();

$app->config('debug', true);

//página index do ecommerce
$app->get('/', function() {
    
	$page = new Page();

	$page->setTpl("index");
});

//página index do dashboard
$app->get('/admin', function() {
	
	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("index");
});

//página de login do usuário do dashboard
$app->get('/admin/login', function() {
    
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("login");
});

//login do usuário válido do dashboard
$app->post('/admin/login', function() {

	User::login($_POST["login"], $_POST["password"]);

	header("Location: /admin");
	exit;
});

//logout do usuário do dashboard
$app->get('/admin/logout', function() {

	User::logout();

	header("Location: /admin/login");
	exit;

});

//página da listagem dos usuários
$app->get("/admin/users", function(){

	User::verifyLogin();

	$users = User::listAll();

	$page = new PageAdmin();

	$page->setTpl("users",array(
		"users"=>$users
	));
});

//página para criação do usuário
$app->get("/admin/users/create", function(){

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("users-create");
});

//deleção do usuário e redirect para página da lista de usuários
$app->get("/admin/users/:iduser/delete", function ($iduser) {

	User::verifyLogin();

	$user = new User();

	$user->getById((int)$iduser);

	$user->delete();

	header("Location: /admin/users");
	exit;
});

//página para alteração do usuário
$app->get("/admin/users/:iduser", function($iduser){

	User::verifyLogin();

	$user = new User();

	$user->getById((int)$iduser);

	$page = new PageAdmin();

	$page->setTpl("users-update", array(
		"user"=>$user->getValues()
	));
});

//criação do usuário e redirect para página da lista de usuário
$app->post("/admin/users/create", function () {

	User::verifyLogin();

    $user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;

	$_POST['despassword'] = password_hash($_POST["despassword"], PASSWORD_DEFAULT, [
		"cost"=>12
	]);

	$user->setData($_POST);

    $user->save();

    header("Location: /admin/users");
	exit;
});

//alteração do usuário e redirect para página da lista de usuários
$app->post("/admin/users/:iduser", function ($iduser) {

	User::verifyLogin();

	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;

	$user->getById((int)$iduser);

	$user->setData($_POST);

	$user->update();

	header("Location: /admin/users");
	exit;
});

//página para digitar o seu e-mail e receber as instruções para redefinir a sua senha.
$app->get('/admin/forgot', function() {
    
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot");
});

//Envio do e-mail para receber as instruções de alteração da senha.
$app->post("/admin/forgot", function () {

	$user = User::getForgot($_POST["email"]);

	header("Location: /admin/forgot/sent");
	exit;
});

//E-mail enviado! 
$app->get('/admin/forgot/sent', function ()
{
	
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-sent");
});

//acessando a página de "Reset Password"
$app->get("/admin/forgot/reset", function(){

	$user = User::validForgotDecrypt($_GET["code"]);

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]
	));

});

//Alterando a senha do usuário e enviando para pagina de "forgot-reset-success"
$app->post("/admin/forgot/reset", function(){

	$forgot = User::validForgotDecrypt($_POST["code"]);	

	User::setFogotUsed($forgot["idrecovery"]);

	$user = new User();

	$user->get((int)$forgot["iduser"]);

	$password = User::getPasswordHash($_POST["password"]);

	$user->setPassword($password);

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-reset-success");
});

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

//página para alteração da categoria
$app->get("/categories/:idcategory", function($idcategory){

	$category = new Category();

	$category->getById((int)$idcategory);

	$page = new Page();

	$page->setTpl("category", [
		"category"=>$category->getValues()
	]);
});

$app->run();

 ?>