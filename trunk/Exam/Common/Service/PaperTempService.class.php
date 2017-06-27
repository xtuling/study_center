<?php
/**
 * 试卷题目对应关系表
 * @author: houyingcai
 * @email:    594609175@qq.com
 * @date :  2017-05-19 17:50:41
 * @version $Id$
 */

namespace Common\Service;

use Common\Model\BankModel;
use Common\Model\PaperTempModel;

class PaperTempService extends AbstractService
{

    // 构造方法
    public function __construct()
    {
        $this->_d = new PaperTempModel();
        $this->_d_bank = new BankModel();
        $this->_d_temp = new PaperTempModel();
        parent::__construct();
    }


    /**
     * 获取备选题列表
     * @author：daijun
     * @param array $params
     * @return array|bool
     */
    public function get_temp_list($params = array())
    {
        // 默认值
        $page = !empty($params['page']) ? intval($params['page']) : 1;
        $limit = !empty($params['limit']) ? intval($params['limit']) : self::DEFAULT_LIMIT_ADMIN;

        // 分页
        list($start, $limit) = page_limit($page, $limit);

        // 排序
        $order_option = array('a.order_num' => 'ASC');

        // 组装查询条件
        $where['a.ep_id'] = $params['ep_id'];

        if (!empty($params['title'])) {
            $where['b.title like ?'] = '%' . trim($params['title']) . '%';
        }

        if (!empty($params['et_type'])) {
            $where['b.et_type'] = intval($params['et_type']);
        }

        $eb_data = array();
        if (!empty($params['eb_name'])) {
            // 去题库表查询题库ID集合
            $eb_list = $this->_d_bank->list_by_conds(
                array(
                    'eb_name like ?' => '%' . trim($params['eb_name']) . '%'
                )
            );

            // 查询题库ID 初始化
            $where['b.eb_id'] = 0;

            // 如果符合条件的题库存在
            if (!empty($eb_list)) {
                $eb_ids = array_column($eb_list, 'eb_id');
                $where['b.eb_id'] = $eb_ids;
            }
        }

        // 查询所有的题库信息
        $eb_list = $this->_d_bank->list_all();
        $eb_data = array_combine_by_key($eb_list, 'eb_id');

        // 分页参数
        $page_option = array($start, $limit);

        // 联合查询总数
        $total = $this->_d->count_by_where($where);

        $topic_list = array();
        if ($total > 0) {
            // 关联查询需要的相关信息
            $topic_list = $this->_d->list_by_where($where, $page_option, $order_option,
                'a.et_id,b.title,b.eb_id,b.et_type,a.score');

        }

        // 循环格式化
        foreach ($topic_list as $k => $v) {
            $topic_list[$k]['et_id'] = intval($v['et_id']);
            $topic_list[$k]['title'] = $v['title'];
            $topic_list[$k]['eb_name'] = $eb_data[$v['eb_id']]['eb_name'];
            $topic_list[$k]['et_type'] = intval($v['et_type']);
            $topic_list[$k]['score'] = intval($v['score']);
        }

        // 组装返回数据
        return array(
            'total' => intval($total),
            'limit' => intval($limit),
            'page' => intval($page),
            'list' => $topic_list
        );
    }

}
