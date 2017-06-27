<?php
/**
 * EnterprisePlugin.class.php
 * 企业应用接口操作类
 *
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt LGPL
 * @copyright  Copyright (c) 2014 - ? VcySDK (http://www.vchangyi.com/)
 * @author     zhuxun37
 * @version    1.0.0
 */
namespace VcySDK;

use VcySDK\Config;
use VcySDK\Logger;

class EnterprisePlugin
{

    // 应用启用状态：启用
    const AVAILABLE_OPEN = 1;

    // 应用启用状态：已删除
    const AVAILABLE_DEL = 2;

    // 已关闭
    const AVAILABLE_CLOSE = 3;

    /**
     * 接口调用类
     *
     * @var object|Service
     */
    private $serv = null;

    /**
     * 应用安装
     * %s = {apiUrl}/b/{enumber}/enplugin/install
     *
     * @var string
     */
    const INSTALL_URL = '%s/enplugin/install';

    /**
     * 卸载应用
     * %s = {apiUrl}/b/{enumber}/enplugin/uninstall
     *
     * @var string
     */
    const UNINSTALL_URL = '%s/enplugin/uninstall';

    /**
     * 获取已安装应用详情
     * %s = {apiUrl}/b/{enumber}/enplugin/detail
     *
     * @var string
     */
    const GET_URL = '%s/enplugin/detail';

    /**
     * 获取已安装应用列表
     * %s = {apiUrl}/b/{enumber}/enplugin/list
     *
     * @var string
     */
    const LIST_URL = '%s/enplugin/list';

    /**
     * 套件应用授权
     * %s = {apiUrl}/b/suie/install
     *
     * @var string
     */
    const SUITE_INSTALL_URL = '%s/suite/install';

    /**
     * 获取套件授权登录信息
     * %s = {apiUrl}/b/suie/install
     *
     * @var string
     */
    const SUITE_LOGIN_INFO_URL = '%s/qy/login/wx-suite-login-info';

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
     * 开启和关闭状态, 都认为已安装
     *
     * @param int $available 可用状态
     *
     * @return bool
     */
    public function isInstall($available)
    {

        return in_array($available, array(self::AVAILABLE_OPEN, self::AVAILABLE_CLOSE));
    }

    /**
     * 生成Uc授权地址
     *
     * @param string $suiteId     套件ID
     * @param string $callbackUrl 回调地址
     * @param array  $appIds      授权的应用ID
     *
     * @return string
     * @throws Exception
     */
    public function getSuiteInstallUrl($suiteId, $callbackUrl = '', $appIds = array())
    {

        $params = array(
            'suiteId' => $suiteId,
            'epEnumber' => Config::instance()->enumber,
            'callbackUrl' => $callbackUrl,
            'appids' => implode(',', (array)$appIds)
        );

        return $this->serv->generateApiUrlS(self::SUITE_INSTALL_URL) . '?' . http_build_query($params);
    }

    /**
     * @param array $plugin 应用信息
     *
     * @return bool|mixed
     * @throws Exception
     */
    public function install($plugin)
    {

        return $this->serv->postSDK(self::INSTALL_URL, $plugin, 'generateApiUrlE');
    }

    /**
     * @param array $plugin 应用信息
     *                      + eplId string 企业应用ID
     *
     * @return bool|mixed
     * @throws Exception
     */
    public function uninstall($plugin)
    {

        return $this->serv->postSDK(self::UNINSTALL_URL, $plugin, 'generateApiUrlE');
    }

    /**
     * @param array $condition 应用查询条件
     *
     * @return bool|mixed
     * @throws Exception
     */
    public function fetch($condition)
    {

        return $this->serv->postSDK(self::GET_URL, $condition, 'generateApiUrlE');
    }

    /**
     * 获取企业应用列表
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
     * 获取套件授权登录信息
     *
     * @param $param
     *        + token 系统生成的登录信息凭证(只能使用一次)
     *
     * @return array|bool
     */
    public function suiteLoginInfo($param)
    {

        return $this->serv->postSDK(self::SUITE_LOGIN_INFO_URL, $param, 'generateApiUrlS');
    }
}
