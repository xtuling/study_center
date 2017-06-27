<?php
/**
 * AbstractService.class.php
 * Service 层基类
 * @author: tangxingguo
 * @version: $Id$
 * @copyright: vchangyi.com
 */
namespace Common\Service;

use Common\Common\Tag;
use Common\Common\User;

abstract class AbstractService extends \Com\Service
{

    // 构造方法
    public function __construct()
    {
        parent::__construct();
    }
}
