<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/9/18
 * Reader: zhoutao 2017-06-13 10:11:48
 * Time: 11:52
 */

namespace Api\Controller\Invite;

use Common\Model\AttrModel;
use Common\Service\AttrService;
use Common\Service\InviteLinkService;
use VcySDK\Service;
use VcySDK\Enterprise;

class FormController extends AbstractController
{
    protected $_require_login = false;

    /**
     * 邀请函表单接口
     * @author liyifei
     */
    public function Index_post()
    {

        $link_id = (int)I('post.link_id');

        // 检查邀请链接是否可用
        $this->_checkLinkId($link_id);

        $inviteLinkService = new InviteLinkService();
        if (empty($link_id) || !($link = $inviteLinkService->get($link_id))) {
            E('1009:邀请连接错误');
            return false;
        }
        $default_data = unserialize($link['default_data']);

        // 读取所有已设置开启的属性详情
        $form = [];
        $attrServ = new AttrService();
        $attrs = $attrServ->getAttrList(true, [AttrModel::ATTR_TYPE_SPECIAL, AttrModel::ATTR_TYPE_LEADER]);
        foreach ($attrs as $k => $attr) {
            if ('memJob' == $attr['field_name'] && !empty($default_data['job'])) {
                continue;
            } elseif ('memRole' == $attr['field_name'] && !empty($default_data['role'])) {
                continue;
            }

            $form[$k] = [
                'field_name' => $attr['field_name'],
                'attr_name' => $attr['attr_name'],
                'order' => intval($attr['order']),
                'type' => intval($attr['type']),
                'option' => $attr['option'],
                'is_required' => $attr['is_required'],
                'area' => AttrModel::AREA_PERSONAL,
            ];
        }

        $attrs = array_combine_by_key($attrs, 'field_name');
        $default = array();
        foreach ($default_data as $_k => $_v) {
            if ('department' == $_k) {
                $default['dpName'] = array('组织', $_v['dpName']);
            } elseif ('job' == $_k) {
                $default[$attrs['memJob']['field_name']] = array($attrs['memJob']['attr_name'], $_v);
            } elseif ('role' == $_k) {
                $default[$attrs['memRole']['field_name']] = array($attrs['memRole']['attr_name'], $_v);
            }
        }

        $enterpriseService = new Enterprise(Service::instance());
        $enterpriseInfo = $enterpriseService->detail();

        $this->_result = [
            'list' => array_values($form),
            'default_data' => $default,
            'qy_logo' => $enterpriseInfo['corpSquareLogo']
        ];

        return true;
    }
}
