<?php
/**
 * 同步通讯录接口
 *
 */
namespace Apicp\Controller\Contact;


use VcySDK\Member;
use VcySDK\Service;

class SynchronizationController extends AbstractController
{
    public function Index()
    {
        $sdk=new Member(Service::instance());
        $sdk->sync();
        return $this->_result = array();
    }


    public function Test()
    {

        return $this->_result = array();
    }

}
