<?php
/**
 * 编辑题库题目
 * @author: houyingcai
 * @email:  594609175@qq.com
 * @date :  2017-05-23 16:21:53
 * @version $Id$
 */

namespace Apicp\Controller\Topic;


class SaveController extends AbstractController
{

    public function Index_post()
    {
        // 编辑题库题目
        if (!$this->topic_serv->update_topic($this->_result, I('post.'))) {

            E('_ERR_TOPIC_UPDATE_FAILED');

            return false;
        }

        return true;
    }

}
