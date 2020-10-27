<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\ModelBasic;

class OrderStatus extends ModelBasic {

	const EM_ABERTO = 1;
	const AGUARDANDO_PAGAMENTO = 2;
	const PAGO = 3;
	const ENTREGUE = 4;

}

?>