<?php
/**
 * 根据关联标签获取属性列表
 * AttrListController.class.php
 * User: 何岳龙
 * Date: 2017-04-24
 */

namespace Apicp\Controller\Tag;

use Common\Service\TagService;

class AttrListController extends AbstractController
{

    public function Index_post()
    {

        $params = I('post.');

        // 验证属性类型
        if (!is_numeric($params['type']) && !in_array($params['type'],
                array(TagService::JOB_TYPE_MEDAL, TagService::ROLE_TYPE_MEDAL),true)
        ) {
            E('_ERR_ATTR_TYPE');

            return false;
        }

        // 获取属性类型列表
        $list = $this->tag_serv->get_attr_list($params);

        // 返回数据
        $this->_result = array('list' => $list);

        return true;

    }

}
