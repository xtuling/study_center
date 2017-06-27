<?php
/**
 * LikeController.class.php
 * 【考试中心-手机端】考试排名点赞接口
 * @author: 蔡建华
 * @date :  2017-05-23
 * @version $Id$
 */

namespace Api\Controller\Rank;

use Common\Service\LikeService;

class LikeController extends AbstractController
{
    /**
     * @var LikeService
     */
    protected $like_serv;

    public function before_action($action = '')
    {
        if (!parent::before_action($action)) {
            return false;
        }
        $this->like_serv = New LikeService();

        return true;
    }

    public function Index_post()
    {
        /**
         *  根据答卷ID进行点赞，如果已经点赞返回已经点赞，否则插入一条点赞记录
         */
        $ea_id = I('post.ea_id');
        if (!$data = $this->like_serv->add_like_data($ea_id, $this->uid)) {
            E('_ERR_LIKE_FALSE');
            return false;
        }
        // 返回数据
        $this->_result = $data;

        return true;
    }
}
