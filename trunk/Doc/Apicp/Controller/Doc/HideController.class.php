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

class HideController extends \Apicp\Controller\AbstractController
{
    /**
     * Hide
     * @author tangxingguo
     * @desc 文件隐藏接口
     * @param Array file_ids:true 文件、文件夹ID
     * @param Int is_show:true 文件、文件夹是否显示（1=隐藏；2=显示）
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'file_ids' => 'require|array',
            'is_show' => 'require|integer|in:1,2',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData= $validate->postData;
        $fileIds = $postData['file_ids'];
        $isShow = $postData['is_show'];

        // 检查文件
        $fileServ = new FileService();
        $fileList = $fileServ->list_by_conds(['file_id in (?)' => $fileIds]);
        if (empty($fileList)) {
            E('_ERR_FILE_SELECT_IS_NULL');
        }

        // 更新
        foreach ($fileList as $v) {
            $fileServ->update($v['file_id'], ['is_show' => $isShow]);
        }
    }
}
