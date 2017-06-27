<?php
/**
 * 获取题库列表
 * @author: houyingcai
 * @email:  594609175@qq.com
 * @date :  2017-05-23 16:13:57
 * @version $Id$
 */

namespace Apicp\Controller\Bank;

class ListController extends AbstractController
{

    public function Index_post()
    {
        // 获取题库列表
        if (!$this->bank_serv->get_bank_list($this->_result, I('post.'))) {

            E('_ERR_BANK_LIST_FAILED');

            return false;
        }

        return true;
    }

}
