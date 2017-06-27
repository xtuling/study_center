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
use Common\Service\FileService;

class RenameController extends \Apicp\Controller\AbstractController
{
    /**
     * Rename
     * @author tangxingguo
     * @desc 重命名接口
     * @param Int file_id:true 文件ID
     * @param String file_name:true 新文件名
     * @return array
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'file_id' => 'require|integer',
            'file_name' => 'require',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;
        $fileId = $postData['file_id'];
        $fileName = $postData['file_name'];

        // 文件信息
        $fileServ = new FileService();
        $fileInfo = $fileServ->get($fileId);
        if (empty($fileInfo)) {
            E('_ERR_FILE_FOLDER_IS_NULL');
        }

        // 如果是文件，限定后缀修改范围
        if ($fileInfo['file_type'] == Constant::FILE_TYPE_IS_DOC) {
            $suffix_range = array_merge(Constant::ALLOW_UPLOAD_FILE_TYPE, Constant::ALLOW_UPLOAD_IMAGE_TYPE);
            $suffix = strtoupper(end(explode('.', $fileName)));
            if (!in_array($suffix, $suffix_range)) {
                E('_ERR_FILE_SUFFIX_OUT_RANGE');
            }
        }

        // 检查并计算新文件名
        $fileServ->checkFileName($fileName, $fileInfo['parent_id'], $fileInfo['file_type'], $fileId);

        // 更新文件名
        $fileServ->update($fileId, ['file_name' => $fileName]);

        $this->_result = ['file_name' => $fileName];
    }
}
