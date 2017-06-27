<?php
/**
 * Created by IntelliJ IDEA.
 * User: zhuxun37
 * Date: 2017/5/17
 * Time: 上午10:00
 */

namespace Apicp\Controller\Department;


use Common\Service\DepartmentService;

class DownTplController extends AbstractController
{

    public function Index_get() {

        $departmentService = new DepartmentService();
        $departmentService->exportTpl();

        return true;
    }

}