<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/9/17
 * Time: 22:21
 */
namespace Apicp\Controller\User;

abstract class AbstractController extends \Common\Controller\Apicp\AbstractController
{
    /**
     * 清理人员缓存
     * @return bool
     */
    protected function clearUserCache()
    {
        clear_sys_cache(['Common.User']);

        return true;
    }
}
