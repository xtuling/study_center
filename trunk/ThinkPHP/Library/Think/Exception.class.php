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
namespace Think;

/**
 * ThinkPHP系统异常基类
 */
class Exception extends \Exception
{

    // 是否提示给用户, 有自定义错误号的, 都认为是显示给用户的错误提示
    protected $_is_show = true;

    /**
     * 异常构造方法
     *
     * @param string $message  异常详情
     * @param string $code     异常错误号
     * @param string $previous 前一个异常
     */
    public function __construct($message = null, $code = null, $previous = null)
    {

        // 如果是语言
        if (preg_match('/^[\w+\.\_]+$/i', $message)) {
            $message = L($message);
        }
        // 记录日志
        Log::write($message . "(No:{$code})");
        // 判断是否有错误编号
        if (preg_match('/^(\s*\d+\s*):/', $message)) {
            $this->_is_show = true;
            $pos = stripos($message, ':');
            $ncode = substr($message, 0, $pos);
            $message = substr($message, $pos + 1);
            // list($ncode, $message) = explode(":", $message);
            // 如果错误号为空, 则取详情中得编号
            if (empty($code)) {
                $code = $ncode;
            }
        }
        $code = (int)$code;

        parent::__construct($message, $code, $previous);
    }

    // 判断是否显示
    public function is_show()
    {

        return $this->_is_show;
    }
}
