<?php
/**
 * Login.class.php
 * 用户操作
 * $Author$
 */

namespace Common\Common;

use Com\Cookie;
use VcySDK\Adminer;
use VcySDK\Exception;
use VcySDK\Service;

class Logincp
{

    /**
     * @type array 用户信息
     */
    public $user = array();

    /**
     * @type string cookie前缀
     */
    public $_prekey = '';

    /**
     * 超级管理员
     */
    const TYPE_SUPER_ADMIN = 1;

    /**
     * 实例化
     * @param array $options
     * @return Logincp
     */
    public static function &instance($options = array())
    {

        static $instance;
        if (empty($instance)) {
            $instance = new self($options);
        }

        return $instance;
    }

    // 构造方法
    public function __construct($options = array())
    {

        // 如果 cookie 键值存在
        if (!empty($options) && isset($options['prekey'])) {
            $this->_prekey = (string)$options['prekey'];
        } else {
            $this->_prekey = 'apicp_';
        }
    }

    /**
     * 设置 cookie 键值前缀
     *
     * @param string $prekey 键值前缀
     *
     * @return boolean
     */
    public function setPrekey($prekey)
    {

        $this->_prekey = $prekey;

        return true;
    }

    /**
     * 获取cookie信息
     *
     * @param string $name cookie 的名称
     *
     * @return string
     */
    public function getCookie($name)
    {

        $cookie = &Cookie::instance();
        $val = $cookie->get($this->_prekey . $name);

        return empty($val) ? '' : $val;
    }

    /**
     * 设置cookie信息
     *
     * @param string $name   cookie 的名称
     * @param string $value  值
     * @param string $expire 过期时间
     *
     * @return bool
     */
    public function setCookie($name, $value = '', $expire = null)
    {

        $cookie = &Cookie::instance();
        // 如果有效时长小于 0, 则删除
        if (null !== $expire && 0 > $expire) {
            return $cookie->remove($this->_prekey . $name);
        } else {
            return $cookie->set($this->_prekey . $name, $value, $expire);
        }
    }

    // 初始化用户信息
    public function initUser()
    {

        // 如果已经有用户信息了
        if (!empty($this->user)) {
            return true;
        }

        // 取 uid, auth, lastlogin
        $uid = $this->getCookie('uid');
        $qyDomain = $this->getCookie('qyDomain');
        $auth = $this->getCookie('auth');
        $lastlogin = (int)$this->getCookie('lastlogin');
        // 如果 Cookie 值为空或者登陆时长超过一天
        if (empty($uid) || empty($auth) || empty($lastlogin) || $lastlogin + 86400 < NOW_TIME) {
            return false;
        }
        // 如果 Cookie域名跟访问域名不一致
        if (strtolower($qyDomain) != strtolower(QY_DOMAIN)) {
            return false;
        }

        // 判断用户信息并登录
        try {
            $adminerSdk = new Adminer(Service::instance());
            $member = $adminerSdk->fetch(['eaId' => $uid]);

            // 获取并验证Cookie内的密码
            $pwd = $this->getCookie('pwdAuth');
            if (!empty($pwd)) {
                $adminers = $adminerSdk->checkPwd(array(
                    'eaMobile' => empty($member['eaMobile']) ? $member['eaEmail'] : $member['eaMobile'],
                    'eaPassword' => $pwd
                ));
                // 如果当前登陆企业不在返回的数据里 说明账号密码已经对应不上
                $epIds = array_column(array_column($adminers, 'enterpriseInfo'), 'epEnumber');
                if (!in_array(QY_DOMAIN, $epIds)) {
                    E('PLEASE_LOGIN');
                    return false;
                }
            }
        } catch (Exception $e) {
            $sdkCode = $e->getSdkCode();
            $errCode = [
                cfg('UC_ADMIN_LOGIN_ERROR'),
                cfg('UC_DATA_NOT_FOUNT')
            ];
            if (!in_array($sdkCode, $errCode)) {
                E('PLEASE_LOGIN');
                return false;
            }
        }

        // 验证校验字串是否正确
        $authPwd = $this->getAuthPwd($uid, $qyDomain);
        if ($auth != $this->_generateAuth($uid, $authPwd, $lastlogin, $qyDomain)) {
            return false;
        }

        $this->user = $member;

        return true;
    }

    /**
     * 判断是否超级管理员
     *
     * @return bool
     */
    public function is_super_admin()
    {

        if (isset($this->user['eaType']) && self::TYPE_SUPER_ADMIN == $this->user['eaType']) {
            return true;
        }

        return false;
    }

    /**
     * 生成Auth秘钥
     *
     * @param string $uid    管理员ID
     * @param string $domain 站点域名(二级)
     *
     * @return bool
     */
    public function getAuthPwd($uid, $domain)
    {

        return md5($uid . "\t" . $domain);
    }

    /**
     * 生成验证字串
     *
     * @param int    $uid       用户UID
     * @param string $passwd    密码
     * @param int    $lastlogin 最后登录时间
     * @param string $domain    企业标识
     *
     * @return string
     */
    protected function _generateAuth($uid, $passwd, $lastlogin, $domain)
    {

        return md5($passwd . "\t" . $uid . "\t" . $lastlogin . "\t" . $domain);
    }

    /**
     * 刷新校验字串
     *
     * @param int    $uid    用户UID
     * @param string $passwd 密码
     * @param string $domain 站点标识(二级域名)
     *
     * @return boolean
     */
    public function flushAuth($uid, $passwd, $domain = 'comm')
    {
        $this->setCookie('qyDomain', $domain);
        $this->setCookie('uid', $uid);
        $this->setCookie('lastlogin', NOW_TIME);
        $this->setCookie('auth', $this->_generateAuth($uid, $passwd, NOW_TIME, $domain));

        $resauth = &ResAuth::instance();
        $resauth->writeCookie(ResAuth::USER_TYPE_ADMIN, $uid);
        return true;
    }

    /**
     * 生成登录 token
     *
     * @param string $enumber 企业标识
     * @param string $eaId    管理员ID
     * @param string $passwd  密码
     *
     * @return bool|string
     */
    public function generateLoginToken($enumber, $eaId, $passwd)
    {

        $data = array($enumber, $eaId, $passwd . random(8), NOW_TIME);

        return authcode(implode("\t", $data), cfg('COOKIE_SECRET'), 'ENCODE');
    }

    /**
     * 解析并校验登录 token
     *
     * @param array  $data   企业标识/管理员ID
     * @param string $token  登录token
     * @param string $passwd 密码
     * @param int    $expire 超时时长, 单位: s
     *
     * @return bool
     */
    public function parseLoginToken(&$data, $token, $passwd, $expire = 0)
    {

        $source = authcode($token, cfg('COOKIE_SECRET'));
        list($tmpEnumber, $tmpEaId, $tmpPasswd, $tmpTs) = explode("\t", $source);
        // 验证密码是否正确
        if ($passwd != substr($tmpPasswd, 0, 32)) {
            return false;
        }

        // 判断是否超时
        if (0 < $expire && NOW_TIME - $tmpTs > $expire) {
            return false;
        }

        $data = array($tmpEnumber, $tmpEaId);

        return true;
    }
}
