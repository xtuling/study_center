<?php
/**
 * 删除题库题目
 * @author: houyingcai
 * @email:  594609175@qq.com
 * @date :  2017-05-23 16:23:32
 * @version $Id$
 */

namespace Apicp\Controller\Topic;

class DeleteController extends AbstractController
{

    public function Index_post()
    {
        // 删除题库题目
        if (!$this->topic_serv->delete_topic(I('post.'))) {

            E('_ERR_TOPIC_ADD_FAILED');

            return false;
        }

        return true;
    }

}
