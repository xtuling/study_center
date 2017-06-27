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
use Common\Service\RightService;

class InfoController extends \Api\Controller\AbstractController
{
    /**
     * Info
     * @author tangxingguo
     * @desc 文件（夹）详情接口
     * @param int file_id:true 文件、文件夹ID
     * @return array
                array(
                    'file_id' => 123, // 文件ID
                    'file_name' => '好好学习.pdf', // 文件名称
                    'at_suffix' => 'pdf', // 文件尾缀
                    'at_size' => 123456, // 附件尺寸（单位字节）
                    'path' => '资料库/课程中心/学习资料', // 文件位置
                    'update_time' => 1493264288000, // 最后更新时间
                    'created' => 1493264288000, // 创建时间
                    'total_file' => 10, // 包含文件数
                    'file_type' => 2, // 文件类型（1=文件夹；2=文件）
                    'at_convert_url' => 'http://dsc.vchangyi.com/123.pdf', // 预览URL
                    'is_download' =>1, // 是否允许下载（1=不允许；2=允许）
                    'at_url' => 'http://dsc.vchangyi.com/123.pdf', // 下载URL（is_download=2时存在）
                    'my_is_favorite' => 1, // 我是否收藏（1=未收藏，2=已收藏）
                );
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'file_id' => 'require|integer',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $fileId = $validate->postData['file_id'];
        $user = $this->_login->user;

        // 取当前文件信息
        $fileServ = new FileService();
        $fileInfo = $fileServ->get_by_conds(['file_id' => $fileId, 'file_status' => Constant::FILE_STATUS_NORMAL]);
        if (empty($fileInfo)) {
            E('_ERR_FILE_DATA_IS_NULL');
        }

        // 数据处理
        $getPathId = $fileId;
        switch ($fileInfo['file_type']) {
            // 文件类型：文件
            case Constant::FILE_TYPE_IS_DOC:
                $getPathId = $fileInfo['parent_id'];
                $fileInfo['at_suffix'] = end(explode('.', $fileInfo['file_name']));

                // 目录下载权限检查
                $rightServ = new RightService();
                $downloadRight = $rightServ->checkDownloadRight($user, $fileInfo['parent_id']);
                $parentInfo = $fileServ->get_by_conds(['file_id' => $fileInfo['parent_id']]);
                if ($fileInfo['parent_id'] == 0 || ($parentInfo['is_download'] == Constant::FILE_DOWNLOAD_RIGHT_ON && $downloadRight)) {
                    $fileInfo['is_download'] = Constant::FILE_MY_DOWNLOAD_RIGHT_YES;
                } else {
                    $fileInfo['is_download'] = Constant::FILE_MY_DOWNLOAD_RIGHT_NO;
                }

                // 文件url加入鉴权参数
                $fileInfo['at_url'] .= '&_id=' . $fileId;
                $fileInfo['at_convert_url'] .= $fileInfo['at_convert_url'] ? '&_id=' . $fileId : '';
                break;

            // 文件类型：文件夹
            case Constant::FILE_TYPE_IS_FOLDER:
                list($totalSize, $totalNum) = $fileServ->getFolderInfo($fileId);
                $fileInfo['total_file'] = $totalNum;
                $fileInfo['at_size'] = $totalSize;
                break;
        }

        // 路径
        $path = '资料库';
        if ($getPathId != 0) {
            $pathArr = $fileServ->getPaths($getPathId);
            $path .= '/' . implode('/', array_column($pathArr, 'file_name'));
        }
        $fileInfo['path'] = $path;

        // RPC查询收藏结果
        $data = [
            'uid' => $user['memUid'],
            'dataId' => $fileId,
        ];
        $rpcFavorite = &RpcFavoriteHelper::instance();
        $status = $rpcFavorite->getStatus($data);
        $fileInfo['my_is_favorite'] = Constant::FILE_MY_FAVORITE_NO;
        if (isset($status['collection']) && $status['collection'] == RpcFavoriteHelper::COLLECTION_YES) {
            $fileInfo['my_is_favorite'] = Constant::FILE_MY_FAVORITE_YES;
        }

        $this->_result = $fileInfo;
    }
}
