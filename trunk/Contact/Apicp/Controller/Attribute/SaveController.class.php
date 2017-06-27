<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/9/13
 * Time: 17:15
 */

namespace Apicp\Controller\Attribute;

use Common\Model\AttrModel;
use Common\Service\AttrService;
use Common\Service\InviteUserService;

class SaveController extends AbstractController
{

    /**
     * 保存属性接口（新增、编辑保存）
     * @author liyifei
     * @time 2016-09-18 16:41:19
     */
    public function Index_post()
    {

        // 接收参数
        $attrId = I('post.attr_id', -1, 'Intval');
        $type = I('post.type', -1, 'Intval');
        $isOpenCp = I('post.is_open_cp', -1, 'Intval');
        $isOpen = I('post.is_open', -1, 'Intval');
        $isShow = I('post.is_show', -1, 'Intval');
        $isRequiredCp = I('post.is_required_cp', -1, 'Intval');
        $isRequired = I('post.is_required', -1, 'Intval');
        $option = I('post.option', '');
        $attrName = I('post.attr_name', '', 'trim');

        // 准备参数
        $params = [];
        if (!empty($attrName)) {
            $params['attr_name'] = $attrName;
        }
        // 类型是单选或多选 并且 option不为空
        $getOption = in_array($type, [AttrModel::ATTR_TYPE_RADIO, AttrModel::ATTR_TYPE_CHECKBOX]) && !empty($option);
        $params['option'] = serialize($getOption ? $option : [[], []]);

        if ($type != -1) {
            $params['type'] = $type;
        }
        if ($isOpenCp != -1) {
            $params['is_open_cp'] = $isOpenCp;
        }
        if ($isOpen != -1) {
            $params['is_open'] = $isOpen;
        }
        if ($isShow != -1) {
            $params['is_show'] = $isShow;
        }
        if ($isRequiredCp != -1) {
            $params['is_required_cp'] = $isRequiredCp;
        }
        if ($isRequired != -1) {
            $params['is_required'] = $isRequired;
        }

        // 是否有未审核的人员
        $inviteUserServ = new InviteUserService();
        if ($inviteUserServ->haveInviteUserWait() && (1 == $params['is_required_cp'] || 1 == $params['is_required'])) {
            E('_ERR_CANNOT_EDIT_ATTR');
        }

        // 获取是否有未审核的邀请人员
        /**$inviteUserServ = new InviteUserService();
        $haveInviteUserWait = $inviteUserServ->haveInviteUserWait();*/

        // 根据属性ID进行修改或创建操作
        $attrServ = new AttrService();
        if ($attrId == -1) {
            /**if ($haveInviteUserWait) {
                E('_ERR_CANNOT_EDIT_ATTR');
            }*/
            // 创建属性
            $result = $attrServ->addAttr($params);
        } else {
            // 修改了必填属性或者是否开启，需要查看是否有未通过审核的用户
            /**if ($haveInviteUserWait && ($isRequired != -1 || $isOpen != -1)) {
                E('_ERR_CANNOT_EDIT_ATTR');
            }*/
            // 修改属性
            $result = $attrServ->updateAttrById($attrId, $params);
        }
        if (!$result) {
            E('_ERR_ATTR_EDIT_FAIL');
        }

        return false;
    }
}
