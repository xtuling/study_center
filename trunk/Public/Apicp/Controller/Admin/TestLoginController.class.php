<?php
/**
 * 测试登录
 * Created by 原习斌
 * Date: 2016/9/22
 */

namespace Apicp\Controller\Admin;

use Com\Validator;
use VcySDK\Service;
use VcySDK\Adminer;

class TestLoginController extends AbstractAnonymousController
{

    /**
     * SDK的Adminer对象
     *
     * @var Adminer
     */
    protected $_adminer;

    public function before_action($action = '')
    {

        if (! parent::before_action($action)) {
            return false;
        }

        // 调用UC，登陆接口
        $serv_sdk = &Service::instance();

        // 实例化
        $this->_adminer = new Adminer($serv_sdk);

        return true;
    }

    public function Index()
    {

        // 接收数据
        $mobile = '15291990997'; // 用户手机
        $passwd = md5('123456'); // 密码
        // 如果是手机号密码登录
        $listAdminer = $this->_listAdminer($mobile, $passwd);
        // 登录失败
        if (empty($listAdminer)) {
            return false;
        }

        // 获取菜单列表反序列化输出
        $this->_result = $listAdminer;

        return true;
    }


    /**
     * 手机号密码登录
     *
     * @param string $username 手机号码
     * @param string $passwd   MD5密文密码
     *
     * @return string|array 登录成功返回ID，否则返回false
     */
    protected function _listAdminer($username, $passwd)
    {

        // 判断是否为手机号码
        if (! Validator::is_mobile($username)) {

            $this->_set_error("_ERR_PHONE_FORMAT");

            return false;
        }

        // 判断是否为 md5 之后的密码
        if (! Validator::is_password($passwd)) {

            $this->_set_error("_ERR_PWD_INVALID");

            return false;
        }

        // 登录验证
        $adminers = $this->_adminer->checkPwd(array(
            'eaMobile' => $username,
            'eaPassword' => $passwd
        ));


        // 重新检查数据格式, 保证数据格式的正确性
        if (! is_array($adminers) || empty($adminers)) {
            $this->_set_error("_ERR_ADMINER_NOT_EXIST");

            return false;
        }

        // 遍历管理员列表, 反序列化权限字串
        foreach ($adminers as &$_adminer) {
            // 如果用户信息不存在
            if (! isset($_adminer['adminerInfo']) || ! is_array($_adminer['adminerInfo'])) {
                continue;
            }

            // 生成登录用token, 用于之后的用户选择指定企业进行登陆的操作
            $_adminer['adminerInfo']['loginToken'] = $this->_login->generateLoginToken($_adminer['enterpriseInfo']['epEnumber'], $_adminer['adminerInfo']['eaId'], $passwd);
            // 如果角色信息不存在, 则忽略
            if (! isset($_adminer['adminerRoleInfo']) || ! is_array($_adminer['adminerRoleInfo'])) {
                continue;
            }

            $_adminer['adminerRoleInfo']['earCpmenu'] = empty($_adminer['adminerRoleInfo']['earCpmenu']) ? '{}' : unserialize($_adminer['adminerRoleInfo']['earCpmenu']);
        }

        list($enumber, $eaId) = array($adminers[0]['enterpriseInfo']['epEnumber'],$adminers[0]['adminerInfo']['eaId']);
//        // 记录登录日志
//        $this->_adminer->loginLog(array(
//            'eaId' => $eaId,
//            'eaIp' => get_client_ip(),
//            'eaLastlogin' => MILLI_TIME
//        ));

        // 写 Cookie
        $this->_login->flushAuth($eaId, $this->_login->getAuthPwd($eaId, $enumber), $enumber);


        return $adminers;
    }

}
