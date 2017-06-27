<?php
/**
 * 删除题库
 * @author: houyingcai
 * @email:  594609175@qq.com
 * @date :  2017-05-23 16:17:38
 * @version $Id$
 */

namespace Apicp\Controller\Bank;

class DeleteController extends AbstractController
{

    public function Index_post()
    {
        // 删除题库
        if (!$this->bank_serv->delete_bank(I('post.'))) {

            E('_ERR_DELETE_BANK_FAILED');

            return false;
        }

        return true;
    }

}
