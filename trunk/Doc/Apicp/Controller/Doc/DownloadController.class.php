<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017-5-18
 * Time: 14:07:02
 */
namespace Apicp\Controller\Doc;

use Common\Common\Constant;
use Common\Service\FileService;

class DownloadController extends \Apicp\Controller\AbstractController
{
    /**
     * Download
     * @author liyifei
     * @desc 文件下载接口
     * @param Int file_id:true 文件ID
     * @return void
     */
    public function Index_get()
    {
        $fileId = I('get.file_id', 0, 'intval');
        if (!$fileId) {
            E('_ERR_FILE_PARAM_IS_NULL');
        }

        $fileServ = new FileService();
        $file = $fileServ->get($fileId);
        if (empty($file)) {
            E('_ERR_FILE_SELECT_IS_NULL');
        }

        if ($file['file_type'] != Constant::FILE_TYPE_IS_DOC) {
            E('_ERR_FILE_FOLDER_UNALLOW_DOWN');
        }

        $content = file_get_contents($file['at_url']);

        header('Content-type: application/octet-stream;charset=utf-8');
        header('Accept-Ranges: bytes');
        header("Accept-Length: {$file['at_size']}");
        header("Content-Disposition: attachment; filename={$file['file_name']}");

        exit($content);
    }
}
