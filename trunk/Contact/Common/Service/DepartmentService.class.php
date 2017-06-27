<?php
/**
 * Created by IntelliJ IDEA.
 * User: zhuxun37
 * Date: 2017/5/16
 * Time: 下午6:28
 */

namespace Common\Service;


use Com\PythonExcel;
use Common\Common\Cache;
use Common\Common\Department;
use Common\Common\User;

class DepartmentService extends AbstractService
{

    protected $_fixTitles = array(
        'departmentPath' => '组织路径',
        'departmentName' => '组织名称',
        'dpSerialNum' => '组织编号',
        'dptId' => '组织类型',
        'dpLead' => '组织负责人'
    );

    // 构造方法
    public function __construct()
    {

        parent::__construct();
    }

    /**
     * 导入数据
     * @param $result
     * @param $department
     * @param $request
     * @return bool
     */
    public function prepareImport(&$result, &$department, $request)
    {

        $result['finish'] = 0;
        $index = (int)$request['index'];
        $title_id = (int)$request['title_id'];
        // 读取 title 和待导入数据
        $importDataService = new ImportDataService();
        $dataList = $importDataService->list_by_pks(array($title_id, $index));
        $dataList = array_combine_by_key($dataList, 'cid_id');

        // 获取组织类型
        $titleData = json_decode($dataList[$title_id]['data']);
        $departmentData = json_decode($dataList[$index]['data']);
        $result['title'] = $titleData;
        $result['data'] = $departmentData;
        $this->_getDepartmentType($type, $titleData, $departmentData);
        if (empty($type)) {
            E('1003:部门类型不存在');
            return false;
        }

        // 获取顶级部门, 确认顶级部门必须存在
        Department::instance()->get_top_dpId($topDpId);
        if (empty($topDpId)) {
            $result['finish'] = 1;
            E('1004:顶级组织不存在, 请先同步');
            return false;
        }

        // 重新整理组织数据
        list($titles, $configs) = $this->_generateTitleByType($type);
        $titleDataFlip = array_flip($titleData);
        $department = array(
            'extList' => array()
        );
        foreach ($titles as $_k => $_title) {
            $index = $titleDataFlip[$_title];
            $department[$_k] = $departmentData[$index];

            // 处理组织名称
            if ('departmentPath' == $_k) {
                $indexName = $titleDataFlip[$titles['departmentName']];
                $this->getDepartmentByPath($department, $department[$_k], $departmentData[$indexName]);
                unset($department[$_k]);
            } elseif ('departmentName' == $_k) { // 忽略部门名称
                unset($department[$_k]);
                continue;
            } elseif ('dpLead' == $_k) {
                $user = new User();
                $conds = array('memUsername' => $department[$_k]);
                $leadList = $user->listByConds($conds, 1, 2);
                if (1 == count($leadList['list'])) {
                    $department[$_k] = $leadList['list'][0]['memUid'];
                } else {
                    $department[$_k] = '';
                }
                continue;
            }

            // 处理组织类型
            if ('dptId' == $_k) {
                $department[$_k] = $type['dptId'];
            }

            // 如果不是扩展数据
            if (empty($configs[$_k])) {
                continue;
            }

            // 如果扩展信息为空
            if (empty($department[$_k])) {
                unset($department[$_k]);
                continue;
            }

            // 如果是 areaselect
            if ('select' == $configs[$_k]['dfcType']) { // 如果是 select
                foreach ($configs[$_k]['dfcValues'] as $_val) {
                    if ($_val['name'] == $department[$_k]) {
                        $department['extList'][$_k] = $_val['id'];
                        break;
                    }
                }
            } else {
                $department['extList'][$_k] = $department[$_k];
            }

            unset($department[$_k]);
        }

        // 重新整理地区数据
        foreach ($department['extList'] as $_k => $_val) {
            if ('areaselect' == $configs[$_k]['dfcType']) {
                $department['extList'][$_k] = rjson_encode(array(
                    'province' => $_val,
                    'city' => $department[$_k . 'city'],
                    'town' => $department[$_k . 'town']
                ));
                unset($department[$_k . 'city'], $department[$_k . 'town']);
            }
        }

        return true;
    }

    /**
     * 根据部门路径获取部门ID
     * @param $currentDepartment
     * @param $path
     * @param $name
     * @param $isEdit
     * @return bool
     */
    public function getDepartmentByPath(&$currentDepartment, $path, $name = '', $isEdit = true)
    {
        Department::instance()->get_top_dpId($topDpId);
        if (empty($topDpId)) {
            E('1004:顶级组织不存在, 请先同步');
        }
        // 获取部门路径上的所有部门名称
        if (!empty($name)) {
            $path = $path . '/' . $name;
        }
        $path = str_replace('\\', '/', $path);
        $path = preg_replace('/\/+/i', '/', $path);
        $dpNames = explode('/', $path);
        // 验证部门路径上的部门是否存在
        list($currentDpId, $allDepartment, $currentDepartment) =
            $this->verifyDepPath($currentDepartment, $dpNames, $topDpId);
        if (!empty($currentDepartment['dpParentid'])) {
            return true;
        }

        // 如果修改的是顶级部门, 则报错
        if ($isEdit && $currentDpId == $topDpId && 1 == count($dpNames)) {
            E('1006:顶级部门不允许更改');
        }

        $currentDepartment['dpId'] = $allDepartment[$currentDpId]['dpId'];
        $currentDepartment['dpParentid'] = $allDepartment[$currentDpId]['dpParentid'];
        $currentDepartment['dpName'] = $allDepartment[$currentDpId]['dpName'];
        return true;
    }

    /**
     * 根据部门名称从关联部门信息中获取部门ID
     * @param $dpId
     * @param $parentId
     * @param $dpName
     * @param $relateDps
     * @return bool
     */
    protected function _getDpIdFromRelateDps(&$dpId, $parentId, $dpName, $relateDps)
    {
        foreach ($relateDps as $_dp) {
            if ($parentId == $_dp['dpParentid'] && $dpName == $_dp['dpName']) {
                $dpId = $_dp['dpId'];
                return true;
            }
        }

        return false;
    }

    /**
     * 根据部门类型生成完整的 title
     * @param $type
     * @return array
     */
    protected function _generateTitleByType($type)
    {

        $titles = $this->_fixTitles;
        $fieldConfigs = Cache::instance()->get('Common.DepartmentFieldConfig');
        $configs = $fieldConfigs[$type['dptId']];
        foreach ($configs as $_config) {
            if ('areaselect' == $_config['dfcType']) {
                $titles[$_config['dfcId']] = $_config['dfcName'] . '(省)';
                $titles[$_config['dfcId'] . 'city'] = $_config['dfcName'] . '(市)';
                $titles[$_config['dfcId'] . 'town'] = $_config['dfcName'] . '(区)';
            } else {
                $titles[$_config['dfcId']] = $_config['dfcName'];
            }
        }

        return array($titles, $configs);
    }

    /**
     * 获取部门类型数据
     * @param $type
     * @param $title
     * @param $data
     * @return bool
     */
    protected function _getDepartmentType(&$type, $title, $data)
    {

        $type = array();
        $typeIndex = 0;
        foreach ($title as $_index => $_title) {
            if ('组织类型' == $_title) {
                $typeIndex = $_index;
                break;
            }
        }

        $typeName = $data[$typeIndex];
        $types = Cache::instance()->get('Common.DepartmentType');
        foreach ($types as $_type) {
            if ($typeName == $_type['dptName']) {
                $type = $_type;
                return true;
            }
        }

        return false;
    }

    /**
     * 读取 Excel 文件, 为导入做准备
     * @param $result
     * @param $request
     * @param $user
     * @return bool
     */
    public function readExcel(&$result, $request, $user)
    {

        // 分页和每页数
        $page = (int)$request['page'];
        $limit = (int)$request['limit'];
        $start = $limit * ($page - 1);
        $result = array(
            'rowsReaded' => 0,
            'page' => $page,
            'limit' => $limit
        );

        // 附件Url
        $attachmentUrl = $request['attachmentUrl'];
        //list($attachmentUrl,) = explode('?', $attachmentUrl);
        //$attachmentUrl = str_replace('http://t-rep.vchangyi.com/', '/Users/zhuxun37/web/javaDownloads/', $attachmentUrl);
        if (empty($attachmentUrl)) {
            E('1002:Excel文件地址为空');
            return false;
        }

        // 导入唯一标识
        $importFlag = $request['importFlag'];
        if (empty($importFlag)) {
            $importFlag = NOW_TIME . random(8);
        }
        $result['importFlag'] = $importFlag;

        // 获取附件
        $localFile = get_sitedir() . $importFlag . '.xls';
        if (!file_exists($localFile)) {
            file_put_contents($localFile, file_get_contents($attachmentUrl));
        }

        // 如果文件大小为 0
        if (0 >= filesize($localFile)) {
            E('1004:Excel文件错误或文件内容为空');
            return false;
        }

        $importDataService = new ImportDataService();
        // 读取指定行
        $data = PythonExcel::instance()->read($localFile, $start, $start + $limit);
        if (empty($data)) {
            return true;
        }

        $result['rowsReaded'] = count($data);
        // 如果是第一页, 则需要额外保存表头
        if (1 == $page) {
            $importDataService->insert(array(
                'ea_id' => $user['eaId'],
                'import_flag' => $importFlag,
                'data_type' => 'title',
                'data' => rjson_encode($data[0])
            ));
            unset($data[0]);
        }

        // 其他数据入库
        $insertData = array();
        foreach ($data as $_data) {
            $insertData[] = array(
                'ea_id' => $user['eaId'],
                'import_flag' => $importFlag,
                'data_type' => 'department',
                'data' => rjson_encode($_data)
            );
        }
        $importDataService->insert_all($insertData);

        return true;
    }

    // 导出模板
    public function exportTpl()
    {

        list($titles,) = $this->_generateTitle();

        $this->_forExportTitle($titles);
        $filename = NOW_TIME . random(8) . '.xls';
        PythonExcel::instance()->write(get_sitedir() . $filename, array_values($titles), array());

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename=departmentTpl.xls');
        header("Content-Transfer-Encoding:binary");
        echo file_get_contents(get_sitedir() . $filename);
        exit;
    }

    // 导出
    public function export()
    {

        // 生成标题
        list($titles, $mergeConfigs) = $this->_generateTitle();
        $departments = Department::instance()->listAll();

        $types = Cache::instance()->get('Common.DepartmentType');
        $configs = Cache::instance()->get('Common.DepartmentFieldConfig');
        $dfcId2config = array();
        foreach ($configs as $_config) {
            $dfcId2config = array_merge($dfcId2config, $_config);
        }

        // 读取部门负责人信息
        $uids = array_column($departments, 'dpLead');
        $memberList = User::instance()->listByUid($uids);

        // 组织 excel 数据
        $data = array();
        foreach ($departments as $_dp) {
            $row = array();
            // 剔除顶级部门
            if (false === stripos($_dp['departmentPath'], '/', 1)) {
                continue;
            }
            // 根据标题获取组织数据
            foreach ($titles as $_k => $_val) {
                // 组织类型数据
                if ('dptId' == $_k) {
                    $row[] = empty($_dp[$_k]) || empty($types[$_dp[$_k]]) ? '' : $types[$_dp[$_k]]['dptName'];
                    continue;
                } elseif ('departmentPath' == $_k) { // 如果是路径, 则需要拆分成两段
                    $paths = explode('/', $_dp[$_k]);
                    $dpName = array_pop($paths);
                    $row['departmentPath'] = implode('/', $paths);
                    $row['departmentName'] = $dpName;
                    continue;
                } elseif ('departmentName' == $_k) {
                    continue;
                } elseif ('dpLead' == $_k) {
                    $row['dbLead'] = empty($memberList[$_dp[$_k]]) ? '' : $memberList[$_dp[$_k]]['memUsername'];
                    continue;
                }

                // 如果当前键值对应的值为空并且需要合并处理
                if (empty($_dp[$_k]) && isset($mergeConfigs[$_k])) {
                    foreach ($mergeConfigs as $_dfcId) {
                        if (!empty($_dp[$_dfcId])) {
                            $_dp[$_k] = $_dp[$_dfcId];
                        }
                    }
                }

                // 地区数据特殊处理
                if (!empty($dfcId2config[$_k]) && !empty($_dp[$_k]) && 'areaselect' == $dfcId2config[$_k]['dfcType']) {
                    $pct = (array)$_dp[$_k];
                    $row[] = empty($pct['province']) ? '' : $pct['province'];
                    $row[] = empty($pct['city']) ? '' : $pct['city'];
                    $row[] = empty($pct['town']) ? '' : $pct['town'];
                    continue;
                }

                // 选项数据特殊处理
                if (!empty($dfcId2config[$_k]) && !empty($_dp[$_k]) && 'select' == $dfcId2config[$_k]['dfcType']) {
                    $config = $dfcId2config[$_k];
                    $configVals = array_combine_by_key($config['dfcValues'], 'id');
                    $row[$_k] = empty($_dp[$_k]) ? '' : $configVals[$_dp[$_k]]['name'];
                    continue;
                }

                $row[$_k] = empty($_dp[$_k]) ? '' : $_dp[$_k];
            }

            $data[] = array_values($row);
        }

        $this->_forExportTitle($titles);
        $filename = rgmdate(NOW_TIME, 'Y') . rgmdate(NOW_TIME, 'm') . rgmdate(NOW_TIME, 'd') . '_组织列表';
        $filename = $filename  . '.xls';
        PythonExcel::instance()->write(get_sitedir() . $filename, array_values($titles), $data);

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl;charset=UTF-8");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename="' . $filename . '"');

        // 根据浏览器类型来判断是否需要特殊处理中文字符
        $encoded_filename = urlencode($filename);
        $encoded_filename = str_replace("+", "%20", $encoded_filename);
        $ua = $_SERVER["HTTP_USER_AGENT"];
        if (preg_match("/MSIE/", $ua) || preg_match("/rv:11.0/", $ua) || preg_match("/Edge/", $ua)) {
            header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
        } else if (preg_match("/Firefox/", $ua)) {
            header('Content-Disposition: attachment; filename*="utf8\'\'' . $filename . '"');
        } else {
            header('Content-Disposition: attachment; filename="' . $filename . '"');
        }

        header("Content-Transfer-Encoding:binary");
        echo file_get_contents(get_sitedir() . $filename);
        exit;
    }

    /**
     * 整理输出头信息
     * @param $titles
     * @return bool
     */
    protected function _forExportTitle(&$titles)
    {

        $configs = Cache::instance()->get('Common.DepartmentFieldConfig');
        // 必填
        $titles['dptId'] = array(
            'value' => $titles['dptId'],
            'pattern' => array('pattern' => 'solid', 'fore_colour' => 'red')
        );
        $titles['departmentPath'] = array(
            'value' => $titles['departmentPath'],
            'pattern' => array('pattern' => 'solid', 'fore_colour' => 'red')
        );
        $titles['departmentName'] = array(
            'value' => $titles['departmentName'],
            'pattern' => array('pattern' => 'solid', 'fore_colour' => 'red')
        );
        foreach ($titles as $_k => $_title) {
            if (!empty($configs[$_k]) && 0 < $configs[$_k]['dfcRequire']) {
                $titles[$_k] = array(
                    'value' => $_title,
                    'pattern' => array('pattern' => 'solid', 'fore_colour' => 'red')
                );
            }
        }

        return true;
    }

    /**
     * 生成 Excel 标题
     * @return array
     */
    protected function _generateTitle()
    {

        $titles = $this->_fixTitles;
        // 如果有同名字段, 则记录合并
        $mergeConfigs = array();
        $name2id = array();

        $fieldConfigs = Cache::instance()->DepartmentFieldConfig();
        foreach ($fieldConfigs as $_dptId => $_configs) {
            foreach ($_configs as $_cfg) {
                if (isset($name2id[$_cfg['dfcName']])) {
                    $sourceId = $name2id[$_cfg['dfcName']];
                    if (!isset($mergeConfigs[$sourceId])) {
                        $mergeConfigs[$sourceId] = array();
                    }

                    $mergeConfigs[$sourceId][] = $_cfg['dfcId'];
                    continue;
                }

                // 如果是地区类型字段, 则拆成3列
                if ('areaselect' == $_cfg['dfcType']) {
                    $titles[$_cfg['dfcId']] = $_cfg['dfcName'] . '(省)';
                    $titles[$_cfg['dfcId'] . 'city'] = $_cfg['dfcName'] . '(市)';
                    $titles[$_cfg['dfcId'] . 'town'] = $_cfg['dfcName'] . '(区)';
                } else {
                    $titles[$_cfg['dfcId']] = $_cfg['dfcName'];
                }
                $name2id[$_cfg['dfcName']] = $_cfg['dfcId'];
            }
        }

        return array($titles, $mergeConfigs);
    }

    /**
     * 生成默认数据
     * @param $titles
     * @return array
     */
    protected function _generateDefault($titles)
    {

        $data = array();
        foreach ($titles as $_k => $val) {
            $data[$_k] = '';
        }

        return $data;
    }


    /**
     * 获取扩展信息, 并验证
     * @param $ext
     * @param $dptId
     * @param $request
     * @return bool
     */
    public function getExt(&$ext, $dptId, $request)
    {

        $ext = array();
        $configs = Cache::instance()->get('Common.DepartmentFieldConfig');
        foreach ($configs[$dptId] as $_config) {
            $val = $request[$_config['dfcId']];

            // 如果是地区, 省/市/区三级不全, 则视为未填
            if ('areaselect' == $_config['dfcType']) {
                if (empty($val['province']) || empty($val['city']) || empty($val['town'])) {
                    $val = array(
                        'province' => '',
                        'city' => '',
                        'town' => ''
                    );
                }
            }

            // 必填检查, 注意地区数据判断
            if (0 != $_config['dfcRequire'] && (empty($val) || (isset($val['province']) && empty($val['province'])))) {
                E(L('1001:{$name}不能为空', array('name' => $_config['dfcName'])));
                return false;
            }

            // 字符串验证
            if ('string' == $_config['dfcType']) {
                if (0 < $_config['dfcMin'] && count($val) < $_config['dfcMin']) {
                    E(L('1002:{$name}长度不能小于{$min}', array('name' => $_config['dfcName'], 'min' => $_config['dfcMin'])));
                    return false;
                }
                if (0 < $_config['dfcMax'] && count($val) > $_config['dfcMax']) {
                    E(L('1002:{$name}长度不能大于{$max}', array('name' => $_config['dfcName'], 'max' => $_config['dfcMax'])));
                    return false;
                }
            }

            // 数字验证
            if ('number' == $_config['dfcType']) {
                if (0 < $_config['dfcMin'] && $val < $_config['dfcMin']) {
                    E(L('1002:{$name}不能小于{$min}', array('name' => $_config['dfcName'], 'min' => $_config['dfcMin'])));
                    return false;
                }
                if (0 < $_config['dfcMax'] && val < $_config['dfcMax']) {
                    E(L('1002:{$name}不能大于{$max}', array('name' => $_config['dfcName'], 'max' => $_config['dfcMax'])));
                    return false;
                }
            }

            $ext[$_config['dfcId']] = 'areaselect' == $_config['dfcType'] ? rjson_encode($val) : $val;
        }

        return true;
    }

    /**
     * 验证部门路径上的部门是否存在
     * @param      $currentDepartment array 当前部门
     * @param      $dpNames array 所有部门路径名称
     * @param      $topDpId string 顶级部门ID
     * @param bool $isRepeat 是否重新获取进来 的标识
     * @return array|bool
     */
    protected function verifyDepPath($currentDepartment, $dpNames, $topDpId, $isRepeat = false)
    {
        $depServ = new Department();
        $allDepartment = $depServ->listAll();

        // 获取部门路径内名称相同的部门数据
        $relateDps = array();
        foreach ($allDepartment as $_dp) {
            if (in_array($_dp['dpName'], $dpNames)) {
                $relateDps[] = $_dp;
            }
        }

        // 从一级部门开始
        $currentDpId = $topDpId;
        foreach ($dpNames as $_index => $_name) {
            // 判断一级组织是否存在
            if (0 == $_index && $_name == $allDepartment[$topDpId]['dpName']) {
                $currentDpId = $topDpId;
                continue;
            }

            // 获取名称对应的部门ID
            if (!$this->_getDpIdFromRelateDps($currentDpId, $currentDpId, $_name, $relateDps)) {
                if ($_index + 1 == count($dpNames)) {
                    $currentDepartment['dpParentid'] = $currentDpId;
                    $currentDepartment['dpName'] = $_name;

                    return [$currentDpId, $allDepartment, $currentDepartment];
                }

                // 重新获取 进来的 直接返回, 防止死循环
                if ($isRepeat) {
                    return false;
                }
                // 按照部门路径的长度 进行重新获取部门
                for ($i = 0; $i < count($dpNames); $i ++) {
                    // 等待上级部门创建
                    sleep(2);
                    $verifyDepPathReturn = $this->verifyDepPath($currentDepartment, $dpNames, $topDpId, true);
                    if (false !== $verifyDepPathReturn) {
                        return $verifyDepPathReturn;
                    }
                }
                E('1005:部门层级错误');
            }
        }

        return [$currentDpId, $allDepartment, $currentDepartment];
    }
}
