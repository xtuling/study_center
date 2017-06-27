<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/6/26
 * Time: 17:54
 */
namespace Frontend\Controller\Index;

use VcySDK\Attach as Att;
use Common\Common\Attach;
use Common\Common\Constant;
use Common\Service\SourceAttachService;

class UpdateAuthController extends AbstractController
{
    /**
     * 是否必须登录
     */
    protected $_require_login = false;

    /**
     * 更新附件资源权限
     * @author liyifei
     */
    public function Index()
    {
        $saServ = new SourceAttachService();
        $list = $saServ->list_by_conds([
            'at_type' => Constant::ATTACH_TYPE_FILE,
        ]);

        // 鉴权信息
        $conf = getResAuthConfig('course');

        $attachServ = &Attach::instance();
        if (!empty($list)) {
            foreach ($list as $file) {
                if (empty($file['at_id'])) {
                    continue;
                }

                // 查询该附件信息（无附件信息、已加入鉴权时，跳过）
                $atInfo = $attachServ->getAttachDetail($file['at_id']);
                if (empty($atInfo) || (!empty($atInfo) && $atInfo['atAuthRequired'] == Att::AUTH_REQUIRED_TRUE)) {
                    continue;
                }

                $conf['atId'] = $file['at_id'];
                $attachServ->updateAuth($conf);
            }
        }

        exit('SUCCESS');
    }
}
