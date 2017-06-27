<?php
/**
 * Created by PhpStorm.
 * User: zhonglei
 * Date: 2017/6/5
 * Time: 14:33
 */
namespace Rpc\Controller\Exam;

use Think\Log;
use Com\PackageValidate;
use Common\Service\StudyService;
use Common\Service\ExamService;

class UpdateController extends \Rpc\Controller\AbstractController
{
    /**
     * Update
     * @author zhonglei
     * @desc 更新课程测评结果RPC接口
     * @param String uid:true 用户ID
     * @param Int article_id:true 课程ID
     * @param Int is_pass:true 测评是否通过（1=未通过；2=已通过）
     * @return bool
     */
    public function Index()
    {
        $post_data = $this->_params;

        // 验证规则
        $rules = [
            'uid' => 'require',
            'article_id' => 'require|integer',
            'is_pass' => 'require|in:1,2',
        ];

        // 验证请求数据
        $validate = new PackageValidate();
        $validate->postData = $post_data;
        $validate->validateParams($rules);

        extract($post_data, EXTR_SKIP);
        $conds = ['article_id' => $article_id, 'uid' => $uid];

        $studyServ = new StudyService();
        $study = $studyServ->get_by_conds($conds);

        // 未找到学习数据
        if (empty($study)) {
            Log::record("not found study, article_id: {$article_id}, uid: {$uid}", Log::INFO);
            return false;
        }

        $examServ = new ExamService();
        $exam = $examServ->get_by_conds($conds);

        // 新增
        if (empty($exam)) {
            $examServ->insert([
                'article_id' => $article_id,
                'uid' => $uid,
                'username' => $study['username'],
                'is_pass' => $post_data['is_pass'],
            ]);

        // 更新
        } elseif ($exam['is_pass'] != $post_data['is_pass']) {
            $examServ->update($exam['exam_id'], ['is_pass' => $post_data['is_pass']]);
        }

        return true;
    }
}
