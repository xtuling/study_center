<?php
/**
 * Created by PhpStorm.
 * User: zhonglei
 * Date: 2017/5/9
 * Time: 15:21
 */

namespace Common\Common;

use Common\Service\AwardService;
use Common\Service\RightService;
use Common\Service\StudyService;
use Common\Service\UserAwardService;

class ArticleHelper
{
    /**
     * 实例化
     * @author zhonglei
     * @return ArticleHelper
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
     * 发送新课程消息通知
     * @author zhonglei
     * @param array $article 课程数据
     * @param array $rights 权限数据
     * @return bool
     */
    public function sendNotice($article, $rights)
    {
        if (!is_array($article) || empty($article) || !is_array($rights) || empty($rights)) {
            return false;
        }

        $msgServ = &Msg::instance();
        $msg_data = [
            [
                'title' => "【{$article['class_name']}】{$article['article_title']}",
                'description' => $article['summary'],
                'url' => oaUrl('Frontend/Index/Detail/Index', ['article_id' => $article['article_id'], 'data_id' => $article['data_id'], 'article_type' => $article['article_type']]),
                'picUrl' => $article['cover_url'],
            ]
        ];

        // 全公司
        if (isset($rights[Constant::RIGHT_TYPE_ALL]) && $rights[Constant::RIGHT_TYPE_ALL] == Constant::RIGHT_IS_ALL_TRUE) {
            $msgServ->sendNews('@all', null, null, $msg_data);

        // 其它
        } else {
            $rightServ = new RightService();
            $uids = $rightServ->getUidsByRight($rights);
            $uid_groups = array_chunk($uids, Msg::USER_MAX_COUNT);

            // 分批发送
            foreach ($uid_groups as $uid_group) {
                $msgServ->sendNews($uid_group, null, null, $msg_data);
            }
        }

        return true;
    }

    /**
     * 发送未读提醒消息
     * @author liyifei
     * @param array $uids 需要发送人员的UID一维数组
     * @param array $article 新闻信息
     *          + string class_name 一级分类名称
     *          + string title 新闻标题
     *          + string cover_url 新闻封面地址
     *          + string article_id 新闻ID
     *          + string summary 新闻摘要
     * @return bool
     */
    public function sendUnreadMsg($uids, $article)
    {
        if (empty($uids) || !isset($article['article_title'], $article['cover_url'], $article['article_id'], $article['summary'])) {
            return false;
        }
        
        $msgServ = &Msg::instance();
        $msg_data = [
            [
                'title' => "【{$article['class_name']}】{$article['article_title']}",
                'description' => $article['summary'],
                'url' => oaUrl('Frontend/Index/Detail/Index', ['article_id' => $article['article_id'], 'data_id' => $article['data_id'], 'article_type' => $article['article_type']]),
                'picUrl' => $article['cover_url'],
            ]
        ];

        // 分批发送
        $uid_groups = array_chunk($uids, Msg::USER_MAX_COUNT);
        foreach ($uid_groups as $uid_group) {
            $msgServ->sendNews($uid_group, null, null, $msg_data);
        }

        return true;
    }

    /**
     * 获取课程可学、已学、未学人员ID数组
     * @author zhonglei
     * @param int $article_id 课程ID
     * @return array [0 => 可学人员ID, 1 => 已学人员ID, 2 => 未学人员ID]
     */
    public function getStudyData($article_id)
    {
        $uids_all = [];
        $uids_study = [];
        $uids_unstudy = [];

        // 查询条件
        $conds = ['article_id' => $article_id];

        // 获取权限数据
        $rightServ = new RightService();
        $right_list = $rightServ->list_by_conds($conds);
        $rights = $rightServ->formatDBData($right_list);

        if (empty($rights)) {
            return [$uids_all, $uids_study, $uids_unstudy];
        }

        // 获取可学数据
        $uids_all = $rightServ->getUidsByRight($rights);

        // 获取已学数据
        $studyServ = new StudyService();
        $study_list = $studyServ->list_by_conds($conds);
        $uids_study = array_column($study_list, 'uid');

        // 未学数据
        $uids_unstudy = array_values(array_diff($uids_all, $uids_study));
        return [$uids_all, $uids_study, $uids_unstudy];
    }

    /**
     * @desc 取用户当前可获取的激励信息
     * @author tangxingguo
     * @param array $user 用户信息
     * @return array 激励信息
     */
    public function getAwardByUser($user)
    {
        if (!isset($user['memUid'], $user['memUsername'])) {
            return [];
        }
        // 用户适用的所有激励ID
        $rightServ = new RightService();
        $awardIds = $rightServ->listByRight($user, 'award_id');
        if (empty($awardIds)) {
            return [];
        } else {
            $awardIds = array_column($awardIds, 'award_id');
        }

        // 已经获得的激励ID
        $userAwardServ = new UserAwardService();
        $userAwardList = $userAwardServ->list_by_conds(['uid' => $user['memUid']]);
        if (!empty($userAwardList)) {
            $userAwardIds = array_column($userAwardList, 'award_id');
        }

        // 还可获得的激励ID
        $awardIds = isset($userAwardIds) ? array_diff($awardIds, $userAwardIds) : $awardIds;
        if (empty($awardIds)) {
            return [];
        }

        // 已学完的课程ID
        $studyServ = new StudyService();
        $studyList = $studyServ->list_by_conds(['uid' => $user['memUid']]);
        if (empty($studyList)) {
            return [];
        }
        $studyArticleIds = array_column($studyList, 'article_id');

        // 取当前满足条件的激励
        $awards = [];
        $awardServ = new AwardService();
        $awardList = $awardServ->list_by_conds(['award_id in (?)' => $awardIds]);
        foreach ($awardList as $v) {
            // 激励对应的课程
            $article_ids = unserialize($v['article_ids']);
            // 已学完的课程与激励课程交集
            $intersect = array_intersect($article_ids, $studyArticleIds);
            // 交集大于激励发送条件
            if (count($intersect) >= $v['condition']) {
                $awards[] = $v;
            }
        }

        // 激励入库
        if (!empty($awards)) {
            foreach ($awards as $v) {
                $data = [
                    'award_id' => $v['award_id'],
                    'uid' => $user['memUid'],
                    'username' => $user['memUsername'],
                    'award_action' => $v['award_action'],
                    'award_type' => $v['award_type'],
                    'medal_id' => $v['medal_id'],
                    'integral' => $v['integral'],
                    'article_ids' => $v['article_ids'],
                ];
                $userAwardServ->insert($data);

                // 激励推送UC
                if ($v['award_type'] == Constant::AWARD_TYPE_IS_MEDAL) {
                    // 勋章
                    $this->pushMedal($v['medal_id'], $user, $v);
                } elseif ($v['award_type'] == Constant::AWARD_TYPE_IS_INTEGRAL) {
                    // 积分
                    $this->pushIntegral($user, $v);
                }
            }
        }

        return empty($awards) ? [] : $awards[0];
    }

    /**
     * @desc 推送勋章到UC
     * @author tangxingguo
     * @param int $medal_id 勋章ID
     * @param array $user 用户信息
     * @param array award 激励信息
     */
    private function pushMedal($medal_id, $user, $award)
    {
        $integralUtil = &Integral::instance();
        $integralUtil->endowMedal($medal_id, $user['memUid'], $user['memUsername']);
        // 推送消息
        $this->sendAwardNotice($user, $award);
    }

    /**
     * @desc 推送积分到UC
     * @author tangxingguo
     * @param array $user 用户信息
     * @param array $award 激励信息
     */
    private function pushIntegral($user, $award)
    {
        $integralUtil = &Integral::instance();
        $integralUtil->asynUpdateIntegral([
            // 用户id
            'memUid' => $user['memUid'],
            // 积分类型 (默认mi_type0)
            'miType' => 'mi_type0',
            // 积分策略key，integral不为空时，传业务自己的积分规则key
            'irKey' => 'dt_course',
            // 积分变更说明
            'remark' => '课程中心-' . $award['award_action'],
            // 变更的积分值
            'integral' => intval($award['integral']),
            // 应用标识（消息推送必要）
            'msgIdentifier' => APP_IDENTIFIER,
        ]);
    }

    /**
     * @desc 激励推送
     * @author tangxingguo
     * @param array $user 用户信息
     * @param array $award 激励信息
     * @return bool;
     */
    public function sendAwardNotice($user, $award)
    {
        // 获取勋章名称
        $integralServ = new Integral();
        $integralList = $integralServ->listMedal();
        if (!empty($integralList)) {
            $integralList = array_combine_by_key($integralList, 'im_id');
        }
        $medalName = isset($integralList[$award['medal_id']]) ? $integralList[$award['medal_id']]['name'] : '';
        $msgServ = &Msg::instance();
        $msg_data = [
            [
                'title' => "恭喜您，获得【{$medalName}】勋章",
                'description' => "获取渠道：课程中心-{$award['award_action']}\n获取时间：" . rgmdate(MILLI_TIME, 'Y-m-d H:i'),
                'url' => oaUrl('Frontend/Index/Award/Index'),
            ]
        ];

        $msgServ->sendNews($user['memUid'], null, null, $msg_data);
    }
}
