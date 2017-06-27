<?php
/**
 * Created by IntelliJ IDEA.
 * User: zhuxun37
 * Date: 2017/5/16
 * Time: 下午6:12
 */

namespace Apicp\Controller\Department;

use Common\Service\DepartmentService;

class ExportController extends AbstractController
{

    public function Index_get()
    {

        $departmentService = new DepartmentService();
        $departmentService->export();

        return true;
    }

}