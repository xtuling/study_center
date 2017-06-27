<?php
/**
 * DelController.class.php
 * 删除同事圈评论
 * User: 代军
 * Date: 2017-04-24
 */

namespace Apicp\Controller\Comment;

use Common\Service\CircleService;

class DelController extends AbstractController
{
    /**
     * @var  CircleService 帖子信息表
     */
    protected $_circle_serv;

    public function before_action($action = '')
    {

        if (!parent::before_action($action)) {
            return false;
        }
        // 实例化帖子信息表
        $this->_circle_serv = new CircleService();

        return true;
    }

    public function Index_post()
    {
        // 接收参数
        $id = I('post.id');

        // 参数验证
        if (empty($id)) {
            $this->_set_error('_EMPTY_COMMENTID');

            return false;
        }

        // 执行删除
        if (!$this->_circle_serv->delete($id)) {
            $this->_set_error('_ERR_DATA_DELETE');

            return false;
        }

        return true;
    }

}

