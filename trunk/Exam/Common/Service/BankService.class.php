<?php
/**
 * 考试-题库表
 * @author: houyingcai
 * @email:  594609175@qq.com
 * @date :  2017-05-19 17:43:157
 * @version $Id$
 */

namespace Common\Service;

use Common\Model\BankModel;
use Common\Model\TopicModel;
use Think\Exception;

class BankService extends AbstractService
{
    // 默认页数
    const DEFAULT_PAGE = 1;

    // 构造方法
    public function __construct()
    {
        $this->_d = new BankModel();

        parent::__construct();
    }

    /**
     * 新增题库(后台)
     * @author: 候英才
     * @param array $result 返回活动信息
     * @param array $reqData 请求数据
     * @return bool
     */
    public function add_bank(&$result, $reqData)
    {
        $eb_name = raddslashes($reqData['eb_name']);

        // 题库名称不能为空
        if (empty($eb_name)) {

            E('_EMPTY_BANK_NAME');

            return false;
        }
        // 题库名称过长
        if ($this->utf8_strlen($eb_name) > 15) {

            E('_ERR_BANK_NAME_LENGTH');

            return false;
        }
        // 题库名称已存在
        if ($this->_d->count_by_conds(array('eb_name' => $eb_name))) {

            E('_ERR_BANK_NAME_REPEAT');

            return false;
        }

        // 插入数据库
        $eb_id = $this->_d->insert(array('eb_name' => $eb_name));

        if (!$eb_id) {

            E('_ERR_ADD_BANK_FAILED');

            return false;
        }

        $result = array('eb_id' => intval($eb_id));

        return true;
    }

    /**
     * 删除题库信息（后台）
     * @author: 候英才
     * @param array $reqData 请求数据
     * @return bool
     */
    public function delete_bank($reqData)
    {
        $eb_ids = array_column($reqData['eb_ids'], 'eb_id');

        if (empty($eb_ids) || !is_array($eb_ids)) {

            E('_EMPTY_EB_ID');

            return false;
        }

        $topic_model = new TopicModel();
        $conds = array('eb_id' => $eb_ids);

        try {
            // 删除开始
            $this->_d->start_trans();

            // 删除题库
            $this->_d->delete($eb_ids);
            // 删除题库下的题目
            $topic_model->delete_by_conds($conds);

            // 提交删除
            $this->_d->commit();
        } catch (Exception $e) {

            $this->_d->rollback();
            E('_ERR_DELETE_BANK_FAILED');

            return false;
        }

        return true;
    }

    /**
     * 修改题库名称(后台)
     * @author: 候英才
     * @param array $reqData 请求数据
     * @return bool
     */
    public function save_bank_name($reqData)
    {
        $eb_id = rintval($reqData['eb_id']);
        $eb_name = raddslashes($reqData['eb_name']);

        // 题库ID不能为空
        if (!$eb_id) {

            E('_EMPTY_EB_ID');

            return false;
        }
        // 题库名称不能为空
        if (empty($eb_name)) {

            E('_EMPTY_BANK_NAME');

            return false;
        }
        // 题库名称过长
        if ($this->utf8_strlen($eb_name) > 15) {

            E('_ERR_BANK_NAME_LENGTH');

            return false;
        }
        // 获取旧数据
        $old_bank = $this->_d->get($eb_id);

        // 题库不存在
        if (empty($old_bank)) {

            E('_ERR_BANK_NO_EXISTS');

            return false;
        }
        // 题库名称已存在
        if ($this->_d->count_by_conds(array('eb_name' => $eb_name))) {

            E('_ERR_BANK_NAME_REPEAT');

            return false;
        }
        // 修改数据库
        $this->_d->update($eb_id, array('eb_name' => $eb_name));

        return true;
    }

    /**
     * 获取题库列表（后台）
     * @author: 候英才
     * @param array $result 返回活动信息
     * @param array $params 请求数据
     * @return bool
     */
    public function get_bank_list(&$result, $params)
    {
        $conds = array();
        $eb_name = raddslashes($params['eb_name']);
        // 搜索题库关键词是否为空
        if (!empty($eb_name)) {

            $conds['eb_name like ?'] = '%' . $eb_name . '%';
        }
        // 默认值
        $page = !empty($params['page']) ? intval($params['page']) : self::DEFAULT_PAGE;
        $limit = !empty($params['limit']) ? intval($params['limit']) : self::DEFAULT_LIMIT_ADMIN;

        // 分页
        list($start, $limit) = page_limit($page, $limit);

        // 排序
        $order_option = array('created' => 'DESC');

        // 获取记录总数
        $total = $this->_d->count_by_conds($conds);
        // 获取列表数据
        $list = array();
        if ($total > 0) {
            if ($params['is_page'] == 2) {
                // 不分页
                $list = $this->_d->list_all(null, $order_option);
            } else {
                // 分页
                $list = $this->_d->list_by_conds($conds, array($start, $limit), $order_option);
            }
        }

        // 组装返回数据
        $result['total'] = intval($total);
        $result['limit'] = intval($limit);
        $result['page'] = intval($page);
        $result['list'] = $this->format_bank_list($list);

        return true;
    }

    /**
     * 获取题库列表（RPC）何岳龙
     * @param array $result 返回活动信息
     * @param array $params 请求数据
     * @return bool
     */
    public function get_bank_rpc_list(&$result, $params)
    {
        $conds = array();
        $eb_name = raddslashes($params['eb_name']);
        // 搜索题库关键词是否为空
        if (!empty($eb_name)) {

            $conds['eb_name like ?'] = '%' . $eb_name . '%';
        }

        // 排序
        $order_option = array('created' => 'DESC');

        // 获取记录总数
        $total = $this->_d->count_by_conds($conds);

        // 获取列表数据
        $list = array();
        if ($total > 0) {

            // 获取列表
            $list = $this->_d->list_by_conds($conds, null, $order_option);
        }

        // 组装返回数据
        $result['total'] = intval($total);
        $result['list'] = $this->format_bank_list($list);

        return true;
    }

    /**
     * 格式化题库列表数据
     * @param $data array 需要格式化的数据列表
     * @return array
     */
    protected function format_bank_list($data)
    {
        $list = array();

        if (!empty($data) && is_array($data)) {

            foreach ($data as $key => $val) {
                $list[$key]['eb_id'] = rintval($val['eb_id']);
                $list[$key]['eb_name'] = $val['eb_name'];
                $list[$key]['single_count'] = rintval($val['single_count']);
                $list[$key]['multiple_count'] = rintval($val['multiple_count']);
                $list[$key]['judgment_count'] = rintval($val['judgment_count']);
                $list[$key]['question_count'] = rintval($val['question_count']);
                $list[$key]['voice_count'] = rintval($val['voice_count']);
                $list[$key]['total_count'] = rintval($val['total_count']);
                $list[$key]['created'] = $val['created'];
            }

        }

        return $list;
    }

}
