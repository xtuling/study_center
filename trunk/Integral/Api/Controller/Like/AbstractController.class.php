<?php
/**
 * AbstractController.class.php
 * 基类
 * @author   : zhuxun37
 * @version  : $Id$
 * @copyright: vchangyi.com
 */

namespace Api\Controller\Like;

abstract class AbstractController extends \Api\Controller\AbstractController
{

    public function before_action($action = '')
    {

        if (!parent::before_action($action)) {
            return false;
        }

        return true;
    }

    public function after_action($action = '')
    {

        return parent::after_action();
    }
}
