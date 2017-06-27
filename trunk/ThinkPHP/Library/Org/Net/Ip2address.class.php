<?php
/**
 * ip2address.php
 * IP地址转换地理位置类
 * Create By Deepseath
 * $Author$
 * $Id$
 */
namespace Org\Net;

use Think\Log;

class Ip2address
{

    /**
     * 返回的结果
     */
    public $result = array(
        'address' => '', // 完整地址信息字符串
        'address_list' => array(), // 地址信息数组
        'source' => ''
    ); // 接口获取到的原始数据


    /**
     * 错误编码
     */
    public $errcode = 0;

    /**
     * 错误描述
     */
    public $errmsg = '';

    /**
     * 淘宝ip库接口url
     */
    const URI_TAOBAO = 'http://ip.taobao.com/service/getIpInfo.php?ip=%s';

    /**
     * 首选使用的服务：local=本地ip库、taobao=淘宝ip库
     */
    protected $_first_service = 'taobao';

    /**
     * 待查询的IP地址
     */
    protected $_ip = '';

    /**
     * 请求的url
     */
    protected $__request_url = '';

    /**
     * Snoopy类
     */
    protected $__snoopy = null;

    public function __construct($first_service = 'taobao')
    {
        $this->_first_service = $first_service;
    }

    /**
     * 获取IP所在地
     *
     * @param string $ip
     * @return boolean
     */
    public function get($ip)
    {
        $this->_ip = $ip;
        $validator = new \Com\Validator();
        if (! $validator->is_ip($this->_ip)) {
            return false;
        }
        // 多个获取方式
        $get_methods = array(
            'local',
            'taobao'
        );
        // 首选获取服务方法
        $method = '_get_by_' . $this->_first_service;
        // 临时的错误代码和错误消息
        $errcode = 0;
        $errmsg = '';
        $result = '';
        // 使用首选服务出错，则尝试其他服务
        if (! $this->$method()) {
            // 将首选服务的错误信息输出
            $errcode = $this->errcode;
            $errmsg = $this->errmsg;
            // 遍历其他方法获取
            foreach ($get_methods as $_method) {
                if (rstrtolower($_method) == rstrtolower($this->_first_service)) {
                    // 由于已过使用首选方法，则忽略
                    continue;
                }
                $method = '_get_by_' . $_method;
                if ($this->$method()) {
                    // 获取成功则退出
                    break;
                }
            }
        }
        // 输出首选服务的错误
        if ($errcode) {
            $this->errcode = $errcode;
            $this->errmsg = $errmsg;
        }

        return true;
    }

    /**
     * TODO 本地解析方式尚未实现
     * 通过本地纯真库获取IP所在地位置
     *
     * @return boolean
     */
    protected function _get_by_local()
    {
        $this->errcode = 100;
        $this->errmsg = '尚未实现本地化';

        return false;
    }

    /**
     * 通过淘宝IP API接口获取地址
     *
     * @return boolean
     */
    protected function _get_by_taobao()
    {
        // 将ip地址变量赋值
        $url = sprintf(self::URI_TAOBAO, $this->_ip);
        // 初始化结果
        $sdata = array();
        if (! $this->_get_json_by_httprequest($sdata, $url)) {
            $this->errcode = 1002;
            $this->errmsg = '解析TAOBAO数据发生错误';
            // Log::record('taobao ip api error: '.$url.'|'.$this->errcode.'|'.$this->errmsg);
            Log::record('taobao ip api error: ' . $url . '|' . $this->errcode . '|' . $this->errmsg);

            return false;
        }
        // 无法获取结果集中的返回码，可能该接口发生变动
        if (! isset($sdata['code'])) {
            $this->errcode = '1003';
            $this->errmsg = '获取 IP 位置接口错误';
            Log::record('taobao ip api error: ' . $url . '|' . $this->errcode . '|' . $this->errmsg);

            return false;
        }
        // 结果集中返回码不为空，则获取地址发生解析错误
        if (! empty($sdata['code'])) {
            $this->errcode = '1002';
            $this->errmsg = '获取 IP 位置信息发生错误(code:' . $sdata['code'] . ')';
            Log::record('tobao ip api error:' . $url . '|' . $this->errcode . '|' . $this->errmsg . '|' . $sdata['code'] . '|' . $sdata['data']);

            return false;
        }
        // 如果结果集中数据为空，则可能出现意外的错误
        if (empty($sdata['data'])) {
            $this->errcode = 1004;
            $this->errmsg = '获取 IP 位置信息发生错误';
            Log::record('taobao ip api error:' . $url . '|' . $this->errcode . '|' . $this->errmsg);

            return false;
        }
        // 地址信息原始结果数组
        $data = $sdata['data'];
        // 地址信息数组
        $address_list = array(
            'country' => '',
            'region' => '',
            'city' => '',
            'county' => ''
        );
        // 地址信息字符串
        $address = '';
        // 国家
        if (! empty($data['country']) && is_scalar($data['country'])) {
            $address_list['country'] = trim($data['country']);
            if (! isset($data['country_id']) || rstrtolower($data['country_id']) != 'cn') {
                // 不显示“中国”
                $address .= $address_list['country'];
            }
        }
        // 省份
        if (! empty($data['region']) && is_scalar($data['region'])) {
            $address_list['region'] = trim($data['region']);
            $address .= $address_list['region'];
        }
        // 城市
        if (! empty($data['city']) && is_scalar($data['city'])) {
            $address_list['city'] = trim($data['city']);
            // 省份与城市不一致则显示，主要用于直辖市不重复显示
            if ($address_list['city'] != $address_list['region']) {
                $address .= $address_list['city'];
            }
        }
        // 县
        if (! empty($data['county']) && is_scalar($data['county'])) {
            $address_list['county'] = trim($data['county']);
            $address .= $address_list['county'];
        }
        // 输出结果
        $this->result = array(
            'address' => $address,
            'address_list' => $address_list,
            'source' => $data
        );

        return true;
    }

    /**
     * 通过http请求获取json结果数据
     *
     * @param string $data
     *            <strong style="color:red">(引用结果)</strong>返回的结果
     * @param string $url
     *            请求的url
     * @param string $post
     *            请求的数据
     * @param array $http_header
     *            请求的header头信息
     * @param string $http_method
     *            请求方法
     * @param object $snoopy_reporting
     *            <strong style="color:red">(引用结果)</strong>snoopy信息
     * @return boolean
     */
    protected function _get_json_by_httprequest(&$data, $url, $post = array(), $http_header = array(), $http_method = 'GET', &$snoopy_reporting = array())
    {
        // 初始化结果数据
        $data = '';
        // 通过网络读取数据
        $http_data = '';
        if ($this->__httprequest($http_data, $url, $post, $http_header, $http_method, $snoopy_reporting) === false) {
            return false;
        }
        // 尝试解析json
        $data = @json_decode(trim($http_data), true);
        // 解析失败
        if ($data === null) {
            $this->errcode = 1003;
            $this->errmsg = '解析JSON数据发生错误';
            Log::record('json parse error: ' . $this->__request_url . '|' . $http_data . '|' . $this->errcode . '|' . $this->errmsg);

            return false;
        }

        return true;
    }

    /**
     * http请求私有方法
     *
     * @param string $data
     *            <strong style="color:red">(引用结果)</strong>请求结果
     * @param string $url
     *            请求的url
     * @param string $post
     *            请求的数据
     * @param array $http_header
     *            请求的header头信息
     * @param string $http_method
     *            请求方法
     * @param object $snoopy_reporting
     *            <strong style="color:red">(引用结果)</strong>snoopy信息
     * @return boolean
     */
    protected function __httprequest(&$data, $url, $post = array(), $http_header = array(), $http_method = 'GET', &$snoopy_reporting = array())
    {
        // 初始化结果数据
        $data = '';
        $this->__request_url = $url;
        // 载入 Snoopy 类
        if ($this->__snoopy === null) {
            $this->__snoopy = new \Org\Net\Snoopy();
        }
        // 使用自定义的头字段，格式为 array(字段名 => 值, ... ...)
        if (! empty($http_header) && is_array($http_header)) {
            $this->__snoopy->rawheaders = $http_header;
        }
        switch (rstrtoupper($http_method)) {
            case 'POST': // 使用 POST 协议
            case 'PUT': // 使用 PUT 协议
                $result = $this->__snoopy->submit($url, $post);
                break;
            case 'DELETE': // 使用 DELETE 协议
                $result = $this->__snoopy->submit_by_delete($url, $post);
                break;
            default: // 使用 GET 协议
                if ($post) {
                    if (is_array($post)) {
                        $get_data = http_build_query($post);
                    } else {
                        $get_data = $post;
                    }
                    if (strpos($url, '?') === false) {
                        $url .= '?';
                    } else {
                        $url .= '&';
                    }
                    $url .= $get_data;
                }
                $result = $this->__snoopy->fetch($url);
        }
        // 将Snoopy信息返回，用于调试
        $snoopy_reporting = $this->__snoopy;
        // 如果读取错误
        if (! $result || 200 != $this->__snoopy->status) {
            $this->errcode = 1004;
            $this->errmsg = '请求接口发生网络错误';
            Log::record('$snoopy->submit error: ' . $url . '|' . $result . '|' . $this->__snoopy->status . '|' . $this->errcode . '|' . $this->errmsg);

            return false;
        }
        // 返回结果数据
        $data = $this->__snoopy->results;

        return true;
    }
}
