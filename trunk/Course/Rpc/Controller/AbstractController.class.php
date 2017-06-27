<?php
/**
 * RPC/Abstract
 * 推荐系统 RPC 抽象类
 * @author Deepseath
 * @version $Id$
 */
namespace Rpc\Controller;

use Com\Error;
use Think\Log;

abstract class AbstractController extends \Common\Controller\Rpc\AbstractController
{
    /**
     * 外部传入的参数
     */
    protected $_params = [];

    /**
     * 执行前置动作
     * {@inheritDoc}
     * @see \Common\Controller\Rpc\AbstractController::before_action()
     */
    public function before_action($action = '')
    {
        if (!parent::before_action($action)) {
            return false;
        }
        $this->_params = $this->get_arguments();
        return true;
    }

    /**
     * 执行后置动作
     * {@inheritDoc}
     * @see \Common\Controller\Rpc\AbstractController::after_action()
     */
    public function after_action($action = '')
    {
        if (!parent::after_action($action)) {
            return false;
        }

        return true;
    }

    /**
     * 设置输出错误信息
     * @param mixed $message 错误信息
     * @param int $code 错误号
     * @return bool
     */
    protected function _set_error($message, $code = 0)
    {
        Error::instance()->set_error($message, $code);
        Log::record($message);
        return $code ? false : true;
    }
}
