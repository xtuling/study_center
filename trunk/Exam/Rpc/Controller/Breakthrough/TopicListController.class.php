<?php
/**
 * TopicListController.class.php
 * 获取题库题目列表(闯关用)
 * User: 何岳龙
 * Date: 2017年06月01日
 */

namespace Rpc\Controller\Breakthrough;

use Common\Service\TopicService;

class TopicListController extends AbstractController
{

    // 默认分页大小
    const DEFAULT_LIMIT = 10;

    // 删除状态
    const DELETED = 3;

    // 已删除
    const DELETED_YES = 1;

    // 未删除
    const DELETED_NO = 0;

    // 闯关可以抽题的数组 1：单选，2：判断，4：多选
    const BREAK_TYPE_LIST = [1, 2, 4];

    public function Index()
    {
        // 获取题目ID
        $params = $this->_params;

        if (empty($params['et_ids'])) {
            E('_EMPTY_ETIDS');
        }

        // 拆分题库IDS
        $et_id = explode(',', $params['et_ids']);

        // 组装条件
        $conds = [
            'et_id' => $et_id,
            'et_type' => self::BREAK_TYPE_LIST
        ];

        // 初始化列表
        $list = [];

        // 实例化题目表
        $service = new TopicService();

        // 获取总数
        $total = $service->count_topic_contain_del($conds);

        if ($total) {

            // 获取列表
            $list = $service->list_topic_contain_del($conds);

            // 格式化
            $list = $this->format($list);
        }

        $result = [
            'total' => $total,
            'list' => $list
        ];

        return json_encode($result);

    }

    protected function format($list)
    {
        $list_new = array();
        if (!empty($list) && is_array($list)) {

            foreach ($list as $k => $v) {
                $list_new[$k]['et_id'] = rintval($v['et_id']);
                $list_new[$k]['title'] = $v['title'];
                $list_new[$k]['et_type'] = rintval($v['et_type']);
                $list_new[$k]['deleted'] = $v['status'] == self::DELETED ? self::DELETED_YES : self::DELETED_NO;
            }
        }

        return $list_new;
    }

}
