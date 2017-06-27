<?php
/**
 * 获取试卷管理列表
 * @author: houyingcai
 * @email:  594609175@qq.com
 * @date :  2017-05-23 16:28:26
 * @version $Id$
 */

namespace Apicp\Controller\Paper;

use Common\Service\CategoryService;
use Common\Service\PaperService;

class ListController extends AbstractController
{
    /**
     * @var  PaperService  实例化试卷表对象
     */
    protected $paper_serv;

    /**
     * @var  CategoryService 实例化试卷分类表对象
     */
    protected $cate_serv;

    public function before_action($action = '')
    {
        if (!parent::before_action($action)) {
            return false;
        }

        $this->paper_serv = new PaperService();
        $this->cate_serv = new CategoryService();

        return true;
    }

    public function Index_post()
    {
        $params = I('post.');

        // 默认值
        $page = !empty($params['page']) ? intval($params['page']) : 1;
        $limit = !empty($params['limit']) ? intval($params['limit']) : PaperService::DEFAULT_LIMIT_ADMIN;

        list($start, $limit) = page_limit($page, $limit);

        $page_option = array($start, $limit);

        $order_option = array('updated' => 'desc', 'created' => 'desc');

        // 列表总数
        $total = $this->paper_serv->count_search_where($params);
        if ($total > 0) {

            $fields = 'ep_id,ec_id,paper_type,ep_name,ep_type,begin_time,end_time,paper_time,pass_score,exam_status,cate_status,created,updated';
            $paper_list = $this->paper_serv->list_search_where($params, $page_option, $order_option, $fields);
        }

        // 组装返回数据
        $this->format_paper_list($paper_list);

        $this->_result = array(
            'total' => (int)$total,
            'limit' => (int)$limit,
            'page' => (int)$page,
            'list' => !empty($paper_list) ? $paper_list : array(),
        );

        return true;

    }

    /**
     * 组装返回数据
     * @param array $list 试卷列表
     * @return bool
     */
    protected function format_paper_list(&$list)
    {
        foreach ($list as &$val) {

            switch ($val['exam_status']) {

                // 草稿
                case PaperService::PAPER_DRAFT:
                    $val['exam_status'] = PaperService::STATUS_DRAFT;
                    break;

                // 已发布
                case PaperService::PAPER_PUBLISH:
                    // 【未开始】
                    if ($val['begin_time'] > MILLI_TIME) {

                        $val['exam_status'] = PaperService::STATUS_NOT_START;
                    }
                    // 【已开始】
                    if (
                        $val['begin_time'] < MILLI_TIME &&
                        ($val['end_time'] >= MILLI_TIME || $val['end_time'] == 0)
                    ) {

                        $val['exam_status'] = PaperService::STATUS_ING;
                    }
                    // 【已结束】
                    if ($val['end_time'] > 0 && $val['end_time'] < MILLI_TIME) {

                        $val['exam_status'] = PaperService::STATUS_END;
                    }
                    break;

                // 已终止
                case PaperService::PAPER_STOP:
                    $val['exam_status'] = PaperService::STATUS_STOP;
                    break;

                default:
            }

            // 最后更新时间
            $val['updated_time'] = $val['updated'] ? $val['updated'] : $val['created'];
            unset($val['updated'], $val['created']);

            // 获取分类
            if ($val['ec_id']) {

                $category = $this->cate_serv->get($val['ec_id']);
                $val['ec_name'] = $category['ec_name'];
                unset($val['ec_id']);
            }

            $val['ep_id'] = (int)$val['ep_id'];
            $val['ep_type'] = (int)$val['ep_type'];
            $val['paper_type'] = (int)$val['paper_type'];
            $val['pass_score'] = (int)$val['pass_score'];
            $val['paper_time'] = (int)$val['paper_time'];
            $val['exam_status'] = (int)$val['exam_status'];
            $val['cate_status'] = (int)$val['cate_status'];
        }

        return true;
    }

}
