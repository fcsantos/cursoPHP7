<?php 

namespace Hcode\Model;

use \Hcode\ModelBasic;
use \Hcode\DB\Sql;
use \Hcode\Mailer;

class User extends ModelBasic {

	const SESSION = "User";
	const SECRET = "HcodePhp7_Secret";
	const SECRET_IV = "HcodePhp7_Secret_IV";
	const ERROR = "UserError";
	const ERROR_REGISTER = "UserErrorRegister";
	const SUCCESS = "UserSucesss";

	public static function getFromSession()
	{
		$user = new User();

		if (isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]['iduser'] > 0) {

			$user->setData($_SESSION[User::SESSION]);

		}

		return $user;
	}
	
	public static function checkLogin($inadmin = true)
	{
		if (
			!isset($_SESSION[User::SESSION])
			||
			!$_SESSION[User::SESSION]
			||
			!(int)$_SESSION[User::SESSION]["iduser"] > 0
		) {
			//Não está logado
			return false;

		} else {

			if ($inadmin === true && (bool)$_SESSION[User::SESSION]['inadmin'] === true) {

				return true;

			} else if ($inadmin === false) {

				return true;

			} else {

				return false;

			}
		}
	}

	public static function login($login, $password):User
	{
		$db = new Sql();

		$results = $db->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
			":LOGIN"=>$login
		));

		if (count($results) === 0) {
			throw new \Exception("Não foi possível fazer login.");
		}

		$data = $results[0];

		if (password_verify($password, $data["despassword"])) {

			$user = new User();
			$user->setData($data);

			$_SESSION[User::SESSION] = $user->getValues();

			return $user;

		} else {
			throw new \Exception("Não foi possível fazer login.");
		}
	}

	public static function logout()
	{
		$_SESSION[User::SESSION] = NULL;
	}

	public static function verifyLogin($inadmin = true)
	{
		if (!User::checkLogin($inadmin)) {

			if ($inadmin) {
				header("Location: /admin/login");
			} else {
				header("Location: /login");
			}
			exit;

		}
	}

	public static function listAll()
	{
		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_users u INNER JOIN tb_persons p USING(idperson) ORDER BY p.desperson");
	}

	public function save(){

		$sql = new Sql();

		$results = $sql->select("CALL sp_users_save(:pdesperson, :pdeslogin, :pdespassword, :pdesemail, :pnrphone, :pinadmin)", 
			array(
			":pdesperson"=>$this->getdesperson(),
			":pdeslogin"=>$this->getdeslogin(),
			":pdespassword"=>$this->getdespassword(),
			":pdesemail"=>$this->getdesemail(),
			":pnrphone"=>$this->getnrphone(),
			":pinadmin"=>$this->getinadmin()			
		));

		$this->setData($results[0]);
	}

	public function getById($iduser)
	{
		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users u INNER JOIN tb_persons p USING(idperson) WHERE u.iduser = :iduser", array(
			":iduser"=>$iduser
		));

		$this->setData($results[0]);
	}

	public function update()
	{
		$sql = new Sql();

		$results = $sql->select("CALL sp_usersupdate_save(:iduser, :pdesperson, :pdeslogin, :pdespassword, :pdesemail, :pnrphone, :pinadmin)", 
			array(
			":iduser"=>$this->getiduser(),
			":pdesperson"=>$this->getdesperson(),
			":pdeslogin"=>$this->getdeslogin(),
			":pdespassword"=>$this->getdespassword(),
			":pdesemail"=>$this->getdesemail(),
			":pnrphone"=>$this->getnrphone(),
			":pinadmin"=>$this->getinadmin()			
		));

		$this->setData($results[0]);
	}

	public function delete()
	{
		$sql = new Sql();

		$sql->query("CALL sp_users_delete(:iduser)", array(
			"iduser"=>$this->getiduser()
		));
	}

	public static function getForgot($email)
	{
		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_persons p INNER JOIN tb_users u USING(idperson) WHERE p.desemail = :email",
		array(
			":email"=>$email
		));		

		if (count($results) === 0) {
			throw new \Exception("Não foi possível resuperar a senha.");
		}
		else{
			$data = $results[0];

			$results2 = $sql->select("Call sp_userspasswordsrecoveries_create(:piduser, :pdesip)", array(
				"piduser"=>$data["iduser"],
				"pdesip"=>$_SERVER["REMOTE_ADDR"]
			));

			if (count($results2) === 0) {
				throw new \Exception("Não foi possível resuperar a senha.");				
			}else {
				$dataRecovery = $results2[0];

				$code = openssl_encrypt($dataRecovery['idrecovery'], 'AES-128-CBC', pack("a16", User::SECRET), 0, pack("a16", User::SECRET_IV));

				$code = base64_encode($code);

				$link = "http://www.hcodecommerce.com.br/admin/forgot/reset?code=$code";

				$mailer = new Mailer($data['desemail'], $data['desperson'], "Redefinir senha da Hcode Store", "forgot", array(
					"name"=>$data['desperson'],
					"link"=>$link
				));

				$mailer->send();

				return $data;
			}
		}
	}

	public static function validForgotDecrypt($code)
	{

		$code = base64_decode($code);

		$idrecovery = openssl_decrypt($code, 'AES-128-CBC', pack("a16", User::SECRET), 0, pack("a16", User::SECRET_IV));

		$sql = new Sql();

		$results = $sql->select("
			SELECT *
			FROM tb_userspasswordsrecoveries a
			INNER JOIN tb_users b USING(iduser)
			INNER JOIN tb_persons c USING(idperson)
			WHERE
				a.idrecovery = :idrecovery
				AND
				a.dtrecovery IS NULL
				AND
				DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();
		", array(
			":idrecovery"=>$idrecovery
		));

		if (count($results) === 0)
		{
			throw new \Exception("Não foi possível recuperar a senha.");
		}
		else
		{
			return $results[0];
		}
	}

	public static function setFogotUsed($idrecovery)
	{

		$sql = new Sql();

		$sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE idrecovery = :idrecovery", array(
			":idrecovery"=>$idrecovery
		));

	}
	
	public static function getPasswordHash($password)
	{

		return password_hash($password, PASSWORD_DEFAULT, [
			'cost'=>12
		]);

	}

	public function setPassword($password)
	{

		$sql = new Sql();

		$sql->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser", array(
			":password"=>$password,
			":iduser"=>$this->getiduser()
		));

	}
}

 ?>