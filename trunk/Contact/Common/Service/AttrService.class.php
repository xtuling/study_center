<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/9/18
 * Time: 14:37
 */

namespace Common\Service;

use VcySDK\Service;
use VcySDK\Member;
use Common\Model\AttrModel;

class AttrService extends AbstractService
{

    /**
     * 是否存在自定义信息:是
     */
    const ATTR_CUSTOM_IS_TRUE = 1;

    /**
     * 是否存在自定义信息:否
     */
    const ATTR_CUSTOM_IS_FALSE = 0;

    // 构造方法
    public function __construct()
    {

        parent::__construct();
        $this->_d = new AttrModel();
    }

    /**
     * 交换两个属性的顺序
     * @author liyifei
     * @param int $attrId1 第一个属性ID
     * @param int $attrId2 第二个属性ID
     * @return bool
     */
    public function exchangeOrder($attrId1, $attrId2)
    {

        $attrData1 = $this->_d->get_by_conds(['attr_id' => $attrId1]);
        $attrData2 = $this->_d->get_by_conds(['attr_id' => $attrId2]);
        if (empty($attrData1) || empty($attrData2)) {
            E('_ERR_ATTR_UNDEFINED');
        }

        // 交换属性顺序
        $result1 = $this->_d->update_by_conds(['attr_id' => $attrId1], ['`order`' => $attrData2['order']]);
        if (!$result1) {
            E('_ERR_OPERATE_FIELD');
        }
        $result2 = $this->_d->update_by_conds(['attr_id' => $attrId2], ['`order`' => $attrData1['order']]);
        if (!$result2) {
            E('_ERR_OPERATE_FIELD');
        }

        return true;
    }

    /**
     * 修改属性
     * @author liyifei
     * @param int $attrId 属性ID
     * @param array $params 属性参数
     *      + string option 属性选项值
     *      + string attr_name 属性名称
     *      + int is_open 是否有效
     *      + int is_show 是否前端显示
     * @return int|bool
     */
    public function updateAttrById($attrId, $params)
    {

        // 该属性是否存在
        $data = $this->get($attrId);
        if (empty($data)) {
            E('_ERR_ATTR_UNDEFINED');
        }

        // 属性类型不允许编辑
//        unset($params['type']);

        // 系统属性的属性名、显示顺序、是否开启,三个属性值不可编辑
        /**if ($data['is_system'] == AttrModel::IS_SYSTEM_TRUE) {
            unset($params['order'], $params['is_open'], $params['attr_name']);
        }*/
        if (0 == $data['is_open_edit']) {
            unset($params['is_open']);
        }
        if (0 == $data['is_open_cp_edit']) {
            unset($params['is_open_cp']);
        }
        if (0 == $data['is_required_edit']) {
            unset($params['is_required']);
        }
        if (0 == $data['is_required_cp_edit']) {
            unset($params['is_required_cp']);
        }
        if (0 == $data['is_show_edit']) {
            unset($params['is_show']);
        }

        $return = $this->update_by_conds(['attr_id' => $attrId], $params);
        return $return;
    }

    /**
     * 创建属性
     * @author liyifei
     * @param array $params 属性参数
     *      + string option 属性选项值
     *      + string attr_name 属性名称
     *      + int type 字段类型
     *      + int is_open 是否有效
     *      + int is_show 是否前端显示
     * @return int|bool|mixed
     */
    public function addAttr($params)
    {

        // 参数错误
        if (empty($params['type']) || empty($params['attr_name'])) {
            E('_ERR_PARAM_FORMAT');
        }

        // 属性类型是否存在
        $types = $this->getAllAttrType([AttrModel::ATTR_TYPE_SPECIAL, AttrModel::ATTR_TYPE_LEADER]);
        if (isset($params['type']) && !in_array($params['type'], $types)) {
            E('_ERR_ATTR_TYPE_UNDEFINED');
        }

        // 属性类型为单选、下拉框单选、多选时,选项option必须有值
        $selectType = [
            AttrModel::ATTR_TYPE_RADIO,
            AttrModel::ATTR_TYPE_DROPBOX,
            AttrModel::ATTR_TYPE_CHECKBOX
        ];
        if (isset($params['type']) && in_array($params['type'], $selectType) && !isset($params['option'])) {
            E('_ERR_ATTR_VALUE_IS_EMPTY');
        }

        // 属性名是否重复
        $data = $this->get_by_conds(['attr_name' => $params['attr_name']]);
        if ($data) {
            E('_ERR_ATTR_NAME_IS_REPEAT');
        }

        // 显示顺序字段自动递增
        $data = $this->_d->getMaxOrder();
        if (isset($data['max_order'])) {
            $params['order'] = $data['max_order'] + 1;
        } else {
            $params['order'] = 1;
        }

        // 获取架构可用预留字段
        $allowFields = [
            'custom1',
            'custom2',
            'custom3',
            'custom4',
            'custom5',
            'custom6',
            'custom7',
            'custom8',
            'custom9',
            'custom10'
        ];
        $result = $this->_d->getUsedField($allowFields);
        $usedFields = array_column($result, 'field_name');
        $fields = array_diff($allowFields, $usedFields);
        if (empty($fields)) {
            E('_ERR_FIELD_IS_USED');
        }
        $params['field_name'] = current($fields);

        // 自定义属性均可编辑
        $params['is_open_edit'] = AttrModel::ATTR_IS_EDIT_TRUE;
        $params['is_required_edit'] = AttrModel::ATTR_IS_EDIT_TRUE;
        $params['is_show_edit'] = AttrModel::ATTR_IS_EDIT_TRUE;

        // 自定义属性均显示于身份信息区域
        $params['postion'] = AttrModel::AREA_CUSTOM;

        $return = $this->insert($params);
        return $return;
    }

    /**
     * 删除自定义属性
     * @author liyifei
     * @param int $fieldName 自定义属性字段名
     * @return mixed
     */
    public function deleteAttr($fieldName)
    {

        // 清空所有用户该属性值(自定义属性)
        $memServ = new Member(Service::instance());
        $memServ->delAllExt(['extKey' => $fieldName]);

        // 执行本地数据删除操作
        $result = $this->delete_by_conds(['field_name' => $fieldName]);
        if (!$result) {
            E('_ERR_OPERATE_FIELD');
        }

        return true;
    }

    /**
     * 验证日期时间类型
     * @author liyifei
     * @param string $dateTime 日期时间(标准格式:2016-09-09 12:09:59或2016/09/09 12:09:59)
     * @return bool
     */
    public function checkDateTime($dateTime)
    {

        if (!preg_match('/^\d{4}\/\d{2}\/\d{2} \d{2}:\d{2}:\d{2}$/', $dateTime) && !preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $dateTime)) {
            return false;
        }

        $arr = explode(' ', $dateTime);
        if (count($arr) != 2) {
            return false;
        }

        $resData = $this->checkDate($arr[0]);
        $resTime = $this->checkTime($arr[1]);

        if (!$resData || !$resTime) {
            return false;
        }

        return true;
    }

    /**
     * 验证日期类型
     * @author liyifei
     * @param string $date 日期(标准格式:2016-09-09或2016/09/09或2016-9-9或2016/9/9或20160909)
     * @return bool
     */
    public function checkDate($date)
    {

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) && !preg_match('/^\d{4}\/\d{2}\/\d{2}$/', $date) && !preg_match('/^\d{4}-\d{1}-\d{1}$/', $date) && !preg_match('/^\d{4}\/\d{1}\/\d{1}$/', $date)) {
            // 验证20160909类型日期
            $year = substr($date, 0, 4);
            $month = substr($date, 4, 2);
            $day = substr($date, 6, 2);
            if (checkdate($month, $day, $year)) {
                return true;
            } else {
                return false;
            }
        }

        if (strpos($date, '/') !== false) {
            $flag = '/';
        } elseif (strpos($date, '-') !== false) {
            $flag = '-';
        } else {
            return false;
        }

        $arr = explode($flag, $date);
        if (count($arr) != 3) {
            return false;
        }

        if (!checkdate($arr[1], $arr[2], $arr[0])) {
            return false;
        }

        return true;
    }

    /**
     * 验证时间类型
     * @author liyifei
     * @param string $time 时间(标准格式:12:09:59)
     * @return bool
     */
    public function checkTime($time)
    {

        if (!preg_match('/^\d{2}:\d{2}:\d{2}$/', $time)) {
            return false;
        }

        $arr = explode(':', $time);
        if (count($arr) != 3) {
            return false;
        }

        if (intval($arr[0]) > 24 || intval($arr[1]) > 59 || intval($arr[1]) < 0 || intval($arr[2]) > 59 || intval($arr[2]) < 0) {
            return false;
        }

        return true;
    }
}
