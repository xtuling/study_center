<?php
/**
 * IndexController.class.php
 * 【同事圈-手机端】入口文件
 * User: heyuelong
 * Date:2017年4月27日14:52:14
 */
namespace Frontend\Controller\Index;

class IndexController extends \Common\Controller\Frontend\AbstractController
{

    public function Index()
    {
        redirectFront('/app/page/workmate/workmate-home', array('_identifier' => APP_IDENTIFIER));
    }
}
