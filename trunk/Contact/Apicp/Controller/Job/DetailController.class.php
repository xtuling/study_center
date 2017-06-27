<?php
/**
 * Created by IntelliJ IDEA.
 * User: zhuxun37
 * Date: 2017/5/11
 * Time: 下午3:16
 */

namespace Apicp\Controller\Job;


use Common\Service\JobService;

class DetailController extends AbstractController
{

    public function Index_post()
    {

        $jobService = new JobService();
        $jobService->detail($this->_result, I('post.'));

        return true;
    }

}