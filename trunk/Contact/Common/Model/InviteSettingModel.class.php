<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/9/27
 * Time: 20:08
 */
namespace Common\Model;

class InviteSettingModel extends AbstractModel
{

    /**
     * 邀请人审核
     */
    const APPROVE_INVITER = 1;

    /**
     * 组织负责人审核
     */
    const APPROVE_LEADER = 2;

    // 构造方法
    public function __construct()
    {

        parent::__construct();
    }
}
