<?php
/**
 * Created by IntelliJ IDEA.
 * User: zhuxun37
 * Date: 2017/5/17
 * Time: 下午1:42
 */

namespace Apicp\Controller\Department;

use Common\Service\DepartmentService;
use Common\Service\DeptRightService;
use Common\Service\ImportDataService;

class ImportController extends AbstractController
{
    public function Index_post()
    {

        try {
            $departmentService = new DepartmentService();
            $department = array();
            $departmentService->prepareImport($this->_result, $department, I('post.'));

            $department['isAll'] = 1;
            $department['isDept'] = 1;
            $department['dpIds'] = array();
            $department['departmentExtJson'] = rjson_encode($department['extList'], JSON_FORCE_OBJECT);
            // 部门数据入库
            $dpRightServ = new DeptRightService();
            if (empty($department['dpId'])) {
                $dpRightServ->createDp($department);
            } else {
                $dpRightServ->updateDp($department['dpId'], $department, false);
            }

            $this->clearDepartmentCache();
        } catch (\Exception $e) {
            // 如果读取数据正常, 则抛错
            if (!empty($this->_result['data'])) {
                $importDataService = new ImportDataService();
                $importDataService->update((int)I('post.index'), array('is_error' => 1));
            }

            E($e->getCode() . ":" . $e->getMessage());
        }

        return true;
    }

}
