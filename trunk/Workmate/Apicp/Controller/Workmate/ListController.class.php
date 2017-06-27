<?php
/**
 * ListController.class.php
 * 同事圈列表
 * User: 代军
 * Date: 2017-04-24
 */

namespace Apicp\Controller\Workmate;

use Common\Common\User;
use Common\Service\CircleService;

class ListController extends AbstractController
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

        // 如果状态值为空或者状态值不符合规范
        if ((empty($params['audit_state']) && !is_numeric($params['audit_state']))
            || (intval($params['audit_state']) > CircleService::CIRCLE)
        ) {

            $this->_set_error('_ERR_AUDIT_STATE');

            return false;
        }

        // 每页条数
        $limit = empty($params['limit']) ? self::DEFAULT_LIMIT : intval($params['limit']);
        $page = empty($params['page']) ? 1 : $params['page'];

        list($start, $limit, $page) = page_limit($page, $limit);
        // 分页参数
        $page_option = array($start, $limit);

        // 排序参数
        $order_option = array('a_time' => 'DESC');

        // 组装查询条件
        $where = $this->_circle_serv->get_where_conds($params);

        // 查询总数
        $total = $this->_circle_serv->count_by_conds($where);

        // 获取列表
        $list = array();
        if ($total > 0) {
            $feild =" *,case when audit_time>0   then audit_time  else created  end as a_time";
            $list = $this->_circle_serv->list_by_conds($where, $page_option, $order_option,$feild);
            // 格式化列表数据
            $this->_circle_serv->format_list_data($list);
        }

        // 返回数据
        $this->_result = array(
            'page' => $page,
            'limit' => $limit,
            'total' => intval($total),
            'list' => $list,
        );

        return true;
    }

}

