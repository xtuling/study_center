<?php
/**
 * AbstractController.class.php
 * $author$
 */

namespace Api\Controller\Sms;

abstract class AbstractController extends \Common\Controller\Api\AbstractController
{

    /**
     * 不需要登录
     * @type bool
     */
    protected $_require_login = false;

}
