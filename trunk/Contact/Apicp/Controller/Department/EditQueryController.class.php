<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/9/30
 * Time: 15:06
 */

namespace Apicp\Controller\Department;

use Common\Service\DeptRightService;

class EditQueryController extends AbstractController
{

    /**
     * 【通讯录】编辑查询部门详情
     * @author liyifei
     */
    public function Index_post()
    {

        $dpId = I('post.department_id', '', 'trim');
        if (empty($dpId)) {
            E('_ERR_DPID_IS_NULL');
        }

        $rightServ = new DeptRightService();
        $result = $rightServ->DeptEditQuery($dpId);

        $this->_result = $result;
    }
}
