<?php
/**
 * 【活动中心-手机端】点赞页
 * @author: 蔡建华
 * @date :  2017-05-8
 * @version $Id$
 */

namespace Api\Controller\Activity;

use  Common\Service\LikeService;

class PraiseController extends AbstractController
{

    /**
     * @var bool 接口不强制登录
     */
    protected $_require_login = false;

    /**
     * @var LikeService  点赞对象
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

        // 判断是否外部人员
        if (empty($this->uid)) {
            E('_ERR_LIKE_ADD');
            return false;
        }

        //添加点赞
        if (!$rel = $this->_like_serv->add_like_data($params)) {
            E('_ERR_LIKE_DATA');
            return false;
        }

        $this->_result = [];

        return true;
    }
}
