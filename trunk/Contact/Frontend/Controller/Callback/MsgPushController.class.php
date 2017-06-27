<?php
/**
 * Created by PhpStorm.
 * User: Slok
 * Date: 2016/10/20
 * Time: 14:58
 */
namespace Frontend\Controller\Callback;

use Think\Log;
use Common\Common\Msg;
use Common\Service\TaskService;
use Common\Service\InviteSettingService;
use Common\Service\InviteUserService;

class MsgPushController extends AbstractController
{
    /**
     * 待审批消息推送
     * @author zhonglei
     */
    public function Index()
    {
        Log::record(sprintf('---%s %s MSGPUSH START---', QY_DOMAIN, APP_IDENTIFIER), Log::INFO);

        // 更新计划任务最后一次执行时间
        $taskServ = new TaskService();
        $task = $taskServ->get_by_conds([]);
        $taskServ->update_by_conds(['task_id' => $task['task_id']], ['runtime' => MILLI_TIME]);

        // 获取待审批列表
        $inviteUserServ = new InviteUserService();
        $list = $inviteUserServ->list_by_conds(['check_status' => InviteUserService::CHECK_STATUS_WAIT]);
        $waitCount = count($list);

        Log::record("wait count: {$waitCount}", Log::INFO);

        if ($waitCount > 0) {
            // 获取待审批用户姓名
            $namelist = array_column($list, 'username');
            $names = implode('、', $namelist);

            // 获取审核人uid
            $settingServ = new InviteSettingService();
            $setting = $settingServ->get_by_conds([]);
            $uids = unserialize($setting['check_uids']);

            Log::record(sprintf('check uids: %s', var_export($uids, true)), Log::INFO);

            if (is_array($uids) && $uids) {
                $msgServ = new Msg();
                $msgServ->sendNews($uids, '', [
                    [
                        'title' => "您有{$waitCount}条待处理的邀请审批",
                        'description' => "姓名：{$names}",
                        'url' => frontUrl('/app/page/contacts/myinvite-audit', ['list_type' => InviteUserService::MY_CHECK_LIST]),
                    ]
                ]);
            }
        }

        Log::record(sprintf('---%s %s CREATE TASK END ---', QY_DOMAIN, APP_IDENTIFIER), Log::INFO);
        exit('SUCCESS');
    }
}
