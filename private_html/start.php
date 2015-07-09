<?php

// libの読み込み spl_autoload_registerでは0番目のディレクトリを読まない
define('__PHP_LIBS__', __DIR__ . "/../private_html/src/App");
define('__JS_BASES__', __DIR__ . '/../private_html/templates/Elements');
$libs = [
	__DIR__ . "/../private_html/smarty/libs",
	__PHP_LIBS__,
];

// phpパスへの追加
ini_set(
	'include_path',
	implode(PATH_SEPARATOR, array_merge([ini_get('include_path')], $libs))
);

// classの自動読み込み,以後useだけでおｋ
require_once(__PHP_LIBS__ . '/Functions/listdir.php');
spl_autoload_register(function(){
	foreach (listdir(__PHP_LIBS__, ['', 'php']) as $fileName) {
		require_once($fileName);
	};
});

// エラー表示設定
error_reporting(E_ALL ^ E_NOTICE);	// E_NOTICEは非表示

use src\App\DataBase\DataBase;
use src\App\Smarty\SmartyBase;

class Dispatcher extends SmartyBase
{
	private $controllerRoot = './../private_html/src/Controller';
	private $controllerFile;
	private $viewFile;
	private $readDir;

	/**
	 * view と controller の読込先ディレクトリの設定
	 */
	private function setReadDir($newReadDir)
	{
		$this->readDir = $newReadDir;
	}

	/**
	 * view と controller の読み込みファイルの設定
	 */
	private function setReadFile($indexName, $className)
	{
		$this->controllerFile = $this->controllerRoot . $this->readDir . $className . '.php';
	}
	
	/**
	 * /domain/<xxx>/<yyy>Controller.php の <zzz>Action を実行
	 */
	public function dispatch()
	{
		// パラメーター取得
		$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
		$arg  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);

		// 短いURL用
		if ('' !== $this->get('s', '')) {
			$urlId = intval($this->get('s', ''));
			$db = new DataBase();
			$shortUrl = $db->selectQuery('tbl_short_url')->where('url_id', $urlId)->fetch();
			if (isset($shortUrl['url'])) {
				$this->redirect($shortUrl['url']);
			}
		}

		// パラメーターを / で分割
		$params = array_map(function($prm){
			if ('' == $prm or 'index.php' == $prm) {
				return 'index';
			}
			return $prm;
		}, array_slice(explode('/', $path), 1));

		$this->setReadDir('/');
		if (2 === count($params)) {
			$params[2] = 'index';
		}
		if (2 > count($params)) {
			$params = ['index', 'index', 'index'];
		} else {
			$dirs = array_map(function($param) {
				return ucfirst($param);
			}, array_slice($params, 1, -1));

			$this->setReadDir('/' . implode('/', $dirs) . '/');
		}
		$paramCount = count($params);

		// パラメータより取得したコントローラー名によりクラス振分け
		$indexName = strtolower($params[0]);
		$actionBase = $params[$paramCount-1];
		$className = ucfirst($indexName) . 'Controller';
		$this->setReadFile($indexName, $className);

		// ない場合はindexへ
		if (!file_exists($this->controllerFile)) {
			error_log('file "' . $this->controllerFile . '" is not found. move to index');
			$this->setReadDir('/');
			$indexName    = 'index';
			$actionBase   = $indexName;
			$className    = 'IndexController';
			$this->setReadFile($indexName, $className);

			if (!file_exists($this->controllerFile)) {
				echo $this->controllerFile;
				error_log('index file is not found.');
				throw RuntimeException('index file not found.');
			}
		}

		// クラスファイル読込
		require_once($this->controllerFile);
		
		// クラスインスタンス生成
		$classFullName = '\src\Controller' . str_replace('/', '\\', $this->readDir) . $className;
		$controllerInstance = new $classFullName();

		// 土台smartyファイル読み込み
		$controllerInstance->append('view.html');
		$controllerInstance->append('Elements/header.html');
		$controllerInstance->append('Elements/footer.html');
		$controllerInstance->append('Elements/SNSbutton.html');
		
		// viewファイルを取得
		$actionMethod = 'indexAction';
		$this->viewFile = $controllerInstance->getViewFile($this->readDir, $actionBase, $actionMethod);

		// アクションメソッドを実行
		if (is_array($actionMethod)) {
			$controllerInstance->$actionMethod[0](array_slice($actionMethod, 1));
		} else {
			$controllerInstance->$actionMethod();
		}

		// smarty 親変数
		$controllerInstance->set('nowtime', (new DateTime('NOW'))->format('Y-m-d H:i:s'));
		$controllerInstance->set('pageName', $params[1]);

		// smarty表示
		$controllerInstance->show($this->viewFile);
	}
}



$dispatcher = new Dispatcher();
$dispatcher->dispatch();

