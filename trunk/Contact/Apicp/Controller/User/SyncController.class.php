<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/9/23
 * Time: 18:04
 */
namespace Apicp\Controller\User;

use Common\Common\User;
use Common\Common\Department;

class SyncController extends AbstractController
{

    /**
     * 【通讯录】同步用户
     * @author liyifei
     */
    public function Index_post()
    {
        // 将微信企业号用户同步至UC
        $userServ = new User();
        $userServ->sync();

        // FIXME zhoutao 2017-06-09 22:48:27 这里的清理缓存用处不大, 应该是在UC同步完后的回调那清理缓存
        clear_sys_cache([
            'Common.User',
            'Common.Department'
        ]);
        return true;
    }
}
