<?php
/**
 * 获取题库题目详情
 * @author: houyingcai
 * @email:  594609175@qq.com
 * @date :  2017-05-23 16:26:08
 * @version $Id$
 */

namespace Apicp\Controller\Topic;

class DetailController extends AbstractController
{

    public function Index_post()
    {
        // 获取题目详情
        if (!$this->topic_serv->get_bank_topic($this->_result, I('post.'))) {

            E('_ERR_TOPIC_DETAIL_FAILED');

            return false;
        }

        return true;
    }

}
