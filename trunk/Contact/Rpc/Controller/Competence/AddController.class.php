<?php
/**
 * Created by IntelliJ IDEA.
 * User: zhuxun37
 * Date: 2017/5/11
 * Time: 下午3:14
 */

namespace Rpc\Controller\Competence;


use Common\Service\CompetenceService;

class AddController extends AbstractController
{

    /**
     * 新增能力模型 RPC 接口
     *
     * @uses 调用方法：\Com\Rpc::phprpc(rpc 接口 url)->invoke(接口方法名, 需要传入的参数数组key-value);
     */
    public function Index()
    {

        $result = array();
        $jobService = new CompetenceService();
        $jobService->addCompetence($result, $this->get_arguments());

        return $result;
    }

}