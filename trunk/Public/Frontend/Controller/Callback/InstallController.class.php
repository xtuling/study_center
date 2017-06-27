<?php
/**
 * Created by PhpStorm.
 * User: zhonglei
 * Date: 17/6/12
 * Time: 19:31
 */
namespace Frontend\Controller\Callback;

use Common\Service\CommonSettingService;

class InstallController extends AbstractController
{
    /**
     * 应用默认数据安装接口
     * @author zhonglei
     */
    public function Index()
    {
        // 插入默认数据
        $CommonSettingServ = new CommonSettingService();
        $CommonSettingServ->setDefaultData();

        exit('SUCCESS');
    }
}
