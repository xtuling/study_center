<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/4/20
 * Time: 18:33
 */
namespace Apicp\Controller\News;

use VcySDK\Service;
use VcySDK\Cron;
use VcySDK\FileConvert;
use Common\Common\Attach;

class TestController extends \Apicp\Controller\AbstractController
{
    public function Index_post()
    {
        // 获取音频、文件附件详情
        $at_id = '8F2646C57F00000118934A49780F9DDB';
        $attachServ = &Attach::instance();
//        $res = $attachServ->getAttachDetail($at_id);

        // 获取视频附件转码后url
        $video_id = '9031868222912310513';
        $convertServ = new FileConvert(Service::instance());
//        $res = $convertServ->getVodPlayUrl($video_id);
//        $res = $this->getVideoUrl($video_id);

        // 获取、删除计划任务
        $cron_id = '8F270BF17F0000015EA8CEBC321305D7';
        $cronSdk = new Cron(Service::instance());
        $res = $cronSdk->get($cron_id);

        $this->_result = $res;
    }

    /**
     * 通过视频的fileId获得视频的url，如果url为空，说明没有转码成功
     * @param $fileId string 视频的id
     * @return array|bool
     */
    public function getVideoUrl($fileId)
    {
        $url = '%s/qcloud/getVodPlayUrl?fileId='.$fileId;
        $serv = Service::instance();
        $result = $serv->postSDK($url, [], 'generateApiUrls');
        return empty($result['url']) ? '' : $result['url'];
    }
}
