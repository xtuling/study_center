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
        $this->clearCache();
        exit('SUCCESS');
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
