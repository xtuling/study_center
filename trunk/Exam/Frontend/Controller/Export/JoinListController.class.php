<?php
/**
 * 考试已参与|未参与导出
 * @author: houyingcai
 * @email:  594609175@qq.com
 * @date :  2017-06-01 09:41:09
 * @version $Id$
 */

namespace Frontend\Controller\Export;

use Common\Service\AnswerService;
use Common\Service\PaperService;
use Com\PythonExcel;

class JoinListController extends \Common\Controller\Frontend\AbstractController
{
    // 已参与
    const HAS_JOINED = 1;
    // 未参与
    const NOT_JOINED = 2;

    /**
     * @var  PaperService  实例化答卷表对象
     */
    protected $paper_serv;

    /**
     * @var  AnswerService  实例化答卷表对象
     */
    protected $answer_serv;

    protected $_require_login = false;

    public function before_action($action = '')
    {
        if (!parent::before_action($action)) {
            return false;
        }

        $this->paper_serv = new PaperService();
        $this->answer_serv = new AnswerService();

        return true;
    }

    public function Index()
    {
        $type = I('type', 0, 'intval');
        $ep_id = I('ep_id', 0, 'intval');

        // 试卷ID不能为空
        if (!$ep_id) {

            E('_EMPTY_EP_ID');

            return false;
        }

        // 导出类型不正确
        if (!in_array($type, array(self::HAS_JOINED, self::NOT_JOINED))) {

            E('_ERR_EXPORT_TYPE');

            return false;
        }

        // 获取试卷信息
        $paper = $this->paper_serv->get($ep_id);

        // 试卷不存在
        if (empty($paper)) {

            E('_ERR_PAPER_NOT_FOUND');

            return false;
        }

        // 初始化列表数据
        $lists = array();
        $userlist = array();

        // 【1】测评试卷已参与人员列表
        if (
            $type == self::HAS_JOINED &&
            $paper['paper_type'] == PaperService::EVALUATION_PAPER_TYPE
        ) {

            $conds = array('ep_id' => $ep_id);

            // 排序方式
            $order_by = array(
                'my_score' => 'DESC',
                'created' => 'ASC',
            );
            // 参与这场考试的人员的考试信息
            $lists = $this->answer_serv->list_by_conds($conds, null, $order_by);

            // 参与考试的所有人的UID
            $uids = array_column($lists, 'uid');
            // 参与考试的人员的详细信息列表
            if (!empty($uids)) {

                $userlist = $this->answer_serv->getUser($uids);
            }

            // 格式化返回字段信息
            foreach ($lists as $key => &$val) {

                $dpNames = array_column($userlist[$val['uid']]['dpName'], 'dpName');

                $val['ranking'] = intval($key + 1);
                $val['username'] = $userlist[$val['uid']]['memUsername'];
                $val['dpName'] = implode(',', $dpNames);
                $val['begin_time'] = $paper['begin_time'];
                $val['end_time'] = $paper['end_time'];

            }
        }

        // 【2】模拟试卷已参与人员列表
        if (
            $type == self::HAS_JOINED &&
            $paper['paper_type'] == PaperService::SIMULATION_PAPER_TYPE
        ) {

            $conds = array('ep_id' => $ep_id, 'my_time > ?' => 0);
            // 排序方式
            $order_by = array(
                'my_max_score' => 'DESC',
                'created' => 'ASC',
            );
            // 参与考试的人员的考试信息
            $lists = $this->answer_serv->get_mock_join_list(
                $conds,
                null,
                $order_by,
                'ea_id,uid,my_score,created'
            );

            // 参与考试的所有人的UID
            $uids = array_column($lists, 'uid');

            // 参与考试的人员的详细信息列表
            if (!empty($uids)) {

                $userlist = $this->answer_serv->getUser($uids);
            }

            // 格式化返回字段信息
            foreach ($lists as $key => &$val) {

                $val['ranking'] = intval($key + 1);
                $val['username'] = $userlist[$val['uid']]['memUsername'];

                // 获取用户部门信息
                $dpNames = array_column($userlist[$val['uid']]['dpName'], 'dpName');
                $val['dpName'] = implode(',', $dpNames);

                // 获取用户第一次参与模拟和最后一次参与模拟的时间
                $record = $this->answer_serv->get_by_conds(
                    array(
                        'uid' => $val['uid'],
                        'ep_id' => $ep_id,
                    ),
                    array(),
                    'min(my_begin_time) as begin_time, max(my_begin_time) as end_time'
                );

                $val['begin_time'] = $record['begin_time'];
                $val['end_time'] = $record['end_time'];
            }
        }

        // 【3】未参与考试人员列表
        if ($type == self::NOT_JOINED) {

            $conds = array(
                'epc_id' => $ep_id,
                'er_type' => AnswerService::RIGHT_PAPER,
            );

            // 获取未参与考试人员列表
            $unjoin_data = $this->answer_serv->get_unjoin_data($conds, $ep_id, $paper['is_all']);

            // 未参加人的列表
            $unjoin_user_list = $unjoin_data['unjoin_list'];

            // 根据用户ID查询用户信息
            if (!empty($unjoin_user_list)) {

                $userlist = $this->answer_serv->getUser($unjoin_user_list);
            }

            // 处理数据
            $lists = $this->nojoin_data($userlist, $paper);
        }

        // 执行导出
        $this->_download($lists, $paper['paper_type'], $type);

        return true;
    }

    /**
     * 处理未参加人员数据
     * @param array $members 所有未参加的人员
     * @param array $paper 考卷详情
     * @return array 未参加人员
     */
    private function nojoin_data($members, $paper)
    {
        $status = '';

        switch ($paper['exam_status']) {
            // 已发布
            case PaperService::PAPER_PUBLISH:
                // 【已开始】
                if (
                    $paper['begin_time'] < MILLI_TIME &&
                    ($paper['end_time'] >= MILLI_TIME || $paper['end_time'] == 0)
                ) {

                    $status = '已开始';
                }
                // 【已结束】
                if ($paper['end_time'] > 0 && $paper['end_time'] < MILLI_TIME) {

                    $status = '已结束';
                }
                break;

            // 已终止
            case PaperService::PAPER_STOP:
                $status = '已终止';
                break;

            default:
        }

        $data = array();

        foreach ($members as $key => $value) {

            $data[] = array(
                'uid' => $key,
                'username' => $value['memUsername'],
                'dpName' => implode(',', array_column($value['dpName'], 'dpName')),
                'created' => $paper['begin_time'],
                'exam_status' => $status,
            );
        }

        return $data;
    }

    /**
     * 导出模板
     * @param array $list 列表数据
     * @param int $paper_type 试卷类型
     * @param int $export_type 导出类型
     */
    private function _download($list = array(), $paper_type = 0, $export_type = 0)
    {
        // 初始化导出数据
        $file_name = '';
        $title = array();
        $row_data = array();

        // 【1】测评试卷（已参与）
        if (
            $export_type == self::HAS_JOINED &&
            $paper_type == PaperService::EVALUATION_PAPER_TYPE
        ) {

            $file_name = '测评试卷已参与考试人员统计' . date('_YmdHi');

            $title = array(
                '排名',
                '姓名',
                '部门',
                '考试时间',
                '用时',
                '分数',
                '状态',
            );

            foreach ($list as $k => $v) {

                $begin_time = rgmdate(strval($v['my_begin_time']), 'Y-m-d H:i');
                $end_time = rgmdate(strval($v['my_begin_time'] + $v['my_time']), 'Y-m-d H:i');

                $m = floor(intval($v['my_time'] / 1000) / 60) . '分';
                $s = intval($v['my_time'] / 1000) % 60;
                $s = $s ? $s . '秒' : '';

                $row_data[] = array(
                    $v['ranking'],
                    $v['username'],
                    $v['dpName'],
                    $begin_time . '-' . $end_time,
                    $m . $s,
                    $v['my_score'],
                    $v['my_is_pass'] ? '通过' : '未通过',
                );
            }
        }

        // 【2】模拟试卷（已参与）
        if (
            $export_type == self::HAS_JOINED &&
            $paper_type == PaperService::SIMULATION_PAPER_TYPE
        ) {

            $file_name = '模拟试卷已参与考试人员统计' . date('_YmdHi');

            $title = array(
                '姓名',
                '部门',
                '考试时间',
                '参与次数',
                '历史最高分',
                '排名',
            );

            foreach ($list as $k => $v) {
                $begin_time = !empty($v['begin_time']) ? rgmdate(strval($v['begin_time']), 'Y-m-d H:i') : '';
                $end_time = !empty($v['end_time']) ? rgmdate(strval($v['end_time']), 'Y-m-d H:i') : '';
                $row_data[] = array(
                    $v['username'],
                    $v['dpName'],
                    $begin_time . '-' . $end_time,
                    $v['join_count'],
                    $v['my_max_score'],
                    $v['ranking'],
                );
            }
        }

        // 【3】未参与考试
        if ($export_type == self::NOT_JOINED) {

            $file_name = '未参与考试人员统计' . date('_YmdHi');

            $title = array(
                '姓名',
                '部门',
                '创建时间',
                '状态',
            );

            foreach ($list as $k => $v) {
                $row_data[] = array(
                    $v['username'],
                    $v['dpName'],
                    rgmdate(strval($v['created']), 'Y-m-d H:i'),
                    $v['exam_status'],
                );
            }
        }

        // Python导出excel
        $realpath = APP_PATH . 'Data' . D_S . $file_name . ".xls";
        $ret = PythonExcel::instance()->write($realpath, $title, $row_data);
        if ($ret) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . $file_name . '.xls');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($realpath));
            readfile($realpath);
        }

        exit;
    }

}
