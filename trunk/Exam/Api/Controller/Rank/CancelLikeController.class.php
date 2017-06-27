<?php
/**
 *  CancelLikeController.class.php
 * 【考试中心-手机端】考试排名取消点赞接口
 * @author: 蔡建华
 * @date :  2017-05-23
 * @version $Id$
 */

namespace Api\Controller\Rank;

use Common\Service\LikeService;

class CancelLikeController extends AbstractController
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
         * 根据答卷ID进行点赞，如果尚未点赞则返回抛出尚未点赞无需取消，如果已点赞则删除点赞记录
         */
        $ea_id = I('post.ea_id');
        if (!$data = $this->like_serv->del_like_data($ea_id, $this->uid)) {
            E('_ERR_UNLIKE_FALSE');
            return false;
        }
        // 返回数据
        $this->_result = $data;

        return true;
    }
}
