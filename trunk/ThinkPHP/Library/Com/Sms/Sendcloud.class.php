<?php
/**
 * @api sendcloud php对接短信接口
 */
namespace Com\Sms;

class Sendcloud
{
    /** 账号信息 */
    protected $_account = '';
    /** 密码 */
    protected $_passwd = '';
    /** 短信接口地址 */
    const SEND_BATCH_URL = 'http://sendcloud.sohu.com/smsapi/send';

    /**
     * &instance
     * 获取一个短信发送类的实例
     *
     * @return object
     */
    public static function &instance($account = null, $password = null)
    {
        static $instance = null;
        if (! $instance) {
            $instance = new Sendcloud($account, $password);
        }

        return $instance;
    }

    /**
     * __construct
     *
     * @param mixed $group
     * @return void
     */
    public function __construct($account = null, $password = null)
    {
        $this->_account = $account === null ? cfg('SMS_SENDCLOUD.ACCOUNT') : $account;
        $this->_passwd = $password === null ? cfg('SMS_SENDCLOUD.PASSWORD') : $password;
    }

    /**
     * 发送短信
     *
     * @param string $tplname
     *            模板名称
     * @param string $mobiles
     *            手机号码
     * @param string $vars
     *            变量名称
     * @param int $type
     *            类型名称
     */
    public function send(&$result, $tplname, $mobiles, $vars, $type = 0)
    {
        $sig = '';
        // 请求参数
        $params = array(
            'smsUser' => $this->_account,
            'templateId' => (string) $tplname,
            'msgType' => (string) $type,
            'phone' => is_array($mobiles) ? implode('|', $mobiles) : $mobiles,
            'vars' => json_encode($vars)
        );
        // 生成密钥
        $this->_generate_signature($sig, $params);
        $params['signature'] = $sig;
        // 生成请求数据
        $data = http_build_query($params);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-Type:application/x-www-form-urlencoded',
                'content' => $data
            )
        );
        // 请求接口
        $context = stream_context_create($options);
        $result = file_get_contents(self::SEND_BATCH_URL, FILE_TEXT, $context);
        // 记录发送日志
        \Think\Log::record('params:' . var_export($params, true));
        // 判断返回值
        if (! $this->_check_result($result)) {
            return false;
        }

        return true;
    }

    /**
     * 生成签名
     *
     * @param string $sig
     *            签名
     * @param array $params
     *            签名所必须的参数
     * @return boolean
     */
    protected function _generate_signature(&$sig, $params)
    {
        $param_str = "";
        ksort($params);
        foreach ($params as $_key => $_value) {
            $param_str .= $_key . '=' . $_value . '&';
        }
        $param_str = trim($param_str, '&');
        $sig = md5($this->_passwd . "&" . $param_str . "&" . $this->_passwd);

        return true;
    }

    /**
     * 判断返回值
     *
     * @param string $result
     *            接口返回值
     */
    protected function _check_result($result)
    {
        $res = json_decode($result, true);
        // 如果返回错误
        if (null === $res || ! isset($res['statusCode']) || 200 != $res['statusCode']) {
            \Think\Log::record('send error, result:' . $result);

            return false;
        }
        \Think\Log::record('send ok, result:' . $result);

        return true;
    }
}
