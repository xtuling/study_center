<?php
/**
 * Created by PhpStorm.
 *
 * User: zhoutao
 * Date: 16/7/14
 * Time: 下午2:56
 */

namespace Frontend\Controller\Index;

abstract class AbstractController extends \Common\Controller\Frontend\AbstractController
{
    public function before_action($action = '')
    {

        return parent::before_action($action);
    }

    public function after_action($action = '')
    {

        parent::after_action($action);
    }
}
