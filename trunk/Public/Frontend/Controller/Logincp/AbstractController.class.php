<?php
/**
 * Created by PhpStorm.
 * 应用回调控制层
 * User: zhoutao
 * Date: 16/7/14
 * Time: 下午2:56
 */

namespace Frontend\Controller\Logincp;

abstract class AbstractController extends \Common\Controller\Frontend\AbstractController
{

    // 是否必须登录
    protected $_require_login = false;

}
