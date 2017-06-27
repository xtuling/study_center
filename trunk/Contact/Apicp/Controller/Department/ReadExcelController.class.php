<?php
/**
 * Created by IntelliJ IDEA.
 * User: zhuxun37
 * Date: 2017/5/17
 * Time: 上午11:22
 */

namespace Apicp\Controller\Department;


use Common\Service\DepartmentService;
use Common\Service\ImportDataService;

class ReadExcelController extends AbstractController
{

    public function Index_post()
    {

        $departmentService = new DepartmentService();
        $departmentService->readExcel($this->_result, I('post.'), $this->_login->user);

        // 如果读取的行数小于 limit 值, 则说明已读取完毕
        if ($this->_result['rowsReaded'] < $this->_result['limit']) {
            $importDataService = new ImportDataService();
            $condition = array(
                'ea_id' => $this->_login->user['eaId'],
                'import_flag' => $this->_result['importFlag']
            );
            $orderOption = array('cid_id' => 'ASC');
            $rows = $importDataService->list_by_conds($condition, null, $orderOption, 'cid_id');
            $this->_result['cidIds'] = array();
            foreach ($rows as $_row) {
                $this->_result['cidIds'][] = $_row['cid_id'];
            }
        }

        return true;
    }

}