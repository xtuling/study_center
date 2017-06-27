<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/5/5
 * Time: 10:23
 */
namespace Api\Controller\Course;

use Com\PackageValidate;
use Common\Common\Constant;
use Common\Common\RpcFavoriteHelper;
use Common\Service\ArticleService;

class FavoriteController extends \Api\Controller\AbstractController
{
    /**
     * Favorite
     * @author liyifei
     * @desc 收藏、取消收藏课程
     * @param Int article_id:true 课程ID
     * @return array
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

        // 课程详情
        $articleServ = new ArticleService();
        $article = $articleServ->get($postData['article_id']);
        if (empty($article)) {
            E('_ERR_ARTICLE_NOT_FOUND');
        }

        // 登录人员
        $user = $this->_login->user;

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
            case RpcFavoriteHelper::COLLECTION_NO:
                // 封面类型
                switch ($article['source_type']) {
                    case Constant::SOURCE_TYPE_IMG_TEXT:
                        $coverType = RpcFavoriteHelper::COVER_TYPE_IMAGE;
                        break;
                    case Constant::SOURCE_TYPE_AUDIO_IMG:
                        $coverType = RpcFavoriteHelper::COVER_TYPE_RADIO;
                        break;
                    case Constant::SOURCE_TYPE_VEDIO:
                        $coverType = RpcFavoriteHelper::COVER_TYPE_VIDEO;
                        break;
                    default:
                        $coverType = RpcFavoriteHelper::COVER_TYPE_NONE;
                        break;
                }
                $data = [
                    'uid' => $user['memUid'],
                    'dataId' => $article['article_id'],
                    'title' => $article['article_title'],
                    'cover_type' => $coverType,
                    'cover_id' => $article['cover_id'],
                    'cover_url' => $article['cover_url'],
                    'url' => APP_DIR . '/Frontend/Index/Detail?article_id=' . $article['article_id'] . '&data_id=' . $article['data_id'],
                ];
                $res = $rpcFavorite->addFavorite($data);
                break;

            // 已收藏，执行取消收藏动作
            case RpcFavoriteHelper::COLLECTION_YES:
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
