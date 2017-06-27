<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/9/13
 * Time: 17:17
 */

namespace Apicp\Controller\Attribute;

use Common\Service\AttrService;

class InfoController extends AbstractController
{

    /**
     * 编辑属性查询接口
     * @author liyifei
     * @time   2016-09-18 18:08:38
     */
    public function Index_post()
    {

        // 接收参数
        $attrId = I('post.attr_id', 0, 'Intval');
        if (empty($attrId)) {
            E('_ERR_ATTR_UNDEFINED');
            return false;
        }

        // 属性是否存在
        $attrServ = new AttrService();
        $data = $attrServ->get_by_conds(['attr_id' => $attrId]);
        if (empty($data)) {
            E('_ERR_ATTR_UNDEFINED');
            return false;
        }

        // 处理返回值
        $result = [
            'attr_id' => $data['attr_id'],
            'attr_name' => $data['attr_name'],
            'type' => $data['type'],
            'order' => $data['order'],
            'option' => empty($data['option']) ? '' : unserialize($data['option']),
            'is_system' => $data['is_system'],
            'is_open' => $data['is_open'],
            'is_required' => $data['is_required'],
            'is_show' => $data['is_show']
        ];

        $this->_result = $result;
    }

    /**
     * 模拟数据接口:编辑属性查询接口
     * @author liyifei
     * @time   2016-09-13 17:24:19
     */
    public function Test_get()
    {

        $this->_result = [
            "attr_name" => "喜爱的运动项目",
            "type" => 2,
            "order" => 7,
            "option" => [
                0 => "篮球",
                1 => "足球"
            ],
            "is_system" => 0,
            "is_open" => 1,
            "is_required" => 0,
            "is_show" => 1
        ];
    }
}
