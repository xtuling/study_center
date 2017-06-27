<?php
/**
 * CollectionCancelController.class.php
 * 同事圈取消收藏
 * User: Xtong
 * Date: 2017年06月02日
 */

namespace Api\Controller\Workmate;

use Common\Common\User;
use Common\Model\CircleModel;
use Common\Service\CircleService;
use VcySDK\Logger;

class CollectionCancelController extends AbstractController
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
        $id = I('post.id');

        // 如果话题ID不存在
        if (empty($id)) {

            $this->_set_error('_EMPTY_CIRCLE_ID');

            return false;
        }

        // 不支持外部人员收藏
        if (empty($this->uid)) {

            $this->_set_error('_NOT_ALLOW_OUTER_COLLECTION');

            return false;
        }

        // 实例化话题表
        $service = new CircleService();

        // 获取话题详情
        $info = $service->get_by_conds(
            array(
                'id' => $id,
                'pid' => CircleModel::CIRCLE_PID
            )
        );

        // 如果话题详情不存在
        if (empty($info)) {

            $this->_set_error('_EMPTY_CIRCLE_INFO');

            return false;
        }

        // 获取收藏状态 xtong 2017年06月02日
        $url = convertUrl(QY_DOMAIN . '/Public/Rpc/Collection/CollectionDelete');

        $params =[
            'uid'=>$this->uid,
            'app'=>'workmate',
            'dataId'=>$id
        ];

        $res = \Com\Rpc::phprpc($url)->invoke('Index',$params);

        Logger::write('收藏取消：'.var_export($res,true));

        // 返回成功
        $this->_result = [];

        return true;
    }

}

