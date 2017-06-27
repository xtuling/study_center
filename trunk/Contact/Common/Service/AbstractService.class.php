<?php
/**
 * AbstractService.class.php
 * Service 层基类
 * @author   : zhuxun37
 * @version  : $Id$
 * @copyright: vchangyi.com
 */

namespace Common\Service;

use Common\Common\Cache;
use Common\Common\User;
use Common\Common\Attach;
use Common\Model\AttrModel;

abstract class AbstractService extends \Com\Service
{
    // 属性字段表
    protected $_attr = null;

    // 构造方法
    public function __construct()
    {

        parent::__construct();
        $this->_attr = new AttrModel();
    }

    /**
     * 获取属性信息列表
     * @author liyifei
     * @param bool  $isAll        是否获取所有属性 false=仅前端显示的属性; true=所有属性(包括显示和不显示)
     * @param array $typeArr      过滤属性类型
     * @param bool  $isCp
     * @param bool  $ignoreIsOpen 忽略是否开启标识
     * @return array
     */
    public function getAttrList($isAll = true, $typeArr = [], $isCp = false, $ignoreIsOpen = true)
    {

        // 所有开启的属性
        if ($isCp) {
            $conds = ['is_open_cp' => AttrModel::ATTR_IS_OPEN_TRUE];
        } elseif ($ignoreIsOpen) {
            $conds = ['is_open' => AttrModel::ATTR_IS_OPEN_TRUE];
        }
        // 获取所有属性或仅前端显示的属性
        if (!$isAll) {
            $conds['is_show'] = AttrModel::ATTR_IS_SHOW_TRUE;
        }

        // 排序条件
        $orders = [
            'is_system' => 'desc',
            '`order`' => 'asc',
            'attr_id' => 'asc',
        ];
        $attrs = $this->_attr->list_by_conds($conds, [], $orders);

        // 获取配置
        $settings = Cache::instance()->get('Common.AppSetting');

        // 需过滤的属性类型
        $result = [];
        foreach ($attrs as $k => $v) {
            if (is_array($typeArr) && in_array($v['type'], $typeArr)) {
                continue;
            }
            $result[$k] = $v;
            // 将单选、下拉框单选、多选类型的属性选项,反序列化
            if ($v['type'] == AttrModel::ATTR_TYPE_RADIO || $v['type'] == AttrModel::ATTR_TYPE_DROPBOX || $v['type'] == AttrModel::ATTR_TYPE_CHECKBOX) {
                $result[$k]['option'] = unserialize($v['option']);
            }

            if ('memJob' == $result[$k]['field_name'] && 'select' == $settings['jobMode']['value']) {
                $this->_getJobs($result[$k]);
            } else if ('memRole' == $result[$k]['field_name'] && 'select' == $settings['roleMode']['value']) {
                $this->_getRoles($result[$k]);
            }
        }

        return array_values($result);
    }

    /**
     * 职位属性特殊处理
     * @param $attr
     * @return bool
     */
    protected function _getJobs(&$attr)
    {

        $jobService = new JobService();
        $jobService->searchList($result, array('limit' => 1000));
        $attr['type'] = (string)AttrModel::ATTR_TYPE_DROPBOX;
        $attr['option'] = array();
        foreach ($result['list'] as $_job) {
            $attr['option'][] = array(
                'name' => $_job['jobName'],
                'value' => $_job['jobName']
            );
        }

        return true;
    }

    /**
     * 角色属性特殊处理
     * @param $attr
     * @return bool
     */
    protected function _getRoles(&$attr)
    {

        $roleService = new RoleService();
        $roleService->searchList($result, I('post.'));
        $attr['type'] = (string)AttrModel::ATTR_TYPE_DROPBOX;
        $attr['option'] = array();
        foreach ($result['list'] as $_job) {
            $attr['option'][] = array(
                'name' => $_job['roleName'],
                'value' => $_job['roleName']
            );
        }

        return true;
    }

    /**
     * 检查属性值,并返回错误信息
     * @author liyifei
     * @param array  $values 属性的键值对数组, 格式为 ['memUsername' => 'xxx', 'memMobile' => 13166666666]
     * @param string $require_field
     * @param array $needToVerify
     * @return array
     */
    public function checkValue($values, $require_field = 'is_required_cp', $needToVerify = [])
    {

        $attrs = $this->getAttrList(true, array(), 'is_required_cp' == $require_field ? true : false);
        $errors = [];

        // 在职状态为离职时,离职日期不可为空(此处暂不使用常量表示离职状态)
        if ($values['memActive'] == 4 && strlen($values['leaveDate']) == 0) {
            $errors[] = '离职日期不可为空';
        }

        foreach ($attrs as $attr) {
            // 只验证需要验证的字段
            if (!empty($needToVerify) && !in_array($attr['field_name'], $needToVerify)) {
                continue;
            }

            $field_name = $attr['field_name'];
            $value = isset($values[$field_name]) && $values[$field_name] !== '' ? $values[$field_name] : '';

            // 不验证的必填项(手机端提交表单时,不给被邀请人选择直属上级的权限,所以当直属上级为必填时,要跳过验证)
            $notCheckType = [
                AttrModel::ATTR_TYPE_SPECIAL,
                AttrModel::ATTR_TYPE_LEADER
            ];

            // 必填属性
            if ($attr[$require_field] == AttrModel::ATTR_IS_REQUIRED_TRUE && $value === '' && !in_array($attr['type'], $notCheckType)) {
                $errors[] = $attr['attr_name'] . '-必填项值错误';
                continue;
            }

            // 非必填项的属性值为空时,不做验证
            if ($attr[$require_field] == AttrModel::ATTR_IS_REQUIRED_FALSE && $value === '') {
                continue;
            }

            // 验证手机号格式
            if ($attr['field_name'] == 'memMobile' && strlen($value) != 11) {
                $errors[] = $attr['attr_name'] . '-格式错误';
                continue;
            }

            // 验证邮箱格式
            if ($attr['field_name'] == 'memEmail' && !preg_match('/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/', $value)) {
                $errors[] = $attr['attr_name'] . '-格式错误';
                continue;
            }

            // 验证账号(规则:1~32位字符、仅可用大小写字母、数字、下划线)
            if ($attr['field_name'] == 'memUserid' && !preg_match('/^[a-zA-Z0-9_]{1,32}$/', $value)) {
                $errors[] = $attr['attr_name'] . '-格式错误';
                continue;
            }

            switch ($attr['type']) {
                // 单选类型
                case AttrModel::ATTR_TYPE_RADIO:
                    $key = array_column($attr['option'], 'value');
                    if (!in_array($value, $key)) {
                        $errors[] = $attr['attr_name'] . '-选项不存在';
                    }
                    break;

                // 下拉框单选类型(同单选类型验证)
                case AttrModel::ATTR_TYPE_DROPBOX:
                    $key = array_column($attr['option'], 'value');
                    if (!in_array($value, $key)) {
                        $errors[] = $attr['attr_name'] . '-选项不存在';
                    }
                    break;

                // 多选类型
                case AttrModel::ATTR_TYPE_CHECKBOX:
                    $value = unserialize($value);
                    $key = array_column($attr['option'], 'value');
                    foreach ($value as $v) {
                        if (!in_array($v['value'], $key)) {
                            $errors[] = $attr['attr_name'] . '-选项不存在';
                            break;
                        }
                    }
                    break;

                // 图片类型
                case AttrModel::ATTR_TYPE_PICTURE:
                    // 前端提交图片附件ID数组
                    $value = unserialize($value);
                    if (!is_array($value)) {
                        $errors[] = $attr['attr_name'] . '-格式错误';
                    }
                    break;
            }
        }

        return $errors;
    }

    /**
     * 根据属性类型,格式化相应属性值
     * @author liyifei
     * @param int        $type  属性类型
     * @param string|int $value 属性值
     * @return mixed
     */
    public function formatValueByType($type, $value)
    {

        $result = '';
        switch ($type) {
            // 图片类型,从架构获取图片信息
            case AttrModel::ATTR_TYPE_PICTURE:
                if (!empty($value)) {
                    $result = $this->formatImg($value);
                }
                break;

            // 多选类型,将值反序列化转为数组
            case AttrModel::ATTR_TYPE_CHECKBOX:
                if (!empty($value)) {
                    $result = array_column(unserialize($value), 'name');
                }
                break;

            // 直属上级
            case AttrModel::ATTR_TYPE_LEADER:
                if (!empty($value)) {
                    $result = $this->formatLeader($value);
                }
                break;

            default:
                $result = is_null($value) ? '' : $value;
        }

        return $result;
    }

    /**
     * 格式化直属上级、部门负责人返回值
     * @author liyifei
     * @param string $uids 以英文逗号分隔的uid字符串,如"B4B3BA5B7F00000173E870DA6ADFEA2A,B4B3BA207F00000173E870DAB46163B0"
     * @return array
     */
    public function formatLeader($uids)
    {

        $uidArr = explode(',', $uids);

        $result = [];
        if (!empty($uidArr)) {
            $commUser = new User();
            $list = $commUser->listByUid($uidArr);
            foreach ($list as $v) {
                $result[] = [
                    'uid' => $v['memUid'],
                    'name' => $v['memUsername'],
                    'face' => $v['memFace'],
                ];
            }
        }

        return $result;
    }

    /**
     * 根据图片ID数组,获取附件Url,并格式化返回
     * @author liyifei
     * @param array|string $atids 图片id数组 或 序列化后的附件id
     * @return mixed
     */
    public function formatImg($atids)
    {

        // 非数组时,即为序列化后的附件id,需反序列化
        if (!is_array($atids)) {
            $atids = unserialize($atids);
        }

        $result = [];
        $newAttach = new Attach();
        $list = $newAttach->listAttachUrl($atids);
        foreach ($list as $v) {
            $result[] = [
                'id' => $v['atId'],
                'url' => $v['atAttachment'],
                'file_name' => $v['atFilename'],
            ];
        }

        return $result;
    }

    /**
     * 获取筛选后的属性类型
     * @author liyifei
     * @param array $filters 去除的属性类型
     * @return array
     */
    public function getAllAttrType($filters = [])
    {

        $allType = [
            AttrModel::ATTR_TYPE_SINGLE_TEXT,
            AttrModel::ATTR_TYPE_MULTIPLE_TEXT,
            AttrModel::ATTR_TYPE_NUMBER,
            AttrModel::ATTR_TYPE_DATE,
            AttrModel::ATTR_TYPE_TIME,
            AttrModel::ATTR_TYPE_DATE_TIME,
            AttrModel::ATTR_TYPE_RADIO,
            AttrModel::ATTR_TYPE_CHECKBOX,
            AttrModel::ATTR_TYPE_ADDRESS,
            AttrModel::ATTR_TYPE_PICTURE,
            AttrModel::ATTR_TYPE_DROPBOX,
            AttrModel::ATTR_TYPE_SPECIAL,
            AttrModel::ATTR_TYPE_LEADER,
        ];

        if (!empty($filters) && is_array($filters)) {
            foreach ($allType as $k => $v) {
                if (in_array($v, $filters)) {
                    unset($allType[$k]);
                }
            }
        }

        return $allType;
    }
}
