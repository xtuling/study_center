<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/4/27
 * Time: 14:51
 */
namespace Apicp\Controller\Source;

use Com\PackageValidate;
use Common\Common\Constant;
use Common\Service\SourceAttachService;
use Common\Service\SourceService;

class InfoController extends \Apicp\Controller\AbstractController
{
    /**
     * Info
     * @author tangxingguo
     * @desc 素材详情接口
     * @param Int source_id:true 素材主键ID
     * @return array 素材详情
                    array(
                        'source_id' => 'xxx', // 素材主键ID
                        'source_type' => 1, // 素材类型（1=图文素材；2=音图素材；3=视频素材；4=文件素材；5=外部素材）
                        'source_title' => '素材1', // 素材名称
                        'author' => '吆西', // 作者
                        'content' => 'aaa', // 内容描述
                        'link' => 'http://www.baidu.com/001.mp3', // 外部素材url(source_type=5时使用)
                        'audio_imgs' => array( // 音图内容(source_type=2时使用)
                            array(
                                'at_id' => '111', // 图片附件ID
                                'at_url' => 'http://www.vchangyi.com/001.jpg', // 图片附件地址
                                'audios' => array( // 音频附件
                                    array(
                                        'at_id' => '111', // 音频附件ID
                                        'at_name' => 'ccc', // 音频附件名称
                                        'at_time' => '123432', // 音频附件时长(毫秒)
                                        'at_url' => 'http://www.vchangyi.com/001.jpg', // 音频附件地址
                                    ),
                                )
                            ),
                        ),
                        'videos' => array( // 视频内容(source_type=3时使用)
                            array(
                                'at_id' => '8765432345', // 视频附件ID
                                'at_name' => 'aaa', // 视频附件名称
                                'at_time' => '12345432', // 视频附件时长(毫秒)
                                'at_size' => '123233', // 视频附件尺寸（单位字节）
                                'at_url' => 'http://www.vchangyi.com/001.jpg', // 视频附件地址
                                'at_convert_url' => 'http://www.vchangyi.com/001.mp3', // 视频附件转码后的Url(文件、视频附件转码成功后才有值)
                                'at_suffix' => '.xml', // 附件后缀
                             ),
                        ),
                        'files' => array( // 文件内容(source_type=4时使用,只有1个附件)
                            array(
                                'at_id' => '8765432345', // 文件附件ID
                                'at_name' => 'aaa', // 文件附件名称
                                'at_time' => '12345432', // 文件附件时长(毫秒)
                                'at_size' => '123233', // 文件附件尺寸（单位字节）
                                'at_url' => 'http://www.vchangyi.com/001.jpg', // 文件附件地址
                                'at_convert_url' => 'http://www.vchangyi.com/001.mp3', // 文件附件转码后的Url(文件、视频附件转码成功后才有值)
                                'at_suffix' => '.xml', // 附件后缀
                            )
                        ),
                        'attachs' => array( // 附件(source_type=1、2、3时使用,最多5个附件)
                            array(
                                'at_id' => '8765432345', // 附件ID
                                'at_name' => 'aaa', // 附件名称
                                'at_time' => '12345432', // 附件时长(毫秒)
                                'at_size' => '123233', // 附件尺寸（单位字节）
                                'at_url' => 'http://www.vchangyi.com/001.jpg', // 附件地址
                                'at_convert_url' => 'http://www.vchangyi.com/001.mp3', // 附件转码后的Url(文件、视频附件转码成功后才有值)
                                'at_suffix' => '.xml', // 附件后缀
                            ),
                        ),
                        'is_download' => 1, // 附件是否支持下载（1=不支持；2=支持）
                        'source_status' => 2, // 素材状态（1=转码中；2=正常）
                    );
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'source_id' => 'require|integer',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;
        $sourceId = $postData['source_id'];

        // 取素材
        $sourceServ = new SourceService();
        $sourceInfo = $sourceServ->get($sourceId);
        if (empty($sourceInfo)) {
            E('_ERR_SOURCE_DATA_NOT_FOUND');
        }
        if ($sourceInfo['source_type'] == Constant::SOURCE_TYPE_AUDIO_IMG && $sourceInfo['audio_imgs']) {
            // 音频素材存在音频文件，格式化
            $sourceInfo['audio_imgs'] = unserialize($sourceInfo['audio_imgs']);
        }

        // 取素材附件
        $attach = [
            'videos' => [],
            'files' => [],
            'attachs' => [],
        ];
        $attachServ = new SourceAttachService();
        $list = $attachServ->list_by_conds(['source_id' => $sourceId]);
        if ($list) {
            foreach ($list as $k => $v) {
                // 取尾缀
                $v['at_suffix'] = end(explode('.', $v['at_name']));
                switch ($v['at_type']) {
                    // 视频文件
                    case Constant::ATTACH_TYPE_VIDEO:
                        $attach['videos'][] = $v;
                        break;
                    // 文件附件
                    case Constant::ATTACH_TYPE_FILE:
                        if ($sourceInfo['source_type'] == Constant::SOURCE_TYPE_FILE) {
                            $attach['files'][] = $v;
                        } else {
                            $attach['attachs'][] = $v;
                        }
                        break;
                }
            }
        }

        // 合并数据
        $sourceInfo = array_merge($sourceInfo, $attach);
        $this->_result = $sourceInfo;
    }
}
