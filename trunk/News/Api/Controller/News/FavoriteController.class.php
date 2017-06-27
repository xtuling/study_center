<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/21
 * Time: 10:19
 */
namespace Api\Controller\News;

use Com\PackageValidate;
use Common\Common\Constant;
use Common\Common\RpcFavoriteHelper;
use Common\Service\ArticleService;
use Common\Service\AttachService;

class FavoriteController extends \Api\Controller\AbstractController
{
    /**
     * Favorite
     * @author liyifei
     * @desc 收藏、取消收藏
     * @param int article_id:true 新闻公告ID
     */
    public function Index_post()
    {
        $user = $this->_login->user;

        // 验证规则
        $rules = [
            'article_id' => 'require|integer',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;

        // 新闻详情
        $articleServ = new ArticleService();
        $article = $articleServ->get($postData['article_id']);
        if (empty($article)) {
            E('_ERR_ARTICLE_NOT_FOUND');
        }

        // 新闻附件详情
        $attachServ = new AttachService();
        $list = $attachServ->list_by_conds(['article_id' => $article['article_id']]);

        // 附件类型：无
        $coverType = RpcFavoriteHelper::COVER_TYPE_NONE;
        // 附件类型：图文
        if ($article['is_show_cover'] == Constant::NEWS_IS_SHOW_COVER_TRUE) {
            $coverType = RpcFavoriteHelper::COVER_TYPE_IMAGE;
        }
        if (!empty($list)) {
            $types = array_column($list, 'at_type');
            // 附件类型：音频
            if (in_array(Constant::ATTACH_TYPE_AUDIO, $types)) {
                $coverType = RpcFavoriteHelper::COVER_TYPE_RADIO;
            }
            // 附件类型：视频
            if (in_array(Constant::ATTACH_TYPE_VIDEO, $types)) {
                $coverType = RpcFavoriteHelper::COVER_TYPE_VIDEO;
            }
        }

        // RPC查询收藏结果
        $data = [
            'uid' => $user['memUid'],
            'dataId' => $article['article_id'],
        ];
        $rpcFavorite = &RpcFavoriteHelper::instance();
        $status = $rpcFavorite->getStatus($data);
        if (empty($status) || !isset($status['collection'])) {
            E('_ERR_FAVORITE_STATUS_EMPTY');
        }

        // 根据收藏结果，决定新增/取消收藏
        switch ($status['collection']) {
            // 未收藏，执行收藏动作
            case $rpcFavorite::COLLECTION_NO:
                $data = [
                    'uid' => $user['memUid'],
                    'dataId' => $article['article_id'],
                    'title' => $article['title'],
                    'cover_type' => $coverType,
                    'cover_id' => $article['cover_id'],
                    'cover_url' => $article['cover_url'],
                    'url' => APP_DIR . '/Frontend/Index/Detail?article_id=' . $article['article_id'],
                ];
                $res = $rpcFavorite->addFavorite($data);
                break;

            // 已收藏，执行取消收藏动作
            case $rpcFavorite::COLLECTION_YES:
                $data = [
                    'uid' => $user['memUid'],
                    'dataId' => $article['article_id'],
                ];
                $res = $rpcFavorite->cancelFavorite($data);
                break;

            default:
                $res = false;
                break;
        }

        if (!$res) {
            E('_ERR_FAVORITE_OPERATE_FAIL');
        }
    }
}
