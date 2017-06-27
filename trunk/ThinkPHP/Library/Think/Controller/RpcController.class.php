<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace Think\Controller;

/**
 * ThinkPHP RPC控制器类
 */
class RpcController
{
    // 方法列表
    protected $allowMethodList = '';
    // 是否 debug
    protected $debug = false;
    // rpc server
    protected $_rpc_server = null;

    /**
     * 架构函数
     * 
     * @access public
     */
    public function __construct()
    {
        // 控制器初始化
        if (method_exists($this, '_initialize')) {
            $this->_initialize();
        }
        // 导入类库
        Vendor('phpRPC.phprpc_server');
        // 实例化phprpc
        $this->_rpc_server = new \PHPRPC_Server();
        if ($this->allowMethodList) {
            $methods = $this->allowMethodList;
        } else {
            $methods = get_class_methods($this);
            $methods = array_diff($methods, array(
                '__construct',
                '__call',
                '_initialize'
            ));
        }
        $this->_rpc_server->add($methods, $this);
        if (APP_DEBUG || $this->debug) {
            $this->_rpc_server->setDebugMode(true);
        }
        $this->_rpc_server->setEnableGZIP(true);
        // 设置 rpc 通讯秘钥
        $this->set_rpc_key();
        $this->_rpc_server->start();
        echo $this->_rpc_server->comment();
    }

    /**
     * 魔术方法 有不存在的操作的时候执行
     * 
     * @access public
     * @param string $method
     *            方法名
     * @param array $args
     *            参数
     * @return mixed
     */
    public function __call($method, $args)
    {}
    
    // 获取参数信息
    public function get_arguments()
    {
        return $this->_rpc_server->getArguments();
    }
    
    // 设置 rpc 秘钥
    public function set_rpc_key()
    {
        return true;
    }
}
