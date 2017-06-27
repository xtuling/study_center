<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2016/12/21
 * Time: 13:32
 */
namespace Apicp\Controller\Contract;

use Common\Service\ContractService;

class ListController extends AbstractController
{

    /**
     * 【通讯录】合同信息列表
     * @author tangxingguo
     * @time 2016-12-21 13:34:06
     */
    public function Index_post()
    {
        // 接收参数
        $keyword = I('post.keyword', '', 'trim');
        $signing_type = I('post.signing_type', '', 'trim');
        $page = I('post.page', 1, 'intval');
        $limit = I('post.limit', 10, 'intval');
        $dpids = I('post.dp_ids');

        $ContractServ = new ContractService();

        // 取合同信息列表
        $contractList = $ContractServ->getContractList($keyword, $dpids, $signing_type, $page, $limit);
        $result = [
            'page' => $page,
            'limit' => $limit,
            'total' => $contractList['total'],
            'list' => $contractList['list'],
        ];

        $this->_result = $result;
    }
}
