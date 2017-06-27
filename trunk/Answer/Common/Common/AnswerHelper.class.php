<?php
/**
 * Created by PhpStorm.
 * User: zhonglei
 * Date: 2017/5/9
 * Time: 15:21
 */

namespace Common\Common;

use Common\Service\ClassService;
use Common\Service\ConfigService;
use Common\Service\QuestionService;

class AnswerHelper
{
    /**
     * 实例化
     * @author zhonglei
     * @return AnswerHelper
     */
    public static function &instance()
    {
        static $instance;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * @desc 根据UID取用户部门、头像
     * @author tangxingguo
     * @param array $uids 人员UID
     * @return array 人员信息
     *              + dpNames格式[['uid' => '部门1;部门2']
     *              + faceList格式[['uid' => 'url']
     */
    public function getUserInfo($uids)
    {
        $dpNames = [];
        $faceList = [];
        // 取人员信息
        $userServ = &User::instance();
        $users = $userServ->listAll(['memUids' => $uids]);
        if (!empty($users)) {
            // 取出人员部门
            $dpList = array_column($users, 'dpName', 'memUid');
            // 部门数组转字串
            foreach ($dpList as $k => $v) {
                $dpName = array_column($v, 'dpName');
                $dpNames[$k] = empty($dpName) ? '' : implode(';', $dpName);
            }

            // 头像
            $faceList = array_column($users, 'memFace', 'memUid');
        }

        return [$dpNames, $faceList];
    }

    /**
     * @desc 格式化消息数据
     * @author tangxingguo
     * @param array $noticeTpl 消息模板
     * @param array $descData 描述对应数据
     * @param array $urlParams 跳转链接参数
     * @return array
     */
    public function formatNoticeText($noticeTpl, $descData, $urlParams)
    {
        if (!isset($noticeTpl['title']) || !isset($noticeTpl['desc']) || !isset($noticeTpl['url'])) {
            return [];
        }

        // 描述数据需与描述标题对应
        if (!empty(array_diff($noticeTpl['desc'], array_keys($descData)))) {
            return array_diff($noticeTpl['desc'], array_keys($descData));
        }

        // 标题
        $title = '【' . Constant::APP_NAME . '】' . $noticeTpl['title'];

        // 格式化描述
        $desc = rgmdate(MILLI_TIME, 'Y-m-d') . "\n";
        $descList = Constant::NOTICE_DESC_LIST;
        foreach ($noticeTpl['desc'] as $k => $v) {
            $desc .= $descList[$v] . ":" . $descData[$v] . "\n";
        }

        // 格式化url
        $url = oaUrl($noticeTpl['url'], $urlParams);

        return [$title, $desc, $url];
    }

    /**
     * @desc
     * @author tangxingguo
     * @param array $uids 消息接收人员UID
     * @param array $noticeTpl 消息模板
     * @param array $descData 描述对应数据
     * @param array $urlParams 跳转链接参数
     */
    public function sendNotice($uids, $noticeTpl, $descData, $urlParams)
    {
        // 格式化消息内容
        list($title, $desc, $url) = $this->formatNoticeText($noticeTpl, $descData, $urlParams);

        // 推送消息
        $msgServ = &Msg::instance();
        $msg_data = [
            [
                'title' => $title,
                'description' => $desc,
                'url' => $url,
            ]
        ];

        $msgServ->sendNews($uids, null, null, $msg_data);
    }

    /**
     * @desc 提问审批通过，发送消息给发起人
     * @author tangxingguo
     * @param string $uid 发起人UID
     * @param array $questionInfo 提问信息
     */
    public function passNoticeToQer($uid, $questionInfo)
    {
        // 消息模板
        $noticeTpl = Constant::NOTICE_TPL_LIST['qu_pass_qer'];

        // 跳转链接参数
        $urlParams = ['question_id' => $questionInfo['question_id']];

        // 消息内容
        $notice = [
            'question_title' => $questionInfo['question_title'],
            'class_name' => $this->getClassName($questionInfo['class_id']),
            'q_time' => rgmdate($questionInfo['created'], 'Y-m-d H:i:s'),
            'checker_name' => $questionInfo['checker_name'],
            'check_time' => rgmdate($questionInfo['check_time'], 'Y-m-d H:i:s'),
        ];
        if ($questionInfo['checker_type'] == Constant::CHECKER_IS_ADMIN) {
            // 管理员审核，审核人名改为管理员
            $notice['checker_name'] = Constant::ADMIN_NAME;
        }

        // 发送消息
        $this->sendNotice($uid, $noticeTpl, $notice, $urlParams);
    }

    /**
     * @desc 提问审批通过，发送消息给其他人
     * @author tangxingguo
     * @param string $uid 提问人UID
     * @param array $questionInfo 提问信息
     */
    public function passNoticeToOther($uid, $questionInfo)
    {
        // 消息模板
        $noticeTpl = Constant::NOTICE_TPL_LIST['qu_pass_other'];

        // 跳转链接参数
        $urlParams = ['question_id' => $questionInfo['question_id']];

        // 消息内容
        $notice = [
            'question_title' => $questionInfo['question_title'],
            'class_name' => $this->getClassName($questionInfo['class_id']),
            'integral' => intval($questionInfo['integral']),
            'q_time' => rgmdate($questionInfo['created'], 'Y-m-d H:i:s'),
        ];

        // 发送人员
        $right = Config::instance()->getCacheData()['rights'];
        $configServ = new ConfigService();
        $uids = $configServ->getUidsByRight($right);
        $uids = array_values(array_diff($uids, [$uid]));

        // 发送消息
        $this->sendNotice($uids, $noticeTpl, $notice, $urlParams);
    }

    /**
     * @desc 提问审批驳回，发送消息给发起人（无积分）
     * @author tangxingguo
     * @param string $uid 发起人UID
     * @param array $questionInfo 提问信息
     */
    public function failNoticeToQer($uid, $questionInfo)
    {
        // 消息模板
        $noticeTpl = Constant::NOTICE_TPL_LIST['qu_fail_qer'];

        // 跳转链接参数
        $urlParams = ['question_id' => $questionInfo['question_id']];

        // 消息内容
        $notice = [
            'question_title' => $questionInfo['question_title'],
            'class_name' => $this->getClassName($questionInfo['class_id']),
            'q_time' => rgmdate($questionInfo['created'], 'Y-m-d H:i:s'),
            'checker_name' => $questionInfo['checker_name'],
            'check_time' => rgmdate($questionInfo['check_time'], 'Y-m-d H:i:s'),
        ];
        if ($questionInfo['checker_type'] == Constant::CHECKER_IS_ADMIN) {
            // 管理员审核，审核人名改为管理员
            $notice['checker_name'] = Constant::ADMIN_NAME;
        }

        // 发送消息
        $this->sendNotice($uid, $noticeTpl, $notice, $urlParams);
    }

    /**
     * @desc 获取分类名
     * @author tangxingguo
     * @param int $classId 分类ID
     * @return string
     */
    public function getClassName($classId)
    {
        $classServ = new ClassService();
        $classInfo = $classServ->get($classId);
        return empty($classInfo) ? '' : $classInfo['class_name'];
    }
}
