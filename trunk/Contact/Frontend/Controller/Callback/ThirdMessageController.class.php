<?php
/**
 * 由Uc中转的三方消息回调
 * User: zhuxun37
 * Date: 16/8/11
 * Time: 下午3:41
 */

namespace Frontend\Controller\Callback;

use Common\Common\Cache;
use Think\Log;
use Common\Common\User;
use Common\Common\Msg;
use Common\Service\InviteUserService;

class ThirdMessageController extends AbstractController
{

    public function before_action($action = '')
    {
        if (! parent::before_action($action)) {
            return false;
        }

        // 微信消息 Json 解析
        $this->callBackData['wxMessage'] = json_decode($this->callBackData['wxMessage'], true);

        Log::record(sprintf('---%s %s ThirdMessage START---', QY_DOMAIN, APP_IDENTIFIER), Log::INFO);
        Log::record(var_export($this->callBackData, true), Log::INFO);
        Log::record(sprintf('---%s %s ThirdMessage END ---', QY_DOMAIN, APP_IDENTIFIER), Log::INFO);

        return true;
    }

    /**
     * 三方消息回调
     *
     * wxMessage 为第三方消息内容, $this->callBackData 格式如下:
     * {
     *   "msg":"成功",
     *   "code":"SUCCESS",
     *   "wxMessage": {
     *     "agentId":114,
     *     "toUserName":"wx276cb16432f0bdd9",
     *     "fromUserName":"zhuxun37",
     *     "createTime":1470972131,
     *     "msgType":"text",
     *     "content":"讲话稿",
     *     "msgId":4617283198342698634
     *   },
     *   "memUid":"78D547B77F00000162CAAD4E526287C7",
     *   "epEnumber":"t5thr"
     * }
     *
     * @return bool
     */
    public function Index()
    {
        switch ($this->callBackData['wxMessage']['msgType']) {
            case cfg('UC_CALLBACK_MSG_TYPE_TEXT'):
                $this->search();
                break;
            case cfg('UC_CALLBACK_MSG_TYPE_SUBSCRIBE'):
                $this->notice();
                break;
            case cfg('UC_CALLBACK_MSG_TYPE_UNSUBSCRIBE'):
                $this->deleteUser();
                break;
        }

        $this->clearCache();

        exit('SUCCESS');
    }

    /**
     * 搜索人员
     * @author zhonglei
     * @return bool
     */
    private function search()
    {
        $uid = $this->callBackData['memUid'];
        $content = trim($this->callBackData['wxMessage']['content']);

        if (empty($content) || strlen($content) > 20) {
            return false;
        }

        $userServ = new User();
        $result = $userServ->listByConds(['memUsername' => $content]);
        $total = $result['total'];

        if ($total == 0) {
            $text = '没有找到相关联系人';
        } elseif ($total == 1) {
            $memUid = $result['list'][0]['memUid'];
            $user = $userServ->getByUid($memUid);
            $deptname = $user['dpName'] ? $user['dpName'][0]['dpName'] : '';
            $text = "姓名：{$user['memUsername']}\n部门：{$deptname}";

            if ($user['memMobile']) {
                $text .= "\n手机：{$user['memMobile']}";
            }

            if ($user['memEmail']) {
                $text .= "\n邮箱：{$user['memEmail']}";
            }

            $url = frontUrl('/app/page/contacts/member-detail', ['uid' => $memUid]);
            $link = "<a href=\"{$url}\">点击查看详情</a>";
            $text .= "\n{$link}";
        } else {
            $url = frontUrl('/app/page/contacts/member-list', ['kw' => $content]);
            $link = "<a href=\"{$url}\">点击查看详情</a>";
            $text = "找到{$total}个联系人\n{$link}";
        }

        $msgServ = new Msg();
        $msgServ->sendText($uid, [], $text);
        return true;
    }

    /**
     * 关注通知
     * @author zhonglei
     * @return bool
     */
    private function notice()
    {
        $uid = $this->callBackData['memUid'];

        // 获取邀请数据
        $inviteUserServ = new InviteUserService();
        $invite = $inviteUserServ->get_by_conds([
            'uid' => $uid,
            'is_notice' => InviteUserService::INVITER_IS_NOTICE_FALSE
        ]);

        if ($invite) {
            $userServ = new User();
            $user = $userServ->getByUid($uid);

            // 发送消息
            $msgServ = new Msg();
            $msgServ->sendNews($invite['invite_uid'], '', [
                [
                    'title' => "{$user['memUsername']}已经成功加入企业号",
                    'description' => "姓名：{$user['memUsername']}\n微信号：{$user['memWeixin']}\n职位：{$user['jobName']}",
                    'url' => frontUrl('/app/page/contacts/myinvite-audit', ['list_type' => InviteUserService::MY_INVITE_LIST]),
                ]
            ]);

            // 更新邀请数据
            $inviteUserServ->update_by_conds(['invite_id' => $invite['invite_id']], ['is_notice' => InviteUserService::INVITER_IS_NOTICE_TRUE]);
        }
    }

    /**
     * 删除人员
     * @author zhonglei
     * @return void
     */
    private function deleteUser()
    {
        $uid = $this->callBackData['memUid'];
        $userServ = new User();
        $userServ->clearUserCache($uid);
    }

    /**
     * 清理缓存应用权限缓存
     *
     * @return bool
     */
    protected function clearCache()
    {

        $cache = &Cache::instance();

        // 人员关注事件
        if ($this->callBackData['wxMessage']['msgType'] == cfg('UC_CALLBACK_MSG_TYPE_SUBSCRIBE')) {
            // 清除应用权限信息
            $cache->set('Common.Jurisdiction', null);
        }

        return true;
    }
}
