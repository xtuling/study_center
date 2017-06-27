<?php
/**
 * Created by PhpStorm.
 * User: zhonglei
 * Date: 2017/3/20
 * Time: 14:14
 */

namespace Common\Common;

class Constant
{
    /**
     * 权限是否为全公司：否
     */
    const RIGHT_IS_ALL_FALSE = 1;

    /**
     * 权限是否为全公司：是
     */
    const RIGHT_IS_ALL_TRUE = 2;

    /**
     * 权限类型：全公司
     */
    const RIGHT_TYPE_ALL = 1;

    /**
     * 权限类型：部门
     */
    const RIGHT_TYPE_DEPARTMENT = 2;

    /**
     * 权限类型：标签
     */
    const RIGHT_TYPE_TAG = 3;

    /**
     * 权限类型：人员
     */
    const RIGHT_TYPE_USER = 4;

    /**
     * 权限类型：职位
     */
    const RIGHT_TYPE_JOB = 5;

    /**
     * 权限类型：角色
     */
    const RIGHT_TYPE_ROLE = 6;

    /**
     * 分页:默认页数
     */
    const PAGING_DEFAULT_PAGE = 1;

    /**
     * 分页:默认当前页数据总数
     */
    const PAGING_DEFAULT_LIMIT = 20;

    /**
     * 审核人身份：用户
     */
    const CHECKER_IS_USER = 1;

    /**
     * 审核人身份：管理员
     */
    const CHECKER_IS_ADMIN = 2;

    /**
     * 提问解决状态：未解决
     */
    const QUESTION_STATUS_IS_UNRESOLVED = 1;

    /**
     * 提问解决状态：已解决
     */
    const QUESTION_STATUS_IS_SOLVE = 2;

    /**
     * 提问审核状态：待审核
     */
    const QUESTION_CHECK_STATUS_WAIT = 1;

    /**
     * 提问审核状态：已审核通过
     */
    const QUESTION_CHECK_STATUS_PASS = 2;

    /**
     * 提问审核状态：未通过
     */
    const QUESTION_CHECK_STATUS_FAIL = 3;

    /**
     * 审核类型：驳回
     */
    const QUESTION_CHECK_IS_FAIL = 1;

    /**
     * 审核类型：通过
     */
    const QUESTION_CHECK_IS_PASS = 1;

    /**
     * 回答审核状态：待审核
     */
    const ANSWER_CHECK_STATUS_WAIT = 1;

    /**
     * 回答审核状态：已审核通过
     */
    const ANSWER_CHECK_STATUS_PASS = 2;

    /**
     * 回答审核状态：未通过
     */
    const ANSWER_CHECK_STATUS_FAIL = 3;

    /**
     * 是否最佳答案：不是
     */
    const ANSWER_IS_BEST_FALSE = 1;

    /**
     * 是否最佳答案：是
     */
    const ANSWER_IS_BEST_TRUE = 2;

    /**
     * 回答人身份：用户
     */
    const ANSWER_PERON_IS_USER = 1;

    /**
     * 回答人身份：管理员
     */
    const ANSWER_PERON_IS_ADMIN = 2;

    /**
     * 应用名称
     */
    const APP_NAME = '问答中心';

    /**
     * 管理员名称
     */
    const ADMIN_NAME = '管理员';

    /**
     * 消息描述文案
     */
    const NOTICE_DESC_LIST = [
        'question_title' => '标题',
        'q_username' => '发起人',
        'a_username' => '回答人',
        'class_name' => '所属分类',
        'q_time' => '提问时间',
        'a_time' => '回答时间',
        'integral' => '悬赏金',
        'checker_name' => '审核人',
        'check_time' => '审核时间',
        'set_best_time' => '处理时间',
    ];

    /**
     * 消息模板
     */
    const NOTICE_TPL_LIST = [
        // 提问审核通过推送提问者
        'qu_pass_qer' => [
            'title' => '恭喜您，您的提问已通过审核，现已正式发布',
            'desc' => ['question_title', 'class_name', 'q_time', 'checker_name', 'check_time'],
            'url' => '',
        ],
        // 提问审核通过推送其他人
        'qu_pass_other' => [
            'title' => '有新的提问，快来回答吧',
            'desc' => ['question_title', 'class_name', 'integral', 'q_time'],
            'url' => '',
        ],
        // 提问审核驳回（无积分）
        'qu_fail_qer' => [
            'title' => '抱歉，您的提问未通过审核，无法发布',
            'desc' => ['question_title', 'class_name', 'q_time', 'checker_name', 'check_time'],
            'url' => '',
        ],
    ];
}
