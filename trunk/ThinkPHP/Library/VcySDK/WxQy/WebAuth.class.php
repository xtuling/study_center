<?php
/**
 * WebAuth.class.php
 * Web授权接口操作类
 *
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt LGPL
 * @copyright  Copyright (c) 2014 - ? VcySDK (http://www.vchangyi.com/)
 * @author     zhuxun37
 * @version    1.0.0
 */

namespace VcySDK\WxQy;

use VcySDK\Logger;
use VcySDK\Config;
use VcySDK\Error;
use VcySDK\Exception;

class WebAuth
{

    /**
     * 接口调用类
     *
     * @var object
     */
    private $serv = null;

    /**
     * 用户登录接口, 获取用户信息
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const USERLOGIN_URL = '%s/commondapi/wxuserlogin';

    /**
     * 授权地址
     *
     * @var string
     */
    const OAUTH_URL = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=%s&redirect_uri=%s&response_type=code&scope=%s&state=%s#wechat_redirect';

    /**
     * 企业号号的Js签名
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const JS_SIGNATURE_URL = '%s/jssignature';

    /**
     * 企业号JS 通讯录管理组签名
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const JS_GROUPSIGNATURE_URL = '%s/js-groupsignature';

    /**
     * 企业号JS 通讯录管理组签名 (不用应用)
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const JS_GROUPSIGNATURE_WITHOUT_IDENTIFIER_URL = '%s/base/js-groupsignature';

    /**
     * 企业号JS 生物识别接口
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const JS_STOER_URL = '%s/base/js-stoer';

    // 通过 code 获取用户信息时, 用户信息不存在
    const NOT_FIND_USER = 'NOTFINDUSER';

    // code 被使用过或已过期
   // const OAUTH_CODE_ERRCODE = array(40029, 42003);

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
     * 获取授权地址
     *
     * @param string $url   授权成功后的回调地址
     * @param string $scope 授权作用域, snsapi_base: 只能获取 openid; snsapi_userinfo: 可以获取用户详细信息
     * @param string $state 自定义参数
     *
     * @return string
     * @throws Exception
     */
    public function oauth($url, $scope = 'snsapi_base', $state = '')
    {

        $appid = Config::instance()->appid;
        if (empty($appid)) {
            Logger::write('appid is empty');
            throw new Exception(Error::APPID_EMPTY);
        }
        $this->delAnchor($url);

        return sprintf(self::OAUTH_URL, $appid, urlencode($url), $scope, $state);
    }

    /**
     * 获取Js签名
     *
     * @param string $url 目标URL
     *
     * @throws Exception
     * @return boolean
     */
    public function jssignature($url)
    {

        // 菜单相关参数
        $this->delAnchor($url);
        $params = array(
            'url' => $url
        );

        return $this->serv->postSDK(self::JS_SIGNATURE_URL, $params, 'generateApiUrlA');
    }

    /**
     * 切除锚点
     *
     * @param $url
     *
     * @return bool
     */
    private function delAnchor(&$url)
    {

        $urlArr = explode('#', $url);
        $url = $urlArr[0];

        return true;
    }

    /**
     * 用户登录接口, 获取用户信息
     *
     * @param string $code 服务号授权返回的code
     *
     * @return array
     * @throws Exception
     */
    public function userLogin($code)
    {

        // 菜单相关参数
        $params = array(
            'code' => $code
        );

        try {
            $member = $this->serv->postSDK(self::USERLOGIN_URL, $params, 'generateApiUrlA');
        } catch (Exception $e) {
            // 如果是未找到用户
            $code = $e->getSdkCode();
            if (self::NOT_FIND_USER == $code || in_array($code, array(40029, 42003))) {
                $member = array();
            } else {
                throw new Exception($e);
            }
        }

        return $member;
    }

    /**
     * 企业号JS 通讯录管理组签名
     *
     * @param $condition
     *
     * @return mixed
     */
    public function jsGroupSignAture($condition)
    {

        return $this->serv->postSDK(self::JS_GROUPSIGNATURE_URL, $condition, 'generateApiUrlA');
    }

    /**
     * 企业号JS 通讯录管理组签名 (不使用应用名)
     *
     * @param $condition
     *
     * @return mixed
     */
    public function jsGroupSignAtureWithoutIdentifier($condition)
    {

        return $this->serv->postSDK(self::JS_GROUPSIGNATURE_WITHOUT_IDENTIFIER_URL, $condition, 'generateApiUrlB');
    }

    /**
     * 企业号JS 生物识别接口
     *
     * @param $condition
     *
     * @return mixed
     */
    public function JS_STOER_URL($condition)
    {

        return $this->serv->postSDK(self::JS_STOER_URL, $condition, 'generateApiUrlA');
    }
}
