<?php
/**
 * Created by PhpStorm.
 * User: zhonglei
 * Date: 2017/6/5
 * Time: 14:33
 */
namespace Common\Service;

use Com\Rpc;
use Common\Common\Constant;
use Common\Model\ExamModel;

class ExamService extends AbstractService
{
    // 构造方法
    public function __construct()
    {
        parent::__construct();
        $this->_d = new ExamModel();
    }

    /**
     * @desc 根据题目ID获取题目列表
     * @author tangxingguo
     * @param array $etIds 题目ID
     * @return array 题目列表
     */
    public function listById($etIds)
    {
        if (!is_array($etIds)) {
            return [];
        }
        $param_arr = [
            'et_ids' => implode(',', $etIds),
        ];

        $url = convertUrl(QY_DOMAIN . '/Exam/Rpc/Breakthrough/TopicList');
        $res = Rpc::phprpc($url)->invoke('Index', $param_arr);
        $res = json_decode($res, true);
        $list = isset($res['list']) ? $res['list'] : [];
        $etTypeList = Constant::EXAM_TYPE_LIST;
        foreach ($list as $k => $v) {
            $list[$k]['et_type'] = isset($etTypeList[$v['et_type']]) ? $etTypeList[$v['et_type']] : '';
        }

        return $list;
    }
}
