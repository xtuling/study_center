<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/4/11
 * Time: 18:16
 */
namespace Apicp\Controller\News;

use Com\PackageValidate;
use Common\Service\ArticleService;

class TopController extends \Apicp\Controller\AbstractController
{
    /**

     * Top
     *
     * @author zhonglei

     * @desc 新闻置顶/取消置顶

     * @param Int article_id:true 新闻ID

     * @return mixed

     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'article_id' => 'require|integer',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;
        $article_id = $postData['article_id'];

        $artServ = new ArticleService();
        $article = $artServ->get($article_id);

        if (empty($article)) {
            E('_ERR_ARTICLE_NOT_FOUND');
        }

        $top_time = $article['top_time'] > 0 ? 0 : MILLI_TIME;
        $artServ->update($article_id, ['top_time' => $top_time]);
    }
}
