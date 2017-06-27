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

class InfoController extends \Apicp\Controller\AbstractController
{
    /**
     * Info
     * @author tangxingguo
     * @desc 激励详情
     * @param Int award_id:true 激励ID
     * @return Array 课程详情
                    array(
                        'award_id' => 1, // 激励ID
                        'award_action' => '第一个激励', // 激励行为
                        'description' => '第一个激励描述', // 描述
                        'award_type' => 1, // 激励类型（1=勋章；2=积分）
                        'medals' => array( // 勋章
                            'im_id' => 3, // 勋章ID
                            'icon' => 'http://qy.vchangyi.com/icon.jpg', // 勋章图标URL或者前端路径
                            'icon_type' => 1, // 勋章图标来源（1=用户上传；2=系统预设）
                            'name' => '勋章1', // 勋章名称
                            'desc' => '这是一个勋章', // 勋章描述
                        ),
                        'integral' => 3, // 积分
                        'right' => array( // 发放对象
                            'is_all' => 1, // 是否全公司(1=否，2=是)
                            'tag_list' => array(// 标签
                                array(
                                    'tag_id' => '3CDBB2867F0000012C7F8D28432943BB',// 标签ID
                                    'tag_name' => 'liyifei001',// 标签名
                                ),
                            ),
                            'dp_list' => array(// 部门
                                array(
                                    'dp_id' => 'B65085507F0000017D3965FCB20CA747',// 部门ID
                                    'dp_name' => '一飞冲天',// 部门名
                                ),
                            ),
                            'user_list' => array(// 人员
                                array(
                                    'uid' => 'B4B3BA5B7F00000173E870DA6ADFEA2A',// 人员UID
                                    'username' => '缘来素雅',// 人员姓名
                                    'face' => 'http://shp.qpic.cn/bizmp/gdZUibR6BHrmiar6pZ6pLfRyZSVaXJicn2CsvKRdI9gccXRfP2NrDvJ8A/'// 人员头像
                                ),
                            ),
                            'job_list' => array(// 职位
                                array(
                                    'job_id' => 'B65085507F0000017D3965FCB20CA747',// 职位ID
                                    'job_name' => '一飞冲天',// 职位名称
                                ),
                            ),
                            'role_list' => array(// 角色
                                array(
                                    'role_id' => 'B65085507F0000017D3965FCB20CA747',// 角色ID
                                    'role_name' => '好哈',// 角色名称
                                ),
                            ),
                        ),
                        'articles' => array( // 已选课程列表
                            'article_id' => 110, // 课程ID
                            'article_title' => '店长必学', // 课程名称
                        ),
                        'condition' => 12, // 勋章发送条件（必须学习课程数量）
                    );
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'award_id' => 'require|integer',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;
        $awardId = $postData['award_id'];

        $awardServ = new AwardService();
        $info = $awardServ->get($awardId);
        if (empty($info)) {
            E('激励不存在');
        }

        // 勋章
        if ($info['award_type'] == Constant::AWARD_TYPE_IS_MEDAL) {
            $IntegralServ = new Integral();
            $integralList = $IntegralServ->listMedal();
            if (!empty($integralList)) {
                $integralList = array_combine_by_key($integralList, 'im_id');
            }
            $info['medals'] = isset($integralList[$info['medal_id']]) ? $integralList[$info['medal_id']] : [];
        }

        // 权限
        $rightServ = new RightService();
        $info['right'] = $rightServ->getData(['award_id' => $info['award_id']]);

        // 课程
        $articleIds = unserialize($info['article_ids']);
        $articleServ = new ArticleService();
        $articleList = $articleServ->list_by_conds(['article_id' => $articleIds]);
        if (!empty($articleList)) {
            $articleList = array_combine_by_key($articleList, 'article_id');
        }
        foreach ($articleIds as $v) {
            $article = ['article_id' => $v];
            if (isset($articleList[$v])) {
                $article['article_title'] = $articleList[$v]['article_title'];
                $info['articles'][] = $article;
            }
        }

        $this->_result = $info;
    }
}
