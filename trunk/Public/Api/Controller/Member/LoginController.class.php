<?php

/**
 * 前端登陆
 * 鲜彤 2016-07-27 13:27:30
 * zhoutao 2016-08-16 11:52:01
 */

namespace Api\Controller\Member;

use Common\Common\Cache;
use Common\Common\Department;
use Common\Common\Login;
use Common\Common\User;
use Common\Common\WxApi;

class LoginController extends AbstractController
{

    protected $_require_login = false;

    protected $params = [];

    public function Index()
    {

        $this->params = I('get.', '', 'trim');
        $this->checkLogin();

        return true;
    }

    protected function checkLogin()
    {

        // 判断用户未登陆
        if (! empty($this->_login->user)) {
            // 设置输出的用户信息
            $user = [];
            $this->format_user($user);

            // 取jsapi授权签名相关
            $jscfg = $this->getJsConfig($this->params['_fronturl']);
            $this->_result = array('user' => $user, 'jscfg' => $jscfg);

            return true;
        }

        $gets = [];
        // 如果允许外部人员访问, 并且已经读取到用户信息
        if ($this->isOuter($result, $gets)) {
            return true;
        }

        // 判断重定向url参数
        $url = $this->params['_fronturl'];
        if (empty($url)) {
            E('_ERR_AUTH_URL_INVALID');
            return false;
        }

        // 生成微信企业号authcode地址
        // 解析url
        $urls = parse_url($url);
        // 解析参数
        parse_str($urls['query'], $queries);
        // 剔除 code 参数
        unset($queries['code']);
        if (! empty($urls['fragment'])) {
            unset($queries['_fronthash']);
            $queries['_fronthash'] = urlencode($urls['fragment']);
            $urls['query'] = http_build_query($queries);
        }

        // 重新拼 url
        $gets['_fronturl'] = oaUrl($urls['path'] . '?' . $urls['query']);

        $this->_result = array('authurl' => oaUrl('/Common/Frontend/Member/JsLogin' . '?' . http_build_query($gets)));
        E('PLEASE_AUTH_WECHAT');

        return true;
    }

    // 设置输出用户信息
    public function format_user(&$user)
    {

        $user['uid'] = $this->_login->user['memUid'];
        $user['username'] = $this->_login->user['memUsername'];
        $user['mobilephone'] = $this->_login->user['memMobile'];
        $user['email'] = $this->_login->user['memEmail'];
        $user['weixin'] = $this->_login->user['memWeixin'];
        $user['gender'] = $this->_login->user['memGender'];
        $user['active'] = $this->_login->user['memActive'];
        $user['qywxstatus'] = $this->_login->user['memSubscribeStatus'];
        $user['face'] = $this->_login->user['memFace'];

        // 获取用户关联信息
        $this->getDepartments($user);

        return true;
    }

    /**
     * 获取用户关联部门信息
     *
     * @param $user
     *
     * @return bool
     */
    protected function getDepartments(&$user)
    {

        // 获取用户关联部门
        $memDepServ = &Department::instance();
        $departments = $memDepServ->list_dpId_by_uid($user['uid']);
        $user['department'] = [];
        if (!empty($departments)) {
            $user['department'] = $memDepServ->listById($departments, [
                'dpId',
                'dpName',
                'isChildDepartment',
                'childrensDepartmentCount',
                'departmentMemberCount',
                'dpLevelCount'
            ]);
            $user['department'] = array_values($user['department']);
        }

        return true;
    }

    /**
     * 获取微信 jsapi config
     *
     * @param string $url 当前页面URL
     *
     * @return array
     */
    protected function getJsConfig($url)
    {

        return WxApi::instance()->getJsSign($url);
    }

    /**
     * 判断是否允许外部人员
     *
     * @param $result
     * @param $gets
     *
     * @return bool
     */
    protected function isOuter(&$result, &$gets)
    {
        if (empty($this->_login->openid)) {
            return false;
        }

        // 外部用户信息
        $user = ['wx_openid' => $this->_login->openid];

        // 取jsapi授权签名相关
        $serv = &WxApi::instance();
        $jscfg = $serv->getJsSign($this->params['_fronturl']);
        $this->_result = array('user' => $user, 'jscfg' => $jscfg);
        
        return true;
    }
}
