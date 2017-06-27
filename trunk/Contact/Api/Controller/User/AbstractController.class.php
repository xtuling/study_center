<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/9/18
 * Time: 11:11
 */

namespace Api\Controller\User;

use Common\Common\Cache;

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

