<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/6/8
 * Time: 11:53
 */

namespace Apicp\Controller\Course;

use Com\PackageValidate;
use Common\Common\Constant;

class CompetenceController extends \Apicp\Controller\AbstractController
{

    /**
     * Competence
     * @author liyifei
     * @desc 能力模型列表
     * @param Int page:1 当前页
     * @param Int limit:20 每页数据总数
     * @return array 能力模型列表数据
                    array(
                        'page' => 1, // 当前页
                        'limit' => 20, // 当前页条数
                        'total' => 100, // 总条数
                        'list' => array( // 列表数据
                            array(
                                "cm_id": "1", // 模型ID
                                "cm_name": "能力A", // 能力模型名称
                                "cm_level": "1", // 能力等级
                                "cm_desc": "对能力A的描述", // 能力模型描述
                                "cm_displayorder": "1", // 排序号
                            ),
                        ),
                    ),
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'page' => 'integer',
            'limit' => 'integer',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;

        // RPC取能力模型信息
        $url = convertUrl(QY_DOMAIN . '/Contact/Rpc/Competence/List');
        $data = [
            'page' => isset($postData['page']) ? $postData['page'] : Constant::PAGING_DEFAULT_PAGE,
            'limit' => isset($postData['limit']) ? $postData['limit'] : Constant::PAGING_DEFAULT_LIMIT,
        ];

        $list = \Com\Rpc::phprpc($url)->invoke('index', $data);
        if (!is_array($list)) {
            // RPC返回错误时，提示报错
            E('_ERR_COMPETENCE_DATA_NOT_FOUND');
        }

        $this->_result = $list;
    }
}
