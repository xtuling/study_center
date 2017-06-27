<?php
/**
 * AbstractController.class.php
 * $author$
 */

namespace Apicp\Controller\ImgCode;

abstract class AbstractController extends \Common\Controller\Apicp\AbstractController
{

    /**
     * 跳过登陆验证
     *
     * @var string
     */
    protected $_require_login = false;

}
