<?php
/**
 * AbstractController.class.php
 * $author$
 */

namespace Apicp\Controller\Sms;

abstract class AbstractController extends \Common\Controller\Apicp\AbstractController
{

    /**
     * 不需要登录
     * @type bool
     */
    protected $_require_login = false;

}
