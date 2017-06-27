<?php
/**
 * Created by IntelliJ IDEA.
 * User: zhuxun37
 * Date: 2017/5/16
 * Time: 上午7:46
 */

namespace Apicp\Controller\Department;


use Common\Common\Cache;

class ListFieldConfigController extends AbstractController
{

    public function Index_post()
    {

        $this->_result = Cache::instance()->DepartmentFieldConfig();

        return true;
    }

}