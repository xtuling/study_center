<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/6/2
 * Time: 17:08
 */
namespace Apicp\Controller\Doc;

use Common\Service\ChunkService;

class UploadCancelController extends \Apicp\Controller\AbstractController
{
    /**
     * UploadCancel
     * @author liyifei
     * @desc 取消文件上传接口
     * @param Array file_keys:true 文件唯一标识
     * @return bool
     */
    public function Index_post()
    {
        $fileKeys = I('post.file_keys');
        if (empty($fileKeys) || !is_array($fileKeys)) {
            E('_ERR_FILE_PARAM_IS_NULL');
        }

        // 删除数据库分片数据
        $chunkServ = new ChunkService();
        $chunkServ->delete_by_conds(['file_key' => $fileKeys]);

        // 删除本地文件分片
        $chunkServ->deleteFile($fileKeys);
    }
}
