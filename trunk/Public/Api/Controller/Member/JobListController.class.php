<?php
/**
 * Created by IntelliJ IDEA.
 * User: zhuxun37
 * Date: 2017/6/2
 * Time: 上午10:27
 */

namespace Api\Controller\Member;


use Common\Common\Job;

class JobListController extends AbstractController
{

    public function Index()
    {

        $this->_result = Job::instance()->listAll();
        return true;
    }

}