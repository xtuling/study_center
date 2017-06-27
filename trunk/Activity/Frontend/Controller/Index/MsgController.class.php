<?php
/**
 *
 * @author: houyingcai
 * @email:    594609175@qq.com
 * @date :  2017-05-10 17:22:44
 * @version $Id$
 */

namespace Frontend\Controller\Index;

use Common\Service\CommentService;

class MsgController extends \Common\Controller\Frontend\AbstractController
{
    // 消息ID类型为活动
    const ACTIVITY = 1;

    // 消息ID类型为回复
    const REPLY = 2;


    /**
     * 不是必须登录
     * @var string $_require_login
     */
    protected $_require_login = false;

    public function Index()
    {
        $params = I('get.');

        // 跳转活动详情
        if ($params['type'] == self::ACTIVITY) {

            redirectFront('/app/page/activity/activity-detail',
                array('_identifier' => APP_IDENTIFIER, 'ac_id' => $params['id']));
        }

        // 跳转评论详情
        if ($params['type'] == self::REPLY) {

            $comm_serv = new CommentService();
            $comm_data = $comm_serv->get($params['id']);

            redirectFront('/app/page/activity/activity-reply',
                array('_identifier' => APP_IDENTIFIER, 'comment_id' => $params['id'], 'ac_id' => $comm_data['ac_id']));

        }
    }


}