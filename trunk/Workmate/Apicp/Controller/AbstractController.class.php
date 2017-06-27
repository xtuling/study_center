<?php
/**
 * 同事圈
 * User: 代军
 * Date: 2017-04-24
 */
namespace Apicp\Controller;

use \Common\Controller\Apicp\AbstractController as BaseAbstractController;

abstract class AbstractController extends BaseAbstractController
{

    // 每页条数
    const DEFAULT_LIMIT = 15;

    // 帖子待审核
    const AUDIT_ING = 0;
    // 帖子审核通过
    const AUDIT_OK = 1;
    // 帖子审核驳回
    const AUDIT_NO = 2;

    // 后台审核标识
    const AUDIT_ADMIN = 2;

}
