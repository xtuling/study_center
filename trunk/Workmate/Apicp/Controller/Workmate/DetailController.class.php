<?php
/**
 * DetailController.class.php
 * 同事圈详情
 * User: 代军
 * Date: 2017-04-24
 */

namespace Apicp\Controller\Workmate;

use Common\Service\CircleService;

class DetailController extends AbstractController
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
            $this->_set_error('_EMPTY_ID');

            return false;
        }

        // 获取帖子基本信息
        $data = $this->_circle_serv->get($id);
        if (empty($data)) {
            $this->_set_error('_ERR_DATA_EXIST');

            return false;
        }

        // 格式化详情数据
        $this->_circle_serv->format_detail($data);

        // 返回数据
        $this->_result = $data;

        return true;
    }

}

