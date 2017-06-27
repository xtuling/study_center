<?php
/**
 * 获取题库题目列表
 * @author: houyingcai
 * @email:  594609175@qq.com
 * @date :  2017-05-23 16:19:39
 * @version $Id$
 */

namespace Apicp\Controller\Topic;

class ListController extends AbstractController
{

    public function Index_post()
    {
        // 根据题库ID获取题目列表
        if (!$this->topic_serv->get_bank_topic_list($this->_result, I('post.'))) {

            E('_ERR_TOPIC_LIST_FAILED');

            return false;
        }

        return true;
    }

}
