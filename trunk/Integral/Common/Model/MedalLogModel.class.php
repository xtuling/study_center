<?php
/**
 * MedalModel.class.php
 * 人员获得勋章日志表 Model
 */

namespace Common\Model;

class MedalLogModel extends AbstractModel
{
    // 获得勋章状态: 成功获得
    const GET_STATUS_SUCCESS = 1;
    // 获得勋章状态: 申请中
    const GET_STATUS_APPLY = 2;
    // 获得勋章状态: 获得失败
    const GET_STATUS_FAILURE = 3;

    // 构造方法
    public function __construct()
    {
        parent::__construct();
    }
}
