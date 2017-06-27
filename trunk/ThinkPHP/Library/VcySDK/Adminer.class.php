<?php
/**
 * Adminer.class.php
 * 后台管理员接口操作类
 *
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt LGPL
 * @copyright  Copyright (c) 2014 - ? VcySDK (http://www.vchangyi.com/)
 * @author     zhuxun37
 * @version    1.0.0
 */
namespace VcySDK;

use VcySDK\Logger;
use VcySDK\Config;

class Adminer
{

    // 超级管理员标识
    const SUPER_MANAGER = 15;

    // 登陆用系统(超级)管理员标识
    const SUPER_MANAGER_LOGIN = 1;

    // 管理员没被禁用(正常)
    const MANAGER_ABLE_LOGIN = 1;

    // 管理员被禁用
    const MANAGER_DISABLE_LOGIN = 2;

    // 返回所有管理员
    const FILTER_TYPE_ALL = 1;

    // 返回已经补全手机号码的管理员
    const FILTER_TYPE_MOBILE = 2;

    // 管理员邀请邮件模板名
    const INVITE_EMAIL_TYPE_INVITE = 'hr_invite_mail';

    // 管理员已激活
    const IS_ACTIVATED = 1;

    /**
     * 超级管理员标识
     */
    const TYPE_SUPER_ADMIN = 1;

    /**
     * 接口调用类
     *
     * @var object|Service
     */
    private $serv = null;

    /**
     * 企业管理员新增
     * %s = {apiUrl}/b/{enumber}/adminer/register
     *
     * @var string
     */
    const REGISTER_URL = '%s/adminer/register';

    /**
     * 编辑企业管理员
     * %s = {apiUrl}/b/{enumber}/adminer/modify
     *
     * @var string
     */
    const MODIFY_URL = '%s/adminer/modify';

    /**
     * 删除企业管理员
     * %s = {apiUrl}/b/{enumber}/adminer/del
     *
     * @var string
     */
    const DEL_URL = '%s/adminer/del';

    /**
     * 获取企业管理员详情
     * %s = {apiUrl}/b/{enumber}/adminer/detail
     *
     * @var string
     */
    const GET_URL = '%s/adminer/detail';

    /**
     * 获取企业管理员列表
     * %s = {apiUrl}/b/{enumber}/adminer/page-list
     *
     * @var string
     */
    const LIST_URL = '%s/adminer/page-list';

    /**
     * 修改企业管理员密码
     * %s = {apiUrl}/b/{enumber}/adminer/modify-pwd
     *
     * @var string
     */
    const MODIFY_PWD_URL = '%s/adminer/modify-pwd';

    /**
     * 管理员登录记录
     * %s = {apiUrl}/b/{enumber}/adminer/login-log
     *
     * @var string
     */
    const LOGIN_LOG_URL = '%s/adminer/login-log';

    /**
     * 管理员密码验证
     * %s = {apiUrl}/s/login/check-pwd
     *
     * @var string
     */
    const CHECK_PWD_URL = '%s/login/check-pwd';

    /**
     * 验证手机号是否已被绑定
     * %s = {apiUrl}/b/{enumber}/adminer/valid-bind-mobile
     *
     * @var string
     */
    const VALID_BIND_MOBILE = '%s/adminer/valid-bind-mobile';

    /**
     * 验证手机号是否可以绑定(单点登录绑定手机号使用)
     * %s = {apiUrl}/b/{enumber}/valid-mobile-can-bind
     *
     * @var string
     */
    const VALID_CAN_BIND_MOBILE = '%s/adminer/valid-mobile-can-bind';

    /**
     * 管理员绑定手机号(单点登录)
     * %s = {apiUrl}/b/{enumber}/adminer/bind-mobile
     *
     * @var string
     */
    const BIND_MOBILE = '%s/adminer/bind-mobile';

    /**
     * 完善超级管理员信息
     * %s = {apiUrl}/b/{enumber}/adminer/complete-owner-info
     *
     * @var string
     */
    const COMPLETE_OWNER_INFO = '%s/adminer/complete-owner-info';

    /**
     * 根据手机号查询所属全部企业、管理员列表
     * %s = {apiUrl}/b/{enumber}/adminer/enterprise-adminer-list
     *
     * @var string
     */
    const ENTERPRISE_ADMINER_LIST = '%s/adminer/enterprise-adminer-list';

    /**
     * 重置管理员密码
     * %s = {apiUrl}/b/{enumber}/adminer/reset-adminer-pwd
     *
     * @var string
     */
    const RESET_ADMINER_PWD = '%s/adminer/reset-adminer-pwd';

    /**
     * 移交超级管理员
     * %s = {apiUrl}/b/{enumber}/adminer/transfer-super-admin
     *
     * @var string
     */
    const TRANSFER_SUPER_ADMIN = '%s/adminer/transfer-super-admin';

    /**
     * 获取微信登录URL
     * %s = {apiUrl}/b/{enumber}/adminer/wx-login-url
     *
     * @var string
     */
    const WECHAT_LOGIN_URL = '%s/adminer/wx-login-url';

    /**
     * 管理员密码验证
     * %s = {apiUrl}/s/qy/login/go-wx-manager-page
     *
     * @var string
     */
    const GOTO_WX_MANAGER_PAGE = '%s/qy/login/go-wx-manager-page';

    /**
     * 企业管理员,角色总数
     */
    const ADMINER_AND_ROLE_TOTAL = '%s/statistics/manager';

    /**
     * 企业管理员,角色总数
     */
    const INVITE_SEND_INVITATION = '%s/adminer/invite/send-invitation';

    /**
     * 企业管理员邀请-验证邀请有效性
     */
    const INVITE_INVITATION_ACTIVE = '%s/adminer/invite/invitation-active';

    /**
     * 企业管理员邀请-设置管理员密码（初始化管理员密码）
     */
    const INIT_SET_PASSWORD = '%s/adminer/set-pwd';

    /**
     * 初始化
     *
     * @param object $serv 接口调用类
     */
    public function __construct($serv)
    {

        $this->serv = $serv;
    }

    /**
     * 获取微信后台登录地址
     *
     * @param array $data 请求参数
     *
     * @return string
     * @throws Exception
     */
    public function getWechatManagerPageUrl($data = array())
    {

        $url = $this->serv->generateApiUrlS(self::GOTO_WX_MANAGER_PAGE);
        if (! empty($data) && is_array($data)) {
            $url = $url . (false === stripos($url, '?') ? '?' : '&') . http_build_query($data);
        }

        return $url;
    }

    /**
     * @param array $adminer 管理员信息
     *
     * @return bool|mixed
     * @throws Exception
     */
    public function register($adminer)
    {

        return $this->serv->postSDK(self::REGISTER_URL, $adminer, 'generateApiUrlE');
    }

    /**
     * @param array $adminer 管理员信息
     *
     * @return bool|mixed
     * @throws Exception
     */
    public function modify($adminer)
    {

        return $this->serv->postSDK(self::MODIFY_URL, $adminer, 'generateApiUrlE');
    }

    /**
     * 删除管理员信息
     *
     * @param $condition
     *        + string eaId 管理员ID
     *
     * @return bool|mixed
     * @throws Exception
     */
    public function del($condition)
    {

        return $this->serv->postSDK(self::DEL_URL, $condition, 'generateApiUrlE');
    }

    /**
     * @param array $condition 管理员查询条件
     *
     * @return bool|mixed
     * @throws Exception
     */
    public function fetch($condition)
    {

        return $this->serv->postSDK(self::GET_URL, $condition, 'generateApiUrlE');
    }

    /**
     * 获取企业管理员列表
     *
     * @param array $condition 查询条件数据
     * @param mixed $orders    排序字段
     * @param int   $page      当前页码
     * @param int   $perpage   每页记录数
     *
     * @return boolean|multitype:
     */
    public function listAll($condition = array(), $page = 1, $perpage = 30, $orders = array())
    {

        // 查询参数
        $condition = $this->serv->mergeListApiParams($condition, $orders, $page, $perpage);

        return $this->serv->postSDK(self::LIST_URL, $condition, 'generateApiUrlE');
    }

    /**
     * @param array $condition 更新信息
     *
     * @return bool|mixed
     * @throws Exception
     */
    public function modifyPWD($condition)
    {

        return $this->serv->postSDK(self::MODIFY_PWD_URL, $condition, 'generateApiUrlE');
    }

    /**
     * 管理员登录记录
     *
     * @param $condition
     *        + string eaId 管理员ID
     *        + int ealErrcode 登录错误码
     *        + string ealErrmsg 登录错误详情
     *        + string eaIp 最近一次登录IP
     *        + int eaLastlogin 最近一次登录时间戳
     *
     * @return bool|mixed
     * @throws Exception
     */
    public function loginLog($condition)
    {

        return $this->serv->postSDK(self::LOGIN_LOG_URL, $condition, 'generateApiUrlE');
    }

    /**
     * 管理员密码验证
     *
     * @param $data
     *        + string eaMobile 登录手机号， eaMobile和eaEmail不能同时为空，eaMobile不为空时，忽略eaEmail
     *        + string eaEmail 登录邮箱，eaMobile和eaEmail不能同时为空,，eaMobile不为空时，忽略eaEmail
     *        + string eaPassword 登录密码，非明文传递，业务md5(明文)处理一次
     *
     * @return bool|mixed
     * @throws Exception
     */
    public function checkPwd($data)
    {
        return $this->serv->postSDK(self::CHECK_PWD_URL, $data, 'generateApiUrlS');
    }

    /**
     * 验证手机号是否已被绑定
     *
     * @param $data
     *        + string eaMobile 需要绑定的手机号
     *        + string epEnumber 企业帐号，不为空时，验证所传企业当前手机号是否已被绑定(单企业唯一性)； 否则验证系统全局唯一性
     *
     * @return bool|mixed
     * @throws Exception
     */
    public function validMoblie($data)
    {

        return $this->serv->postSDK(self::VALID_BIND_MOBILE, $data, 'generateApiUrlS');
    }

    /**
     * 绑定手机
     *
     * @param $data
     *        + string eaId 管理员ID
     *        + string eaMobile 需要绑定的手机号
     *        + string eaPassword 管理员登录密码， 非明文，业务平台需md5(明文)一次
     *
     * @return bool|mixed
     * @throws Exception
     */
    public function bindMobile($data)
    {

        return $this->serv->postSDK(self::BIND_MOBILE, $data, 'generateApiUrlE');
    }

    /**
     * 验证手机号是否可以绑定(单点登录绑定手机号使用)
     *
     * @param $data
     *        + string eaMobile 需要绑定的手机号
     *
     * @return bool|mixed
     * @throws Exception
     */
    public function validMobileBind($data)
    {

        return $this->serv->postSDK(self::VALID_CAN_BIND_MOBILE, $data, 'generateApiUrlE');
    }

    /**
     * 完善账号信息
     *
     * @return bool|mixed
     * @throws Exception
     */
    public function completeOwnerInfo($data)
    {

        return $this->serv->postSDK(self::COMPLETE_OWNER_INFO, $data, 'generateApiUrlE');
    }

    /**
     * 根据手机号查询所属全部企业、管理员列表
     *
     * @return bool|mixed
     * @throws Exception
     */
    public function enterpriseAdminerList($data)
    {

        return $this->serv->postSDK(self::ENTERPRISE_ADMINER_LIST, $data, 'generateApiUrlS');
    }

    /**
     * 移交超级管理员
     *
     * @param array $data 移交目标数据
     *                    + transferAdminType 移交超管类型 1-选择已存在的管理员, 2-新增管理员移交
     *                    + newSuperEaId 被移交的管理员ID, transferAdminType=1时必须
     *                    + eaMobile 新增管理员手机号, transferAdminType=2时必须
     *                    + eaRealname 新增管理员姓名, transferAdminType=2时必须
     *                    + eaPassword 新增管理员密码, transferAdminType=2时必须
     *                    + eaEmail 新增管理员邮箱
     *
     * @return array|bool
     * @throws Exception
     */
    public function transferSuperAdmin($data)
    {

        return $this->serv->postSDK(self::TRANSFER_SUPER_ADMIN, $data, 'generateApiUrlE');
    }

    /**
     * 重置管理员密码
     *
     * @return bool|mixed
     * @throws Exception
     */
    public function resetAdminerPwd($data)
    {

        return $this->serv->postSDK(self::RESET_ADMINER_PWD, $data, 'generateApiUrlE');
    }

    /**
     * 获取登录URL
     *
     * @param array $data 请求数据
     *
     * @return array|bool
     * @throws Exception
     */
    public function wechatLoginUrl($data)
    {

        return $this->serv->postSDK(self::WECHAT_LOGIN_URL, $data, 'generateApiUrlE');
    }

    /**
     * 企业管理员,角色总数
     * @return array|bool
     * @throws Exception
     */
    public function adminerAndRoleTotal()
    {

        return $this->serv->postSDK(self::ADMINER_AND_ROLE_TOTAL, [], 'generateApiUrlE');
    }

    /**
     * 企业管理员-发送邀请邮件、短信接口（异步）
     * @param params
     * 字段名 | 是否必填 | 字段类型 | 说明
     * eaId	是	String	管理员ID
     * aiaToken	是	String	邀请链接token, 业务自己生成，单企业唯一
     * mcTplName	是	String	邮件模板名称
     * mcSubject	是	String	邮件标题
     * mcVars	否	Object	邮件内容变量键值
     * smsMessage	是	String	短信内容，邀请链接地址需要格式成段地址
     * @return array|bool
     * @throws Exception
     */
    public function inviteSendInvitation($params)
    {

        return $this->serv->postSDK(self::INVITE_SEND_INVITATION, $params, 'generateApiUrlE');
    }

    /**
     * 验证邀请有效性
     * @param $params
     * + aiaToken String 邀请token
     * @return array|bool
     */
    public function inviteInvitationActive($params)
    {

        return $this->serv->postSDK(self::INVITE_INVITATION_ACTIVE, $params, 'generateApiUrlE');
    }

    /**
     * 设置管理员密码（初始化管理员密码）
     * @param $params
     * @return array|bool
     */
    public function initPwd($params)
    {

        return $this->serv->postSDK(self::INIT_SET_PASSWORD, $params, 'generateApiUrlE');
    }
}
