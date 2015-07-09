<?php

namespace src\Controller\Blog;

use src\App\Smarty\SmartyBase;
use src\App\DataBase\DataBase;

class IndexController extends SmartyBase
{
	/**
	 * getViewFile
	 * 
	 * @cover SmartyBase
	 */
	public function getViewFile($path, $actionName, &$resultActionMethod)
	{
		if ('index' !== $actionName) {
			$filePath = str_split($actionName, 4);
			$viewFile = static::$viewRoot . $path . 'page.html';
			$resultActionMethod = 'page' . $actionName . 'Action';
			if (!method_exists($this, $resultActionMethod)) {
				$resultActionMethod = ['pageAction', $actionName];
			}
		} else {
			$viewFile = static::$viewRoot . $path . $actionName . '.html';
			$resultActionMethod = $actionName . 'Action';
		}
		if (!file_exists(static::$smartyRoot . $viewFile)) {
			$viewFile = static::$viewRoot . $path . 'index.html';
		}
		return $viewFile;
	}

	/**
	 * blogのindexAction
	 * blogのファイル一覧を取得
	 */
	public function indexAction()
	{
		$db = new DataBase();

		// 日付指定
		$beginTime = $this->get('beg', '00000000');
		$endTime   = $this->get('end', '30000000');
		if ('00000000' !== $beginTime or '30000000' !== $endTime) {
			$beginTime = (new \DateTime($beginTime))->format('Ymd');
			$endTime = (new \DateTime($endTime))->format('Ymd');
			var_dump($beginTime);
			var_dump($endTime);

			$db->selectQuery('tbl_blog')->where('datetime', [$beginTime, $endTime], 'BETWEEN')->orderBy('datetime');
		} else {
			$db->selectQuery('tbl_blog')->orderBy('datetime')->limit(5);
		}

		// 最近5件
		$this->set('blogList', $db->fetchAll());
	}

	/**
	 * 記事のデフォルトのAction
	 */
	public function pageAction($param)
	{
		$datetime = new \DateTime($param[0]);
		$db = new DataBase();
		$blog = $db->selectQuery('tbl_blog')->where('datetime', $param[0])->fetch();
		$blog['text'] = str_replace('。', '。<br/>', $blog['text']);

		// コメント取得
		$commentList = $db->selectQuery('tbl_comment')->where('comment_id', $blog['blog_id'])->orderBy('created_at', 'ASC')->fetchAll();

		$this->set('blog', $blog)
			->set('datetime', $datetime->format('Y-m-d'))
			->set('commentList', $commentList);
	}
}
