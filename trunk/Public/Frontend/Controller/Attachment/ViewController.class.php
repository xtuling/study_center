<?php

/**
 * 附件【图片】展示
 * Created by PhpStorm.
 * User: mr.song
 * Date: 2016/7/22
 * Time: 16:33
 */
namespace Frontend\Controller\Attachment;

use Com\Attachment;
use Common\Controller\Frontend\AbstractController;

class ViewController extends AbstractController
{

    public $_require_login = false;

    public function Index()
    {

        $atid = I("get.atid");
        $attachment = Attachment::instance();

        // 获取图片
        $url = $attachment->getAttachUrl($atid);
        $image = file_get_contents($url);

        // 输出
        header('Content-type: image/png');
        echo $image;
    }
}
