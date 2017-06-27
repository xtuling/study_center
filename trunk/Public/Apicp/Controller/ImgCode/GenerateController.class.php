<?php
/**
 * 获取图片验证码
 * Created by PhpStorm.
 *
 */

namespace Apicp\Controller\ImgCode;

use Common\Common\ImgCode;

class GenerateController extends AbstractController
{

    public function Index()
    {

        // 获取验证码宽度
        $width = I("post.width", 200);
        // 获取验证码高度
        $height = I("post.height", 50);
        // 获取字体大小
        $fontSize = I("post.fontSize", 40);

        // 获取图片验证码信息
        $this->_result = ImgCode::instance()->generate($width, $height, $fontSize);

        return true;
    }


}
