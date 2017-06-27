<?php

return array(
    '_ERR_TEST' => '2064001:错误码示例',

    '_ERR_PARAM_CAN_NOT_BE_EMPTY' => '2074050:{$name}不能为空',
    '_ERR_PARAM_MUST_IN_RANGE' => '2074051:提交参数({$name}), 必须在({$range})之中',
    '_ERR_PARAM_MAX_LENGTH' => '2074052:提交参数({$name}), 不得超过长度({$maxLength})',
    '_ERR_EMPTY_DATA' => '207053:{$name}数据不存在',
    '_ERR_INTEGRAL_LEVELS_SIZE' => '207054:积分等级至少保留两级',
    '_ERR_INTEGRAL_FIRST_LEVELS_MAX' => '207055:第一级最大积分值不能小于1',
    '_ERR_INTEGRAL_LAST_LEVELS_MAX' => '207056:最后一级最大积分值不正确',
    '_ERR_INTEGRAL_ADJACENT_LEVELS_MAX' => '207057:第({$currentLevel})级的最大积分值必须大于第({$previousLevel})级的最大积分值',
    '_ERR_INTEGRAL_LEVELS_MAX_NULL' => '207058:第({$currentLevel})级的最大积分值不能为空',
    '_ERR_INTEGRAL_LEVELS_NAME_NULL' => '207059:第({$currentLevel})级的等级名称不能为空',
    '_ERR_INTEGRAL_NAME_NULL' => '207060:积分名称不能为空',
    '_ERR_INTEGRAL_UNIT_NULL' => '207061:积分单位不能为空',
);
