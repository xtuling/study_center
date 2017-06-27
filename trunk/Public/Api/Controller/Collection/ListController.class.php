<?php
/**
 * ListController.class.php
 * 收藏列表
 * @author Deepseath
 * @version $Id$
 */
namespace Api\Controller\Collection;

use Common\Model\CommonCollectionModel;
use Common\Service\CommonCollectionService;

class ListController extends AbstractController
{

    // 默认分页参数
    const DEFAULT_LIMIT = 15;

    public function before_action($action = '')
    {
        if (!parent::before_action($action)) {
            return false;
        }

        return true;
    }

    /**
     * 获取收藏列表
     *
     * @return array
     */
    public function Index()
    {

        $params = I('post.');

        // 每页条数
        $limit = empty($params['limit']) ? self::DEFAULT_LIMIT : intval($params['limit']);
        $page = empty($params['page']) ? 1 : $params['page'];

        list($start, $limit, $page) = page_limit($page, $limit);
        // 分页参数
        $page_option = array($start, $limit);

        // 排序参数
        $order_option = ['c_time'=>'DESC'];

        $collectionService = new CommonCollectionService();

        // 组装查询条件
        $where['uid'] = $this->uid;

        if (!empty($params['title'])) {
            $where['title like ?'] = "%" . $params['title'] . "%";
        }

        // 查询总数
        $total = $collectionService->count_by_conds($where);

        // 获取列表
        $list = $collectionService->list_by_conds($where, $page_option, $order_option);

        $list = $collectionService->formate_list($list);

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
