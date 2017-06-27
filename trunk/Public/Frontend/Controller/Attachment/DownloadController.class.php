<?php
/**
 * 单个附件下载
 * Created by PhpStorm.
 * User: mr.song
 * Date: 2016/7/22
 * Time: 16:18
 */
namespace Frontend\Controller\Attachment;

use Com\Attachment;
use Common\Controller\Frontend\AbstractController;

class DownloadController extends AbstractController
{

    protected $_require_login = false;

    public function Index()
    {

        $atId = I("get.atid");
        $attach = Attachment::instance();
        $attach->downloadAttachmentByAid($atId);
    }
}
