<?php
/**
 * AbstractController.class.php
 * $author$
 */

namespace Apicp\Controller\Admin;

abstract class AbstractAnonymousController extends \Common\Controller\Apicp\AbstractController
{

    /**
     * 是否必须登录
     *
     * @var string
     */
    protected $_require_login = false;

}
