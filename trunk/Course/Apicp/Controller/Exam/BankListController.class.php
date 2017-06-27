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

class BankListController extends \Apicp\Controller\AbstractController
{
    /**
     * BankList
     * @author tangxingguo
     * @desc 获取题库列表
     * @param String eb_name 题库名称关键字
     * @return array 题库列表
                    array(
                        'total' => 22, // 数据总数
                        'list' => array( // 题库列表
                            array(
                                'eb_id' => 13, // 题库ID
                                'eb_name' => '100分进发', // 题库名称
                                'single_count' => 0, // 单选题数量
                                'multiple_count' => 0, // 多选题数量
                                'judgment_count' => 0, // 判断题数量
                                'question_count' => 0, // 问答题数量
                                'voice_count' => 0, // 语音题数量
                                'total_count' => 0, // 总题数
                                'created' => 0, // 题库创建时间
                            )
                        ),
                    );
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'eb_name' => 'max:1024',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;

        // 参数
        $param_arr = [];
        if (isset($postData['eb_name']) && !empty($postData['eb_name'])) {
            $param_arr['eb_name'] = $postData['eb_name'];
        }

        // RPC请求
        $url = convertUrl(QY_DOMAIN . '/Exam/Rpc/Breakthrough/BankList');
        $res = Rpc::phprpc($url)->invoke('Index', $param_arr);

        $this->_result = json_decode($res, true);
    }
}
