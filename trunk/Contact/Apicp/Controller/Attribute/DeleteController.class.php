<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/9/13
 * Time: 17:13
 */

namespace Apicp\Controller\Attribute;

use Common\Model\AttrModel;
use Common\Service\AttrService;
use Common\Service\InviteUserService;

class DeleteController extends AbstractController
{

    /**
     * 删除属性接口
     * @author liyifei
     * @time 2016-09-13 17:22:27
     */
    public function Index_post()
    {

        $attrId = I('post.attr_id', 0, 'Intval');
        $attrServ = new AttrService();
        $data = $attrServ->get($attrId);

        // 属性不存在
        if (empty($data)) {
            E('_ERR_ATTR_IS_EMPTY');
        }

        // 系统属性不可删除
        if (AttrModel::IS_SYSTEM_TRUE == $data['is_system']) {
            E('_ERR_SYSTEM_ATTR_NO_OPERABLE');
        }

        // 是否有未审核的人员
        $inviteUserServ = new InviteUserService();
        if ($inviteUserServ->haveInviteUserWait()) {
            E('_ERR_CANNOT_EDIT_ATTR');
        }

        // 根据field_name删除扩展属性
        $attrServ->deleteAttr($data['field_name']);
    }
}
