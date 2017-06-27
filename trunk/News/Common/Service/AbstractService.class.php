<?php
/**
 * AbstractService.class.php
 * Service 层基类
 * @author: tangxingguo
 * @version: $Id$
 * @copyright: vchangyi.com
 */
namespace Common\Service;

use VcySDK\Service;
use Common\Common\Tag;
use Common\Common\User;
use Common\Common\Constant;
use Common\Model\RightModel;

abstract class AbstractService extends \Com\Service
{

    // 构造方法
    public function __construct()
    {
        $this->_right = new RightModel();

        parent::__construct();
    }
}
