<?php
/**
 * Created by IntelliJ IDEA.
 * User: zhuxun37
 * Date: 2017/5/11
 * Time: 下午3:13
 */

namespace Apicp\Controller\Competence;


use Common\Service\CompetenceService;

class ListController extends AbstractController
{

    public function Index_post()
    {

        $jobService = new CompetenceService();
        $jobService->listCompetence($this->_result, I('post.'));

        return true;
    }

}