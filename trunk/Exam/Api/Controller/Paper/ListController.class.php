<?php
/**
 * 【考试中心-手机端】获取考试列表接口
 *  ListController.class.php
 * @author: 蔡建华
 * @date :  2017-05-23
 * @version $Id$
 */

namespace Api\Controller\Paper;

use Common\Service\PaperService;
use Common\Service\RightService;

class ListController extends AbstractController
{
    /***
     * @var PaperService 试题yService对象
     */
    protected $paper_serv;
    /**
     * @var RightService
     */
    protected $right_serv;

    public function before_action($action = '')
    {
        if (!parent::before_action($action)) {
            return false;
        }
        // 实例化试题Service
        $this->paper_serv = new PaperService();
        // 实例化权限Service
        $this->right_serv = new RightService();

        return true;
    }

    /**
     *  获取考试列表入口
     * @cate_id    是    Int    考试分类ID
     * @page    否    Int   页面
     * @limit    否    Int   条数
     * @return bool
     */
    public function Index_post()
    {
        /*
         * 首先查询当前用户的部门，标签，用户ID，职位相关信息，作为查询条件之一
         * 根据试题权限获取试题列表，按照最后更新时间排序，草件试题排除
         */
        $params = I('post.');
        // 获取当前用户部门，标签，职位
        $right = $this->right_serv->get_by_right($this->_login->user);
        // 获取返回数据
        // 默认值
        $page = !empty($params['page']) ? intval($params['page']) : self::DEFAULT_PAGE;
        $limit = !empty($params['limit']) ? intval($params['limit']) : self::DEFAULT_LIMIT;
        $ec_id = intval($params['ec_id']);
        // 分页
        list($start, $limit) = page_limit($page, $limit);

        //  按照发布时间排序
        $order_option = array('update_time' => 'DESC');
        $data = array();
        $data['right'] = $right;
        $data['ec_id'] = $ec_id;
        // 获取记录总数
        $total = $this->paper_serv->count_by_paper($data);

        // 获取列表数据
        $list = array();
        if ($total > 0) {
            $list = $this->paper_serv->list_by_paper($data, array($start, $limit), $order_option,
                '*,CASE WHEN updated>0 THEN updated else publish_time  END  as update_time');
        } else {
            E('_ERR_DATA_NOT_EXIST');
            return false;
        }
        // 组装返回数据
        $this->_result['total'] = intval($total);
        $this->_result['limit'] = intval($limit);
        $this->_result['page'] = intval($page);
        // 试题数据格式
        $rel = $this->paper_serv->paper_param($list, $this->uid);
        if (!$rel) {
            return false;
        }
        $this->_result['list'] = $rel;
        return true;
    }
}
