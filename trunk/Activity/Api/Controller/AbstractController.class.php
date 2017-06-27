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
    // 默认分页参数
    const DEFAULT_LIMIT = 15;

}
