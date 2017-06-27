<?php
/**
 * AbstractModel.class.php
 * Model 层基类
 * @author: zhuxun37
 * @version: $Id$
 * @copyright: vchangyi.com
 */
namespace Common\Model;

abstract class AbstractModel extends \Com\Model
{
    //草稿
    const STATUS_DRAFT = 1;
    // 全公司
    const EXAM_COMPANY_ALL = 1;

    // 分类权限
    const CATEGORY = 1;
    // 试卷权限
    const PAPER = 0;
    // 未作答

    const DO_PASS_STATE = 0;
    // 答题通过
    const MY_PASS = 1;
    // 答题不通过
    const NO_MY_PASS = 2;

    // 开启
    const EC_OPEN_STATES = 1;

    // 禁用
    const EC_CLOSE_STATES = 0;

    // 构造方法
    public function __construct()
    {
        parent::__construct();

    }
}
