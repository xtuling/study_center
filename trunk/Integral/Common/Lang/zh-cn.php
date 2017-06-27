<?php
/**
 * zh-cn.php
 * 公共的语言文件
 * $Author$
 * $Id$
 */
return array(
    '_ERR_EMPTY_POST' => '1020001:没有数据提交',
    '_ERR_EMPTY_IRID' => '1020002:没有提交策略ID',
    '_ERR_POST_IRID_ISTEXIST' => '1020003:提交的策略ID不存在',
    '_ERR_POST_IRCYCLE_FORMAT' => '1020004:策略循环周期数据格式不正确',
    '_ERR_POST_IRCYCLE_UNIT' => '1020005:策略循环周期数据单位不正确',
    '_ERR_POST_IRCOUNT_FORMAT' => '1020006:策略限制次数数据格式不正确',
    '_ERR_POST_IRNUMBER_FORMAT' => '1020007:策略积分值数据格式不正确',
    '_ERR_OPENEDRULES_CANT_NONE' => '1020008:不能全部禁用积分策略',
    '_ERR_EMPTY_UID' => '1020009:缺少人员ID',
    '_ERR_MIL_OPT_TYPE_DATA_ERROR' => '1020010:积分获得类型数据错误',
    '_ERR_INTEGRAL_DATA_ERROR' => '1020011:积分值数据错误',
    '_ERR_MUST_HAD_OPEN_ONE_RULE' => '1020012:至少开启一项积分规则',
    '_ERR_INTEGRAL_IS_NOT_OPEN' => '1020013:积分功能没有开启',
    '_ERR_INTEGRAL_RULE_CANT_EMPTY' => '1020014:积分规则说明不能为空',

    '_ERR_CANNOT_EMPTY' => '1020100:{$name}不能为空',
    '_ERR_OVER_MAX_COUNT' => '1020101:超出数量限制:{$name}',
    '_ERR_OPERATE_ERROR' => '1020102:{$name}操作失败',
    '_ERR_OVER_DATA_AREA' => '1020103:数据超出范围:{$name}',
    '_ERR_BATCH_OPERATE_ERROR' => '1020104:{$ids}',

    '_ERR_OVER_DATA_REGEXP' => '1020105:数据格式错误:{$name}',
    '_ERR_OVER_PER_EXC_TIMES' => '1020106:您的兑换次数已用完',
    '_ERR_PRIZE_CONVERT_IS_DELETED' => '1020107:当前奖品已被管理员删除，无法进行兑换，请选择其他奖品进行兑换。',
    '_ERR_PRIZE_CONVERT_IS_OFFSALE' => '1020108:当前奖品已被管理员下架，无法进行兑换，请选择其他奖品进行兑换。',
    '_ERR_PRIZE_CONVERT_HAVENT_RESERVE' => '1020109:库存不足，无法兑换',
    '_ERR_PRIZE_CONVERT_NOT_IN_AREA' => '1020110:兑换范围已被管理员修改，您当前不在奖品兑换范围内，请选择其他奖品进行兑换。',
    '_ERR_PRIZE_CONVERT_INTEGRAL_NOT_ENOUGH' => '1020111:您的可用积分已不足',
    '_ERR_ICID_OR_UCID_NULL_ERROR' => '1020112:兑换ID和UC积分操作ID不能同时为空',
    '_ERR_CANEL_MARK_NULL_ERROR' => '1020113:取消兑换理由不能为空',
    '_ERR_ICID_NULL_ERROR' => '1020114:兑换id不能为空',
    '_ERR_CANEL_MARK_LENGTH_NULL_ERROR' => '1020115:取消兑换理由不能大于60',
    '_ERR_CONVERT_NOT_EXIST_ERROR' => '1020116:兑换记录不存在',
    '_ERR_CANEL_RECORD_ALREADY_PROCESSED_ERROR' => '1020117:取消失败, 管理员已处理',
    '_ERR_CANEL_FAILED_ERROR' => '1020118:取消失败, 请重新尝试',
    '_ERR_PRIZE_CONVERT_SYSTEM_ERROR' => '1020119:系统错误，兑换失败',
    '_ERR_PRIZE_OPERATE_SYSTEM_ERROR' => '1020120:系统错误，操作失败',
    '_ERR_PRIZE_OPERATE_WAS_PROCESSED' => '1020121:该兑换申请已被其他管理员处理！',
    '_ERR_CANEL_RECORD_ALREADY_CANELED' => '1020122:兑换申请已取消，请勿重复操作',
    '_ERR_CANEL_RECORD_APPLICANT_ALREADY_CANELED' => '1020123:该兑换申请已被申请人取消，无须处理！',
    '_ERR_BATCH_OPERATE_ERROR_PLS_RETRY' => '1020124批量{$operate}全部失败,请重新尝试',
);
