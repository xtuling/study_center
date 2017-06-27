<?php
/**
 * Rpc.class.php
 * rpc 封装, 默认初始化操作
 * $Author$
 */
namespace Com;

class Rpc
{

    /**
     * 初始化 phprpc 类
     * 
     * @param mixed $data
     *            phprpc 所需的参数, 如果是字串, 则为 url, 如果是数组, 则第一个参数为 url, 第二个参数为秘钥
     * @return Ambigous <\PHPRPC_Client>
     */
    public static function &phprpc($data = array())
    {
        static $instance = array();
        // 如果初始化参数错误
        if (empty($data)) {
            E('_ERR_PHPRPC_INIT_PARAMS_EMPTY');
        }
        // 类型转换
        $data = (array) $data;
        // 如果第二个参数不存在, 则初始化为空字串
        if (2 > count($data)) {
            $data[] = '';
        }
        // 解出 url, secret
        list ($url, $secret) = $data;
        // 如果秘钥为空, 则取默认的 phprpc 秘钥
        if (empty($secret)) {
            $secret = cfg('PHPRPC_SECRET');
        }
        // 如果未初始化, 则进行初始化操作
        $md5 = md5($url . $secret);
        if (empty($instance[$md5])) {
            Vendor('phpRPC.phprpc_client');
            $instance[$md5] = new \PHPRPC_Client($url);
            $instance[$md5]->set_key($secret);
            $instance[$md5]->setEncryptMode(3);
        }
        
        return $instance[$md5];
    }

    /**
     * rpc 请求
     * 
     * @param array $data
     *            数据
     */
    public static function query(&$data)
    {
        // 获取参数数组
        $params = func_get_args();
        // url
        $url = $params[1];
        $func = $params[2];
        // 切出请求数据
        $params = array_slice($params, 3);
        // 请求
        $client = self::phprpc($url);
        $data = call_user_func_array(array(
            $client,
            $func
        ), $params);
        // 如果出错.
        if (self::is_error($data)) {
            \Think\Log::record('failed: ' . 'url=>' . $url . ';func=>' . $func . '; params=>' . var_export($params, true) . '; data:' . var_export($data, true));
            
            return false;
        } else {
            \Think\Log::record('succeed: ' . 'url=>' . $url . ';func=>' . $func . '; params=>' . var_export($params, true) . '; data:' . var_export($data, true));
        }
        
        return true;
    }

    /**
     * 检查 rpc 返回是否错误
     * 
     * @param mixed $object
     *            判断 rpc 的返回是否错误
     * @return boolean
     */
    public static function is_error($object)
    {
        if (is_object($object) && 'PHPRPC_Error' == get_class($object)) {
            \Think\Log::record('phprpc Error: ' . var_export($object, true));
            
            return true;
        } else {
            return false;
        }
    }
}
