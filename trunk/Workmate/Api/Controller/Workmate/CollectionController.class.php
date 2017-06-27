<?php
/**
 * CollectionController.class.php
 * 同事圈收藏
 * User: Xtong
 * Date: 2017年06月02日
 */

namespace Api\Controller\Workmate;

use Common\Common\User;
use Common\Model\CircleModel;
use Common\Service\AttachmentService;
use Common\Service\CircleService;
use VcySDK\Logger;

class CollectionController extends AbstractController
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

        $img = [];

        // 实例化附件表
        $attach_serv = new AttachmentService();

        $res = $attach_serv->list_by_conds([
            'cid' => $id
        ], null);

        if (!empty($res)) {
            foreach ($res as $k => $v) {
                $img[$k]['atId'] = $v['atid'];
                $img[$k]['imgUrl'] = imgUrl($v['atid']);
            }
        }


        // 获取收藏状态 xtong 2017年06月02日
        $url = convertUrl(QY_DOMAIN . '/Public/Rpc/Collection/CollectionNew');

        $params = [
            'uid' => $this->uid,
            'app' => 'workmate',
            'dataId' => $id,
            'title' => $info['content'],
            'cover_type' => 0,
            'url' => 'Workmate/Frontend/Index/Msg?type=3&id=' . $id,
            'circle_uid' => $info['uid'],
            'circle_face' => User::instance()->avatar($info['uid']),
            'circle_name' => $info['username'],
            'circle_img' => $img,
        ];

        $res = \Com\Rpc::phprpc($url)->invoke('Index', $params);

        Logger::write('收藏添加：' . var_export($res, true));

        if (!$res) {
            $this->_set_error('_ERR_ALREADY_COLLECTION');

            return false;
        }

        // 返回成功
        $this->_result = [];

        return true;
    }

}

