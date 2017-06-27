<?php
/**
 * ImageVerify.class.php
 * 积分操作类
 * @license http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt LGPL
 * @copyright  Copyright (c) 2014 - ? VcySDK (http://www.vchangyi.com/)
 * @author zhoutao
 * @version 1.0.0
 */

namespace VcySDK;

class Integral
{
    /** 积分获得类型:自动获取 */
    const AUTO_GET_INTEGRAL = 1;
    /** 积分获得类型:手动增加 */
    const MANUAL_GET_INTEGRAL = 2;
    /** 积分获得类型:手动扣减 */
    const MANUAL_DEDUCT_INTEGRAL = 3;
    /** 积分获得类型:兑换消耗 */
    const MANUAL_EXCHAGE_INTEGRAL = 4;
    /** 积分获得类型:兑换退回 */
    const MANUAL_EXCHAGE_BACK_INTEGRAL = 5;

    /** 是否是管理员拒绝兑换: 是 */
    const IS_ADMIN_TRUE = 1;
    /** 是否是管理员拒绝兑换: 否 */
    const IS_ADMIN_FALSE = 2;

    /**
     * 积分获得类型对应数据
     * @var array
     */
    protected $integralTypeWithNumber = [
        'AUTO_GET_INTEGRAL' => 1,
        'MANUAL_GET_INTEGRAL' => 2,
        'MANUAL_DEDUCT_INTEGRAL' => 3,
        'MANUAL_EXCHAGE_INTEGRAL' => 4,
        'MANUAL_EXCHAGE_BACK_INTEGRAL' => 5,
    ];
    /**
     * 积分获得类型对应名称
     * @var array
     */
    protected $integralTypeWithChinese = [
        'AUTO_GET_INTEGRAL' => '自主获得',
        'MANUAL_GET_INTEGRAL' => '手动增加',
        'MANUAL_DEDUCT_INTEGRAL' => '手动扣减',
        'MANUAL_EXCHAGE_INTEGRAL' => '兑换消耗',
        'MANUAL_EXCHAGE_BACK_INTEGRAL' => '兑换退回'
    ];

    /**
     * 接口调用类
     *
     * @var object
     */
    private $serv = null;

    /**
     * 获取数据
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->$name;
    }

    /**
     * 积分统计历史数据列表
     * %s = {apiUrl}/{enumber}/{app}
     *
     * @var string
     */
    const HISTORY_TOTAL = '%s/integral/total-list';

    /**
     * 用户积分详情
     * %s = {apiUrl}/{enumber}/{app}
     *
     * @var string
     */
    const INTEGRAL_DETAIL = '%s/integral/detail';

    /**
     * 用户积分操作明细接口
     * %s = {apiUrl}/{enumber}/{app}
     *
     * @var string
     */
    const INTEGRAL_DETAILLIST = '%s/integral/detailList';

    /**
     * 积分增减接口（含批量）
     * %s = {apiUrl}/{enumber}/{app}
     *
     * @var string
     */
    const INTEGRAL_ADD = '%s/integral/add';

    /**
     * 积分增减接口（自动）
     * %s = {apiUrl}/{enumber}/{app}
     *
     * @var string
     */
    const INTEGRAL_AUTOADD = '%s/integral/autoAdd';

    /**
     * 获取企业积分策略配置
     * %s = {apiUrl}/{enumber}/{app}
     *
     * @var string
     */
    const GET_STRATEGY_SETTING = '%s/integral/get-strategy-setting';

    /**
     * 企业积分策略配置
     * %s = {apiUrl}/{enumber}/{app}
     *
     * @var string
     */
    const UPDATE_SETTING = '%s/integral/update-setting';

    /**
     * 企业积分策略信息修改
     * %s = {apiUrl}/{enumber}/{app}
     *
     * @var string
     */
    const UPDATE_STRATEGY = '%s/integral/update-strategy';

    /**
     * 积分增减接口（含批量）
     * %s = {apiUrl}/{enumber}/{app}
     *
     * @var string
     */
    const INTEGRAL_UPDATE = '%s/integral/update';

    /**
     * 用户积分查询
     * %s = {apiUrl}/{enumber}/{app}
     *
     * @var string
     */
    const INTEGRAL_MEMBER_LIST = '%s/integral/member-list';

    /**
     * 用户积分操作明细接口（积分首页使用）
     * %s = {apiUrl}/{enumber}/{app}
     *
     * @var string
     */
    const INTEGRAL_INDEX_LIST = '%s/integral/integralIndexList';

    /**
     * 微信端积分增减接口（系统异步操作）
     * %s = {apiUrl}/{enumber}/{app}
     *
     * @var string
     */
    const ASYN_UPDATE_INTEGRAL = '%s/integral/asynUpdateIntegral';

    /**
     * 积分兑换接口接口（消息异步推送）
     * %s = {apiUrl}/{enumber}/{app}
     *
     * @var string
     */
    const INTEGRAL_EXCHANGE = '%s/integral/exchange';

    /**
     * 获取指定用户的积分排名
     * %s = {apiUrl}/{enumber}/{app}
     *
     * @var string
     */
    const INTEGRAL_RANKING = '%s/integral/getRanking';

    /**
     * 获取用户的所有积分排名
     * %s = {apiUrl}/{enumber}/{app}
     *
     * @var string
     */
    const INTEGRAL_ALL_RANKING = '%s/integral/getAllRanking';

    /**
     * 积分等级配置信息接口
     * %s = {apiUrl}/{enumber}/{app}
     *
     * @var string
     */
    const GET_INTEGRAL_LEVEL_SETTING = '%s/integral/level/get-setting';

    /**
     * 积分等级配置信息修改
     * %s = {apiUrl}/{enumber}/{app}
     *
     * @var string
     */
    const UPDATE_INTEGRAL_LEVEL_SETTING = '%s/integral/level/update-setting';

    /**
     * 企业积分公共配置信息接口
     * %s = {apiUrl}/{enumber}/{app}
     *
     * @var string
     */
    const GET_INTEGRAL_COMMON_SETTING = '%s/integral/common/get-setting';

    /**
     * 企业积分公共配置信息修改接口
     * %s = {apiUrl}/{enumber}/{app}
     *
     * @var string
     */
    const UPDATE_INTEGRAL_COMMON_SETTING = '%s/integral/common/update-setting';

    /**
     * 点赞
     * %s = {apiUrl}/{enumber}/{app}
     *
     * @var string
     */
    const UPDATE_LIKE = '%s/like/update';

    /**
     * 初始化
     *
     * @param object $serv 接口调用类
     */
    public function __construct($serv)
    {

        $this->serv = $serv;
    }

    /**
     * 用户积分查询
     *
     * @param array $condition 条件
     *        + userName String 员工姓名
     *        + depId String 部门id
     *        + pageNum Int 当前页，空时默认为1
     *        + pageSize Int 页大小,空时默认为5,最大1000
     */
    public function integralList($condition)
    {

        return $this->serv->postSDK(self::INTEGRAL_LIST, $condition, 'generateApiUrlA');
    }

    /**
     * 用户积分查询
     *
     * @param array $condition 条件
     *        + beginMitdTime String 统计开始时间戳（单位：毫秒）
     *        + endMitdTime String 统计结束时间戳（单位：毫秒）
     *        + pageNum Int 当前页，空时默认为1
     *        + pageSize Int 页大小,空时默认为20
     */
    public function historyTotal($condition)
    {

        return $this->serv->postSDK(self::HISTORY_TOTAL, $condition, 'generateApiUrlA');
    }

    /**
     * 用户积分查询
     *
     * @param array $condition 条件
     *        + uid String 用户ID
     */
    public function detail($condition)
    {

        return $this->serv->postSDK(self::INTEGRAL_DETAIL, $condition, 'generateApiUrlA');
    }

    /**
     * 用户积分查询
     *
     * @param array $condition 条件
     *        + uid String 用户ID
     *        + uName String 用户名
     *        + startTime Int 查询开始时间（毫秒）
     *        + endTime Int 查询结束时间（毫秒）
     *        + integralTypeId Int 积分获得类型(1: 自动获得, 2: 手动增加, 3: 手动扣减)
     *        + realName String 员工姓名
     *        + dpId String 部门主键
     *        + pageNum Int 当前页，空时默认为1
     *        + pageSize Int 页大小,空时默认为5,最大1000
     */
    public function detailList($condition)
    {

        return $this->serv->postSDK(self::INTEGRAL_DETAILLIST, $condition, 'generateApiUrlA');
    }

    /**
     * 积分增减接口（含批量）
     *
     * @param array $params 条件
     *        + uids String 用户id(多个用户之间逗号分隔)
     *        + integralTypeId Int 积分获得类型(1: 自动获得, 2: 手动增加, 3: 手动扣减)
     *        + miType String 积分类型 (默认 mi_type0)
     *        + integral Int 积分值
     *        + remark String 备注
     */
    public function add($params)
    {

        return $this->serv->postSDK(self::INTEGRAL_ADD, $params, 'generateApiUrlA');
    }

    /**
     * 积分增减接口（自动）
     *
     * @param array $params 条件
     *        + uid String 用户id
     *        + identifier String 应用标识
     *        + integralTypeId Int 积分获得类型(1: 自动获得, 2: 手动增加, 3: 手动扣减)
     *        + miType String 积分类型 (默认 mi_type0)
     *        + integral Int 积分值
     *        + remark String 备注
     */
    public function autoAdd($params)
    {

        return $this->serv->postSDK(self::INTEGRAL_AUTOADD, $params, 'generateApiUrlA');
    }

    /**
     * 获取企业积分策略配置
     *
     * @return mixed
     */
    public function getStrategySetting()
    {

        return $this->serv->postSDK(self::GET_STRATEGY_SETTING, '', 'generateApiUrlA');
    }

    /**
     * 企业积分策略配置
     *
     * @param $params
     * + eirsId String 策略主键
     * + eirsEnable Int 是否启用禁用1:启用 2：禁用（默认禁用）
     * + eirsDesc String 策略说明
     * @return mixed
     */
    public function updateSetting($params)
    {

        return $this->serv->postSDK(self::UPDATE_SETTING, $params, 'generateApiUrlA');
    }

    /**
     * 企业积分策略信息修改
     *
     * @param $params
     * + irId String 策略id
     * + miType String 积分类型。固定值: mi_type0
     * + enable Int 是否启用（1:启用；2:禁用）
     * + irCycle String 策略循环周期限制（格式：<数量|单位>。单位有：天、周、月、年）
     * + irCount Int 策略限制次数
     * + irNumber Int 策略积分值
     * @return mixed
     */
    public function updateStrategy($params)
    {

        return $this->serv->postSDK(self::UPDATE_STRATEGY, $params, 'generateApiUrlA');
    }

    /**
     * 企业积分策略信息修改
     *
     * @param $params
     * + uids String 用户id(多个用户之间逗号分隔)
     * + milOptType Int 积分获得类型(2: 手动增加, 3: 手动扣减)
     * + miType String 积分类型 (默认 mi_type0)
     * + integral Int 积分值
     * + remark String 备注
     * + milCreateMemUid String 操作人id
     * + milCreateMemUsername String 操作人名称
     * @return mixed
     */
    public function integralUpdate($params)
    {

        return $this->serv->postSDK(self::INTEGRAL_UPDATE, $params, 'generateApiUrlA');
    }

    /**
     * 用户积分查询
     *
     * @param $params
     * + memUsername String 员工姓名
     * + dpId String 部门id
     * + memUids array 用户UID数组
     * + miType String 积分类别，固定格式：mi_type[0-9]
     * + pageNum Int 当前页，空时默认为1
     * + pageSize Int 页大小,空时默认为20,最大1000
     * @return mixed
     */
    public function integralMemberList($params)
    {

        return $this->serv->postSDK(self::INTEGRAL_MEMBER_LIST, $params, 'generateApiUrlA');
    }

    /**
     * 用户积分操作明细接口（积分首页使用）
     *
     * @param $params
     * + memUsername String 员工姓名
     * + milOptType String 积分获得类型(1: 自动获得, 2: 手动增加, 3: 手动扣减)
     * + dpId String 部门id
     * + startTime String 查询开始时间（毫秒）
     * + endTime String 查询结束时间（毫秒）
     * + pageNum Int 当前页，空时默认为1
     * + pageSize Int 页大小,空时默认为20,最大1000
     * @return mixed
     */
    public function integralIndexList($params)
    {

        return $this->serv->postSDK(self::INTEGRAL_INDEX_LIST, $params, 'generateApiUrlA');
    }

    /**
     * 微信端积分增减接口（系统异步操作）
     * [ 配合积分规则,在相应的位置执行该方法 ]
     *
     * @param $params
     * + memUid String 用户ID
     * + irKey String 积分策略key
     * + miType String 积分类型 (默认mi_type0)
     * @return mixed
     */
    public function asynUpdateIntegral($params)
    {

        return $this->serv->postSDK(self::ASYN_UPDATE_INTEGRAL, $params, 'generateApiUrlA');
    }

    /**
     * 积分兑换接口（消息异步推送）
     *
     * @param $params
     * + memUid String 用户ID
     * + integral Int 积分值
     * + milOptType Int 积分获得类型(4:兑换消耗，5:兑换退回)
     * + isAdmin Int 是否是管理员拒绝兑换(1:是，2:不是(自己取消))milOptType＝5时，此参数为必填
     * + prizeName String 奖品名称
     * + businessId Int 业务操作ID
     * + remark String 备注
     * + miType String 积分类型 (默认mi_type0)
     * + milCreateMemUid String 操作人id
     * + milCreateMemUsername String 操作人名称
     * @return mixed
     */
    public function integralExchange($params)
    {

        return $this->serv->postSDK(self::INTEGRAL_EXCHANGE, $params, 'generateApiUrlA');
    }

    /**
     * 获取指定用户的积分排名.
     *
     * @param $params
     * @return mixed
     */
    public function getRanking($params)
    {

        return $this->serv->postSDK(self::INTEGRAL_RANKING, $params, 'generateApiUrlA');
    }

    /**
     * 获取所有用户的积分排名.
     *
     * @param $params
     * @return mixed
     */
    public function getAllRanking($params)
    {

        return $this->serv->postSDK(self::INTEGRAL_ALL_RANKING, $params, 'generateApiUrlA');
    }

    /**
     * 企业积分等级配置信息
     * @return mixed
     */
    public function getIntegralLevelSetting()
    {

        return $this->serv->postSDK(self::GET_INTEGRAL_LEVEL_SETTING, '', 'generateApiUrlA');
    }

    /**
     * 企业积分等级配置信息修改
     *
     * @param $params
     * + eisId String 等级id
     * + miType String 等级升级类型 1-累计获得积分 2-当前可用积分
     * + levels Array 积分等级数据
     *     + name String 等级名称
     *     + max Int 最大积分值
     * @return mixed
     */
    public function updateIntegralLevelSetting($params)
    {

        return $this->serv->postSDK(self::UPDATE_INTEGRAL_LEVEL_SETTING, $params, 'generateApiUrlA');
    }

    /**
     * 获取企业积分公共配置信息
     * @return mixed
     */
    public function getEpIntegralCommonSetting()
    {

        return $this->serv->postSDK(self::GET_INTEGRAL_COMMON_SETTING, '', 'generateApiUrlA');
    }


    /**
     * 企业积分公共配置信息修改
     *
     * @param $params
     * + eisId String 配置ID
     * + eisKey String 配置key
     * + eisType String 类型 0-非数组 1-数组
     * + eisValue String 配置值
     * + eisComment String 配置说明
     * @return mixed
     */
    public function updateEpIntegralCommonSetting($params)
    {

        return $this->serv->postSDK(self::UPDATE_INTEGRAL_COMMON_SETTING, $params, 'generateApiUrlA');
    }

    /**
     * 点赞.
     *
     * @param $params
     * @return mixed
     */
    public function updateLike($params)
    {

        return $this->serv->postSDK(self::UPDATE_LIKE, $params, 'generateApiUrlA');
    }
}
