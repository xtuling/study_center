<?php
/**
 * 获取题库列表
 * @author: houyingcai
 * @email:  594609175@qq.com
 * @date :  2017-05-23 16:13:57
 * @version $Id$
 */

namespace Rpc\Controller\Breakthrough;

use Common\Service\BankService;

class BankListController extends AbstractController
{

    public function Index()
    {
        // 初始化
        $service = new BankService();

        // 获取题库列表
        if (!$service->get_bank_rpc_list($result, $this->_params)) {

            E('_ERR_BANK_LIST_FAILED');

            return false;
        }

        return json_encode($result);

    }

}
