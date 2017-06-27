<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/6/6
 * Time: 15:47
 */
namespace Common\Common;

use Common\Service\ReadService;
use Common\Service\RightService;

class NewsHelper
{
    /**
     * 实例化
     * @author zhonglei
     * @return NewsHelper
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
                'title' => "【{$article['class_name']}】{$article['title']}",
                'description' => $article['summary'],
                'url' => oaUrl('Frontend/Index/Detail/Index', ['article_id' => $article['article_id']]),
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
        if (empty($uids) || !isset($article['title'], $article['cover_url'], $article['article_id'], $article['summary'])) {
            return false;
        }

        $msgServ = &Msg::instance();
        $msg_data = [
            [
                'title' => "【{$article['class_name']}】{$article['title']}",
                'description' => $article['summary'],
                'url' => oaUrl('Frontend/Index/Detail/Index', ['article_id' => $article['article_id']]),
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
     * 获取新闻可读、已读、未读人员ID数组
     * @author zhonglei
     * @param int $article_id 新闻ID
     * @return array [0 => 可读人员ID, 1 => 已读人员ID, 2 => 未读人员ID]
     */
    public function getReadData($article_id)
    {
        $uids_all = [];
        $uids_read = [];
        $uids_unread = [];

        // 查询条件
        $conds = ['article_id' => $article_id];

        // 获取权限数据
        $rightServ = new RightService();
        $right_list = $rightServ->list_by_conds($conds);
        $rights = $rightServ->formatDBData($right_list);

        if (empty($rights)) {
            return [$uids_all, $uids_read, $uids_unread];
        }

        // 获取可读数据
        $uids_all = $rightServ->getUidsByRight($rights);

        // 获取已读数据
        $readServ = new ReadService();
        $study_list = $readServ->list_by_conds($conds);
        $uids_read = array_column($study_list, 'uid');

        // 未读数据
        $uids_unread = array_values(array_diff($uids_all, $uids_read));
        return [$uids_all, $uids_read, $uids_unread];
    }
}