<?php
/**
 * Created by IntelliJ IDEA.
 * User: zhuxun37
 * Date: 2017/5/11
 * Time: 下午3:14
 */

namespace Apicp\Controller\Competence;


use Common\Service\CompetenceService;

class AddController extends AbstractController
{

    public function Index_post()
    {

        $jobService = new CompetenceService();
        $jobService->addCompetence($this->_result, I('post.'));

        return true;
    }

}