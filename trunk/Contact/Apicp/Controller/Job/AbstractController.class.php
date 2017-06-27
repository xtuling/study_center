<?php
/**
 * 职位操作基类
 * User: zhuxun37
 * Date: 17/5/11
 * Time: 15:21
 */

namespace Apicp\Controller\Job;

abstract class AbstractController extends \Common\Controller\Apicp\AbstractController
{
    /**
     * 清理职位缓存
     * @return bool
     */
    protected function clearJobCache()
    {
        clear_sys_cache(['Common.Job']);

        return true;
    }
}
