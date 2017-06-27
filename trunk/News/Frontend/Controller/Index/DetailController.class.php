<?php
/**
 * Created by PhpStorm.
 * User: zhonglei
 * Date: 17/2/22
 * Time: 09:59
 */
namespace Frontend\Controller\Index;

use Common\Common\Constant;
use Common\Service\ArticleService;

class DetailController extends AbstractController
{
    /**
     * 跳转至手机端新闻详情页
     */
    public function Index()
    {
        $article_id = I('get.article_id', 0, 'intval');
        if (empty($article_id)) {
            E('_ERR_ARTICLE_ID_IS_EMPTY');
        }

        // 取新闻信息
        $articleServ = new ArticleService();
        $articleInfo = $articleServ->get($article_id);
        if (empty($articleInfo)) {
            redirectFront('/app/page/news/detail/detail', ['article_id' => $article_id]);
        }

        // 跳转至详情或外链
        if ($articleInfo['is_jump'] == Constant::NEWS_IS_JUMP_TRUE && !empty($articleInfo['link'])) {
            header('location:' . $articleInfo['link']);
        } else {
            redirectFront('/app/page/news/detail/detail', ['article_id' => $article_id]);
        }
    }
}
