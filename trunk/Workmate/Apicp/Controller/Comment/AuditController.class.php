<?php
/**
 * AuditController.class.php
 * 同事圈评论审核
 * User: 代军
 * Date: 2017-04-24
 */

namespace Apicp\Controller\Comment;

use Common\Service\CircleService;

class AuditController extends AbstractController
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
        // 实例化信息同事圈帖子信息表
        $this->_circle_serv = new CircleService();

        return true;
    }

    public function Index_post()
    {
        // 接收参数
        $params = I('post.');

        // 参数验证
        if (empty($params['id'])) {
            $this->_set_error('_EMPTY_COMMENTID');

            return false;
        }
        // 参数验证
        if (!is_numeric($params['audit_state'])) {
            $this->_set_error('_EMPTY_AUDITSTATE');

            return false;
        }

        // 参数有效性验证
        $c_data = $this->_circle_serv->get($params['id']);
        if (empty($c_data)) {
            $this->_set_error('_ERR_COMMENTDATA_EXIST');

            return false;
        }

        // 判断数据原始审核状态
        if ($c_data['audit_state'] != self::AUDIT_ING) {
            $this->_set_error('_ERR_COMMENTSTATUS_EXIST');

            return false;
        }

        // 获取登录用户信息
        $user = $this->_login->user;

        // 组装更新数据
        $data = array(
            'audit_state' => $params['audit_state'],
            'audit_type' => self::AUDIT_ADMIN,
            'audit_uid' => $user['eaId'],
            'audit_uname' => $user['eaRealname'],
            'audit_time' => MILLI_TIME
        );

        // 执行数据更新
        if (!$this->_circle_serv->update($params['id'], $data)) {
            $this->_set_error('_ERR_DATA_SAVE');

            return false;
        }

        // 此处推送消息给评论发布人
        $this->_circle_serv->send_comment_msg_admin($c_data, $params, $user);

        return true;
    }

}

