<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/21
 * Time: 10:19
 */
namespace Api\Controller\Doc;

use Com\PackageValidate;
use Common\Common\Constant;
use Common\Common\RpcFavoriteHelper;
use Common\Service\FileService;

class FavoriteController extends \Api\Controller\AbstractController
{
    /**
     * Favorite
     * @author liyifei
     * @desc 收藏、取消收藏
     * @param Int file_id:true 文件ID
     * @return void
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'file_id' => 'require|integer',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;

        // 课程详情
        $fileServ = new FileService();
        $file = $fileServ->get($postData['file_id']);
        if (empty($file)) {
            E('_ERR_FILE_DATA_IS_NULL');
        }

        // 登录人员
        $user = $this->_login->user;

        // RPC查询收藏结果
        $data = [
            'uid' => $user['memUid'],
            'dataId' => $file['file_id'],
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
                $data = [
                    'uid' => $user['memUid'],
                    'dataId' => $file['file_id'],
                    'title' => $file['file_name'],
                    'cover_type' => RpcFavoriteHelper::COVER_TYPE_NONE,
                    'url' => APP_DIR . '/Frontend/Index/Detail?file_id=' . $file['file_id'],
                    'file_type' => strtoupper(end(explode('.', $file['file_name']))),
                    'file_size' => $file['at_size'],
                    'is_dir' => $file['file_type'] == Constant::FILE_TYPE_IS_FOLDER ? RpcFavoriteHelper::IS_DIR_TRUE : RpcFavoriteHelper::IS_DIR_FALSE,
                ];
                $res = $rpcFavorite->addFavorite($data);
                break;

            // 已收藏，执行取消收藏动作
            case RpcFavoriteHelper::COLLECTION_YES:
                $data = [
                    'uid' => $user['memUid'],
                    'dataId' => $file['file_id'],
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
