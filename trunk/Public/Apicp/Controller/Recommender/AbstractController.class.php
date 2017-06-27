<?php
/**
 * Recommender/Apicp/Abstract
 * 推荐系统 APICP 抽象类
 * @author Deepseath
 * @version $Id$
 */
namespace Apicp\Controller\Recommender;

abstract class AbstractController extends \Common\Controller\Apicp\AbstractController
{

    /**
     * 执行前置动作
     * {@inheritDoc}
     * @see \Common\Controller\Apicp\AbstractController::before_action()
     */
    public function before_action($action = '')
    {
        if (!parent::before_action($action)) {
            return false;
        }

        return true;
    }

    /**
     * 执行后置动作
     * {@inheritDoc}
     * @see \Common\Controller\Apicp\AbstractController::after_action()
     */
    public function after_action($action = '')
    {
        if (!parent::after_action($action)) {
            return false;
        }

        return true;
    }

}
