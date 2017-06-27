<?php
/**
 * MemberMedalService.class.php
 * 用户勋章 service
 * @reader zs_anything 2017-05-27
 */
namespace Common\Service;

use Common\Common\Attach;
use Common\Model\MedalModel;
use Common\Model\MemberMedalModel;

class MemberMedalService extends AbstractService
{

    // 构造方法
    public function __construct()
    {
        parent::__construct();
        $this->_d = new MemberMedalModel();
    }

    /**
     * 获取我的勋章
     * @param $loginUid
     * @return array|bool|mixed
     */
    public function getMyMedals($loginUid) {

        $medalModel = new MedalModel();
        $medalList = $medalModel->list_all();

        if(empty($medalList)) {
            return [];
        }

        $myDiffMedalTotal = $this->getMemberDiffMedalTotal($loginUid);

        $customIconIds = [];
        foreach ($medalList as &$medal) {

            // 勋章icon是自定义
            if(MedalModel::ICON_TYPE_USER_UPLOAD == $medal['icon_type']) {
                $customIconIds[] = $medal['icon'];
            }

            $medal['total'] = 0;
            if(isset($myDiffMedalTotal[$medal['im_id']])) {
                $medal['total'] = $myDiffMedalTotal[$medal['im_id']];
            }
        }

        $medalList = $this->formatCustomIconAddress($customIconIds, $medalList);

        return $medalList;
    }

    /**
     * 格式化用户自定义的勋章icon地址
     * @param $customIconIds
     * @param $medalList
     * @return mixed
     */
    private function formatCustomIconAddress($customIconIds, $medalList)
    {
        $attachUtil = new Attach();
        $attArr = $attachUtil->listAttachUrl($customIconIds);

        foreach ($medalList as &$medal) {
            if (isset($attArr[$medal['icon']])) {
                $medal['icon'] = $attArr[$medal['icon']]['atAttachment'];
            }
        }

        return $medalList;
    }

    /**
     * 获取用户不同勋章的获得总数
     * @param $loginUid
     * @return array
     */
    private function getMemberDiffMedalTotal($loginUid)
    {
        $myMedalsList = $this->_d->list_by_conds([
            'mem_uid' => $loginUid
        ]);

        $resArr = [];
        foreach ($myMedalsList as $item) {
            $resArr[$item['im_id']] = $item['im_total'];
        }

        return $resArr;
    }

    /**
     * 增加人员获得勋章数
     * @param $imId
     * @param $uid
     * @return mixed
     */
    public function addMedalTotal($imId, $uid)
    {
        return $this->_d->addMedalTotal($imId, $uid);
    }
}
