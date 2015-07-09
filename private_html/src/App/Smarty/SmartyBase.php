<?php
/**
 * smarty�̃x�[�X
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
	 * smarty�ϐ��̍쐬,�f�B���N�g���̎w��
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
	 * smarty�ϐ��̍쐬
	 */
	public function set($varName, $varValue)
	{
		$this->smarty->assign($varName, $varValue);
		return $this;
	}
	
	/**
	 * get�l�̎󂯎��
	 */
	public function get($varName, $defaultValue = '')
	{
		if (isset($_GET[$varName])) {
			return $_GET[$varName];
		}
		return $defaultValue;
	}
	
	/**
	 * post�l�̎󂯎��
	 */
	public function post($varName, $defaultValue = '')
	{
		if (isset($_POST[$varName])) {
			return $_POST[$varName];
		}
		return $defaultValue;
	}

	/**
	 * �e���v���[�g�̒ǉ�
	 */
	public function append($templateName)
	{
		$this->extendsFile[] = $templateName;
	}
	
	/**
	 * �e���v���[�g�̕\��
	 */
	public function show($templateName)
	{
		$files = implode('|', array_merge($this->extendsFile, [$templateName]));
		$this->smarty->display('extends:' . $files);
	}

	/**
	 * view�t�@�C�����̎擾
	 * 
	 * @return string view�t�@�C��
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
	 * �J�ڑO��URL�擾
	 */
	public function getReferer()
	{
		return $_SERVER['HTTP_REFERER'];
	}

	/**
	 * �w��URL�փ��_�C���N�g
	 *
	 * @param string $url �J�ڐ��URL
	 */
	public function redirect($url)
	{
		header('location: ' . $url);
		exit();
	}
}

