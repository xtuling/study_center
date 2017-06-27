<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/4/27
 * Time: 14:49
 */
namespace Apicp\Controller\Source;

use VcySDK\Service;
use VcySDK\FileConvert;
use Com\PackageValidate;
use Common\Common\Constant;
use Common\Service\SourceService;
use Common\Service\SourceAttachService;
use Common\Service\TaskService;

class SaveController extends \Apicp\Controller\AbstractController
{
    /**
     * Save
     *
     * @author zhonglei
     *
     * @desc 保存素材接口
     *
     * @param Int source_id:true 素材ID（新增时传0）
     *
     * @param String source_title:true 素材标题（最长64字符）
     *
     * @param Int source_type:true 素材类型（1=图文素材；2=音图素材；3=视频素材；4=文件素材；5=外部素材）
     *
     * @param String author 作者（最长50字符）
     *
     * @param String content 素材内容（source_type=1时必传）
     *
     * @param Array audio_imgs 音图数据（source_type=2时必传）
     *
     * @param Int audio_imgs[].at_id:true 图片附件ID
     *
     * @param String audio_imgs[].at_url:true 图片附件地址
     *
     * @param Array audio_imgs[].audios:true 音频附件
     *
     * @param String audio_imgs[].audios[].at_id:true 音频附件ID
     *
     * @param String audio_imgs[].audios[].at_name:true 音频附件名称
     *
     * @param Int audio_imgs[].audios[].at_time:true 音频播放时长（毫秒）
     *
     * @param String audio_imgs[].audios[].at_url:true 音频附件地址
     *
     * @param Array videos 视频数据（source_type=3时必传）
     *
     * @param Int videos[].source_attach_id:true 数据ID（新增时传0）
     *
     * @param String videos[].at_id:true 视频附件ID
     *
     * @param String videos[].at_name:true 视频附件名称
     *
     * @param Int videos[].at_size:true 视频附件尺寸（字节）
     *
     * @param Array files 文件数据（source_type=4时必传）
     *
     * @param Int files[].source_attach_id:true 数据ID（新增时传0）
     *
     * @param String files[].at_id:true 文件附件ID
     *
     * @param String files[].at_name:true 文件附件名称
     *
     * @param Int files[].at_size:true 文件附件尺寸（字节）
     *
     * @param String files[].at_url:true 文件附件地址
     *
     * @param String link 外部素材url（source_type=5时必传）
     *
     * @param Array attachs 附件数据(source_type=1、2、3时可传，目前最多上传5个附件)
     *
     * @param Int attachs[].source_attach_id:true 数据ID（新增时传0）
     *
     * @param String attachs[].at_id:true 附件ID
     *
     * @param String attachs[].at_name:true 附件名称
     *
     * @param Int attachs[].at_size:true 附件尺寸（字节）
     *
     * @param String attachs[].at_url:true 附件地址（最长500）
     *
     * @param Int is_download 附件是否支持下载（1=不支持；2=支持）
     *
     * @return Array
     *
     * array(
     *     'source_id' => 1 // 素材ID
     * )
     *
     */
    public function Index_post()
    {
        // 请求数据
        $post_data = I('post.');

        // 验证规则
        $rules = [
            'source_id' => 'require|integer',
            'source_title' => 'require|max:64',
            'source_type' => 'require|integer|in:1,2,3,4,5',
            'author' => 'max:50',
            'audio_imgs' => 'array',
            'videos' => 'array',
            'files' => 'array',
            'link' => 'max:500',
            'attachs' => 'array',
            'is_download' => 'integer|in:1,2',
        ];

        // 验证请求数据
        $validate = new PackageValidate();
        $validate->postData = $post_data;
        $validate->validateParams($rules);

        // 格式化请求数据
        list($source_data, $attach_datas) = $this->_formatPostData($post_data);
        $source_id = $post_data['source_id'];

        $sourceServ = new SourceService();
        $sourceAttachServ = new SourceAttachService();

        // 新增素材
        if ($source_id == 0) {
            $source_data['source_key'] = $sourceServ->createSourceKey($source_data);
            $source_id = $sourceServ->insert($source_data);

        // 编辑素材
        } else {
            $source = $sourceServ->get($source_id);

            if (empty($source)) {
                E('_ERR_SOURCE_DATA_NOT_FOUND');
            }

            if ($source_data['source_type'] != $source['source_type']) {
                E('_ERR_SOURCE_TYPE_CAN_NOT_CHANGE');
            }

            $sourceServ->update($source_id, $source_data);
        }

        // 保存附件
        list($count, $at_ids) = $sourceAttachServ->saveData($source_id, $attach_datas);

        if ($count > 0) {
            // 更新素材状态
            $sourceServ->update($source_id, ['source_status' => Constant::SOURCE_STATUS_CONVERT]);

            // 创建计划任务
            $taskServ = new TaskService();
            $taskServ->create($source_id);

            // 文件转码
            if (count($at_ids) > 0) {
                if (!empty($at_ids)) {
                    $convertServ = new FileConvert(Service::instance());
                    $convertServ->convert([
                        'atIds' => $at_ids,
                        'convertType' => FileConvert::CONVERT_TYPE_HTML,
                        'high' => FileConvert::CONVERT_IS_HIGH_TRUE,
                    ]);
                }
            }
        }

        $this->_result = [
            'source_id' => $source_id,
        ];
    }

    /**
     * 格式化请求数据，并返回素材数据、附件数据
     * @param array $post_data 请求数据
     * @return array
     */
    private function _formatPostData($post_data)
    {
        // 音图数据
        if (isset($post_data['audio_imgs'])) {
            if (!is_array($post_data['audio_imgs'])) {
                E('_ERR_SOURCE_AUDIO_IMGS_INVALID');
            }

            $keys = [
                // 音图数据允许的key
                'audio_imgs' => ['at_id', 'at_url', 'audios'],
                // 音频数据允许的key
                'audios' => ['at_id', 'at_name', 'at_time', 'at_url'],
            ];

            $audio_imgs = [];

            foreach ($post_data['audio_imgs'] as $audio_img) {
                // 验证key是否存在
                if (!is_array($audio_img) || array_diff($keys['audio_imgs'], array_keys($audio_img))) {
                    E('_ERR_SOURCE_AUDIO_IMGS_INVALID');
                }

                // 验证音频数据是否为数组
                if (!is_array($audio_img['audios'])) {
                    E('_ERR_SOURCE_AUDIO_IMGS_AUDIOS_INVALID');
                }

                // 过滤数据
                $data = array_intersect_key_reserved($audio_img, $keys['audio_imgs'], true);
                $data['audios'] = [];

                foreach ($audio_img['audios'] as $audio) {
                    // 验证key是否存在
                    if (!is_array($audio) || array_diff($keys['audios'], array_keys($audio))) {
                        E('_ERR_SOURCE_AUDIO_IMGS_AUDIOS_INVALID');
                    }

                    // 过滤数据
                    $data['audios'][] = array_intersect_key_reserved($audio, $keys['audios'], true);
                }

                $audio_imgs[] = $data;
            }

            $post_data['audio_imgs'] = $audio_imgs;
        }

        // 视频数据
        if (isset($post_data['videos'])) {
            if (!is_array($post_data['videos'])) {
                E('_ERR_SOURCE_VIDEOS_INVALID');
            }

            $keys = ['source_attach_id', 'at_id', 'at_name', 'at_size'];
            $videos = [];

            foreach ($post_data['videos'] as $video) {
                // 验证key是否存在
                if (!is_array($video) || array_diff($keys, array_keys($video))) {
                    E('_ERR_SOURCE_VIDEOS_INVALID');
                }

                $data = array_intersect_key_reserved($video, $keys, true);
                $data['at_type'] = Constant::ATTACH_TYPE_VIDEO;
                $videos[] = $data;
            }

            $post_data['videos'] = $videos;
        }

        // 文件数据
        if (isset($post_data['files'])) {
            if (!is_array($post_data['files'])) {
                E('_ERR_SOURCE_FILES_INVALID');
            }

            $keys = ['source_attach_id', 'at_id', 'at_name', 'at_size', 'at_url'];
            $files = [];

            foreach ($post_data['files'] as $file) {
                // 验证key是否存在
                if (!is_array($file) || array_diff($keys, array_keys($file))) {
                    E('_ERR_SOURCE_FILES_INVALID');
                }

                $data = array_intersect_key_reserved($file, $keys, true);
                $data['at_type'] = Constant::ATTACH_TYPE_FILE;
                $files[] = $data;
            }

            $post_data['files'] = $files;
        }

        // 附件数据
        if (isset($post_data['attachs'])) {
            if (!is_array($post_data['attachs'])) {
                E('_ERR_SOURCE_ATTACHS_INVALID');
            }

            $keys = ['source_attach_id', 'at_id', 'at_name', 'at_size', 'at_url'];
            $attachs = [];

            foreach ($post_data['attachs'] as $attach) {
                // 验证key是否存在
                if (!is_array($attach) || array_diff($keys, array_keys($attach))) {
                    E('_ERR_SOURCE_ATTACHS_INVALID');
                }

                $data = array_intersect_key_reserved($attach, $keys, true);
                $data['at_type'] = Constant::ATTACH_TYPE_FILE;
                $attachs[] = $data;
            }

            $post_data['attachs'] = $attachs;
        } else {
            $post_data['attachs'] = [];
        }

        $keys = ['source_type', 'source_title', 'author', 'content'];
        $source = array_intersect_key_reserved($post_data, $keys, true);
        $source_attachs = [];

        switch ($post_data['source_type']) {
            // 图文
            case Constant::SOURCE_TYPE_IMG_TEXT:
                if (!isset($source['content']) || empty($source['content'])) {
                    E('_ERR_SOURCE_CONTENT_EMPTY');
                }

                $source_attachs = $post_data['attachs'];
                break;

            // 音图
            case Constant::SOURCE_TYPE_AUDIO_IMG:
                if (!isset($post_data['audio_imgs'])) {
                    E('_ERR_SOURCE_AUDIO_IMGS_EMPTY');
                }

                $source['audio_imgs'] = serialize($post_data['audio_imgs']);
                $source_attachs = $post_data['attachs'];
                break;

            // 视频
            case Constant::SOURCE_TYPE_VEDIO:
                if (!isset($post_data['videos'])) {
                    E('_ERR_SOURCE_VIDEOS_EMPTY');
                }

                $source_attachs = array_merge($post_data['videos'], $post_data['attachs']);
                break;

            // 文件
            case Constant::SOURCE_TYPE_FILE:
                if (!isset($post_data['files'])) {
                    E('_ERR_SOURCE_FILES_EMPTY');
                }

                $source_attachs = $post_data['files'];
                break;

            // 外部
            case Constant::SOURCE_TYPE_LINK:
                if (!isset($post_data['link']) || empty($post_data['link'])) {
                    E('_ERR_SOURCE_LINK_EMPTY');
                }

                $source['link'] = $post_data['link'];
                break;
        }

        // 附件是否支持下载
        if (in_array($post_data['source_type'], [Constant::SOURCE_TYPE_IMG_TEXT, Constant::SOURCE_TYPE_AUDIO_IMG, Constant::SOURCE_TYPE_VEDIO])) {
            $source['is_download'] = isset($post_data['is_download']) ? $post_data['is_download'] : Constant::ATTACH_IS_DOWNLOAD_FALSE;
        }

        $source['ea_id'] = $this->_login->user['eaId'];
        $source['ea_name'] = $this->_login->user['eaRealname'];
        $source['update_time'] = MILLI_TIME;

        return [$source, $source_attachs];
    }
}
