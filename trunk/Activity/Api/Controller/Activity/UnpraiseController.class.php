<?php
/**
 * 【活动中心-手机端】取消点赞
 * @author: 蔡建华
 * @date :  2017-05-8
 * @version $Id$
 */

namespace Api\Controller\Activity;

use  Common\Service\LikeService;

class UnpraiseController extends AbstractController
{
    /**
     * @var  LikeService 点赞对象
     */
    protected $_like_serv;

    public function before_action($action = '')
    {
        if (!parent::before_action($action)) {
            return false;
        }

        $this->_like_serv = new LikeService();
        return true;
    }

    public function Index_post()
    {
        $params = I('post.');
        $params['uid'] = $this->uid;

        //取消点赞
        if (!$rel = $this->_like_serv->del_like_data($params)) {
            E('_ERR_CANCEL_LIKE');
            return false;
        }
        return true;
    }
}
