<?php
/**
 * Sms.php
 * 短信消息服务
 */
namespace Com\Sms;

class Jianzhou
{
    /** 用户名 */
    protected $_account = '';
    /** 密码 */
    protected $_passwd = '';
    /** 发送普通短信 */
    const SIMPLE_URL = 'http://www.jianzhou.sh.cn/JianzhouSMSWSServer/http/sendBatchMessage';

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
            $instance = new Jianzhou($account, $password);
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
        $this->_account = $account === null ? cfg('SMS_JIANZHOU.ACCOUNT') : $account;
        $this->_passwd = $password === null ? cfg('SMS_JIANZHOU.PASSWORD') : $password;
    }

    /**
     * 批量发送消息
     *
     * @param int $result
     *            发送状态值
     * @param array $mobiles
     *            目标手机
     * @param string $msg
     *            消息
     * @param string $timed
     *            定时时间
     */
    public function send_batch_message($result, $mobiles, $msg, $timed = '')
    {
        // 如果发送的用户名和密码为空
        if (empty($this->_account) || empty($this->_passwd)) {
            E('_err_sms_account_or_passwd_is_empty');

            return false;
        }
        // 如果接收手机号码为空
        $mobiles = (array) $mobiles;
        if (empty($mobiles)) {
            E('_err_sms_mobile_is_empty');

            return false;
        }
        // 如果信息为空
        if (empty($msg)) {
            E('_err_sms_msg_is_empty');

            return false;
        }
        // 使用 snoopy 进行发送
        $result = '';
        $data = array(
            'account' => $this->_account,
            'password' => $this->_passwd,
            'destmobile' => implode(';', $mobiles),
            'msgText' => $msg,
            'sendDateTime' => $timed
        );
        // 消息发送
        if (! rfopen($result, self::SIMPLE_URL, $data, null, 'post')) {
            E('_err_sms_submit_error');

            return false;
        }
        // 获取结果
        $ret = (int) $result;
        // 如果是小于等于 0 的值, 则说明出错了
        if (0 >= $ret) {
            E(L('_err_sms_send_error', array(
                'error' => $result
            )));

            return false;
        }

        return true;
    }
}
