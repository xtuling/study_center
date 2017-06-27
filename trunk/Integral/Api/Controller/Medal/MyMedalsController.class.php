<?php

/**
 * Created by PhpStorm.
 * 我的勋章
 * User: zsanything
 * Date: 17/5/24
 * Time: 下午2:19
 */
namespace Api\Controller\Medal;

use Api\Controller\Integral\AbstractController;
use Common\Service\MemberMedalService;

class MyMedalsController extends AbstractController
{

    public function Index_post()
    {

        $memberMedalService = new MemberMedalService();
        $myMedals = $memberMedalService->getMyMedals($this->uid);

        $myMedals = array_diff_key_reserved($myMedals, ['domain','status', 'created', 'updated', 'deleted']);

        $this->_result = [
            'my_medals' => $myMedals
        ];

        return true;
    }

}
