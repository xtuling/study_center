<?php
/**
 * 编辑题库名称
 * @author: houyingcai
 * @email:  594609175@qq.com
 * @date :  2017-05-23 16:15:33
 * @version $Id$
 */

namespace Apicp\Controller\Bank;

class SaveController extends AbstractController
{

    public function Index_post()
    {
        // 编辑题库名称
        if (!$this->bank_serv->save_bank_name(I('post.'))) {

            E('_ERR_SAVE_EB_NAME_FAILED');

            return false;
        }

        return true;
    }

}
