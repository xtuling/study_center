<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/11
 * Time: 18:36
 */
namespace Apicp\Controller\Answer;

use Com\PackageValidate;
use Common\Common\User;
use Common\Service\ClassService;
use Common\Service\ImgService;
use Common\Service\QuestionService;

class QuestionInfoController extends \Apicp\Controller\AbstractController
{
    /**
     * QuestionInfo
     * @author
     * @desc 提问详情接口
     * @param Int question_id 提问ID
     * @return array 提问详情
                    array(
                        'question_id' => 1, // 提问ID
                        'question_title' => '测试', // 提问标题
                        'class_id' => 1, // 分类ID
                        'class_name' => '测试', // 分类名称
                        'integral' => 10, // 悬赏积分
                        'uid' => 10, // 用户UID
                        'username' => 10, // 用户姓名
                        'face' => 'http://qy.vchangyi.com', // 用户头像
                        'description' => 10, // 描述
                        'created' => 1234567890, // 提问时间
                        'check_status' => 1, // 审核状态（1=未审核；2=审核通过；3=审核未通过）
                        'is_solve' => 1, // 解决状态（1=未解决；2=已解决）
                        'imgs' => array(
                            array(
                                'at_id' => 'abcdefg', // 图片ID
                                'at_url' => 'http://qy.vchagyi.com', // 图片URL
                            ),
                        ),
                    );
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'question_id' => 'require|integer',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $questionId = $validate->postData['question_id'];

        // 取提问
        $questionServ = new QuestionService();
        $questionInfo = $questionServ->get($questionId);
        if (empty($questionInfo)) {
            // 提问不存在
            E('_ERR_ANSWER_QUESTION_NOT_FOUND');
        }

        // 分类名称
        $classServ = new ClassService();
        $classInfo = $classServ->get($questionInfo['class_id']);
        $questionInfo['class_name'] = empty($classInfo) ? '' : $classInfo['class_name'];

        // 用户头像
        $userServ = &User::instance();
        $questionInfo['face'] = $userServ->avatar($questionInfo['uid']);

        // 提问图片
        $imgServ = new ImgService();
        $questionInfo['imgs'] = $imgServ->list_by_conds(['question_id' => $questionInfo['question_id']]);

        $this->_result = $questionInfo;
    }
}
