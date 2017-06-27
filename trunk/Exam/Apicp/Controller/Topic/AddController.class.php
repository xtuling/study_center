<?php
/**
 * 添加题库题目
 * @author: houyingcai
 * @email:  594609175@qq.com
 * @date :  2017-05-23 16:24:51
 * @version $Id$
 */

namespace Apicp\Controller\Topic;

class AddController extends AbstractController
{

    public function Index_post()
    {
        // 添加题库题目
        if (!$this->topic_serv->add_topic($this->_result, I('post.'))) {

            E('_ERR_TOPIC_ADD_FAILED');

            return false;
        }

        return true;
    }

}
