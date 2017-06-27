<?php
/**
 * Created by PhpStorm.
 * User: Slok
 * Date: 16/9/22
 * Time: 11:14
 */

namespace Apicp\Controller\User;

use Common\Service\AttrService;
use Common\Service\UserService;

class DownTplController extends AbstractController
{

    /**
     * 下载人员批量导入模板
     * @author zhonglei
     */
    public function Index_get()
    {

        $attrServ = new AttrService();
        $attrs = $attrServ->getAttrList(true, array(), true);

        $userService = new UserService();
        $userService->exportTpl($attrs);

        return true;
    }
}
