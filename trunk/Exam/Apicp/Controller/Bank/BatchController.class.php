<?php
/**
 * 导入题目至题库
 * BatchController.class.php
 * User: 何岳龙
 * Date: 2017-04-24
 */

namespace Apicp\Controller\Bank;

use Common\Service\TopicService;

class BatchController extends AbstractController
{
    /**
     * 初始化题目表
     * @var TopicService
     */
    protected $topic_serv;

    public function before_action($action = '')
    {
        if (!parent::before_action($action)) {
            return false;
        }

        $this->topic_serv = new TopicService();

        return true;
    }

    public function Index_post()
    {

        // 获取数据
        $params = I('post.');

        // 验证数据
        if (!$this->validation($params)) {

            return false;
        }

        // 重组数组
        $this->topic_serv->get_data($params['data']);

        // 初始化
        $i = 0;

        // 循环遍历数据
        foreach ($params['data'] as $key => $v) {

            // 如果大于头部标题长度则删除多余变量
            if ($i >= $params['head_total']) {

                unset($params['data'][$key]);
            }

            $i++;
        }

        // 错误原因
        $error = $this->topic_serv->is_parameter($params['data']);

        // 如果不存在错误
        if (empty($error)) {

            $this->topic_serv->insert_xls_data($params);
        }

        // 初始化
        $list = array();

        // 遍历数组
        foreach ($params['data'] as $key => $item) {

            $list[] = array(
                'key' => $key,
                'name' => $item
            );

        }

        $this->_result = array('list' => $list, 'result' => $error);

        return true;
    }

    /**
     * 验证数据
     * @param array $params POST数据
     * @return bool
     */
    protected function validation($params = array())
    {
        // 题库ID不能为空
        if (empty($params['eb_id'])) {

            $this->_set_error('_EMPTY_ED_ID');

            return false;
        }

        // 表头长度不能为空或者不是整数
        if (empty($params['head_total']) || !is_numeric($params['head_total'])) {

            $this->_set_error('_ERR_HEAD_TOTAL');

            return false;
        }

        return true;
    }
}
