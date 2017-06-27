<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017-5-18
 * Time: 14:07:02
 */
namespace Apicp\Controller\Doc;

use Com\PackageValidate;
use Common\Common\Constant;
use Common\Common\Attach;
use Common\Common\RpcFavoriteHelper;
use Common\Service\FileService;
use Common\Service\RightService;
use Common\Service\TaskService;

class DeleteController extends \Apicp\Controller\AbstractController
{
    /**
     * Delete
     * @author liyifei
     * @desc 文件删除接口
     * @param Array file_ids:true 文件、文件夹ID
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'file_ids' => 'require|array',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;

        // 区分顶级文件夹及文件
        $fileServ = new FileService();
        list($folders, $files) = $fileServ->diffFolderFile($postData['file_ids']);
        if (empty($folders) && empty($files)) {
            E('_ERR_FILE_PARAM_IS_NULL');
        }

        // 获取所有文件夹（顶级文件夹及子文件夹）
        $folderIds = $fileServ->getChildIds($folders);
        if (!empty($folderIds)) {
            // 获取所有文件夹下的文件
            $fileList = $fileServ->list_by_conds([
                'parent_id' => $folderIds,
                'file_type' => Constant::FILE_TYPE_IS_DOC,
            ]);
            if (!empty($fileList)) {
                $tmpFiles = array_column($fileList, 'file_id');
                $files = array_merge($files, $tmpFiles);
            }
        }

        if (!empty($files)) {
            // 删除文件计划任务（数据库记录、UC计划任务）
            $taskServ = new TaskService();
            $task_list = $taskServ->list_by_conds(['file_id' => $files]);
            if (!empty($task_list)) {
                foreach ($task_list as $v) {
                    $taskServ->delTask($v['file_id'], $v['cron_id']);
                }
            }

            // 删除资源服务器文件
            $docList = $fileServ->list_by_conds(['file_id' => $files, 'file_type' => Constant::FILE_TYPE_IS_DOC]);
            if (!empty($docList)) {
                $at_ids = array_column($docList, 'at_id');
                $attachServ = &Attach::instance();
                $attachServ->deleteFile($at_ids);
            }

            // 删除数据库文件
            $fileServ->delete_by_conds(['file_id' => $files]);

            // 删除应用数据时，RPC同步收藏状态
            $rpcFavorite = &RpcFavoriteHelper::instance();
            $rpcFavorite->updateStatus($files);
        }

        if (!empty($folderIds)) {
            // 删除文件夹
            $fileServ->delete_by_conds(['file_id' => $folderIds]);

            // 删除文件夹权限
            $rightServ = new RightService();
            $rightServ->delete_by_conds(['file_id' => $folderIds]);
        }
    }
}
