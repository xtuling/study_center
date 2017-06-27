<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/9/18
 * Time: 14:38
 */

namespace Common\Service;

use Com\PythonExcel;
use Common\Common\Cache;
use Common\Model\UserModel;
use VcySDK\Service;
use VcySDK\Member;
use Common\Common\User;
use Common\Common\Department;
use Common\Model\AttrModel;

class UserService extends AbstractService
{

    /**
     * 查询人员状态:全部
     */
    const USER_STATUS_ALL = 0;

    /**
     * 查询人员状态:已关注
     */
    const USER_STATUS_FOLLOW = 1;

    /**
     * 查询人员状态:已禁用
     */
    const USER_STATUS_DISABLE = 2;

    /**
     * 查询人员状态:未关注
     */
    const USER_STATUS_UNFOLLOW = 4;

    /**
     * 操作人员:启用
     */
    const ENABLE_USER = 1;

    /**
     * 操作人员:禁用
     */
    const DISABLE_USER = 2;

    /**
     * 操作人员:复职
     */
    const REHAB_USER = 3;

    /**
     * 操作人员:离职
     */
    const QUIT_USER = 4;

    /**
     * (架构)人员状态:启用
     */
    const STATUS_ENABLE = 1;

    /**
     * (架构)人员状态:禁用
     */
    const STATUS_DISABLE = 0;

    /**
     * 是否递归查询部门下所有子部门人员信息:是
     */
    const DEPT_CHILDREN_FLAG = 1;

    /**
     * 一年的天数
     */
    const YEAR_OF_DAY = 365;

    /**
     * 一天的毫秒数
     */
    const DAY_OF_MILLISECOND = 86400000;

    /**
     * 用户加入方式: 管理员添加
     */
    const ADMIN_ADD_JOIN = 1;

    /**
     * 用户加入方式: 邀请加入
     */
    const USER_INVITE_JOIN = 2;

    // 构造方法
    public function __construct()
    {

        parent::__construct();
        $this->_attr = new AttrModel();
    }

    /**
     * 保存人员(新增、修改人员信息)
     * @author liyifei
     * @param string $uid  人员UID
     * @param array  $data 校验过的表单数据
     * @return bool|mixed
     */
    public function saveUser($uid, $data)
    {

        // 调用架构接口,创建、修改用户信息
        $memServ = new Member(Service::instance());
        if ($uid) {
            $data['memUid'] = $uid;
            unset($data['memJoinType']);
            unset($data['memJoinInviter']);
            $result = $memServ->update($data);

            // 清空人员缓存
            $userServ = new User();
            $userServ->clearUserCache($uid);
        } else {
            $result = $memServ->add($data);
        }

        // 清空部门缓存(增加和删除人员,涉及到部门成员数量)
        $deptCom = new Department();
        $deptCom->clearDepCache();
        Cache::instance()->set('Common.Job', null);

        return $result;
    }

    /**
     * 单个或批量删除用户
     * @author liyifei
     * @param array $uids 人员uid
     * @return array|bool
     */
    public function delete($uids)
    {

        $userServ = new User();
        if (count($uids) > 1) {
            $userServ->batDelete($uids);
        } else {
            $userServ->delete($uids[0]);
        }

        // 清空人员缓存
        $userServ->clearUserCache($uids);

        // 清空部门缓存(增加和删除人员,涉及到部门成员数量)
        $deptCom = new Department();
        $deptCom->clearDepCache();
    }

    /**
     * 用户详情(手机端展示信息)
     * @author liyifei
     * @param string $uid 人员ID
     * @return mixed
     */
    public function getUserInfoByUid($uid)
    {

        // 属性列表
        $attrs = $this->getAttrList(false, array(), false, false);

        // 用户信息(从缓存架构用户信息表中的数据获取用户信息,参数设为true越过缓存)
        $commUser = new User();
        $userInfo = $commUser->getByUid($uid);

        $list = [];
        foreach ($attrs as $attr) {
            // 根据属性类型不同,将属性值转为与前端约定好的格式
            $attrValue = $this->formatValueByType($attr['type'], $userInfo[$attr['field_name']]);

            // 以下属性,不在list中出现
            $noAttr = [
                'memUsername',
                'memGender',
                'memFace',
                'dpName',
                //'memJob',
            ];
            // 属性值为空时(用户未填写), 不显示(广久说为空也显示出来)
            if (in_array($attr['field_name'], $noAttr)) {
                continue;
            }

            // 属性类型为单选、下拉框单选时,将属性值由单选value转为单选name显示
            if (in_array($attr['type'], [
                    AttrModel::ATTR_TYPE_RADIO,
                    AttrModel::ATTR_TYPE_DROPBOX
            ])) {
                foreach ($attr['option'] as $item) {
                    if ($item['value'] == $attrValue) {
                        $attrValue = $item['name'];
                    }
                }
            }

            $list[] = [
                'field_name' => $attr['field_name'],
                'attr_name' => $attr['attr_name'],
                'attr_value' => $attrValue,
                'option' => $attr['option'],
                'postion' => $attr['postion'],
                'type' => $attr['type'],
                'order' => $attr['order'],
                'is_allow_user_modify' => (int)$attr['is_allow_user_modify']
            ];
        }

        //  邀请人名称为空时, 说明是历史数据
        if (empty($userInfo['memJoinInviter'])) {
            $joinDesc = "管理员添加";
        } else {
            $joinDesc = $userInfo['memJoinType'] == self::ADMIN_ADD_JOIN ? "管理员（{$userInfo['memJoinInviter']}）添加"
                : "{$userInfo['memJoinInviter']}邀请加入";
        }

        // add by zhuxun37, 加入方式特殊处理
        $list[] = array(
            'field_name' => 'memJoin',
            'attr_name' => '加入方式',
            'attr_value' => $joinDesc,
            'option' => '',
            'type' => AttrModel::AREA_CONTACT,
            'order' => 0
        );

        return $list;
    }

    /**
     * 用户详情(管理后台)
     * @author liyifei
     * @param string $uid 人员ID
     * @return mixed
     */
    public function userInfo($uid)
    {

        // 用户信息
        $newUser = new User();
        $userInfo = $newUser->getByUid($uid, true);

        // Edit by liyifei at 2016-11-10 18:41:36 in V1.1.0 展示所有属性,包括空值的属性
        $attrs = $this->getAttrList(true, array(), true);

        $list = [];
        foreach ($attrs as $attr) {
            // 以下属性,不在list中出现
            if (in_array($attr['field_name'], ['memUsername', 'dpName'])) {
                continue;
            }

            // 根据属性类型不同,将属性值转为与前端约定好的格式
            $attrValue = $this->formatValueByType($attr['type'], $userInfo[$attr['field_name']]);

            $list[] = [
                'field_name' => $attr['field_name'],
                'attr_name' => $attr['attr_name'],
                'attr_value' => $attrValue,
                'option' => $attr['option'],
                'type' => $attr['type'],
                'order' => $attr['order'],
            ];
        }

        //  邀请人名称为空时, 说明是历史数据
        if (empty($userInfo['memJoinInviter'])) {
            $joinDesc = "管理员添加";
        } else {
            $joinDesc = $userInfo['memJoinType'] == self::ADMIN_ADD_JOIN ? "管理员（{$userInfo['memJoinInviter']}）添加"
                : "{$userInfo['memJoinInviter']}邀请加入";
        }

        // add by zhuxun37, 加入方式特殊处理
        $list[] = array(
            'field_name' => 'memJoin',
            'attr_name' => '加入方式',
            'attr_value' => $joinDesc,
            'option' => '',
            'type' => AttrModel::AREA_CONTACT,
            'order' => 0
        );

        // Edit by liyifei in V1.3.0 人员详情展示"司龄"属性(当前日期-入职日期)
        /**$age = [
            'attr_name' => '司龄',
            'attr_value' => '',
        ];
        $endTime = $userInfo['leaveDate'] ? $userInfo['leaveDate'] : MILLI_TIME;
        if ($userInfo['memJoinTime'] !== null) {
            $myMilliTime = $endTime - $userInfo['memJoinTime'];
            if ($myMilliTime < self::YEAR_OF_DAY * self::DAY_OF_MILLISECOND) {
                $myDay = ceil($myMilliTime / (self::DAY_OF_MILLISECOND)) > 0 ? ceil($myMilliTime / (self::DAY_OF_MILLISECOND)) : 0;
                if ($myDay < 365) {
                    // 司龄小于365天按天显示
                    $age['attr_value'] = $myDay . '天';
                } else {
                    // 司龄等于365天时,显示为年
                    $age['attr_value'] = "1年";
                }

            } else {
                // 司龄大于等于365天按年显示,小数点后保留一位,四舍五入
                $year = floor($myMilliTime / (self::YEAR_OF_DAY * self::DAY_OF_MILLISECOND));
                $age['attr_value'] = $year + round(($myMilliTime - $year * (self::YEAR_OF_DAY * self::DAY_OF_MILLISECOND)) / (self::YEAR_OF_DAY * self::DAY_OF_MILLISECOND), 1) . '年';
            }
        }

        array_push($list, $age);*/

        return $list;
    }

    /**
     * 根据条件查询读取人员列表(可查看通讯录范围内的人员列表)
     * @author liyifei
     * @param array $conds 搜索条件
     *                     + string $uid 人员UID
     *                     + string $dpId 部门ID
     *                     + string $keyword 关键字
     *                     + string $index 姓名首字母
     * @param int   $page  当前页码
     * @param int   $limit 每页数据总数
     * @return mixed
     */
    public function getListByConds($conds = [], $page = 1, $limit = 30)
    {

        // 验证参数
        if (empty($conds['uid'])) {
            E('_ERR_PARAMS_IS_NULL');
        }

        // 实例化
        $newUser = new User();
        $rightServ = new DeptRightService();

        // 读取登录人员所在部门的通讯录可见范围中部门列表
        $range = $rightServ->getRangByUid($conds['uid']);
        $dpList = array_column($range, 'dpId');

        // 初始化搜索条件
        $condition = [];

        // 根据部门ID搜索
        if (!empty($conds['dpId'])) {
            // 无权查看,且自己所在的部门ID集合
            $notDpIds = [];
            $myDpIds = $newUser->listDepartment(['memUid' => $conds['uid']]);
            foreach ($myDpIds as $myDpId) {
                if (!in_array($myDpId, $dpList)) {
                    $notDpIds[] = $myDpId;
                }
            }

            // 查看自己所在的部门,且无权查看本部门通讯录时,仅返回该用户信息
            if (in_array($conds['dpId'], $notDpIds)) {
                // 返回本人信息详情
                $userInfo = $newUser->getByUid($conds['uid']);
                return $userInfo;
            }

            // 搜索的部门,是否有权限查看
            if (!in_array($conds['dpId'], $dpList)) {
                E('_ERR_CANOT_FIND_USER');
            }

            // 搜索条件
            $condition['dpId'] = $conds['dpId'];
            $condition['departmentChildrenFlag'] = !isset($conds['departmentChildrenFlag']) ? self::DEPT_CHILDREN_FLAG : (int)$conds['departmentChildrenFlag'];

        } else {
            // 当可查看的部门被删除时,仅可查看本人信息
            if (empty($dpList)) {
                // 返回本人信息详情
                $userInfo = $newUser->getByUid($conds['uid']);
                return $userInfo;
            } else {
                $condition['dpIdList'] = $dpList;
            }
        }

        // 搜索关键字
        if (!empty($conds['keyword'])) {
            $condition['memUsername'] = $conds['keyword'];
        }

        // 搜索首字母
        if (!empty($conds['index'])) {
            $condition['index'] = $conds['index'];
        }

        // 排序规则
        $orderList = [
            'memIndex' => 'ASC',
        ];

        // 获取缓存用户列表信息
        return $newUser->listByConds($condition, $page, $limit, $orderList);
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
        // http://t-rep.vchangyi.com/common/20170519/3131f8a0-94b6-4166-929a-9692e470c80e.xls
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
                'data_type' => 'user',
                'data' => rjson_encode($_data)
            );
        }
        $importDataService->insert_all($insertData);

        return true;
    }

    // 导出模板
    public function exportTpl($attr)
    {

        $titles = $this->formatExportTitle($attr);

        $filename = NOW_TIME . random(8) . '.xls';
        PythonExcel::instance()->write(get_sitedir() . $filename, array_values($titles), array());

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename=userTpl.xls');
        header("Content-Transfer-Encoding:binary");
        echo file_get_contents(get_sitedir() . $filename);
        exit;
    }

    /**
     * 获取用户所在部门列表
     * @param $user
     * @return array
     */
    public function listUserDpIds($user)
    {

        $dpIds = array();
        foreach ($user['dpName'] as $_user) {
            $dpIds[] = $_user['dpId'];
        }

        return $dpIds;
    }

    /**
     * 获取用户的顶级部门
     * @param $user
     * @param $currentDpId
     * @return bool|mixed
     */
    public function getUserTopDpId($user, $currentDpId = null)
    {

        $myDpIds = $this->listUserDpIds($user);
        if (1 == $myDpIds) {
            return $myDpIds[0];
        }

        // 获取所有上级部门
        $parentDpIds = array();
        foreach ($myDpIds as $_dpId) {
            Department::instance()->list_parent_cdids($_dpId, $parentDpIds);
        }

        // 获取子部门
        $childIds = Department::instance()->list_childrens_by_cdid($myDpIds, true);
        $ids = array_merge($parentDpIds, $childIds);
        $departments = Department::instance()->listAll();
        // 整理上/下级关系
        $p2c = array();
        foreach ($ids as $_id) {
            $current = $departments[$_id];
            $parentId = empty($current['dpParentid']) ? 0 : $current['dpParentid'];
            if (empty($p2c[$parentId])) {
                $p2c[$parentId] = array();
            }

            $p2c[$parentId][] = $_id;
        }

        if (null == $currentDpId || !in_array($currentDpId, $childIds)) {
            $currentDpId = $this->getTopDpId($p2c, $myDpIds, 0);
        }

        return array($currentDpId, $p2c, $childIds, $ids);
    }

    /**
     * 获取用户顶级部门ID
     * @param $p2c
     * @param $myDpIds
     * @param $currentIds
     * @return mixed
     */
    public function getTopDpId($p2c, $myDpIds, $currentIds)
    {

        $currentIds = (array)$currentIds;
        $results = array();
        foreach ($currentIds as $_id) {
            if (empty($p2c[$_id])) {
                continue;
            }

            foreach ($p2c[$_id] as $_cid) {
                if (in_array($_cid, $myDpIds)) {
                    return $_cid;
                }

                $results[] = $_cid;
            }
        }

        if (empty($results)) {
            return 0;
        }

        return $this->getTopDpId($p2c, $myDpIds, $results);
    }

    /**
     * 重新整理标题
     * @param $attr
     * @return mixed
     */
    protected function formatExportTitle($attr)
    {
        $title = [];
        foreach ($attr as $item) {
            // 没有开启该字段
            if ($item['is_open_cp'] != AttrModel::ATTR_IS_OPEN_TRUE) {
                continue;
            }
            // 是否必填
            if ($item['is_required_cp'] == AttrModel::ATTR_IS_REQUIRED_TRUE) {
                $title[] = [
                    'value' => $item['attr_name'],
                    'pattern' => [
                        'pattern' => 'solid',
                        'fore_colour' => 'red'
                    ]
                ];
            } else {
                $title[] = $item['attr_name'];
            }
        }

        return $title;
    }

}
