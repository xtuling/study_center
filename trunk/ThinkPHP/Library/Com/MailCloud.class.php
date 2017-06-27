<?php
/**
 * MailCloud.class.php
 * 邮件发送服务
 */
namespace Com;

class MailCloud
{
    /** 用户名 */
    protected $_account = '';
    /** 密码 */
    protected $_passwd = '';
    /** 发送人邮箱 */
    protected $_from = '';
    /** 发送人名字 */
    protected $_fromname = '';
    /** 发送普通短信 */
    const TPL_URL = 'https://sendcloud.sohu.com/webapi/mail.send_template.xml';

    /**
     * &get_instance
     * 获取一个短信发送类的实例
     *
     * @return object
     */
    public static function &instance()
    {
        static $instance = null;
        if (! $instance) {
            $instance = new MailCloud();
        }

        return $instance;
    }

    /**
     * __construct
     *
     * @param mixed $group
     * @return void
     */
    public function __construct()
    {
        $this->_account = cfg('MAILCLOUD.ACCOUNT');
        $this->_passwd = cfg('MAILCLOUD.PASSWORD');
        $this->_from = cfg('MAILCLOUD.FROM');
        $this->_fromname = cfg('MAILCLOUD.FROMNAME');
    }

    /**
     * 发送模板邮件
     *
     * @param string $tpl_name
     *            模板名称
     * @param array $mails
     *            接收人邮箱地址
     * @param string $subject
     *            邮箱主题
     * @param array $vars
     *            模板邮件的变量值, 保持和接收人邮箱地址一致的顺序
     */
    public function send_tpl_mail($tpl_name, $mails, $subject, $vars = array(), $from = '', $fromname = '')
    {
        $tpl_vars = array(
            'to' => $mails,
            'sub' => $vars
        );
        // 判断发送人(邮箱)是否为空
        if (empty($from)) {
            $from = $this->_from;
        }
        // 判断发送人名称是否
        if (empty($fromname)) {
            $fromname = $this->_fromname;
        }
        // 发送参数
        $param = array(
            'api_user' => $this->_account,
            'api_key' => $this->_passwd,
            'from' => $from,
            'fromname' => $fromname,
            'template_invoke_name' => $tpl_name,
            'subject' => $subject,
            'substitution_vars' => json_encode($tpl_vars)
        );
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => "Connection: close\r\nContent-type: application/x-www-form-urlencoded",
                'content' => http_build_query($param)
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents(self::TPL_URL, false, $context);
        // 解析xml
        $xml = (array) simplexml_load_string($result);
        // 如果成功
        if (isset($xml['message']) && 'success' == trim($xml['message'])) {
            return true;
        }
        // 记录错误日志
        \Think\Log::record(self::TPL_URL . '=>' . $result . '=>' . var_export($param, true));

        return false;
    }
}
