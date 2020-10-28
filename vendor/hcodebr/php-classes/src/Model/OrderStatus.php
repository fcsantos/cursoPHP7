<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\ModelBasic;

class OrderStatus extends ModelBasic {

	const EM_ABERTO = 1;
	const AGUARDANDO_PAGAMENTO = 2;
	const PAGO = 3;
	const ENTREGUE = 4;

	public static function listAll()
	{

		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_ordersstatus ORDER BY desstatus");

	}
}

?>