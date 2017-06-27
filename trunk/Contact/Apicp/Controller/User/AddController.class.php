<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/10/21
 * Time: 16:44
 */
namespace Apicp\Controller\User;

use VcySDK\Member;
use VcySDK\Service;
use VcySDK\Department;
use Common\Model\AttrModel;
use Common\Service\AttrService;

class AddController extends AbstractController
{

    /**
     * 【通讯录】人员批量导入-新增员工接口(遇到必填却不支持导入的属性时,忽略验证)
     * @author liyifei
     */
    public function Index_post()
    {
        $list = I('post.list');
        if (empty($list)) {
            E('_ERR_PARAM_UNDEFINED');
        }

        // 所有开启的属性
        $attrServ = new AttrService();
        $attrs = $attrServ->getAttrList(true, array(), true);

        // 将属性信息数组有索引数组转为以属性字段为键的关联数组
        $newAttrs = array();
        foreach ($attrs as $key => $v) {
            $newAttrs[$v['field_name']] = $v;
        }

        // 错误人员信息
        $errTag = false;
        $errData = [];
        // 格式化人员属性值并添加为内部人员
        $data = [];
        // 在职状态
        $memActive = null;
        // 离职日期
        $leaveDate = null;

        foreach ($list as $k => $v) {
            // 属性值
            $value = $v['attr_value'];
            // 属性详情信息
            $attr = $newAttrs[$v['field_name']];
            // 存储错误人员信息(用户原始数据)
            $errData[$k] = $v;
            $errData[$k]['type'] = intval($attr['type']);
            $errData[$k]['attr_name'] = $attr['attr_name'];
            $errData[$k]['is_required'] = $attr['is_required'];
            // 重置错误信息(前端再次提交时使用)
            unset($errData[$k]['error_msg']);

            // 图片、地址、直属上级等不保存
            $types = [
                AttrModel::ATTR_TYPE_ADDRESS,
                AttrModel::ATTR_TYPE_PICTURE,
                AttrModel::ATTR_TYPE_LEADER,
            ];
            if (in_array($attr['type'], $types)) {
                continue;
            }

            // 在职状态赋值
            if ($v['field_name'] == 'memActive') {
                $memActive = $value;
            }
            // 离职日期赋值
            if ($v['field_name'] == 'leaveDate') {
                $leaveDate = $value;
            }

            // 必填属性(必填属性、非特殊属性、未填写属性值时,报错)
            if ($attr['is_required'] == AttrModel::ATTR_IS_REQUIRED_TRUE && $value === '') {
                // 单选、下拉框单选、多选为必填,且用户未填写时,将所有选项option写入返回值
                if ($attr['type'] == AttrModel::ATTR_TYPE_RADIO || $attr['type'] == AttrModel::ATTR_TYPE_DROPBOX || $attr['type'] == AttrModel::ATTR_TYPE_CHECKBOX) {
                    $errData[$k]['option'] = $attr['option'];
                }
                $errTag = true;
                $errData[$k]['error_msg'] = "必填项不可为空";
                continue;
            }

            // 验证姓名
            if ($v['field_name'] == 'memUsername' && (mb_strlen($v['attr_value'], 'utf8') < 2 || mb_strlen($v['attr_value'], 'utf8') > 32)) {
                $errTag = true;
                $errData[$k]['error_msg'] = "姓名长度不正确";
                continue;
            }

            // 验证手机号
            if ($v['field_name'] == 'memMobile' && strlen($v['attr_value']) != 11) {
                $errTag = true;
                $errData[$k]['error_msg'] = "手机号格式不正确";
                continue;
            }

            // 验证邮箱
            if ($v['memEmail'] && $v['field_name'] == 'memEmail' && !preg_match('/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/', $value)) {
                $errTag = true;
                $errData[$k]['error_msg'] = "邮箱格式不正确";
                continue;
            }

            // 验证账号(规则:1~32位字符、仅可用大小写字母、数字、下划线)
            if ($v['attr_value'] !== '' && $v['field_name'] == 'memUserid' && !preg_match('/^[a-zA-Z0-9_]{1,32}$/', $value)) {
                $errTag = true;
                $errData[$k]['error_msg'] = "账号格式不正确";
                continue;
            }

            // 根据部门属性,取出部门信息
            if ($v['field_name'] == 'dpName') {
                // 以";"号为分隔符,根据分级部门名称查询部门ID
                $dpDir = explode(';', $v['attr_value']);
                foreach ($dpDir as $dir) {
                    $nameList = explode('/', $dir);
                    $dpId = $this->eachDept($nameList);
                    if (!$dpId) {
                        $errTag = true;
                        $errData[$k]['error_msg'] = "未找到组织信息";
                        break;
                    }
                    $data['dpIdList'][] = $dpId;
                }
                // 跳出当前循环,不可将部门dbName属性放入请求架构接口的参数中!!!
                continue;
            }

            // 根据属性类型,验证和格式化属性值
            if ($value !== '') {
                switch ($attr['type']) {
                    // 日期类型(标准格式: 2016-09-09 或 2016/09/09)
                    case AttrModel::ATTR_TYPE_DATE:
                        $resDate = $attrServ->checkDate($value);
                        if (!$resDate) {
                            $errTag = true;
                            $errData[$k]['error_msg'] = $attr['attr_name'] . "-格式不正确";
                            break;
                        }
                        // 属性值转为毫秒级时间戳
                        $value = strtotime($value) * 1000;
                        break;

                    // 日期时间类型(标准格式: 2016-09-09 09:09:09 或 2016/09/09 09:09:09)
                    case AttrModel::ATTR_TYPE_DATE_TIME:
                        $res = $attrServ->checkDateTime($value);
                        if (!$res) {
                            $errTag = true;
                            $errData[$k]['error_msg'] = $attr['attr_name'] . "-格式不正确";
                            break;
                        }
                        // 属性值转为毫秒级时间戳
                        $value = strtotime($value) * 1000;
                        break;

                    // 时间类型,格式是否正确(标准格式: 09:30:21)
                    case AttrModel::ATTR_TYPE_TIME:
                        $resTime = $attrServ->checkTime($value);
                        if (!$resTime) {
                            $errTag = true;
                            $errData[$k]['error_msg'] = $attr['attr_name'] . "-格式不正确";
                        }
                        break;

                    // 单选类型
                    case AttrModel::ATTR_TYPE_RADIO:
                        $nameArr = array_column($attr['option'], 'name');
                        if (!in_array($value, $nameArr)) {
                            $errTag = true;
                            $errData[$k]['error_msg'] = "选项错误";
                            $errData[$k]['option'] = $attr['option'];
                            break;
                        }
                        // 将选项option的name转为value(写入架构数据库)
                        foreach ($attr['option'] as $option) {
                            if ($option['name'] == $value) {
                                $value = $option['value'];
                            }
                        }
                        break;

                    // 下拉框单选类型
                    case AttrModel::ATTR_TYPE_DROPBOX:
                        $nameArr = array_column($attr['option'], 'name');
                        if (!in_array($value, $nameArr)) {
                            $errTag = true;
                            $errData[$k]['error_msg'] = "选项错误";
                            $errData[$k]['option'] = $attr['option'];
                            break;
                        }
                        // 将选项option的name转为value(写入架构数据库)
                        foreach ($attr['option'] as $option) {
                            if ($option['name'] == $value) {
                                $value = $option['value'];
                            }
                        }
                        break;

                    // 多选类型
                    case AttrModel::ATTR_TYPE_CHECKBOX:
                        $nameArr = array_column($attr['option'], 'name');
                        // 用户填写的数据必须以";"号为分隔符
                        $valueArr = explode(';', $value);
                        $valueTemp = [];
                        foreach ($valueArr as $name) {
                            if (!in_array($name, $nameArr)) {
                                $errTag = true;
                                $errData[$k]['error_msg'] = "选项错误";
                                $errData[$k]['option'] = $attr['option'];
                                break;
                            }
                            foreach ($attr['option'] as $option) {
                                if ($name == $option['name']) {
                                    $valueTemp[] = $option;
                                }
                            }
                        }
                        // 将选项option的value转为name-value的多维数组,并序列化(写入架构数据库)
                        if (!empty($valueTemp)) {
                            $value = serialize($valueTemp);
                        }
                        break;
                }
            }

            // 格式化人员信息,请求架构接口,添加为内部人员
            $data[$v['field_name']] = $value;
        }

        $result = [];
        if ($errTag) {
            // 人员信息有错误
            $result = [
                'message' => '',
                'list' => $errData,
            ];

        } else {
            // 在职状态为离职时,离职日期不可为空
            if ($memActive == "离职" && strlen($leaveDate) == 0) {
                $result = [
                    'message' => '在职状态为离职时,离职日期不可为空',
                    'list' => $errData,
                ];

            } else {
                try {
                    // 请求架构接口,添加人员
                    $newMember = new Member(Service::instance());
                    $newMember->add($data);

                } catch (\VcySDK\Exception $e) {
                    // 接收架构抛错,接收架构错误,并返回错误和人员信息
                    if ($e->getCode() != 0 || ($e->getCode() == 0 && strpos($e->getMessage(), '系统异常') !== false)) {
                        $result = [
                            'message' => $e->getMessage(),
                            'list' => $errData,
                        ];
                    }
                }
                $this->clearUserCache();
            }
        }

        $this->_result = $result;
    }

    /**
     * 轮询查找最底层部门ID
     * @author zhonglei
     * @param array $nameList 部门层级关系(如: ['畅移', '产品部', '产品'])
     * @param array $deptIdList 部门层级关系对应的部门ID
     * @return bool|mixed
     */
    private function eachDept($nameList, $deptIdList = [])
    {
        $newDept = new Department(Service::instance());

        $index = count($deptIdList);
        $name = $nameList[$index];

        if ($index == 0) {
            // 调用UC部门列表接口,获取顶级部门列表数据
            $topDpId = '';
            $dpList = $newDept->listAll([], 1, 99999);
            foreach ($dpList['list'] as $v) {
                if (empty($v['dpParentid'])) {
                    $topDpId = $v['dpId'];
                }
            }
            if (!$topDpId) {
                return false;
            }
            $deptList[] = $newDept->detail(['dpId' => $topDpId]);

        } else {
            $parentId = $deptIdList[$index - 1];
            // 根据parentId取子部门列表数据
            $dpList = $newDept->listAll(['dpId' => $parentId, 1, 99999]);
            $deptList = $dpList['list'];
        }

        foreach ($deptList as $v) {
            if ($v['dpName'] == $name) {
                $deptIdList[$index] = $v['dpId'];
                break;
            }
        }

        if (!isset($deptIdList[$index])) {
            return false;
        }

        if (count($deptIdList) == count($nameList)) {
            return $deptIdList[$index];
        } else {
            return $this->eachDept($nameList, $deptIdList);
        }
    }

}
