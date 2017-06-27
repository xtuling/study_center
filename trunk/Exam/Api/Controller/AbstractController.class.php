<?php
/**
 * Created by PhpStorm.
 * User: yingcai
 * Date: 2017/4/14
 * Time: 上午09:55
 */

namespace Api\Controller;

use \Common\Controller\Api\AbstractController as BaseAbstractController;

abstract class AbstractController extends BaseAbstractController
{
    const DEFAULT_LIMIT = 15;
    const DEFAULT_LIMIT_ONE = 1;
    const DEFAULT_PAGE = 1;
}
