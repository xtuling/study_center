<?php
/**
 * AbstractController.class.php
 * $author$
 */

namespace Common\Controller\Rpc;

use Think\Cache\Driver\Redis;
use Think\Controller\RpcController;
use Think\Db;
use Think\Db\Driver\Mysql;
use Think\Exception;
use Think\Log;

abstract class AbstractController extends RpcController
{

    /**
     * 全局配置
     * @var array
     */
    protected $_setting = array();

    /**
     * 允许的IP列表
     * @var unknown $_allow_ips
     */
    protected $_allow_ips = null;

    /**
     * 前置操作
     * @param string $action
     */
    public function before_action($action = '')
    {

        try {
            // 记录请求日志
            Log::record('Rpc.action: ' . $action . "\nRpc.arguments: " . var_export($this->get_arguments(), true));
            // 检查IP权限
            $this->_check_ip_privileges();
        } catch (Exception $e) {
            // 记录异常
            Log::record($e->getMessage() . ':' . $e->getCode());
            E($e->getMessage(), $e->getCode());

            return false;
        } catch (\Exception $e) {
            // 记录异常
            Log::record($e->getMessage() . ':' . $e->getCode());
            E($e->getMessage(), $e->getCode());

            return false;
        }

        return true;
    }

    /**
     * 后置操作
     * @param string $action
     */
    public function after_action($action = '')
    {

        return true;
    }

    /**
     * 清理各种连接
     *
     * @return bool
     */
    public function __destruct()
    {

        // 关闭 redis
        $redis = \Think\Cache::getInstance();
        if ($redis instanceof Redis) {
            $redis->close();
        }

        // 关闭 mysql
        $mysql = Db::getInstance();
        if ($mysql instanceof Mysql) {
            $mysql->close();
        }

        parent::__destruct();
    }

    /**
     * 检查IP权限
     *
     * @return bool
     */
    protected function _check_ip_privileges()
    {

        $ips = $this->_get_allow_ips();
        $clientip = get_client_ip();
        if (!empty($ips) && !in_array($clientip, $ips)) {
            E('_ERR_IP_DENIED');

            return false;
        }

        return true;
    }

    /**
     * 获取允许访问的IP列表
     *
     * @return array|null
     */
    protected function _get_allow_ips()
    {

        // 如果已经获取了IP了
        if (null !== $this->_allow_ips) {
            return $this->_allow_ips;
        }

        $this->_allow_ips = cfg('RPC_ALLOW_IPS');
        // 如果配置允许的IP列表
        if (empty($this->_allow_ips)) {
            $this->_allow_ips = array();

            return $this->_allow_ips;
        }

        // 如果配置非数组, 则
        if (!is_array($this->_allow_ips)) {
            $this->_allow_ips = array();
        }

        return $this->_allow_ips;
    }
}
