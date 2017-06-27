<?php
/**
 * 检查手机和密码, 并返回匹配记录列表
 * Created by IntelliJ IDEA.
 * User: zhuxun37
 * Date: 2016/7/29
 * Time: 15:35
 */

namespace Apicp\Controller\Admin;

use Com\Validator;
use Common\Service\CommonHidemenuService;
use VcySDK\Service;
use VcySDK\Adminer;

class ChkMobilePwdController extends AbstractAnonymousController
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
        $mobile =I('post.mobile'); // 用户手机
        $passwd =I('post.passwd'); // 密码
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

        $servHideMenu = new CommonHidemenuService();

        // 遍历管理员列表, 反序列化权限字串
        foreach ($adminers as $key => &$_adminer) {
            // 如果用户信息不存在
            if (! isset($_adminer['adminerInfo']) || ! is_array($_adminer['adminerInfo'])) {
                continue;
            }
            // 被禁用(过滤企业)
            if (isset($_adminer['adminerInfo']['eaUserstatus']) && $_adminer['adminerInfo']['eaUserstatus'] == Adminer::MANAGER_DISABLE_LOGIN) {
                unset($adminers[$key]);
            }

            // 生成登录用token, 用于之后的用户选择指定企业进行登陆的操作
            $_adminer['adminerInfo']['loginToken'] = $this->_login->generateLoginToken($_adminer['enterpriseInfo']['epEnumber'], $_adminer['adminerInfo']['eaId'], $passwd);

            // 定制企业用户需要隐藏的菜单
            $hideMenu = $servHideMenu->getMenus($_adminer['enterpriseInfo']['epEnumber']);
            $_adminer['enterpriseInfo']['hideMenu'] = empty($hideMenu['menus']) ? [] : unserialize($hideMenu['menus']) ;
        }


        return $adminers;
    }

}
