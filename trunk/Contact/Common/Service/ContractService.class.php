<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/20
 * Time: 11:31
 */
namespace Common\Service;

use Common\Common\User;
use Common\Model\ContractModel;

class ContractService extends AbstractService
{
    /**
     * 试用期薪资、合同工资位数上限
     */
    const PAY_DIGIT_MOST = 10;

    /**
     * 工作地点字符最大长度
     */
    const WORK_PLACE_DIGIT_MOST = 20;

    /**
     * 自定义输入内容字符最大长度
     */
    const CUSTOM_INPUT_DIGIT_MOST = 50;

    /**
     * 当前页码
     */
    const CONTRACT_LIST_PAGE = 1;

    /**
     * 自定义输入内容字符最大长度
     */
    const CONTRACT_LIST_PERPAGE = 10;

    // 构造方法
    public function __construct()
    {

        parent::__construct();
        $this->_d = new ContractModel();
    }

    /**
     * 格式化数据，将值为null的元素值初始化为''或默认数据数组内同下标数组的值
     * @author tangxingguo
     * @param array $infoData 待初始化的数据
     * @param string $data 统一初始化的数据
     * @param array $defaultData 默认数据数组，不存在则设置为统一数据
     * @return array|bool 返回格式化后的数据
     */
    public function disposeData($infoData, $data = '', $defaultData = [])
    {
        foreach ($infoData as $k => $v) {
            if (strlen($v) < 1 && !is_array($v)) {
                $infoData[$k] = isset($defaultData[$k]) ? $defaultData[$k] : $data;
            }
        }
        return $infoData;
    }

    /**
     * 检查合同信息
     * @author tangxingguo
     * @param string $uid 人员UID
     * @param array $params 属性参数
     *      + int type 合同类型（1=劳动合同；2=劳务合同；3=非全日制合同
     *      + string work_place 工作地点
     *      + float money 合同工资
     *      + int years 合同年限（-1=自定义；0=无固定；1=一年；2=两年；3=三年；4=四年；5=五年；6=六年；7=七年；8=八年；9=九年；10=十年；. . .）
     *      + string begin_time 劳动合同开始日（空=未填写；非空=毫秒级时间戳）
     *      + string end_time 劳动合同结束日（空=未填写；非空=毫秒级时间戳）
     *      + int probation 试用期（-1=自定义；0=无；1=一个月；2=两个月；3=三个月；4=四个月；5=五个月；6=六个月）
     *      + float probation_money 试用期工资
     *      + string probation_begin_time 试用期开始日（空=未填写；非空=毫秒级时间戳
     *      + string probation_end_time 试用期结束日（空=未填写；非空=毫秒级时间戳）
     *      + string signing_time 合同签订日期（空=未填写；非空=毫秒级时间戳）
     *      + string company 工作单位
     *      + string company_place 营业地点
     *      + string corporation 法定代表
     *      + string user_address 员工联系地址
     *      + string user_mobile 员工联系电话
     *      + string urgent_linkman 紧急联系人
     *      + string urgent_mobile 紧急联系人电话
     *      + string urgent_address 紧急联系人地址
     * @return array
     */
    public function checkContractSaveInfo($uid, $params)
    {
        // 用户信息
        $newUser = new User();
        $userInfo = $newUser->getByUid($uid);

        // 工作地点必填，且长度不能超过20
        if (strlen($params['work_place']) < 1 || mb_strlen($params['work_place'], 'utf8') > self::WORK_PLACE_DIGIT_MOST) {
            E('_ERR_PARAM_FORMAT');
        }

        // 合同工资必填，整数位长度不超过10位，参数为数值
        if (strlen($params['money']) < 1 || strlen(floor($params['money'])) > self::PAY_DIGIT_MOST || !is_numeric($params['money'])) {
            E('_ERR_PARAM_FORMAT');
        }

        // 合同为自定义时计算合同年限
        if ($params['years'] == -1) {
            // 自定义合同起始时间与结束时间不能为空
            if (strlen($params['end_time']) < 1 || strlen($params['begin_time']) < 1) {
                E('_ERR_CONTRACT_DATE_UNDEFINED');
            }
        } else {
            // 入职时间存在且合同为固定年限合同的情况下，入职时间必须等于合同起始时间，比对精确到天
            if (rgmdate($params['begin_time'], 'z') != rgmdate($userInfo['memJoinTime'], 'z') && !empty($userInfo['memJoinTime'])) {
                E('_ERR_CONTRACT_JOIN_TIME_ERROR');
            }
        }

        // 无试用期，需清空试用期开始、结束日期
        if ($params['probation'] == 0) {
            if (strlen($params['probation_begin_time']) > 0 || strlen($params['probation_end_time']) > 0) {
                E('_ERR_CONTRACT_PROBATION_DATE_ERROR');
            }
        } elseif ($params['probation'] > 0) {
            // 有试用期且入职时间存在的情况下，入职时间需等于试用期，比对精确到天
            if (rgmdate($params['probation_begin_time'], 'z') != rgmdate($userInfo['memJoinTime'], 'z') && !empty($userInfo['memJoinTime'])) {
                E('_ERR_CONTRACT_PROBATION_DATE_ERROR');
            }
        }

        // 试用期薪资整数位最长10位，参数为数值，为空设置为值为null
        if (strlen(floor($params['probation_money'])) > self::PAY_DIGIT_MOST) {
            E('_ERR_PARAM_FORMAT');
        } elseif (strlen(floor($params['probation_money'])) >1 && !is_numeric($params['probation_money'])) {
            E('_ERR_PARAM_FORMAT');
        } elseif (strlen($params['probation_money']) < 1) {
            $params['probation_money'] = null;
        }

        // 试用期自定义时，起始于结束时间不能为空
        if ($params['probation'] == -1) {
            if (strlen($params['probation_begin_time']) < 1 || strlen($params['probation_end_time']) < 1) {
                E('_ERR_CONTRACT_PROBATION_DATE_UNDEFINED');
            }
        }

        // 合同签订日期必填
        if (strlen($params['signing_time']) < 1) {
            E('_ERR_PARAM_UNDEFINED');
        }

        // 工作单位最长50位
        if (mb_strlen($params['company'], 'utf8') > self::CUSTOM_INPUT_DIGIT_MOST) {
            E('_ERR_PARAM_FORMAT');
        }

        // 营业地点最长50位
        if (mb_strlen($params['company_place'], 'utf8') > self::CUSTOM_INPUT_DIGIT_MOST) {
            E('_ERR_PARAM_FORMAT');
        }

        // 法定代表最长50位
        if (mb_strlen($params['corporation'], 'utf8') > self::CUSTOM_INPUT_DIGIT_MOST) {
            E('_ERR_PARAM_FORMAT');
        }

        // 员工联系地址最长50位
        if (mb_strlen($params['user_address'], 'utf8') > self::CUSTOM_INPUT_DIGIT_MOST) {
            E('_ERR_PARAM_FORMAT');
        }

        // 员工联系电话最长50位
        if (mb_strlen($params['user_mobile'], 'utf8') > self::CUSTOM_INPUT_DIGIT_MOST) {
            E('_ERR_PARAM_FORMAT');
        }

        // 紧急联系人最长50位
        if (mb_strlen($params['urgent_linkman'], 'utf8') > self::CUSTOM_INPUT_DIGIT_MOST) {
            E('_ERR_PARAM_FORMAT');
        }

        // 紧急联系人电话最长50位
        if (mb_strlen($params['urgent_mobile'], 'utf8') > self::CUSTOM_INPUT_DIGIT_MOST) {
            E('_ERR_PARAM_FORMAT');
        }

        // 紧急联系人地址最长50位
        if (mb_strlen($params['urgent_address'], 'utf8') > self::CUSTOM_INPUT_DIGIT_MOST) {
            E('_ERR_PARAM_FORMAT');
        }

        return $params;
    }

    /**
     * 获取用户合同信息列表
     * @author tangxingguo
     * @param string $keyword 人员姓名、拼音关键字
     * @param array $dpids 部门
     * @param int $signingTypeParam 合同签订情况（1=未签订；2=已办理；3=已到期）
     * @param int $page 分页页码
     * @param int $limit 每页数据条数
     * @return array 返回人员合同信息
     */
    public function getContractList($keyword, $dpids, $signingTypeParam, $page, $limit)
    {
        // 明天凌晨时间（合同过期时间定义大于今天）
        $checkTime = rstrtotime(rgmdate(MILLI_TIME, 'Y-m-d'), 1);
        $where = array();

        // 查询条件重组
        if (strlen($keyword) > 0) {
            $where['memUsername'] = $keyword;
        }
        if (!empty($dpids)) {
            $where['dpIdList'] = $dpids;
        }

        // 根据合同是否过期取合同表内的uids
        $contractListRawData = $this->_d->getListBySigningType($signingTypeParam, $checkTime);
        $uids = array_column($contractListRawData, 'uid');
        $uids = empty($uids) ? [''] : $uids;
        // 合同签订情况筛选
        switch ($signingTypeParam) {
            // 合同签订情况选择未签订
            case ContractModel::CONTRACT_SIGNING_TYPE_UNDONE:
                $where['excludeMemuids'] = $uids;
                break;
            // 合同签订情况选择签订
            case ContractModel::CONTRACT_SIGNING_TYPE_DONE:
                $where['memUids'] = $uids;
                break;
            // 合同签订情况选择过期
            case ContractModel::CONTRACT_SIGNING_TYPE_OVERDUE:
                $where['memUids'] = $uids;
                break;
        }

        // UC排序规则
        $orderList = [
            'memIndex' => 'ASC',
        ];

        // 根据条件取UC用户列表
        $userServ = new User();
        $userListRawDdata = $userServ->listByConds($where, $page, $limit, $orderList);
        $contractListUidKey = array_combine($uids, $contractListRawData);
        // 合同列表
        $contractList = null;

        // 根据UID取合同信息，并合并信息
        foreach ($userListRawDdata['list'] as $k => $userInfo) {
            $uid = $userInfo['memUid'];
            $contractInfo = $contractListUidKey[$uid];
            // 合同类型
            $type = null;
            // 合同签订情况
            $signing_type = null;
            // 合同期限
            $contract = null;
            // 	距合同到期天数
            $contract_period = null;
            // 试用期限
            $probation = null;
            // 距试用期满天数
            $probation_period = null;

            // 取合同信息
            if (!empty($contractInfo)) {
                $type = $contractInfo['type'];
                $signing_type = ContractModel::CONTRACT_SIGNING_TYPE_OVERDUE;

                // 合同期限，开始时间存在
                if (strlen($contractInfo['begin_time']) > 0) {
                    $contractStart = rgmdate($contractInfo['begin_time'], 'z');
                    // 合同类型为无固定
                    if ($contractInfo['years'] == 0 && strlen($contractInfo['years']) > 0) {
                        if (strlen($contractInfo['end_time']) < 1) {
                            // 合同签订情况为无固定，并且合同结束日期未定义，合同状态修改为已签订
                            $signing_type = ContractModel::CONTRACT_SIGNING_TYPE_DONE;
                            $contractEnd = '无固定';
                        } else {
                            $contractEnd =rgmdate($contractInfo['end_time'], 'z');
                        }
                    } else {
                        $contractEnd = rgmdate($contractInfo['end_time'], 'z');
                    }
                    $contract = $contractStart . ' ~' . $contractEnd;
                } else {
                    $contract = '';
                }

                // 合同到期天数
                if ($contractInfo['end_time'] > $checkTime) {
                    $contract_period = intval(($contractInfo['end_time'] - $checkTime) / 86400000);
                    $signing_type = ContractModel::CONTRACT_SIGNING_TYPE_DONE;
                }

                // 试用期限，开始时间存在 且 存在试用期
                if (strlen($contractInfo['probation_begin_time']) > 0 && $contractInfo['probation'] != 0) {
                    $probationStart = rgmdate($contractInfo['probation_begin_time'], 'z');
                    $probationEnd = rgmdate($contractInfo['probation_end_time'], 'z');
                    $probation = $probationStart . ' ~' . $probationEnd;
                } elseif (strlen($contractInfo['probation']) > 0 && $contractInfo['probation'] == 0) {
                    // 无试用期
                    $probation = '无';
                } else {
                    $probation = '';
                }

                // 试用期满天数
                if ($contractInfo['probation_end_time'] > $checkTime) {
                    $probation_period = intval(($contractInfo['probation_end_time'] - $checkTime) / 86400000);
                } else {
                    $probation_period = '';
                }
            }

            // 组合数据
            $rawData = [
                'uid' => $uid,
                'username' => $userInfo['memUsername'],
                'dp_name' => $userInfo['dpName'],
                'job' => $userInfo['memJob'],
                'identity_card' => $userInfo['memIdcard'],
                'type' => $type,
                'signing_type' => $signing_type,
                'contract' => $contract,
                'contract_period' => $contract_period,
                'probation' => $probation,
                'probation_period' => $probation_period
            ];
            // 默认信息，合同默认未签订
            $defaultData=[
                'signing_type' => ContractModel::CONTRACT_SIGNING_TYPE_UNDONE
            ];
            // 格式化数据
            $contractList['list'][$k] = $this->disposeData($rawData, '', $defaultData);
            $contractList['total'] = $userListRawDdata['total'];
        }

        return $contractList;
    }
}
