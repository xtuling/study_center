<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/9/21
 * Time: 13:50
 */
namespace Apicp\Controller\User;

use VcySDK\Member;
use VcySDK\Service;
use Common\Common\User;
use Common\Service\UserService;

class UpdateController extends AbstractController
{

    /**
     * 【通讯录】更新人员状态
     * @author liyifei
     * @time 2016-09-17 22:50:37
     */
    public function Index_post()
    {
        // 接收参数
        $uids = I('post.uids');
        $action = I('post.action');
        $time = I('post.time', 0, 'intval');
        $explain = I('post.explain', '', 'trim');
        $dpIds = I('post.department_ids');

        // 必传参数是否存在
        if (empty($uids) || empty($action)) {
            E('_ERR_PARAM_IS_NULL');
            return false;
        }

        // uid格式是否正确
        if (!is_array($uids)) {
            E('_ERR_PARAM_FORMAT');
            return false;
        }

        // 操作类型是否允许
        $allowAction = [
            UserService::ENABLE_USER,
            UserService::DISABLE_USER
        ];
        if (!in_array($action, $allowAction)) {
            E('_ERR_USER_INVALID_ACTION');
            return false;
        }

        // 根据action操作人员
        $memServ = new Member(Service::instance());
        $conds = [
            'memUids' => $uids,
        ];
        switch ($action)
        {
            // 启用
            case UserService::ENABLE_USER:
                $conds['enable'] = UserService::STATUS_ENABLE;
                break;

            // 禁用
            case UserService::DISABLE_USER:
                $conds['enable'] = UserService::STATUS_DISABLE;
                break;

            // TODO 离职复职功能延期 liyifei 2016-10-10 16:02:15
//            // 复职
//            case UserService::REHAB_USER:
//                if (empty($dpIds) || empty($time)) {
//                    E('_ERR_PARAM_IS_NULL');
//                    return false;
//                }
//                $userServ->rehab($uid, $time, $dpIds);
//                break;
//
//            // 离职
//            case UserService::QUIT_USER:
//                if (empty($time)) {
//                    E('_ERR_PARAM_IS_NULL');
//                    return false;
//                }
//                $userServ->quit($uid, $time, $explain);
//                break;

            // 无效的用户操作
            default:
                E('_ERR_USER_INVALID_ACTION');
                return false;
        }

        $memServ->batchModifyStatus($conds);


        $this->clearUserCache();

        return true;
    }
}
