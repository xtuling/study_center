<?php
/**
 * DelController.class.php
 * 删除同事圈
 * User: 代军
 * Date: 2017-04-24
 */

namespace Apicp\Controller\Workmate;

use Common\Service\AttachmentService;
use Common\Service\CircleService;
use Common\Service\LikeService;

class DelController extends AbstractController
{
    /**
     * @var  CircleService 帖子信息表
     */
    protected $_circle_serv;

    /**
     * @var AttachmentService 附件图片信息表
     */
    protected $_attach_serv;

    /**
     * @var LikeService 帖子及评论点赞信息表
     */
    protected $_like_serv;

    public function before_action($action = '')
    {

        if (!parent::before_action($action)) {
            return false;
        }
        // 实例化帖子信息表
        $this->_circle_serv = new CircleService();

        // 实例化帖子附件信息表
        $this->_attach_serv = new AttachmentService();

        // 实例化帖子点赞信息表
        $this->_like_serv = new LikeService();

        return true;
    }

    public function Index_post()
    {
        // 接收参数
        $params = I('post.');

        // 参数验证
        if (empty($params['list']) || !is_array($params['list'])) {
            $this->_set_error('_EMPTY_DATALIST');

            return false;
        }

        // 将列表转换成ID集合（一维数组）
        $ids = array_column($params['list'], 'id');

        if (empty($ids)) {
            $this->_set_error('_EMPTY_DATALIST');

            return false;
        }

        try {

            // 开始事务
            $this->_circle_serv->start_trans();
            // 删除主表数据
            $this->_circle_serv->delete_by_conds(array('id' => $ids));

            // 删除评论数据
            $this->_circle_serv->delete_by_conds(array('pid' => $ids));

            // 删除附件表数据
            $this->_attach_serv->delete_by_conds(array('cid' => $ids));

            // 删除帖子点赞数据
            $this->_like_serv->delete_by_conds(array('cid' => $ids));

            // 提交事务
            $this->_circle_serv->commit();

        } catch (\Think\Exception $e) {
            \Think\Log::record($e);
            // 事务回滚
            $this->_set_error($e->getMessage(), $e->getCode());
            $this->_circle_serv->rollback();

            return false;

        } catch (\Exception $e) {

            \Think\Log::record($e);
            $this->_set_error($e->getMessage(), $e->getCode());
            // 事务回滚
            $this->_circle_serv->rollback();

            return false;
        }

        // 删除完成后同步更新收藏状态
        $this->_circle_serv->update_collection(implode(',',$ids));

        return true;
    }

}

