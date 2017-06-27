<?php
/**
 * wxworkAuth.class.php
 * 企业微信获取用户基本信息（临时解决方案），只用于前端网页授权（CODE），属于应用层面的授权
 * @author Deepseath
 * @version $Id$
 */
namespace Com;

/**
 * 企业微信获取用户基本信息
 */
class WxworkAuth
{

    /**
     * 获取应用 access_token
     */
    const WXWORK_API_GET_ACCESS_TOKEN = 'https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=%s&corpsecret=%s';

    /**
     * 根据 CODE 获取用户基本信息
     */
    const WXWORK_API_GET_USER_INFO = 'https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo?access_token=%s&code=%s';

    /**
     * 企业 corpId
     */
    protected $_wxworkCorpId = '';

    /**
     * 所属应用的 secret
     */
    protected $_wxworkAppSecret = '';

    /**
     * 所属应用 appId
     */
    protected $_wxworkAppId = '';

    /**
     * 当前应用 token
     */
    private $_accessToken = '';

    /**
     * 网络请求对象
     */
    private $_request;

    /**
     * 构造方法：企业微信获取用户基本信息
     */
    public function __construct()
    {
        $this->_request = \VcySDK\Service::instance();
    }

    /**
     * 自配置文件验证当前企业是否采用企业微信新的应用 secret 方式获取身份
     * @return bool
     */
    public function useWXWorkGetUserInfo()
    {
        $appSecret = cfg('WXWORK_APP_SECRET');
        if (empty($appSecret)) {
            // 未配置则不使用
            return false;
        }

        // appid、secret 配置信息
        $setting = false;
        // 默认配置信息
        $defaults = [];
        // 查找当前应用所对应的配置信息
        foreach ($appSecret as $_app_dir => $_setting) {
            $_app_dir = strtolower($_app_dir);
            if ($_app_dir == 'default') {
                $defaults = $_setting;
                continue;
            }

            if ($_app_dir == strtolower(APP_DIR)) {
                $setting = $_setting;
                continue;
            }
        }

        // 如果没有应用对应则使用默认配置
        if ($setting === false) {
            $setting = $defaults;
        }

        $this->_wxworkAppId = $setting['app_id'];
        $this->_wxworkAppSecret = $setting['app_secret'];
        $this->_wxworkCorpId = $setting['corpid'];

        return true;
    }

    /**
     * 获取当前应用的 token
     */
    public function getAccessToken()
    {
        $appToken = $this->_cache($this->_wxworkAppId);
        if ($appToken !== false) {
            // 缓存存在；
            $this->_accessToken = $appToken['access_token'];
            return true;
        }

        $params = [
            self::WXWORK_API_GET_ACCESS_TOKEN,
            $this->_wxworkCorpId,
            $this->_wxworkAppSecret
        ];

        $token = '';
        $data = [];

        try {
            $this->_request($data, $this->apiUrl($params));
        } catch (\VcySDK\Exception $e) {
            \Think\Log::record('#getAccessToken#get app token::' . print_r($e, true));
            throw new \VcySDK\Exception($e);
        }

        // \Think\Log::record("#getAccessToken#AAA." . print_r($data, true));

        $data['expires_time'] = time() + $data['expires_in'];

        $this->_cache($this->_wxworkAppId, $data);
        // \Think\Log::record('#getAccessToken#BBB.' . print_r($data, true));

        return $data['access_token'];
    }

    /**
     * 根据 Code 值获取用户基本信息
     * @param string $code
     */
    public function getUserInfo($code)
    {
        $token = $this->getAccessToken();

        if (empty($token)) {
            return [];
        }

        $params = [
            self::WXWORK_API_GET_USER_INFO,
            $this->getAccessToken(),
            $code
        ];

        $data = [];

        try {
            $this->_request($data, $this->apiUrl($params));
        } catch (\VcySDK\Exception $e) {
            // 如果是未找到用户
            \Think\Log::record('get user info::' . print_r($e, true));
            throw new \VcySDK\Exception($e);
        }

        // \Think\Log::record('#getUserInfo#AAA.' . print_r($data, true));

        // 默认初始化为外部成员
        $user = [
            'source' => \Common\Common\User::SOURCE_TYPE_GUEST,
            'memUserid' => isset($data['OpenId']) ? $data['OpenId'] : (isset($data['UserId']) ? $data['UserId'] : '')
        ];

        if (isset($data['UserId'])) {
            // 企业内部成员
            $userService = new \Common\Common\User();
            $userList = $userService->listByConds([
                'userids' => [
                    $data['UserId']
                ]
            ]);

            // \Think\Log::record('#getUserInfo#BBB.' . print_r($userList, true));
            if (empty($userList) || ! is_array($userList)) {
                \Think\Log::record('获取内部成员数据失败！' . print_r($data, true));
                return $user;
            }
            foreach ($userList as $_user) {
                $user = $_user[0];
                break;
            }
            // \Think\Log::record('#getUserInfo#CCC.' . print_r($user, true));
            $user['source'] = isset($user['source']) ? $user['source'] : \Common\Common\User::SOURCE_TYPE_MEMBER;
        }

        return $user;
    }

    /**
     * 解析接口的参数变量
     * @param array $params
     * @return string
     */
    public function apiUrl($params = array())
    {
        return call_user_func_array('sprintf', $params);
    }

    /**
     * 缓存操作方法
     * @param string $appId 待获取/设置的应用ID
     * @param array $value 待设置缓存值的数据，未空则是获取数据
     */
    private function _cache($appId, $value = null)
    {
        // 缓存名称
        $cacheName = 'wxworkAccessToken';
        // 缓存数据类型
        settype($cacheName, 'array');
        // 获取缓存数据
        $list = S($cacheName);
        if (! empty($list)) {
            $list = unserialize($list);
        }
        // \Think\Log::record('#_cache#AAA.' . print_r($list, true));
        if (empty($list) || ! is_array($list)) {
            // 初始化缓存值
            $list = [];
        }
        if (! isset($list[$appId])) {
            // 初始化应用缓存信息
            $list[$appId] = [];
        }
        if ($value !== null) {
            // 更新缓存
            $list[$appId] = $value;
            S($cacheName, serialize($value));
            return $value;
        }

        $appTokenCache = $list[$appId];
        if (empty($appTokenCache) || empty($appTokenCache['expires_time'])) {
            // 不存在该应用的缓存
            return false;
        }
        if (time() - $appTokenCache['expires_time'] > $appTokenCache['expires_in'] - 1800) {
            // 缓存已过期，为避免 token 失效，取 token 实际过期周期偏小一些（1800）
            return false;
        }

        return $appTokenCache;
    }

    /**
     * 请求接口数据
     * @param mixed &$data 返回值
     * @param string $url 请求URL
     * @param string $reqParams 请求数据
     * @param mixed $headers 请求头部
     * @param string $method 请求方式, 如: GET/POST/DELETE/PUT
     * @param mixed $files 文件路径
     * @param bool $retry 是否重试
     * @return boolean
     * @throws \VcySDK\Exception
     */
    private function _request(&$data, $url, $reqParams = [], $headers = [], $method = 'GET', $files = null, $retry = false)
    {
        // 载入 Snoopy 类
        $snoopy = new \VcySDK\Net\Snoopy();
        // 使用自定义的头字段，格式为 array(字段名 => 值, ... ...)
        $headers = array();

        // \Think\Log::record("#_request#AAA.URL: {$url} Method: {$method} Post: " . var_export($reqParams, true) . " Header: " . var_export($headers, true));
        $snoopy->rawheaders = $headers;
        $method = rstrtoupper($method);
        // 非 GET 协议, 需要设置
        $methods = array(
            'POST',
            'PUT',
            'DELETE'
        );
        if (! in_array($method, $methods)) {
            $method = 'GET';
        }

        // 设置协议
        if (! empty($files)) {
            // 如果需要传文件
            $method = 'POST';
            $snoopy->set_submit_multipart();
        } else {
            $snoopy->set_submit_normal('');
        }

        // 判断协议
        $snoopy->set_submit_method($method);
        switch (rstrtoupper($method)) {
            case 'POST':
            case 'PUT':
            case 'DELETE':
                $result = $snoopy->submit($url, $reqParams, $files);
                break;
            default:
                // 如果有请求数据
                $this->_buildGetQuery($url, $reqParams);
                $result = $snoopy->fetch($url);
                break;
        }

        // 如果读取错误
        if (! $result || 200 != $snoopy->status) {
            \Think\Log::record('#_request#BBB.$snoopy[' . $method . '] error, url: ' . $url . '|post:' . var_export($reqParams, true) . '|result: ' . var_export($result, true) . '|status: ' . $snoopy->status . '|error: ' . $snoopy->error);
            // 出错时, 返回 $snoopy 对象
            $data = $snoopy;
            throw new \VcySDK\Exception(\VcySDK\Error::API_REQUEST_ERROR);
            return false;
        }

        // 获取返回数据
        $data = $snoopy->results;
        // 如果返回的是 JSON, 则解析 JSON
        if ($this->_isJson($snoopy->headers)) {
            $data = json_decode($data, true);
            // 如果接口返回错误, 则直接抛异常
            if (0 != $data['errcode']) {
                throw new \VcySDK\Exception($data['errmsg'] . '##' . $this->_wxworkAppId . '#' . $this->_wxworkAppSecret . '#' . $this->_wxworkCorpId . '#' . print_r($data, true) . '#' . $url, $data['errcode']);
                return false;
            }
        }

        // \Think\Log::record("#_request#CCC.result: " . var_export($data, true) . "|post: " . var_export($reqParams, true));
        // 如果返回的数据为空, 则
        if (empty($data)) {
            \Think\Log::record('#_request#DDD.$snoopy[' . $method . '] error, url: ' . $url . '|result: ' . var_export($result, true) . '|status: ' . $snoopy->status);
            // 出错时, 返回 $snoopy 对象
            $data = $snoopy;
            throw new \VcySDK\Exception(\VcySDK\Error::API_RESPONSE_DATA_EMPTY);
            return false;
        }

        return true;
    }

    /**
     * 拼凑请求URL
     * @param string $url URL地址
     * @param mixed $params 请求参数
     * @return boolean
     */
    private function _buildGetQuery(&$url, $params)
    {

        // 如果请求数据为空
        if (empty($params)) {
            return true;
        }

        // 拼凑 GET 字串
        if (is_array($params)) {
            $get_data = http_build_query($params);
        } else {
            $get_data = $params;
        }

        // 判断 URL 是否有参数
        if (false === strpos($url, '?')) {
            $url .= '?';
        } else {
            $url .= '&';
        }

        $url .= $get_data;

        return true;
    }

    /**
     * 判断返回值是否为Json数据
     * @param array $headers 头部数据
     * @return boolean
     */
    private function _isJson($headers)
    {

        // 如果头部信息已经解析出来, 则
        if (isset($headers['Content-Type'])) {
            return 0 === strpos($headers['Content-Type'], 'application/json');
        }

        // 遍历返回的头部信息
        foreach ($headers as $_header) {
            // 如果匹配到 json 头
            if (preg_match('/^Content-Type:\s*application\/json/i', $_header)) {
                return true;
            }
        }

        return false;
    }
}
