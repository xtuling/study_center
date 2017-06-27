<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/9/17
 * Time: 22:37
 */

namespace Apicp\Controller\User;

use Common\Model\AttrModel;
use Common\Service\AttrService;
use Common\Service\JobService;
use Common\Service\RoleService;

class AttributeInfoController extends AbstractController
{

    /**
     * 【通讯录】人员属性详情
     * @author liyifei
     * @time   2016-09-17 22:38:54
     */
    public function Index_post()
    {

        $attrService = new AttrService();
        $data = $attrService->getAttrList(true, array(), true);
        if (empty($data)) {
            E('_ERR_ATTR_IS_EMPTY');
        }

        $list = [];
        $isCustom = AttrService::ATTR_CUSTOM_IS_FALSE;
        foreach ($data as $k => $v) {
            // 是否存在自定义属性(方便前端布局)
            if ($v['postion'] == AttrModel::AREA_CUSTOM) {
                $isCustom = AttrService::ATTR_CUSTOM_IS_TRUE;
            } else {
                $isCustom = AttrService::ATTR_CUSTOM_IS_FALSE;
            }
            $list[$k]['field_name'] = $v['field_name'];
            $list[$k]['attr_name'] = $v['attr_name'];
            $list[$k]['is_system'] = $v['is_system'];
            $list[$k]['is_required'] = $v['is_required'];
            $list[$k]['is_required_cp'] = $v['is_required_cp'];
            $list[$k]['order'] = $v['order'];
            $list[$k]['postion'] = $v['postion'];
            $list[$k]['type'] = $v['type'];
            $list[$k]['option'] = $v['option'];
        }

        $this->_result = [
            'is_custom' => $isCustom,
            'list' => array_values($list)
        ];
    }

}
