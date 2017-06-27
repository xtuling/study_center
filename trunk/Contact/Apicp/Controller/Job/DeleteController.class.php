<?php
/**
 * Created by IntelliJ IDEA.
 * User: zhuxun37
 * Date: 2017/5/11
 * Time: 下午3:15
 */

namespace Apicp\Controller\Job;


use Common\Service\JobService;

class DeleteController extends AbstractController
{

    public function Index_post()
    {

        $jobService = new JobService();
        $jobService->delete($this->_result, I('post.'));

        $this->clearJobCache();

        return true;
    }

}