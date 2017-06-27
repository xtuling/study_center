<?php
/**
 * Qylogin.class.php
 * 企业登录操作类
 *
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt LGPL
 * @copyright  Copyright (c) 2014 - ? VcySDK (http://www.vchangyi.com/)
 * @author     zhoutao
 * @version    1.0.0
 */

namespace VcySDK;

class Qylogin
{
    /**
     * 接口调用类
     *
     * @var object
     */
    private $serv = null;

    // 新微信企业号
    const WECHAT_NEW_ENTERPRISE = 1;

    /**
     * 初始化
     *
     * @param Service $serv 接口调用类
     */
    public function __construct(Service $serv)
    {

        $this->serv = $serv;
    }

    /**
     * 企业号-微信企业号二维码登陆授权
     * %s = {apiurl}/a
     *
     * @param String
     */
    const AUTH_PAGE = '%s/qy/login/auth-page';

    /**
     * 企业号-单点登陆至微企号后台
     * %s = {apiurl}/a
     *
     * @param String
     */
    const WX_MANAGER_PAGE = '%s/qy/login/go-wx-manager-page';

    /**
     * 企业号-单点登陆至企业网站
     * %s = {apiurl}/a
     *
     * @param String
     */
    const MANAGER_PAGE = '%s/qy/login/go-manager-page';

    /**
     * 企业号-TOKEN换取用户信息
     * %s = {apiurl}/a/{enumber}
     *
     * @param String
     */
    const GET_ADMIN = '%s/qy/login/get-admin';

    /**
     * 企业号-单点登录到畅移管理后台
     * %s = {apiurl}/a/{enumber}
     *
     * @param String
     */
    const QRCODE_LOGIN = '%s/qy/login/wx-qrcode-login';
    /**
     * 企业号-微信企业号二维码登陆授权
     *
     * @param array $params
     *        + String(15) $target 登录跳转到企业号后台的目标页面，目前有：agent_setting、send_msg、contact
     *        + String $agentid 授权方应用id
     * @return bool|mixed
     * @throws Exception
     */
    public function authPage($params)
    {

        return $this->serv->postSDK(self::AUTH_PAGE, $params, 'generateApiUrlS');
    }

    /**
     * 企业号-单点登陆至微企号后台
     *
     * @param array $params
     *        + string $authCode 授权CODE
     *        + string $expiresIn 有效期
     *        + string $target 登录跳转到企业号后台的目标页面，目前有：agent_setting、send_msg、contact
     *        + string $agentid 授权方应用id
     * @return bool|mixed
     * @throws Exception
     */
    public function wxManagerPage($params)
    {

        return $this->serv->postSDK(self::WX_MANAGER_PAGE, $params, 'generateApiUrlS');
    }

    /**
     * 企业号-单点登陆至企业网站
     *
     * @param array $params
     *        + string $authCode 授权CODE
     *        + string $expiresIn 有效期
     * @return bool|mixed
     * @return mixed 跳转页面后参数里有 token
     * @throws Exception
     */
    public function managerPage($params)
    {

        return $this->serv->postSDK(self::MANAGER_PAGE, $params, 'generateApiUrlS');
    }

    /**
     * 企业号-TOKEN换取用户信息
     *
     * @param array $params
     *        + string $token 单点登陆至企业网站(managerPage方法)跳转页面里的token
     * @return bool|mixed
     * @throws Exception
     */
    public function getAdmin($params)
    {

        return $this->serv->postSDK(self::GET_ADMIN, $params, 'generateApiUrlE', [], null, 'get');
    }

    /**
     * 企业号-TOKEN换取用户信息
     *
     * @param array $params
     *        + string wxAuthCode 微信登陆扫码后的authcode
     * @return bool|mixed
     * @throws Exception
     */
    public function qrcodeLogin($params)
    {

        return $this->serv->postSDK(self::QRCODE_LOGIN, $params, 'generateApiUrlS');
    }
}
