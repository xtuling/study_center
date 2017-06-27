<?php

/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/6/5
 * Time: 10:30
 */
namespace Apicp\Controller\Exam;

use Com\PackageValidate;
use Com\Rpc;
use Common\Common\Constant;

class TitleListController extends \Apicp\Controller\AbstractController
{
    /**
     * TitleList
     * @author tangxingguo
     * @desc 题库题目列表
     * @param Int page:1 当前页
     * @param Int limit:20 每页数据总数
     * @param String title 题目名称
     * @param Array type_list 题目类型(1:单选题 2:判断题 3:问答题 4:多选题 5:语音题)
     * @param Int attr_id 属性ID
     * @param Int eb_id 题库ID
     * @return array 题目列表
                    array(
                        'total' => 22, // 数据总数
                        'limit' => 20, // 每页条数
                        'page' => 1, // 当前页码
                        'list' => array( // 题库列表
                            array(
                                'et_id' => 13, // 题目ID
                                'title' => '送分题', // 题目名称
                                'et_type' => '单选题', // 题目类型
                                'score' => 3.00, // 题目分数
                                'order_num' => 3, // 题目序号(越小越靠前)
                                'use_num' => 31, // 使用次数
                            )
                        ),
                    );
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'page' => 'integer',
            'limit' => 'integer',
            'title' => 'max:1024',
            'type_list' => 'array',
            'attr_id' => 'integer',
            'eb_id' => 'integer',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;

        // 分页默认值
        $postData['page'] = isset($postData['page']) ? $postData['page'] : Constant::PAGING_DEFAULT_PAGE;
        $postData['limit'] = isset($postData['limit']) ? $postData['limit'] : Constant::PAGING_DEFAULT_LIMIT;

        // 参数（默认单选题、判断题、多选题）
        $param_arr = [
            'page' => $postData['page'],
            'limit' => $postData['limit'],
            'type_list' => isset($postData['type_list']) ? $postData['type_list'] : [['type' => 1], ['type' => 2], ['type' => 4]],
        ];
        if (isset($postData['title'])) {
            $param_arr['title'] = $postData['title'];
        }
        if (isset($postData['attr_id'])) {
            $param_arr['attr_id'] = $postData['attr_id'];
        }
        if (isset($postData['eb_id'])) {
            $param_arr['eb_id'] = $postData['eb_id'];
        }

        // RPC请求
        $url = convertUrl(QY_DOMAIN . '/Exam/Rpc/Breakthrough/TitleList');
        $res = Rpc::phprpc($url)->invoke('Index', $param_arr);
        $res = json_decode($res, true);
        if (isset($res['list']) && !empty($res['list'])) {
            // 转化题目类型
            $etTypeList = Constant::EXAM_TYPE_LIST;
            foreach ($res['list'] as $k => $v) {
                $res['list'][$k]['et_type'] = isset($etTypeList[$v['et_type']]) ? $etTypeList[$v['et_type']] : '';
            }
        }

        $this->_result = $res;
    }
}
