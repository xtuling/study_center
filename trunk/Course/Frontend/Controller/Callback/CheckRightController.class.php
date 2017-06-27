<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/6/23
 * Time: 15:14
 */
namespace Frontend\Controller\Callback;

use Think\Log;
use Common\Common\Constant;
use Common\Common\ResAuth;
use Common\Service\ArticleService;
use Common\Service\ClassService;
use Common\Service\RightService;

class CheckRightController extends AbstractController
{
    public function Index()
    {
        Log::record(sprintf('---%s %s CheckRight Start ---', QY_DOMAIN, APP_DIR), Log::INFO);

        // 鉴权失败：缺少必传参数（用户信息、权限载体ID）
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
                // 鉴权失败：文章不存在
                $articleServ = new ArticleService();
                $article = $articleServ->get($id);
                if (empty($article) || $article['article_status'] != Constant::ARTICLE_STATUS_SEND) {
                    Log::record(sprintf('---%s %s CheckRight FAIL : article not found or article status error---', QY_DOMAIN, APP_DIR), Log::INFO);
                    exit('FAIL');
                }

                // 鉴权失败：课程分类不存在或未开启
                $classServ = new ClassService();
                $class = $classServ->get($article['class_id']);
                if (empty($class) || $class['is_open'] == Constant::CLASS_IS_OPEN_FALSE) {
                    Log::record(sprintf('---%s %s CheckRight FAIL : class not found or already closed---', QY_DOMAIN, APP_DIR), Log::INFO);
                    exit('FAIL');
                }

                // 鉴权失败：手机端登录人员无查看权限
                $rightServ = new RightService();
                $checkRes = $rightServ->checkUserRight($data['user'], $id);
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
