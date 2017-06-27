<?php
/**
 * User.class.php
 * 用户操作
 * $Author$
 */

namespace Common\Common;

use Common\Service\SettingService;
use VcySDK\Service;
use VcySDK\Member;

class User
{

    /**
     * 分页查询最大数据条数
     */
    const LIST_MAX_PAGE_SIZE = 1000;

    /**
     * VcySDK 用户操作类
     *
     * @type null|Member
     */
    protected $_memberServ = null;

    /**
     * 所有用户列表
     *
     * @var array
     */
    protected $allUserList = [];

    /**
     * 状态：已删除
     */
    const STATUS_DELETED = 3;

    /**
     * 关注状态：已禁用
     */
    const SUBSCRIBE_STATUS_DISABLED = 2;

    /**
     * 关注状态：未关注
     */
    const SUBSCRIBE_STATUS_NOSUBSCRIBE = 4;

    /**
     * 在职状态：已离职
     */
    const ACTIVE_STATUS_LEAVE = 4;

    /**
     * 在职状态：已退休
     */
    const ACTIVE_STATUS_RETIRE = 5;

    /**
     * 用户来源：内部用户
     */
    const SOURCE_TYPE_MEMBER = 1;

    /**
     * 用户来源：外部用户
     */
    const SOURCE_TYPE_GUEST = 2;

    /**
     * 实例化
     *
     * @return User
     */
    public static function &instance()
    {

        static $instance;
        if (empty($instance)) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * 构造方法
     */
    public function __construct()
    {

        $this->_memberServ = new Member(Service::instance());

        // 获取本地缓存
        $cache = &Cache::instance();
        $this->allUserList = $cache->get('Common.User');
    }

    /**
     * 获取指定用户的头像
     *
     * @param int   $uid  用户UID
     * @param array $user 用户信息详情
     *
     * @return string
     */
    public function avatar($uid, $user = array())
    {

        // 如果用户信息为空, 则根据uid读取
        if ($uid && empty($user)) {
            $user = $this->getByUid($uid);
        }

        // 如果头像信息存在
        if (empty($user['memFace'])) {
            return '';
        }

        if ('//' == substr($user['memFace'], -2)) {
            // 如果后两个字符为 // 则重新取
            $avatar_url = substr($user['memFace'], 0, -1) . '64';
        } elseif ('/' == substr($user['memFace'], -1)) {
            // 以 / 结尾时
            $avatar_url = $user['memFace'] . '64';
        } elseif ('/64' != substr($user['memFace'], -3)) {
            // 如果不是以 /64 结尾
            $avatar_url = $user['memFace'] . '/64';
        } else {
            $avatar_url = $user['memFace'];
        }

        return urlProtocolConversion($avatar_url);
    }

    /**
     * 把用户信息推入数组
     *
     * @param array $users 用户信息数组
     *
     * @return boolean
     */
    public function push($users)
    {

        // 如果不是数组, 则
        if (!is_array($users)) {
            return false;
        }

        // 如果有 uid 下标, 则说明当前数组为用户信息数组
        if (!empty($users['uid'])) {
            $this->allUserList[$users['uid']] = $users;
            return true;
        }

        // 有可能是用户信息集合, 遍历重新进行推入操作
        foreach ($users as $_u) {
            if (!is_array($_u)) {
                continue;
            }

            $this->push($_u);
        }

        return true;
    }

    /**
     * 根据 uid 获取指定用户信息
     *
     * @param string $uid     用户UID
     * @param bool   $fromSDK 是否跳过从本地获取人员
     *
     * @return array
     */
    public function getByUid($uid, $fromSDK = true)
    {

        // uid 长度必须为32
        if (32 != strlen($uid)) {
            return [];
        }

        $user = $fromSDK ? $this->_getByUidFromSDK($uid) : $this->_getByUidFromCache($uid);
        if (!empty($user['memFace'])) {
            $user['memFace'] = urlProtocolConversion($user['memFace']);
        }
        return $user;
    }

    /**
     * _getByUidFromSDK UC查询
     *
     * @param string $uid 用户UID
     *
     * @return array|bool
     */
    protected function _getByUidFromSDK($uid)
    {

        $user = $this->_memberServ->fetch(array('memUid' => $uid));
        if (empty($user)) {
            return [];
        }

        $isFirst = empty($this->allUserList);
        $this->writeCache($isFirst);
        return $user;
    }

    /**
     * 写用户缓存
     *
     * @param bool $isFirst 是否第一次写
     *
     * @return bool
     */
    protected function writeCache($isFirst = false)
    {

        $cache = &Cache::instance();
        if ($isFirst) {
            $cache->set('Common.User', $this->allUserList, cfg('DATA_CACHE_TIME'));
        } else {
            $cache->set('Common.User', $this->allUserList);
        }

        return true;
    }

    /**
     * _getByUidFromCache 缓存查询
     *
     * @param string $uid 用户UID
     *
     * @return array
     */
    protected function _getByUidFromCache($uid)
    {

        // 本地缓存是否存在该人员
        list($userCaches, $isFirst,) = $this->cacheByUid($uid);
        if (!empty($userCaches[$uid])) {
            return $userCaches[$uid];
        }

        // 查询人员
        $user = $this->_getByUidFromSDK($uid);
        if (!empty($user)) {
            $this->allUserList[$uid] = $user;
            // 是否要加入时间, 如不需要就是纯更新数据
            $this->writeCache($isFirst);
            return $user;
        }

        return [];
    }

    /**
     * 根据多个 uid 获取用户信息
     *
     * @param array $uids    用户UID数组
     * @param bool  $fromSDK 是否跳过查询缓存
     *
     * @return array
     */
    public function listByUid($uids, $fromSDK = true)
    {

        if (!is_array($uids) || empty($uids)) {
            return [];
        }

        $uids = array_values($uids);

        $list = $fromSDK ? $this->_listByUidFromSDK($uids) : $this->_listByUidFromCache($uids);
        if (is_array($list)) {
            foreach ($list as $key => &$value) {
                if (!empty($value['memFace'])) {
                    $value['memFace'] = urlProtocolConversion($value['memFace']);
                }
            }
        }

        return $list;
    }

    /**
     * _listByUidFromSDK UC查询
     *
     * @param array $uids 用户UID
     *
     * @return array|mixed
     */
    protected function _listByUidFromSDK($uids)
    {

        $users = $this->listByConds(array('memUids' => $uids));
        if (empty($users['list'])) {
            return [];
        }

        // 整理人员数据
        $users = array_combine_by_key($users['list'], 'memUid');
        $isFirst = empty($this->allUserList);
        $this->allUserList = array_merge($this->allUserList, $users);
        $this->writeCache($isFirst);

        return $users;
    }

    /**
     * _listByUidFromCache 缓存查询
     *
     * @param array $uids 用户UID数组
     *
     * @return array
     */
    protected function _listByUidFromCache($uids)
    {

        // 缓存是否存在该人员
        list($userCaches, $isFirst, $diffUids) = $this->cacheByUid($uids);
        // 如果缓存存在查询人员, 并且没有缓存不存在的人, 直接返回数据
        if (!empty($userCaches) && empty($diffUids)) {
            return $userCaches;
        }

        $users = array();
        if (!empty($diffUids)) {
            $users = $this->_listByUidFromSDK($diffUids);
            // 返回数据 并加入缓存
            if (!empty($users)) {
                $this->allUserList = array_merge($this->allUserList, $users);
                $this->writeCache($isFirst);
            }
        }

        return array_merge($userCaches, $users);
    }

    /**
     * 从本地缓存里获取人员数据
     *
     * @param string|array $uids 人员ID数据
     *
     * @return array
     * + userArr 人员数据
     * + expire 是否需要加入过期时间
     * + 缓存不存在的人员
     */
    public function cacheByUid($uids)
    {

        // 是否需要加入过期时间 (当之前的本地缓存为空时,表示是新的缓存 或者是已经过期被删除的缓存)
        if (empty($this->allUserList) || empty($uids)) {
            return array([], true, $uids);
        }

        // 如果缓存存在该人员
        $users = [];
        // 缓存不存在的人员
        $diffUids = [];
        if (is_array($uids)) {
            foreach ($uids as $_uid) {
                if (isset($this->allUserList[$_uid])) {
                    $users[$_uid] = $this->allUserList[$_uid];
                } else {
                    $diffUids[] = $_uid;
                }
            }
        } else {
            if (isset($this->allUserList[$uids])) {
                $users = array($uids => $this->allUserList[$uids]);
            } else {
                $diffUids = $uids;
            }
        }

        return array($users, false, $diffUids);
    }

    /**
     * 根据条件获取用户信息
     *
     * @param array   $condition 查询条件
     * @param integer $page      当前页码
     * @param integer $perpage   每页数据总数
     * @param array   $orders    排序
     *
     * @return array|bool
     */
    public function listByConds($condition = [], $page = 1, $perpage = 30, $orders = [])
    {

        $result = $this->_memberServ->listAll($condition, $page, $perpage, $orders);
        if (!empty($result['list']) && is_array($result['list'])) {
            foreach ($result['list'] as $key => &$value) {
                if (!empty($value['memFace'])) {
                    $value['memFace'] = urlProtocolConversion($value['memFace']);
                }
            }
        }

        return $result;
    }

    /**
     * 根据条件获取所有用户信息
     *
     * @param array $condition 查询条件
     * @param array $orders    排序
     *
     * @return array
     */
    public function listAll($condition = [], $orders = [])
    {

        $page = 1;
        $page_max = 0;
        $list = [];

        // 获取所有用户
        do {
            $result = $this->_memberServ->listAll($condition, $page, self::LIST_MAX_PAGE_SIZE, $orders);

            if (isset($result['list']) && !empty($result['list'])) {
                $list = array_merge($list, $result['list']);
            }

            // 计算总页数
            if ($page_max === 0) {
                $page_max = ceil($result['total'] / self::LIST_MAX_PAGE_SIZE);
            }

            $page++;
        } while ($page <= $page_max);

        return $list;
    }

    /**
     * 更新用户信息
     *
     * @param array $data 用户数据
     *
     * @return array
     */
    public function update($data)
    {

        return $this->_memberServ->update($data);
    }

    /**
     * 同步人员信息
     * @author zhonglei
     * @return void
     */
    public function sync()
    {

        $this->_memberServ->sync();
        // 记录同步时间
        $settingServ = new SettingService();
        $setting = $settingServ->get_by_conds(['key' => 'synctime']);

        if ($setting) {
            $settingServ->update($setting['setting_id'], ['value' => MILLI_TIME]);
        } else {
            $settingServ->insert(['key' => 'synctime', 'value' => MILLI_TIME]);
        }
    }

    /**
     * 删除人员
     *
     * @param string $memUid 用户UID
     *
     * @return bool|mixed
     */
    public function delete($memUid)
    {

        $param = [
            'memUid' => (string)$memUid
        ];

        return $this->_memberServ->delete($param);
    }

    /**
     * 批量删除人员
     *
     * @param array $uidArr 用户UID数组
     *
     * @return array|bool
     */
    public function batDelete(array $uidArr)
    {

        $param = [
            'memUids' => array_values($uidArr),
        ];

        return $this->_memberServ->batDelete($param);
    }

    /**
     * 根据人员ID获取相应的部门
     *
     * @param array $condition
     * + String memUid 人员ID
     *
     * @return array
     */
    public function listDepartment($condition)
    {

        $departmentArr = $this->_memberServ->listDepartment($condition);
        if (empty($departmentArr)) {
            return [];
        }

        // 获取部门数组
        $dpIds = array_column($departmentArr, 'dpId');

        return $dpIds;
    }

    /**
     * 根据人员ID获取相应的职位
     *
     * @param array $condition
     * + String memUid 人员ID
     *
     * @return array
     */
    public function listJob($condition)
    {

        $jobArr = $this->_memberServ->listJob($condition);
        if (empty($jobArr)) {
            return [];
        }

        // 获取部门数组
        $jobId = array_column($jobArr, 'jobId');

        return $jobId;
    }

    /**
     * 通过 userid 数组批量获取对应的 uid
     *
     * @param array $userids
     *
     * @return array|bool
     */
    public function listUidByUserid($userids)
    {

        if (empty($userids) || !is_array($userids)) {
            return [];
        }

        $userid2uid = [];
        // 先从缓存里搜一遍
        $userid2detail = array_combine_by_key($this->allUserList, 'memUserid');
        foreach ($userids as $_k => $_userid) {
            $userid2uid[$_userid] = $userid2detail[$_userid]['memUid'];
            unset($userids[$_k]);
        }

        // 如果所有数据都在缓存中
        if (empty($userids)) {
            return $userid2uid;
        }

        $users = $this->listByConds(['memUserid' => $userids]);
        $users = empty($users['list']) ? [] : $users['list'];
        // 返回 userid 为键 uid 为值的数组
        foreach ($users as $_user) {
            $userid2uid[$_user['memUserid']] = $_user['memUid'];
        }

        return $userid2uid;
    }

    /**
     * 通过对应的 uid 批量获取 userid 数组
     *
     * @param array $uids
     *
     * @return array
     */
    public function listUseridByUid($uids)
    {

        if (empty($uids) || !is_array($uids)) {
            return [];
        }

        $users = $this->listByUid($uids);
        // 返回 uid 为键 userid 为值的数组
        $uid2userid = [];
        foreach ($users as $_user) {
            $uid2userid[$_user['memUid']] = $_user['memUserid'];
        }

        return $uid2userid;
    }

    /**
     * 删除人员缓存
     *
     * @param string|array $uids
     *
     * @return bool
     */
    public function clearUserCache($uids)
    {

        $cache = &Cache::instance();
        if (empty($uids)) {
            $cache->set('Common.User', null);
            return true;
        }

        // 获取本地缓存
        if (is_string($uids) && isset($this->allUserList[$uids])) {
            unset($this->allUserList[$uids]);
        } elseif (is_array($uids)) { // 人员ID为数组, 并且存在
            foreach ($uids as $_uid) {
                unset($this->allUserList[$_uid]);
            }
        } else {
            return true;
        }

        // 更新缓存
        $this->writeCache();

        return true;
    }

    /**
     * 判断用户是否正常
     *
     * @param array $member 用户信息
     *
     * @return bool
     */
    public function isNormal($member)
    {

        if (self::STATUS_DELETED <= $member['memStatus']) {
            return false;
        }

        return true;
    }

    /**
     * 实时获取设备ID
     * @author zhonglei
     * @param string $uid 用户ID
     * @return string
     */
    public function getDeviceId($uid)
    {

        if (empty($uid)) {
            return '';
        }

        $user = $this->getByUid($uid, true);

        if (empty($user) || !isset($user['memDeviceid']) || empty($user['memDeviceid'])) {
            return '';
        }

        return $user['memDeviceid'];
    }

    /**
     * @param $mobile
     * @param $email
     * @param $weixin
     * @return array|bool
     */
    public function checkMemInfoSingle($mobile, $email, $weixin) {
        return $this->_memberServ->checkMemInfoSingle([
            'memMobile' => $mobile,
            'memEmail' => $email,
            'memWeixin' => $weixin
        ]);
    }
}
