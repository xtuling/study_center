<?php
/**
 * MsgController.class.php
 * 【同事圈-手机端】发送消息跳转
 * User: heyuelong
 * Date:2017年4月27日14:52:14
 */
namespace Frontend\Controller\Index;

class MsgController extends \Common\Controller\Frontend\AbstractController
{
    // 我的评论详情类型
    const MY_COMMENT_INFO = 1;

    // 我的话题详情类型
    const MY_CIRCLE_INFO = 2;

    // 我的评论详情类型
    const CIRCLE_INFO = 3;


    /**
     * 不是必须登录
     * @var string $_require_login
     */
    protected $_require_login = false;

    public function Index()
    {
        $params = I('get.');

        // 跳转我的评论详情
        if ($params['type'] == self::MY_COMMENT_INFO) {

            redirectFront('/app/page/workmate/workmate-approveComment',
                array('_identifier' => APP_IDENTIFIER, 'id' => $params['id']));
        }

        // 跳转我的话题详情
        if ($params['type'] == self::MY_CIRCLE_INFO) {

            redirectFront('/app/page/workmate/workmate-approveDetail',
                array('_identifier' => APP_IDENTIFIER, 'id' => $params['id']));

        }

        // 跳转话题详情
        if ($params['type'] == self::CIRCLE_INFO) {

            redirectFront('/app/page/workmate/workmate-detail',
                array('_identifier' => APP_IDENTIFIER, 'id' => $params['id']));
        }
    }


}
