<?php
/**
 * Created by IntelliJ IDEA.
 * User: zhuxun37
 * Date: 2017/5/11
 * Time: 下午3:13
 */

namespace Apicp\Controller\Job;


use Common\Service\JobService;

class ListController extends AbstractController
{

    public function Index_post()
    {

        $jobService = new JobService();
        $jobService->searchList($this->_result, I('post.'));

        return true;
    }

}