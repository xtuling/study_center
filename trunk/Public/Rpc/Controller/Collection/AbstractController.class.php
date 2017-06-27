<?php
/**
 * RPC/Abstract
 * 收藏系统 RPC 抽象类
 * @author Xtong
 * @version $Id$
 */
namespace Rpc\Controller\Collection;

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

//    /**
//     * 构造方法
//     */
//    public function __construct()
//    {
//        $this->_params = $this->get_arguments();
//    }

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

    /**
     * 检查关键参数是否传入
     * @return boolean
     */
    protected function _checkKeyParams()
    {
        // app uid dataId
        $keys = [
            'app',
            'uid',
            'dataId'
        ];
        foreach ($keys as $_key) {
            if (!isset($this->_params[$_key])) {
                return $this->_set_error('_ERR_RECOMMENDER_ICON_PARAM_LOSE_90000');
            }
        }

        return true;
    }
}
