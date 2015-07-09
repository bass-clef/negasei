<?php

namespace src\Controller;

use \src\App\Smarty\SmartyBase;
use \src\App\DataBase\DataBase;

class IndexController extends SmartyBase
{
	public function indexAction()
	{
		$db = new DataBase();
		$negasei = $db->selectQuery('tbl_negasei')->fetch();

		// アクセスカウンター加算
		$negasei['access_count']++;
		$db->updateQuery('tbl_negasei')->set('access_count', $negasei['access_count'])->execute();

		$this->set('negasei', $negasei);
	}
}
