<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/27
 * Time: 10:59
 */
namespace Apicp\Controller\Award;

use Com\PackageValidate;
use Common\Common\Constant;
use Common\Common\Integral;
use Common\Service\ArticleService;
use Common\Service\AwardService;
use Common\Service\RightService;

class SaveController extends \Apicp\Controller\AbstractController
{
    /**
     * Save
     * @author tangxingguo
     * @desc 保存激励
     * @param Int award_id 激励ID
     * @param String award_action:true 激励行为（最多20字符）
     * @param String description 描述（最多140字符）
     * @param Int award_type:true 激励类型（1=勋章；2=积分）
     * @param Int medal_id 勋章ID（激励类型为勋章时必填）
     * @param Int integral 积分值（激励类型为积分时必填）
     * @param Array right:true 发放对象
     * @param String right.is_all 是否全公司（1=否；2=是）
     * @param Array right.uids 人员ID
     * @param Array right.dp_ids 部门ID
     * @param Array right.tag_ids 标签ID
     * @param Array article_ids:true 课程ID数组
     * @param Int condition:true 勋章发送条件（必须学习课程数量）
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'award_id' => 'integer',
            'award_action' => 'require|max:20',
            'description' => 'max:140',
            'award_type' => 'require|in:1,2',
            'medal_id' => 'integer',
            'integral' => 'integer|max:6',
            'condition' => 'require|integer',
            'article_ids' => 'require|array',
            'right' => 'require|array',
        ];

        // 验证请求数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;

        // 数据检查
        list($awardData, $awardRight) = $this->_checkPostData($postData);

        // 激励设置保存
        $awardServ = new AwardService();
        if (!isset($postData['award_id'])) {
            // 新增
            $postData['award_id'] = $awardServ->insert($awardData);
        } else {
            // 编辑
            $awardServ->update($postData['award_id'], $awardData);
        }

        // 权限保存
        $rightServ = new RightService();
        $rightServ->saveData(['award_id' => $postData['award_id']], $awardRight);
    }

    /**
     * @desc 检查请求数据，返回激励数据与发放对象数据
     * @author tangxingguo
     * @param array $postData 接收到的请求数据
     *  @return array
     */
    private function _checkPostData($postData)
    {
        // 激励数据
        $awardData = [
            'award_action' => $postData['award_action'],
            'description' => isset($postData['description']) ? $postData['description'] : '',
        ];
        // 勋章发送条件不能大于所选课程数量
        if ($postData['condition'] > count($postData['article_ids'])) {
            E('_ERR_AWARD_SEND_COND_FAIL');
        }
        $awardData['condition'] = $postData['condition'];

        // 勋章检查
        if ($postData['award_type'] == Constant::AWARD_TYPE_IS_MEDAL) {
            // 激励类型：勋章
            if (!isset($postData['medal_id'])) {
                E('_ERR_AWARD_TYPE_NEED_MEDAL');
            }

            // 勋章有效性
            $IntegralServ = new Integral();
            $integralList = $IntegralServ->listMedal();
            $medalIds = array_column($integralList, 'im_id');
            if (!in_array($postData['medal_id'], $medalIds)) {
                E('_ERR_AWARD_MEDAL_NOT_FOUND');
            }
            $awardData['award_type'] = Constant::AWARD_TYPE_IS_MEDAL;
            $awardData['medal_id'] = $postData['medal_id'];
        } elseif ($postData['award_type'] == Constant::AWARD_TYPE_IS_INTEGRAL) {
            // 激励类型：积分
            if (!isset($postData['integral'])) {
                E('_ERR_AWARD_TYPE_NEED_INTEGRAL');
            }
            $awardData['award_type'] = Constant::AWARD_TYPE_IS_INTEGRAL;
            $awardData['integral'] = $postData['integral'];
        }

        // 课程有效性
        $articleServ = new ArticleService();
        $articleList = $articleServ->list_by_conds(['article_status' => Constant::ARTICLE_STATUS_SEND]);
        $totalArticleIds = array_column($articleList, 'article_id');
        if (!empty(array_diff($postData['article_ids'], $totalArticleIds)) || empty($postData['article_ids'])) {
            E('_ERR_ARTICLE_DATA_NOT_FOUND');
        }
        $awardData['article_ids'] = serialize($postData['article_ids']);

        // 权限
        $rightServ = new RightService();
        $right = $rightServ->formatPostData($postData['right']);
        if (empty($right)) {
            E('_ERR_CLASS_RIGHT_EMPTY');
        }

        return [$awardData, $postData['right']];
    }
}
