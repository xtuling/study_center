<?php
/**
 * @api 创蓝php对接短信接口
 */
namespace Com\Sms;

class Chuanglan
{
    /** 账号信息 */
    protected $_account = '';
    /** 密码 */
    protected $_passwd = '';
    /** 短信接口地址 */
    const SEND_BATCH_URL = 'http://222.73.117.156:80/msg/HttpBatchSendSM';

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
            $instance = new Chuanglan($account, $password);
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
        $this->_account = $account === null ? cfg('SMS_CHUANGLAN.ACCOUNT') : $account;
        $this->_passwd = $password === null ? cfg('SMS_CHUANGLAN.PASSWORD') : $password;
    }

    /**
     * 发送短信
     *
     * @param string $mobile
     *            手机号码
     * @param string $msg
     *            短信内容
     * @param string $needstatus
     *            是否需要状态报告
     * @param string $product
     *            产品id，可选
     * @param string $extno
     *            扩展码，可选
     */
    public function send(&$result, $mobile, $msg, $needstatus = 'true', $product = '', $extno = '')
    {
        // 如果手机号码或内容为空
        if (empty($mobile) || empty($msg)) {
            \Think\Log::record('mobile or msg is empty, mobile:' . $mobile . '; msg:' . $msg);

            return false;
        }
        // 创蓝接口参数
        $post = array(
            'account' => $this->_account,
            'pswd' => $this->_passwd,
            'msg' => $msg,
            'mobile' => is_array($mobile) ? implode(',', $mobile) : $mobile,
            'needstatus' => $needstatus,
            'product' => $product,
            'extno' => $extno
        );
        // 发送
        $result = $this->__curl_post(self::SEND_BATCH_URL, $post);
        // 记录发送日志
        \Think\Log::record('mobile:' . var_export($mobile, true) . '; msg:' . $msg, \Think\Log::ALERT);
        // 判断返回值
        $ymdhis = '';
        $status = 0;
        $msgid = '';
        if (! $this->_check_result($ymdhis, $status, $msgid, $result)) {
            return false;
        }

        return true;
    }

    /**
     * 判断返回值
     *
     * @param int $ymdhis
     *            年月日时分秒
     * @param int $status
     *            状态, 0: 正常
     * @param string $msgid
     *            消息id
     * @param string $result
     *            接口返回值
     */
    protected function _check_result(&$ymdhis, &$status, &$msgid, $result)
    {
        list ($ymdhis_status, $msgid) = explode("\n", $result);
        list ($ymdhis, $status, $msgid) = explode(',', $ymdhis_status);
        // 如果返回错误
        if (0 != $status) {
            \Think\Log::record('send error, result:' . $result);

            return false;
        }
        \Think\Log::record('send ok, result:' . $result, \Think\Log::ALERT);

        return true;
    }

    /**
     * 查询额度
     */
    public function query_balance()
    {
        // 查询参数
        $post = array(
            'account' => $this->_account,
            'pswd' => $this->_passwd
        );
        $result = $this->__curl_post(self::QUERY_BALANCE_URL, $post);

        return $result;
    }

    /**
     * 处理返回值
     */
    public function exec_result($result)
    {
        $result = preg_split("/[,\r\n]/", $result);

        return $result;
    }

    /**
     * 通过CURL发送HTTP请求
     *
     * @param string $url
     *            //请求URL
     * @param array $post_data
     *            //请求参数
     * @return mixed
     */
    private function __curl_post($url, $post_data)
    {
        $post_data = http_build_query($post_data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    // 魔术获取
    public function __get($name)
    {
        return $this->$name;
    }

    // 魔术设置
    public function __set($name, $value)
    {
        $this->$name = $value;
    }
}
