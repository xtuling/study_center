<?php
/**
 * ApiSig.class.php
 * 全局 Api 签名验证算法
 *
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt LGPL
 * @copyright  Copyright (c) 2014 - ? VcySDK (http://www.vchangyi.com/)
 * @author     zhuxun37
 * @version    1.0.0
 */

namespace VcySDK;

class ApiSig
{

    /**
     * 秘钥
     *
     * @var $secret
     */
    protected $secret = '';

    /**
     * 签名有效时间(单位: 秒)
     *
     * @var $expire
     */
    protected $expire = 600;

    /**
     * 实例化
     *
     * @param string $secret 秘钥
     * @param int    $expire 有效时间
     *
     * @return ApiSig
     */
    public static function &instance($secret = '', $expire = 600)
    {

        static $instance;
        // 有效时间
        $expire = empty($expire) ? Config::instance()->apiSigExpire : $expire;
        // 秘钥
        $secret = empty($secret) ? Config::instance()->apiSecret : $secret;
        // 根据秘钥初始化
        $md5 = md5($secret);
        if (empty($instance[$md5])) {
            $instance[$md5] = new self($secret, $expire);
        }

        return $instance[$md5];
    }

    public function __construct($secret, $expire)
    {

        $this->secret = $secret;
        $this->expire = $expire;
    }

    /**
     * 生成签名
     *
     * @param mixed $obj    签名数据
     * @param array $params 参数
     *
     * @return string
     */
    public function getSig($obj, $params = array())
    {

        // 签名步骤一：按字典序排序参数
        $obj['timestamp'] = ! empty($params['timestamp']) ? $params['timestamp'] : NOW_TIME;
        $String = $this->formatBizQueryParaMap($obj);

        // 签名步骤二：在string后加入KEY
        $String = $String . $this->secret;

        // 签名步骤三: 加密字符串,并大写
        $String = strtoupper(sha1($String));

        return $String;
    }

    /**
     * 格式化参数
     *
     * @param $paraMap
     * @param $urlencode
     *
     * @return string
     */
    protected function formatBizQueryParaMap($paraMap, $urlencode = false)
    {

        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $v) {
            if ($urlencode) {
                $v = urlencode($v);
            }
            $buff .= is_array($v) ? $this->formatBizQueryParaMap($v) : $v;
        }

        return $buff;
    }

    /**
     * 获取签名等参数并验证
     *
     * @param array  $data   要签名的数组
     * @param string $method 获取数据的方式 GET | POST
     *
     * @return bool
     * @throws \VcySDK\Exception
     */
    public function getParamAndCheck($data, $method = 'POST')
    {

        if (empty($data)) {
            // 获取数据
            $data = $method == 'POST' ? I('post.') : I('get.');
        }

        // 验证签名
        if ($this->check($data)) {
            return $data;
        };

        return false;
    }

    /**
     * 检查默认 sig
     *
     * @param $param
     *
     * @return bool
     * @throws \VcySDK\Exception
     */
    public function check($param)
    {

        // 检查时间
        if ($param['timestamp'] + $this->expire < NOW_TIME) {
            throw new Exception(Error::APISIG_REQUEST_EXPIRED);
            return false;
        }

        // 如果签名不相等
        if ($param['sign'] != $this->getSig($param)) {
            throw new Exception(Error::APISIG_ERROR);
            return false;
        }

        return true;
    }
}
