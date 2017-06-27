<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/10/10
 * Time: 17:58
 */
namespace Apicp\Controller\User;

use VcySDK\Member;
use VcySDK\Service;
use Common\Common\User;

class MoveDeptController extends AbstractController
{

    /**
     * 【通讯录】批量用户移动部门
     * @author liyifei
     */
    public function Index_post()
    {
        // 接收参数
        $uids = I('post.uids');
        $dpIds = I('post.dp_ids');

        if(empty($uids) || empty($dpIds)) {
            E('_ERR_PARAM_IS_NULL');
            return false;
        }

        // 参数格式是否正确
        if (!is_array($uids) || !is_array($dpIds)) {
            E('_ERR_PARAM_FORMAT');
            return false;
        }

        // 请求架构接口,操作移动部门
        $memServ = new Member(Service::instance());
        $conds = [
            'memUids' => $uids,
            'dpIdList' => $dpIds,
        ];
        $memServ->moveDept($conds);

        $this->clearUserCache();

        return true;
    }
}
