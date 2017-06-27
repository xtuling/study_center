<?php
/**
 * SettingModel.class.php
 * 积分设置表 Model
 * @author: zhuxun37
 * @version: $Id$
 * @copyright: vchangyi.com
 */

namespace Common\Model;

class SettingModel extends AbstractModel
{

    // 积分等级升级类型 累计获得积分
    const UPGRADE_TYPE_CUMULATIVE = 1;

    // 积分等级升级类型 当前可用积分
    const UPGRADE_TYPE_AVAILABLE = 2;

    // 构造方法
    public function __construct()
    {

        parent::__construct();
    }
}
