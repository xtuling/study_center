<?php
/**
 * 后台人员列表接口
 *
 */
namespace Apicp\Controller\Contact;


class MemListController extends AbstractController
{
    public function Index()
    {


        return $this->_result = array();
    }

    public function Test()
    {

        // 返回值
        $result = $this->result();

        return $this->_result = $result;
    }


    public function result()
    {


        // 构造返回值
        $result = array(
            'total' => 2,
            'page' => 1,
            'limit' => 12,
            'list' => array(
                array(
                    'memUid' => "72A15BAE7F0000016C05A24F4D27CF2B",
                    'memWeixin' => "raftyyang",
                    'memUserid' => "2ed2ee9234efd43632275f22e65802d7",
                    'memMobile' => "18018609007",
                    'memEmail' => "30916407@qq.com",
                    'memActive' => 0,
                    'memUsername' => "杨娴",
                    'memNum' => 0,
                    'memAdmincp' => 0,
                    'memGroupid' => 0,
                    'memGender' => 2,
                    'memFace' => "",
                    'memDisplayorder' => 0,
                    'memSubscribeStatus' => 1,
                    'mfAddress' => "山西省绛县",
                    'mfIdcard' => "610124198911064514",
                    'mfTelephone' => "02198751241",
                    'mfQq' => "271461421",
                    'mfBirthday' => "1989-11-06",
                    'mfRemark' => "备注",
                    'mfExt1' => "",
                    'mfExt2' => "",
                    'mfExt3' => "",
                    'mfExt4' => "",
                    'mfExt5' => "",
                    'mfExt6' => "",
                    'mfExt7' => "",
                    'mfExt8' => "",

                ),
                array(
                    'memUid' => "72A15BAE7F0000016C05A24F4D27CF2A",
                    'memWeixin' => "raftyyang",
                    'memUserid' => "2ed2ee9234efd43632275f22e65802d7",
                    'memMobile' => "18018609007",
                    'memEmail' => "30916407@qq.com",
                    'memActive' => 0,
                    'memUsername' => "张三",
                    'memNum' => 0,
                    'memAdmincp' => 0,
                    'memGroupid' => 0,
                    'memGender' => 2,
                    'memFace' => "",
                    'memDisplayorder' => 0,
                    'memSubscribeStatus' => 1,
                    'mfAddress' => "山西省绛县",
                    'mfIdcard' => "610124198911064514",
                    'mfTelephone' => "02198751241",
                    'mfQq' => "271461421",
                    'mfBirthday' => "1989-11-06",
                    'mfRemark' => "备注",
                    'mfExt1' => "",
                    'mfExt2' => "",
                    'mfExt3' => "",
                    'mfExt4' => "",
                    'mfExt5' => "",
                    'mfExt6' => "",
                    'mfExt7' => "",
                    'mfExt8' => "",

                )
            )
        );

        return $result;
    }


}
