<?php
/**
 * 获取全部标签属性（添加题目用，不分页）
 * @author: houyingcai
 * @email:  594609175@qq.com
 * @date :  2017-05-24 16:28:39
 * @version $Id$
 */

namespace Apicp\Controller\Tag;

class TagAttrListController extends AbstractController
{

    public function Index_post()
    {
        // 获取全部标签属性
        $list = $this->tag_serv->get_all_tag_attr();

        // 组装返回数据
        $this->_result = array('list' => $list);

        return true;
    }

}
