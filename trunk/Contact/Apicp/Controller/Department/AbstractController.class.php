<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/9/17
 * Time: 22:21
 */

namespace Apicp\Controller\Department;

abstract class AbstractController extends \Common\Controller\Apicp\AbstractController
{
    /**
     * 清理部门缓存
     * @return bool
     */
    protected function clearDepartmentCache()
    {
        clear_sys_cache(['Common.Department']);

        return true;
    }
}
