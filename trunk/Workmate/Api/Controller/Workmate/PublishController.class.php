<?php
/**
 * PublishController.class.php
 * 发布同事圈
 * User: heyuelong
 * Date:2017年4月24日10:40:17
 */

namespace Api\Controller\Workmate;

use Common\Service\CircleService;

class PublishController extends AbstractController
{

    /**
     * 是否必须登录
     *
     * @var string $_require_login
     */
    protected $_require_login = false;

    /**
     * 主方法
     * @return boolean
     */
    public function Index_post()
    {
        set_time_limit(0);
        // 获取请求参数
        $params = I('post.');

        // 实例化同事圈帖子信息表
        $service = new CircleService();

        // 验证数据
        if (!$service->publish_validate($params, $this->uid)) {

            return false;
        }

        // 写入数据
        $id = $service->insert_data($params, $this->_login->user, $this->_setting['release']);

        $this->_result = array('id' => intval($id));

        return true;
    }

}

