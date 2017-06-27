<?php
/**
 * MedalModel.class.php
 * 勋章表 Model
 */

namespace Common\Model;

class MedalModel extends AbstractModel
{
    // icon来源: 用户上传
    const ICON_TYPE_USER_UPLOAD = 1;
    // icon来源: 系统预设
    const ICON_TYPE_SYS = 2;
    // 名称最长长度
    const NAME_MAX_LENGTH = 6;
    // 描述最长长度
    const DESC_MAX_LENGTH = 140;

    // 构造方法
    public function __construct()
    {
        parent::__construct();
    }
}
