<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/9/17
 * Time: 22:51
 */
namespace Apicp\Controller\User;

use Common\Common\User;
use Common\Model\AttrModel;
use Common\Service\AttrService;

class EditQueryController extends AbstractController
{

    /**
     * 【通讯录】人员编辑查询
     * @author liyifei
     * @time 2016-09-17 22:38:54
     */
    public function Index_post()
    {
        $uid = I('post.uid', '', 'trim');
        if (empty($uid)) {
            E('_ERR_UID_IS_NULL');
        }

        // 属性信息
        $attrServ = new AttrService();
        $attrs = $attrServ->getAttrList(true, array(), true);

        // 用户信息
        $newUser = new User();
        $userInfo = $newUser->getByUid($uid);

        $list = [];
        $isCustom = AttrService::ATTR_CUSTOM_IS_FALSE;
        foreach ($attrs as $k => $attr) {
            // 是否存在自定义属性(方便前端布局)
            if ($attr['postion'] == AttrModel::AREA_CUSTOM) {
                $isCustom = AttrService::ATTR_CUSTOM_IS_TRUE;
            }
            $list[$k] = [
                'attr_name' => $attr['attr_name'],
                'field_name' => $attr['field_name'],
                'type' => $attr['type'],
                'option' => $attr['option'],
                'is_required' => $attr['is_required'],
                'is_required_cp' => $attr['is_required_cp'],
                'is_system' => $attr['is_system'],
                'postion' => $attr['postion'],
                'order' => $attr['order'],
            ];

            // 根据属性类型不同,将属性值转为与前端约定好的格式
            $list[$k]['attr_value'] = $attrServ->formatValueByType($attr['type'], $userInfo[$attr['field_name']]);

            // 特殊处理"多选"类型属性,格式化为前端需要格式
            if ($attr['type'] == AttrModel::ATTR_TYPE_CHECKBOX) {
                foreach ($attr['option'] as $key => $val) {
                    foreach ($list[$k]['attr_value'] as $v) {
                        if ($val['value'] == $v['value']) {
                            $attr['option'][$key]['checked'] = true;
                        }
                    }
                }
                $list[$k]['attr_value'] = $attr['option'];
            }
        }

        $this->_result = [
            'is_custom' => $isCustom,
            'memSubscribeStatus' => $userInfo['memSubscribeStatus'],
            'list' => array_values($list)
        ];
    }
}
