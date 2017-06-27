<?php
/**
 * Created by IntelliJ IDEA.
 * User: zhuxun37
 * Date: 2017/5/17
 * Time: 上午11:45
 */

namespace Common\Service;


use Com\PythonExcel;
use Common\Model\ImportDataModel;

class ImportDataService extends AbstractService
{

    // 构造方法
    public function __construct()
    {

        parent::__construct();
        $this->_d = new ImportDataModel();
    }

    // 导出模板
    public function exportError($request, $user)
    {

        $importFlag = (string)$request['importFlag'];
        $condition = array(
            'import_flag' => $importFlag,
            'ea_id' => $user['eaId'],
            'is_error' => ImportDataModel::IS_ERROR_TYPE_ERROR
        );
        $data = $this->_d->list_by_conds($condition, null, array('cid_id' => 'ASC'));
        $titles = array();
        $rows = array();
        foreach ($data as $_data) {
            if (empty($titles)) {
                $titles = json_decode($_data['data']);
                continue;
            }

            $rows[] = json_decode($_data['data']);
        }
        $filename = NOW_TIME . random(8) . '.xls';
        PythonExcel::instance()->write(get_sitedir() . $filename, $titles, $rows);

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename=importError.xls');
        header("Content-Transfer-Encoding:binary");
        echo file_get_contents(get_sitedir() . $filename);
        exit;
    }

}