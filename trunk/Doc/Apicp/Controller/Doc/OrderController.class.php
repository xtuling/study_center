<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017-5-18
 * Time: 14:07:02
 */
namespace Apicp\Controller\Doc;

use Com\PackageValidate;
use Common\Service\FileService;

class OrderController extends \Apicp\Controller\AbstractController
{
    /**
     * Order
     * @author tangxingguo
     * @desc 文件排序接口(同目录)
     * @param array file_ids:true 文件IDS
     * @param int parent_id:true 当前目录ID（用于校验文件ID）
     * @return void
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'file_ids' => 'require|array',
            'parent_id' => 'require|integer',
        ];

        // 验证参数
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;
        $fileIds = $postData['file_ids'];
        $parentId = $postData['parent_id'];

        // 取当前目录文件
        $fileServ = new FileService();
        $files = $fileServ->list_by_conds(['parent_id' => $parentId]);
        if (empty($files)) {
            E('_ERR_FILE_SELECT_IS_NULL');
        }

        // 清除不存在的文件
        $dbFileIds = array_column($files, 'file_id');
        $fileIds = array_intersect($fileIds, $dbFileIds);
        if (empty($fileIds)) {
            E('_ERR_FILE_SELECT_IS_NULL');
        }

        // 更新排序
        $order = 1;
        foreach ($fileIds as $fileId) {
            $fileServ->update($fileId, ['`order`' => $order]);
            $order++;
        }
    }
}
