<?php
/**
 * 编辑题目标签
 * SaveController.class.php
 * User: 何岳龙
 * Date: 2017-04-24
 */

namespace Apicp\Controller\Tag;

class SaveController extends AbstractController
{
    public function Index_post()
    {

        $params = I('post.');

        // 添加标签验证
        if (!$this->tag_serv->save_tag_validation($params)) {

            return false;
        }

        // 更新标签数据
        if (!$this->tag_serv->update_tag_data($params)) {

            return false;
        }

        // 返回数据
        $this->_result = array();

        return true;
    }

}
