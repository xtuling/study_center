<?php
/**
 * ConvertProcessModel.class.php
 * 奖品申请进度表 Model
 * @author: zhoutao
 * @version: $Id$
 * @copyright: vchangyi.com
 */

namespace Common\Model;

class ConvertProcessModel extends AbstractModel
{
    /** 操作类型:待处理 */
    const OPERATE_STATUS_ING = 1;
    /** 操作类型:已同意 */
    const OPERATE_STATUS_AGREE = 2;
    /** 操作类型:已拒绝 */
    const OPERATE_STATUS_DEFUSE = 3;
    /** 操作类型:已取消 */
    const OPERATE_STATUS_CANCEL = 4;
    /** mark最长长度 */
    const MARK_MAX_LENGTH = 60;

    /** 奖品申请状态:待处理 */
    const CONVERT_STATUS_ING = 1;
    /** 奖品申请状态:已同意 */
    const CONVERT_STATUS_AGREE = 2;
    /** 奖品申请状态:已拒绝 */
    const CONVERT_STATUS_DEFUSE = 3;
    /** 奖品申请状态:已取消 */
    const CONVERT_STATUS_CANCEL = 4;

    // 构造方法
    public function __construct()
    {

        parent::__construct();
    }
}
