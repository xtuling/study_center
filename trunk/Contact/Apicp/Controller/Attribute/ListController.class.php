<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/9/13
 * Time: 14:54
 */

namespace Apicp\Controller\Attribute;

use Common\Model\AttrModel;
use Common\Service\AttrService;

class ListController extends AbstractController
{

    /**
     * 属性列表接口
     * @author liyifei
     * @time   2016-09-18 15:23:32
     */
    public function Index_post()
    {

        $attrServ = new AttrService();
        $data = $attrServ->list_by_conds([], [], [
            '`order`' => 'asc',
            'attr_id' => 'asc'
        ]);

        $selctTypes = [AttrModel::ATTR_TYPE_RADIO, AttrModel::ATTR_TYPE_CHECKBOX];
        // 将下拉框单选、多选类型的属性选项,反序列化
        foreach ($data as &$item) {
            $isSelectType = in_array($item['type'], $selctTypes) && !empty($item['option']);
            $item['option'] = $isSelectType ? unserialize($item['option']) : [[],[]];
        }

        if (empty($data)) {
            E('_ERR_ATTR_IS_EMPTY');
            return false;
        }

        $this->_result = $data;
    }

    /**
     * 模拟数据接口:属性列表接口
     * @author liyifei
     * @time   2016-09-13 15:54:29
     */
    public function Test_get()
    {

        $this->_result = [
            'list' => [
                [
                    'attr_id' => 1,
                    'attr_name' => '姓名',
                    "is_system" => 1,
                    'order' => 1,
                    'is_open' => 1,
                    'is_open_edit' => 0,
                    'is_required' => 0,
                    'is_required_edit' => 0,
                    'is_show' => 1,
                    'is_show_edit' => 1
                ],
                [
                    'attr_id' => 2,
                    'attr_name' => '最喜欢的运动',
                    "is_system" => 0,
                    'order' => 2,
                    'is_open' => 1,
                    "is_open_edit" => 0,
                    "is_required" => 0,
                    "is_required_edit" => 0,
                    "is_show" => 1,
                    "is_show_edit" => 1
                ]
            ]
        ];
    }
}
