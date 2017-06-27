<?php
/**
 * 前台组件基类
 * AbstractController.class.php
 * $author$ 何岳龙
 * $date$  2016年8月29日11:03:51
 */

namespace Api\Controller\ChooseMem;

abstract class AbstractController extends \Common\Controller\Api\AbstractController
{

    // 全选
    const SELECT_All = 1;

    // 非全选
    const SELECT_NOT_All = 0;

    // 页码
    const PAGE = 1;

    // 查询最大每页显示个数
    const MAX_LIMIT = 300;

}
