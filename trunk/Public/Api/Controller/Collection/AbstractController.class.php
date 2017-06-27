<?php
/**
 * Collection/Api/Abstract
 * 收藏系统 API 抽象类
 * @author Xtong
 * @version $Id$
 */
namespace Api\Controller\Collection;

abstract class AbstractController extends \Common\Controller\Api\AbstractController
{

    /** CommonCollectionService */
    protected $_collectionService = null;

    /**
     * 执行前置动作
     * {@inheritDoc}
     * @see \Common\Controller\Api\AbstractController::before_action()
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
     * @see \Common\Controller\Api\AbstractController::after_action()
     */
    public function after_action($action = '')
    {
        if (!parent::after_action($action)) {
            return false;
        }

        return true;
    }
}
