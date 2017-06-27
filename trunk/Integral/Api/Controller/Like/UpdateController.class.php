<?php
/**
 * Created by PhpStorm.
 * User: gaoyaqiu
 * Date: 2017/5/26
 * Time: 20:56
 */
namespace Api\Controller\Like;

use Com\PackageValidate;
use VcySDK\Integral;
use VcySDK\Service;

class UpdateController extends AbstractController
{
    public function Index()
    {
        // 获取提交数据
        $this->getRequstParams();

        $memUid = $this->postData['mem_uid'];

        $sdk = new Integral(Service::instance());
        $this->_result = $sdk->updateLike([
            // 点赞人
            'createMemUid' => $this->_login->user['memUid'],
            // 被点赞人
            'memUid' => $memUid
        ]);

        return true;
    }

    /**
     * 获取提交数据
     * @return bool
     */
    protected function getRequstParams()
    {
        $validate = new PackageValidate(
            [],
            [],
            [
                'mem_uid'
            ]
        );

        $this->postData = $validate->postData;
    }
}