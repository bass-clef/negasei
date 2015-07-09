<?php

namespace src\Controller\Query\Comment;

use src\App\Smarty\SmartyBase;
use src\App\DataBase\DataBase;

class IndexController extends SmartyBase
{
	public static $COMMENT_ADMIN_ONLY	= 1;
	public static $COMMENT_DELETED		= 2;

	/**
	 * コメントの記録
	 */
	public function recordAction()
	{
		$blogId = intval($this->post('blog_id'));
		$name = htmlspecialchars($this->post('name', ''), ENT_QUOTES | ENT_HTML5);
		$text = $this->post('text', '');
		$admin = 'on' === $this->post('admin') ? true : false;

		// text無しは登録しない
		if ('' === $text) {
			$this->redirect($this->getReferer());
			return;
		}

		// 特殊文字の回避
		$text = str_replace(PHP_EOL, '{{br}}', $text);
		$text = htmlspecialchars($text, ENT_QUOTES | ENT_HTML5);

		// 簡単なシンタックスハイライト
		$syntaxHightLightParams = ['br\\/?', 'hr\\/?', 'i', 'b', 's', 'ol', 'ul', 'li', 'pre'];
		$syntaxHightLights = [];
		foreach ($syntaxHightLightParams as $syntaxHightLightParam) {
			$syntaxHightLights[] = '/\\{{2}(\\/?' . $syntaxHightLightParam . ')\\}{2}/ui';
		}
		$text = preg_replace($syntaxHightLights, '<$1>', $text);

		// nameの匿名化
		$name = '' === $name ? '無も無き無職' : $name;

		$datetime = new \DateTime('NOW');
		$db = new DataBase();
		$numberMax = 1 + intval(
			$db->select('number, comment_id', 'tbl_comment')->where('comment_id', $blogId)->orderBy('created_at')->limit(1)->fetch()['number']
		);

		// コメントの記録
		$db->insertQuery('tbl_comment')->values([
			$blogId,
			$numberMax,
			$name,
			$text,
			$admin<<1,
			$datetime->format('Y-m-d H:i:s')
		]);
		$db->execute();

		// tbl_blogのコメント数記録
		$db->updateQuery('tbl_blog')->set('comment_count', $numberMax)->where('blog_id', $blogId);
		$db->execute();

		$this->redirect($this->getReferer());
	}
}

