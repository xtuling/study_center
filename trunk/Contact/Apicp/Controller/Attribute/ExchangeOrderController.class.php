<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/9/20
 * Time: 13:29
 */

namespace Apicp\Controller\Attribute;

use Common\Service\AttrService;

class ExchangeOrderController extends AbstractController
{

    /**
     * 【通讯录】交换属性顺序接口
     * @author liyifei
     */
    public function Index_post()
    {

        // 接收参数
        $attrId1 = I('post.attr_id1', 0, 'Intval');
        $attrId2 = I('post.attr_id2', 0, 'Intval');

        // 参数判断
        if ($attrId1 == 0 || $attrId2 == 0) {
            E('_ERR_ATTR_ID_IS_NULL');
            return false;
        }
        if ($attrId1 == $attrId2) {
            E('_ERR_ATTR_ID_ALIKE');
            return false;
        }

        // 交换属性顺序
        $attrServ = new AttrService();
        $attrServ->exchangeOrder($attrId1, $attrId2);

        return false;
    }
}
