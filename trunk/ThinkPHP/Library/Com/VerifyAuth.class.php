<?php
/**
 * VerifyAuth.class.php
 * 验证操作的权限
 * $Author$
 */
namespace Com;

use VcySDK\Adminer;
use VcySDK\Service;

class VerifyAuth
{

    /**
     * 权限缓存文件的文件名
     *
     * @var string
     */
    protected $_cache_file;

    /**
     * 缓存的配置
     *
     * @var array
     */
    protected $_cache_options;

    /**
     * 缓存的有权限的url数组
     *
     * @var array
     */
    protected $_cache_list = array();

    /**
     * 管理员ID
     *
     * @var string
     */
    protected $_eaId;

    /**
     * 当前操作
     *
     * @var string
     */
    protected $_current_action;

    /**
     * 实例化
     *
     * @param string $eaId 管理员ID
     *
     * @return VerifyAuth
     */
    public static function &instance($eaId)
    {

        static $instance = array();
        if (empty($instance[$eaId])) {
            $instance[$eaId] = new self($eaId);
        }

        return $instance[$eaId];
    }

    /**
     * 实例化
     *
     * @param string $eaId 管理员ID
     */
    public function __construct($eaId)
    {

        // 管理员ID
        $this->_eaId = $eaId;

        // 缓存的文件名
        $this->_cache_file = 'authAction_' . $eaId;

        // 缓存的配置
        $this->_cache_options = array(
            'temp' => get_sitedir()
        );

        // 初始化一下有权限的url数组
        $this->_get_cache();

        // 如果操作是Index就省略，保持和前端传过来的一致，才好判断相等
        $action = ACTION_NAME;
        $action = $action == 'Index' ? '' : '/' . $action;

        // 当前操作的地址
        $this->_current_action = APP_DIR . '/' . MODULE_NAME . '/' . CONTROLLER_NAME . $action;
    }

    /**
     * 验证权限
     *
     * @return boolean 有权限返回true，否则返回false
     */
    public function verify()
    {

        // 返回是否有权限
        return ! empty($this->_eaId) && is_array($this->_cache_list) && in_array($this->_current_action, $this->_cache_list);
    }

    /**
     * 获取管理员的菜单权限
     *
     * @param array $menu 菜单权限原始数据数组
     *
     * @return bool
     */
    protected function _get_eaCpmenu($menu = array())
    {

        // 如果原始数据是空，就请求一次UC获取
        if (empty($menu)) {
            $sdk = new Adminer(Service::instance());
            $info = $sdk->fetch(array(
                'eaId' => $this->_eaId
            ));

            $menu = isset($info['eaCpmenu']) ? unserialize($info['eaCpmenu']) : array();
        }

        // 如果原始数据是空的，就不继续处理了
        if (empty($menu)) {
            return true;
        }

        // 循环原始数据，摘出有权限的URL
        foreach ($menu as $v) {
            // 摘出url，存入$data
            if (isset($v['api']['url']) && ! empty($v['api']['url'])) {
                $this->_cache_list[] = $v['api']['url'];
            }

            // 如果还有下级，就递归
            if (isset($v['subMenu']) && ! empty($v['subMenu'])) {
                $this->_get_eaCpmenu($v['subMenu']);
            }
        }

        return true;
    }

    /**
     * 获取缓存中的有权限的url数组
     */
    protected function _get_cache()
    {

        // 读取缓存文件，获取有权限的操作
        $this->_cache_list = S($this->_cache_file, '', $this->_cache_options);

        // 如果缓存不存在就去重新缓存一下
        if (empty($this->_cache_list)) {
            $this->_get_eaCpmenu();
            S($this->_cache_file, $this->_cache_list, $this->_cache_options);
        }

        return true;
    }

}
