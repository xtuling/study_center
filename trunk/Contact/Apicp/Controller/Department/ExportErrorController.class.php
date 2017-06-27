<?php
/**
 * Created by IntelliJ IDEA.
 * User: zhuxun37
 * Date: 2017/5/16
 * Time: 下午6:12
 */

namespace Apicp\Controller\Department;

use Common\Service\ImportDataService;

class ExportErrorController extends AbstractController
{

    public function Index_get()
    {

        $importDataService = new ImportDataService();
        $importDataService->exportError(I('get.'), $this->_login->user);

        return true;
    }

}