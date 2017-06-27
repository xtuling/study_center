<?php
/**
 * Message.class.php
 * 发送消息操作类
 *
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt LGPL
 * @copyright  Copyright (c) 2014 - ? VcySDK (http://www.vchangyi.com/)
 * @author     zhuxun37
 * @version    1.0.0
 */
namespace VcySDK;

class Message
{

    /**
     * 接口调用类
     *
     * @var object
     */
    private $serv = null;

    /**
     * 文本消息
     * %s = {apiUrl}/b/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const TEXT_URL = '%s/send_text';

    /**
     * 图文消息
     * %s = {apiUrl}/b/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const NEWS_URL = '%s/send_news';

    /**
     * 图片消息
     * %s = {apiUrl}/b/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const IMAGE_URL = '%s/send_image';

    /**
     * 语音消息
     * %s = {apiUrl}/b/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const VOICE_URL = '%s/send_voice';

    /**
     * 视频消息
     * %s = {apiUrl}/b/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const VIDEO_URL = '%s/send_video';

    /**
     * 文件消息
     * %s = {apiUrl}/b/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const FILE_URL = '%s/send_file';

    /**
     * 初始化
     *
     * @param Service $serv 接口调用类
     */
    public function __construct($serv)
    {

        $this->serv = $serv;
    }

    /**
     * 发送文本消息
     * @param array $params
     *        + string toUser 消息接受者, 多个接收者用 | 分割, 最多1000人, @all 为向关注企业号应用的所有人发送
     *        + string toParty 消息接收部门, 多个接收部门用 | 分割, 最多100个, toUser为@all时忽略此参数
     *        + string toTag 标签ID列表，多个接收者用|分隔。当touser为@all时忽略本参数
     *        + string content 消息内容, 最多2048字节
     *        + string msgtype 消息类型，此时固定为：text（支持消息型应用跟主页型应用）
     *        + string safe 是否保密消息 1:是, 0:不是 默认: 0 不是
     *        + string callbackUrl 回调业务的地址，消息是否发送成功回调通知地址
     *        + string callbackParams JSON字符串,业务自定义参数，回调时原样返回
     * @return bool|mixed
     * @throws Exception
     */
    public function sendText($params)
    {
        $params['msgType'] = 'text';

        return $this->serv->postSDK(self::TEXT_URL, $params, 'generateApiUrlA');
    }

    /**
     * 发送图文消息
     * @param array $params
     *        + string toUser 消息接受者, 多个接收者用 | 分割, 最多1000人, @all 为向关注企业号应用的所有人发送
     *        + string toParty 消息接收部门, 多个接收部门用 | 分割, 最多100个, toUser为@all时忽略此参数
     *        + string toTag 标签ID列表，多个接收者用|分隔。当touser为@all时忽略本参数
     *        + array  articles 图文消息, 支持1~8个图文消息
     *                 + title 标题 不超过128字节, 超过自动截断
     *                 + description 描述 不超过512字节, 超过自动截断
     *                 + url 点击跳转URL
     *                 + picUrl 图片URL 支持JPG、PNG 大图640320，小图8080。如不填，在客户端不显示图片
     *        + string msgtype 消息类型，此时固定为：news（支持消息型应用跟主页型应用）
     *        + string safe 是否保密消息 1:是, 0:不是 默认: 0 不是
     *        + string callbackUrl 回调业务的地址，消息是否发送成功回调通知地址
     *        + string callbackParams JSON字符串,业务自定义参数，回调时原样返回
     * @return bool|mixed
     * @throws Exception
     */
    public function sendNews($params)
    {
        $params['msgType'] = 'news';

        return $this->serv->postSDK(self::NEWS_URL, $params, 'generateApiUrlA');
    }

    /**
     * 发送图片消息
     * @param array $params
     *        + string toUser 消息接受者, 多个接收者用 | 分割, 最多1000人, @all 为向关注企业号应用的所有人发送
     *        + string toParty 消息接收部门, 多个接收部门用 | 分割, 最多100个, toUser为@all时忽略此参数
     *        + string toTag 标签ID列表，多个接收者用|分隔。当touser为@all时忽略本参数
     *        + string atId 附件ID
     *        + string msgtype 消息类型，此时固定为：image（支持消息型应用跟主页型应用）
     *        + string safe 是否保密消息 1:是, 0:不是 默认: 0 不是
     *        + string callbackUrl 回调业务的地址，消息是否发送成功回调通知地址
     *        + string callbackParams JSON字符串,业务自定义参数，回调时原样返回
     * @return bool|mixed
     * @throws Exception
     */
    public function sendImage($params)
    {
        $params['msgType'] = 'image';

        return $this->serv->postSDK(self::NEWS_URL, $params, 'generateApiUrlA');
    }

    /**
     * 发送语音消息
     * @param array $params
     *        + string toUser 消息接受者, 多个接收者用 | 分割, 最多1000人, @all 为向关注企业号应用的所有人发送
     *        + string toParty 消息接收部门, 多个接收部门用 | 分割, 最多100个, toUser为@all时忽略此参数
     *        + string toTag 标签ID列表，多个接收者用|分隔。当touser为@all时忽略本参数
     *        + string atId 附件ID
     *        + string msgtype 消息类型，此时固定为：voice（支持消息型应用跟主页型应用）
     *        + string safe 是否保密消息 1:是, 0:不是 默认: 0 不是
     *        + string callbackUrl 回调业务的地址，消息是否发送成功回调通知地址
     *        + string callbackParams JSON字符串,业务自定义参数，回调时原样返回
     * @return bool|mixed
     * @throws Exception
     */
    public function sendVoice($params)
    {
        $params['msgType'] = 'voice';

        return $this->serv->postSDK(self::VOICE_URL, $params, 'generateApiUrlA');
    }

    /**
     * 发送视频消息
     * @param array $params
     *        + string toUser 消息接受者, 多个接收者用 | 分割, 最多1000人, @all 为向关注企业号应用的所有人发送
     *        + string toParty 消息接收部门, 多个接收部门用 | 分割, 最多100个, toUser为@all时忽略此参数
     *        + string toTag 标签ID列表，多个接收者用|分隔。当touser为@all时忽略本参数
     *        + string atId 附件ID
     *        + string msgtype 消息类型，此时固定为：video（支持消息型应用跟主页型应用）
     *        + string safe 是否保密消息 1:是, 0:不是 默认: 0 不是
     *        + string callbackUrl 回调业务的地址，消息是否发送成功回调通知地址
     *        + string callbackParams JSON字符串,业务自定义参数，回调时原样返回
     * @return bool|mixed
     * @throws Exception
     */
    public function sendVideo($params)
    {
        $params['msgType'] = 'video';

        return $this->serv->postSDK(self::VIDEO_URL, $params, 'generateApiUrlA');
    }

    /**
     * 发送文件消息
     * @param array $params
     *        + string toUser 消息接受者, 多个接收者用 | 分割, 最多1000人, @all 为向关注企业号应用的所有人发送
     *        + string toParty 消息接收部门, 多个接收部门用 | 分割, 最多100个, toUser为@all时忽略此参数
     *        + string toTag 标签ID列表，多个接收者用|分隔。当touser为@all时忽略本参数
     *        + string atId 附件ID
     *        + string msgtype 消息类型，此时固定为：file（支持消息型应用跟主页型应用）
     *        + string safe 是否保密消息 1:是, 0:不是 默认: 0 不是
     *        + string callbackUrl 回调业务的地址，消息是否发送成功回调通知地址
     *        + string callbackParams JSON字符串,业务自定义参数，回调时原样返回
     * @return bool|mixed
     * @throws Exception
     */
    public function sendFile($params)
    {
        $params['msgType'] = 'file';

        return $this->serv->postSDK(self::FILE_URL, $params, 'generateApiUrlA');
    }
}
