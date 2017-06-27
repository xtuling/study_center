<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/6/15
 * Time: 11:34
 */
namespace Frontend\Controller\Callback;

use Think\Log;
use Common\Common\Constant;
use Common\Common\ResAuth;
use Common\Service\FileService;
use Common\Service\RightService;

class CheckRightController extends AbstractController
{
    public function Index()
    {
        Log::record(sprintf('---%s %s CheckRight Start ---', QY_DOMAIN, APP_DIR), Log::INFO);

        // 鉴权失败：缺少必传参数（权限载体ID）
        $auth = I('post.auth');
        $id = I('post._id', 0, 'intval');

        if ($id == 0 || empty($auth)) {
            Log::record(sprintf('---%s %s CheckRight FAIL : uc param missing---', QY_DOMAIN, APP_DIR), Log::INFO);
            Log::record('post: ' . var_export($_POST, true), Log::INFO);
            exit('FAIL');
        }

        // 用户信息（管理员 or 普通用户）
        $resAuth = &ResAuth::instance();
        $data = $resAuth->parseSecret($auth);
        if (empty($data)) {
            Log::record(sprintf('---%s %s CheckRight Fail : parseSecret empty---', QY_DOMAIN, APP_DIR), Log::INFO);
            exit('FAIL');
        }

        switch ($data['user_type']) {
            // 管理员
            case ResAuth::USER_TYPE_ADMIN:
                Log::record(sprintf('---%s %s CheckRight OK : admin logined---', QY_DOMAIN, APP_DIR), Log::INFO);
                exit('OK');

            // 手机端登录用户
            case ResAuth::USER_TYPE_MOBILE:
                // 文件信息
                $fileServ = new FileService();
                $file = $fileServ->get_by_conds([
                    'file_id' => $id,
                    'file_type' => Constant::FILE_TYPE_IS_DOC,
                    'is_show' => Constant::FILE_STATUS_IS_SHOW,
                ]);
                // 鉴权失败：文章不存在
                if (empty($file)) {
                    Log::record(sprintf('---%s %s CheckRight FAIL : file not found---', QY_DOMAIN, APP_DIR), Log::INFO);
                    exit('FAIL');
                }

                // 手机端登录人员查看
                $rightServ = new RightService();
                $checkRes = $rightServ->checkReadRight($data['user'], $file['parent_id']);
                if (!$checkRes) {
                    Log::record(sprintf('---%s %s CheckRight FAIL : have not right---', QY_DOMAIN, APP_DIR), Log::INFO);
                    exit('FAIL');
                }
                break;
        }

        // 鉴权通过
        Log::record(sprintf('---%s %s CheckRight END---', QY_DOMAIN, APP_DIR), Log::INFO);
        exit('OK');
    }
}
