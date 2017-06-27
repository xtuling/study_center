<?php
/**
 * 获取题库题目列表
 * @author: houyingcai
 * @email:  594609175@qq.com
 * @date :  2017-05-23 16:19:39
 * @version $Id$
 */

namespace Rpc\Controller\Breakthrough;

use Common\Service\TopicService;

class TitleListController extends AbstractController
{

    public function Index()
    {
        // 初始化
        $service = new TopicService();

        // 根据题库ID获取题目列表
        if (!$service->get_bank_topic_rpc_list($result, $this->_params)) {

            E('_ERR_TOPIC_LIST_FAILED');

            return false;
        }

        return json_encode($result);
    }

}
