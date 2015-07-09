<?php
/**
 * smartyのベース
 */

namespace src\App\Smarty;

require_once('Smarty.class.php');

class SmartyBase
{
	protected static $viewRoot = 'Controller';
	protected static $smartyRoot = './../private_html/templates/';

	protected $smarty;
	protected $extendsFile;
	
	/**
	 * smarty変数の作成,ディレクトリの指定
	 */
	public function __construct()
	{
		$this->smarty = new \Smarty();
		$this->smarty->template_dir = './../private_html/templates/';
		$this->smarty->compile_dir  = './../private_html/templates_c/';
		$this->smarty->config_dir   = './../private_html/configs/';
		$this->smarty->cache_dir    = './../private_html/cache/';
		$this->smarty->left_delimiter  = '{{';
		$this->smarty->right_delimiter = '}}';

		$this->smarty->assign('root_url', '');
		$this->extendsFile = [];
	}
	
	/**
	 * smarty変数の作成
	 */
	public function set($varName, $varValue)
	{
		$this->smarty->assign($varName, $varValue);
		return $this;
	}
	
	/**
	 * get値の受け取り
	 */
	public function get($varName, $defaultValue = '')
	{
		if (isset($_GET[$varName])) {
			return $_GET[$varName];
		}
		return $defaultValue;
	}
	
	/**
	 * post値の受け取り
	 */
	public function post($varName, $defaultValue = '')
	{
		if (isset($_POST[$varName])) {
			return $_POST[$varName];
		}
		return $defaultValue;
	}

	/**
	 * テンプレートの追加
	 */
	public function append($templateName)
	{
		$this->extendsFile[] = $templateName;
	}
	
	/**
	 * テンプレートの表示
	 */
	public function show($templateName)
	{
		$files = implode('|', array_merge($this->extendsFile, [$templateName]));
		$this->smarty->display('extends:' . $files);
	}

	/**
	 * viewファイル名の取得
	 * 
	 * @return string viewファイル
	 */
	public function getViewFile($path, $actionName, &$resultActionMethod)
	{
		if ('index' !== $actionName) {
			$viewFile = static::$viewRoot . $path . 'contents/' . $actionName . '.html';
		} else {
			$viewFile = static::$viewRoot . $path . $actionName . '.html';
		}
		$resultActionMethod = $actionName . 'Action';
		return $viewFile;
	}

	/**
	 * 遷移前のURL取得
	 */
	public function getReferer()
	{
		return $_SERVER['HTTP_REFERER'];
	}

	/**
	 * 指定URLへリダイレクト
	 *
	 * @param string $url 遷移先のURL
	 */
	public function redirect($url)
	{
		header('location: ' . $url);
		exit();
	}
}

