<?php
/**
 * Login.class.php
 * 用户操作
 * $Author$
 */

namespace Common\Common;

use Com\Cookie;
use VcySDK\Service;
use VcySDK\WxQy\WebAuth;

class Login
{

    // 用户信息
    public $user = array();

    // openid
    public $openid = false;

    // cookie 键值的前缀
    public $_prekey = '';

    /**
     * 实例化
     * @param array $options
     * @return Login
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
            $this->_prekey = 'api_';
        }

        $serv = &Service::instance();
        $this->_webauth = new WebAuth($serv);
    }

    /**
     * 设置 cookie 键值前缀
     *
     * @param string $prekey 键值前缀
     *
     * @return boolean
     */
    public function set_prekey($prekey)
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
     * @param string $name   cookie名称
     * @param string $value  cookie值
     * @param null   $expire 过期时间
     *
     * @return mixed
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

    /**
     * 初始化用户信息
     *
     * @return bool
     */
    public function initUser()
    {
        // 用户信息已存在
        if (!empty($this->user)) {
            return true;
        }

        $cookie = &Cookie::instance();
        $this->openid = $cookie->getx('wx_openid');
        $qydomain = $cookie->getx('qyDomain', '');

        // 外部用户
        if ($this->openid && strtolower($qydomain) == strtolower(QY_DOMAIN)) {
            return true;
        }

        $uid = $this->getCookie('uid');
        $auth = $this->getCookie('auth');
        $lastlogin = $this->getCookie('lastlogin');
        $qydomain = $this->getCookie('qyDomain');

        // 如果 Cookie 值为空
        if (empty($uid) || empty($auth) || empty($lastlogin)) {
            return false;
        }

        // 如果 Cookie 企业标识 跟 访问企业标识 不一致
        if (strtolower($qydomain) != strtolower($qydomain)) {
            return false;
        }

        // 验证校验字串是否正确
        if ($auth != $this->_generateAuth($uid, $lastlogin, QY_DOMAIN)) {
            return false;
        }

        $userServ = &User::instance();
        $user = $userServ->getByUid($uid);

        // 未找到用户或用户已删除
        if (empty($user) || !$userServ->isNormal($user)) {
            $cookie->destroy();
            return false;
        }

        $this->user = $user;
        $this->flushAuth($uid, $qydomain);
        return true;
    }

    /**
     * 自动登陆
     *
     * @return boolean
     */
    public function autoLogin()
    {
        // 用户信息已存在
        if (! empty($this->user)) {
            return true;
        }

        $code = I('get.code');

        if (empty($code)) {
            $uid = cfg('DEBUG_UID');

            if (empty($uid)) {
                return false;
            }

            $userServ = &User::instance();
            $user = $userServ->getByUid($uid);

            // 未找到用户
            if (empty($user)) {
                E('_ERR_INVALID_USER');
            }

            if ($userServ->isNormal($user)) {
                $this->user = $user;
                $this->flushAuth($uid, QY_DOMAIN);

            // 用户已删除
            } else {
                $this->_writeOpenID($user['memUserid']);
            }
        } else {
            // 通过 Code 获取用户信息
            $wxworkAuth = new \Com\WxworkAuth();
            if ($wxworkAuth->useWXWorkGetUserInfo()) {
                // 采用企业微信特有的方式
                $user = $wxworkAuth->getUserInfo($code);
            } else {
                // 采用传统方式
                $user = $this->_webauth->userLogin($code);
            }

            // 内部成员
            if ($user['source'] == User::SOURCE_TYPE_MEMBER) {
                $this->user = $user;
                $this->flushAuth($this->user['memUid'], QY_DOMAIN);

                $cookie = &Cookie::instance();
                $cookie->setx('wx_openid', '', -1);
                $cookie->setx('qyDomain', '', -1);

            // 外部成员
            } else {
                $this->_writeOpenID($user['memUserid']);
            }
        }

        return true;
    }

    /**
     * 获取微信授权地址
     *
     * @return string
     */
    public function getAuthUrl()
    {

        $url = I('get._fronturl', '', 'trim');

        if (empty($url)) {
            // 如果没有接收到前端Url，则获取当前Url
            $url = cfg('PROTOCAL') . I('server.HTTP_HOST') . '/' . QY_DOMAIN . '/' . APP_DIR . I('server.REQUEST_URI');
        }

        $url = urldecode($url);
        // 解析url
        $urls = parse_url($url);
        // 解析参数
        if (!isset($urls['query'])) {
            $urls['query'] = '';
        }
        parse_str($urls['query'], $queries);
        // 加入应用标识
        $queries['_identifier'] = APP_IDENTIFIER;
        // 剔除 code 参数
        unset($queries['code']);

        if (!empty($urls['fragment'])) {
            unset($queries['_fronthash']);
            $queries['_fronthash'] = urlencode($urls['fragment']);
            $urls['query'] = http_build_query($queries);
        }

        // 重新拼 url
        $redirect_url = "{$urls['scheme']}://{$urls['host']}{$urls['path']}?{$urls['query']}";

        return $this->_webauth->oauth($redirect_url, 'snsapi_userinfo');
    }

    /**
     * 刷新校验字符串
     *
     * @param $uid
     * @param $qyDomain
     *
     * @return bool
     */
    public function flushAuth($uid, $qyDomain)
    {
        $this->setCookie('uid', $uid);
        $this->setCookie('lastlogin', NOW_TIME);
        $this->setCookie('auth', $this->_generateAuth($uid, NOW_TIME, $qyDomain));
        $this->setCookie('qyDomain', $qyDomain);

        $resauth = &ResAuth::instance();
        $resauth->writeCookie(ResAuth::USER_TYPE_MOBILE, $uid);
        return true;
    }

    /**
     * 生成验证字串
     *
     * @param string $uid       用户UID
     * @param int    $lastLogin 最后登录时间
     * @param string $qyDomain  企业标识
     *
     * @return string
     */
    protected function _generateAuth($uid, $lastLogin, $qyDomain)
    {

        return md5($uid . "\t" . $lastLogin . "\t" . $qyDomain);
    }

    /**
     * 将 openid 写入 Cookie
     *
     * @param string $openid
     * @return void
     */
    protected function _writeOpenID($openid)
    {

        $this->openid = $openid;
        $cookie = &Cookie::instance();
        $cookie->setx('wx_openid', $this->openid);
        $cookie->setx('qyDomain', QY_DOMAIN);
    }

    /**
     * 判断人员权限
     *
     * @param array  $jurisdiction 权限信息
     * @param string $uid          人员UID
     * @param object $department   部门SDK实例化
     * @param object $tag          标签SDK实例化
     * @return bool
     */
    public function judgeJurisdiction($jurisdiction, $uid, $department, $tag)
    {

        // 如果没有权限信息 并且 是手动安装，则不判断权限
        if (empty($jurisdiction) || !$jurisdiction['appAllow'] || empty($uid)) {
            return true;
        }

        // 人员权限
        $memUids = array_column($jurisdiction['memberList'], 'memUid');
        if (in_array($uid, $memUids)) {
            return true;
        }
        // 部门权限
        $dpIds = array_column($jurisdiction['departmentList'], 'dpId');
        list($mydepList, $parentDepList) = $department->list_dpId_by_uid($uid, true);
        $depList = array_values(array_merge($mydepList, $parentDepList));
        if (array_intersect($dpIds, $depList)) {
            return true;
        }
        // 标签权限
        $tagIds = array_column($jurisdiction['tagList'], 'tagId');
        if (!empty($tagIds)) {
            $tagList = $tag->listUserAll(['tagIds' => $tagIds], 1, 1500);
            foreach ($tagList['list'] as $tag) {
                // 判断标签下人员
                if (!empty($tag['memUid']) && $tag['memUid'] == $uid) {
                    return true;
                }
                // 判断标签下部门
                if ((!empty($tag['dpId']) && in_array($tag['dpId'], $depList))) {
                    return true;
                }
            }
        }

        // 没有权限
        E('_ERR_PHPEXCEL_FILE_CAN_NOT_OPEN');
        return true;
    }
}
