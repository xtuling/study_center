<?php
/**
 * Created by IntelliJ IDEA.
 * User: zhuxun37
 * Date: 16/10/13
 * Time: 上午11:23
 */

namespace Frontend\Controller\Member;


use Org\Util\Image;

class WatermarkController extends AbstractController
{

    // 不需要登录
    protected $_require_login = false;

    public function Index()
    {

        $color_r = I('get.color_r', 0, 'intval');
        $color_g = I('get.color_g', 0, 'intval');
        $color_b = I('get.color_b', 0, 'intval');


        $angle = I('get.angle', 30);
        $width = I('get.width', 20);
        $height = I('get.height', 20);
        $fontSize = I('get.fontSize', 12);

        if (empty($this->_login->user) || empty($this->_login->user['memUsername'])) {
            $wmstring = $this->config['sitename'];
        } else {
            $wmstring = $this->_login->user['memUsername'];
        }

        Image::buildString($wmstring, array($color_r, $color_g, $color_b), '', 'png', 0, $angle, $height, $width, $fontSize, true);
        return true;
    }

}
