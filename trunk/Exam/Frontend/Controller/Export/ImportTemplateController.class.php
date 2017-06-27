<?php
/**
 * 下载考试导入模板
 * ImportTemplateController.class.php
 * User: 何岳龙
 * Date: 2017-05-23
 */
namespace Frontend\Controller\Export;

class ImportTemplateController extends \Common\Controller\Frontend\AbstractController
{

    protected $_require_login = false;

    public function Index()
    {
        $data_path = APP_PATH . 'Data' . D_S . 'Import.xls';
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=试题导入模板.xls');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($data_path));
        readfile($data_path);
    }
}
