<?php
/**
 * 新增题库
 * @author: houyingcai
 * @email:  594609175@qq.com
 * @date :  2017-05-23 16:16:36
 * @version $Id$
 */

namespace Apicp\Controller\Bank;

class AddController extends AbstractController
{

    public function Index_post()
    {
        // 添加题库
        if (!$this->bank_serv->add_bank($this->_result, I('post.'))) {

            E('_ERR_ADD_BANK_FAILED');

            return false;
        }

        return true;

    }

}
