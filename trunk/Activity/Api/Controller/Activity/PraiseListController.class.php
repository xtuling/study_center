<?php
/**
 * 【活动中心-手机端】获取活动点赞/评论点赞列表
 * @author: 蔡建华
 * @date :  2017-05-8
 * @version $Id$
 */

namespace Api\Controller\Activity;

use  Common\Service\LikeService;

class PraiseListController extends AbstractController
{
    /**
     * @var bool 接口不强制登录
     */
    protected $_require_login = false;

    /**
     * @var  LikeService 点赞对象
     */
    protected $_like_serv;

    public function before_action($action = '')
    {
        if (!parent::before_action($action)) {
            return false;
        }
        // 实例化点赞表
        $this->_like_serv = new LikeService();
        return true;
    }

    /**
     * 主方法
     */
    public function Index_get()
    {
        $params = I('get.');

        if (!$data = $this->_like_serv->get_like_list($params)) {
            E('_ERR_DATA_NOT_EXIST');
        }

        // 返回数据
        $this->_result = $data;

        return true;
    }
}
