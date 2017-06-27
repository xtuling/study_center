<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/9/26
 * Time: 20:41
 */
namespace Common\Service;

use Common\Model\CardModel;
use Common\Model\AttrModel;

class CardService extends AbstractService
{

    /**
     * 个人设置名片属性:隐藏
     */
    const SET_CARD_HIDE = 0;
    /**
     * 个人设置名片属性:显示
     */
    const SET_CARD_SHOW = 1;

    // 构造方法
    public function __construct()
    {

        parent::__construct();
        $this->_d = new CardModel();
    }

    /**
     * 个人名片(手机端展示信息)
     * @author liyifei
     * @param string $uid 人员ID
     * @param array $userInfo 用户详情信息
     * @param int $isShow 是否显示（0=不显示；1=显示；2=所有显示和不显示的属性）
     * @return array|bool list 名片的里面的属性列表
     */
    public function getCardByUid($uid, $userInfo, $isShow)
    {
        // 个人名片设置隐藏的属性列表
        $card = [];
        $cardData = $this->get_by_conds(['uid' => $uid]);
        if (!empty($cardData) && !empty($cardData['fields'])) {
            $card = unserialize($cardData['fields']);
        }

        // 管理后台设置开启,且在前端显示的属性列表
        $attrs = $this->getAttrList(false);

        $list = [];
        foreach ($attrs as $attr) {
            // 根据属性类型不同,将属性值转为与前端约定好的格式
            $attrValue = $this->formatValueByType($attr['type'], $userInfo[$attr['field_name']]);

            // 以下属性,不在list中出现
            $noAttr = [
                'memUsername',
                'memGender',
                'memFace',
                'dpName',
                'memJob',
            ];

            // 属性值为空时(用户未填写),不显示
            if (in_array($attr['field_name'], $noAttr) || $attrValue === '') {
                continue;
            }

            // 属性类型为单选、下拉框单选时,将属性值由单选value转为单选name显示
            if ($attr['type'] == AttrModel::ATTR_TYPE_RADIO || $attr['type'] == AttrModel::ATTR_TYPE_DROPBOX) {
                foreach ($attr['option'] as $item) {
                    if ($item['value'] == $attrValue) {
                        $attrValue = $item['name'];
                    }
                }
            }

            // 根据是否显示条件进行筛选
            if ($isShow == self::SET_CARD_SHOW) {
                if (in_array($attr['field_name'], $card)) {
                    continue;
                }
            } elseif ($isShow == self::SET_CARD_HIDE) {
                if (!in_array($attr['field_name'], $card)) {
                    continue;
                }
            }

            $list[] = [
                'field_name' => $attr['field_name'],
                'attr_name' => $attr['attr_name'],
                'attr_value' => $attrValue,
                'option' => $attr['option'],
                'postion' => $attr['postion'],
                'order' => $attr['order'],
                'type' => $attr['type'],
                'is_show' => in_array($attr['field_name'], $card) ? self::SET_CARD_HIDE : self::SET_CARD_SHOW,
            ];
        }

        return $list;
    }
}
