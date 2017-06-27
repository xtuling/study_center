<?php
/**
 * Error.class.php
 * $author$
 */
namespace Com;

use Think\Exception;

class Error
{

    /**
     * 错误号
     *
     * @type array
     */
    protected $_errcodes = array(
        0
    );

    /**
     * 错误信息
     *
     * @type array
     */
    protected $_errmsgs = array(
        'ok'
    );

    public static function &instance()
    {

        static $instance = null;
        if (empty($instance)) {
            $instance = new self();
        }

        return $instance;
    }

    public function __construct()
    {
        // do nothing.
    }

    // 获取错误码
    public function get_errcode()
    {

        return end($this->_errcodes);
    }

    // 获取错误详情
    public function get_errmsg()
    {

        return end($this->_errmsgs);
    }

    /**
     * 设置错误信息
     *
     * @param mixed $message 错误信息
     * @param int   $code    错误号
     *
     * @return bool
     */
    public function set_error($message, $code = 0)
    {

        // 如果需要返回的是异常
        if ($message instanceof Exception) {
            // 如果是显示给用户的错误
            if ($message->is_show() || APP_DEBUG) {
                $this->_errcodes[] = 0 < $message->getCode() ? $message->getCode() : 500;
                $this->_errmsgs[] = $message->getMessage();
            } else { // 如果是系统错误, 则显示默认错误
                return $this->set_error('_ERR_DEFAULT');
            }
        } elseif ($message instanceof \Exception) { // 系统报错
            if (APP_DEBUG) { // 如果是 debug 状态
                $this->_errcodes[] = $message->getCode();
                $this->_errmsgs[] = $message->getMessage();
            } else {
                return $this->set_error('_ERR_DEFAULT');
            }
        } else {
            // 如果是语言
            if (preg_match('/^[\w+\.\_]+$/i', $message)) {
                $message = L($message);
            }
            // 判断是否有错误编号
            if (preg_match('/^(\s*\d+\s*):/', $message)) {
                // list($ncode, $message) = explode(":", $message);
                $pos = stripos($message, ':');
                $ncode = substr($message, 0, $pos);
                $message = substr($message, $pos + 1);
                // 如果错误号为空, 则取详情中得编号
                if (empty($code)) {
                    $code = $ncode;
                }
            }
            $this->_errcodes[] = $code;
            $this->_errmsgs[] = $message;
        }

        return true;
    }

}
