<?php
/**
 * Created by IntelliJ IDEA.
 * User: zhuxun37
 * Date: 2017/5/11
 * Time: 下午3:15
 */

namespace Apicp\Controller\Competence;


use Common\Service\CompetenceService;

class EditController extends AbstractController
{

    public function Index_post()
    {

        $jobService = new CompetenceService();
        $jobService->edit($this->_result, I('post.'));

        return true;
    }

}