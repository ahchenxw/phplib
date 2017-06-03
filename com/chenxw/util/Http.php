<?php
namespace com\chenxw\util;

/**
 * HTTP请求封装，支持通用的GET，POST
 * User: ahchenxw@126.com
 * Date: 2017/5/23
 * Time: 9:54
 */
class Http
{
    private $url; //请求地址
    private $data; //请求数据
    private $post = 0; //是否是POST方式提交数据，默认：0
    private $cookiePath = null; //COOKIE存放路径

    private $userAgent = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36';
    private $followLocation = 1; //执行重定向
    private $referer = null; //伪造来源
    private $autoReferer = 1; //当执行重定向时，自动填充REFERER
    private $sslVerifyPeer = 0;
    private $sslVerifyHost = 2;
    private $returnTransfer = 1;
    private $timeout = 30;

    private $header = 0; //输出HEADER
    private $httpHeader = null;
    private $resultHeader;

    /**
     * @param $url
     * @param string $data
     * @param int $post
     * @return static
     */
    public static function create($url, $data = '', $post = 0)
    {
        $http = new static($url, $data, $post);
        return $http;
    }

    /**
     * HttpUtil constructor.
     * @param string $url
     * @param string|array $data
     * @param int $post
     */
    public function __construct($url, $data = '', $post = 0)
    {
        $this->setUrl($url)->setData($data)->setPost($post);
    }

    /**
     * 发起请求
     * @return string
     */
    public function request()
    {
        $url = $this->url;
        $postFields = $this->data;
        if (!$this->post) {
            $url .= (mb_strpos($url, '?') === false) ? '?' : '&';
            $url .= $this->data;
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $this->sslVerifyPeer);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, $this->sslVerifyHost);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, $this->returnTransfer);

        curl_setopt($curl, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, $this->followLocation);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($curl, CURLOPT_AUTOREFERER, $this->autoReferer);

        curl_setopt($curl, CURLOPT_HEADER, $this->header);
        if ($this->httpHeader) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $this->httpHeader);
        }

        //设置POST数据内容
        curl_setopt($curl, CURLOPT_POST, $this->post);
        if ($this->post) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postFields);
        }
        if ($this->cookiePath) {
            curl_setopt($curl, CURLOPT_COOKIEJAR, $this->cookiePath);
            curl_setopt($curl, CURLOPT_COOKIEFILE, $this->cookiePath);
        }
        if ($this->referer) {
            curl_setopt($curl, CURLOPT_REFERER, $this->referer);
        }
        $result = curl_exec($curl);

        //解析下行Header
        $curlInfo = curl_getinfo($curl);
        if ($this->header) {
            $this->resultHeader = substr($result, 0, $curlInfo['header_size']);
            $result = substr($result, $curlInfo['header_size']);
        }

        curl_close($curl);
        return $result;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = trim($url, " \t\n\r\0\x0B&?");
        return $this;
    }

    /**
     * @param array|string $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = is_array($data) ? http_build_query($data) : $data;
        return $this;
    }

    /**
     * @param int $post
     * @return $this
     */
    public function setPost($post)
    {
        $this->post = intval($post);
        return $this;
    }

    /**
     * @return null
     */
    public function getHttpHeader()
    {
        return $this->httpHeader;
    }

    /**
     * @param $httpHeader
     * @return $this
     */
    public function setHttpHeader($httpHeader)
    {
        $this->httpHeader = $httpHeader;
        return $this;
    }

    /**
     * @return null
     */
    public function getReferer()
    {
        return $this->referer;
    }

    /**
     * @param $referer
     * @return $this
     */
    public function setReferer($referer)
    {
        $this->referer = $referer;
        return $this;
    }

    /**
     * 获取下行Header内容
     * @return mixed
     */
    public function getResultHeader()
    {
        return $this->resultHeader;
    }


    /**
     * @param string $userAgent
     * @return $this
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;
        return $this;
    }

    /**
     * @param $followLocation
     * @return $this
     */
    public function setFollowLocation($followLocation)
    {
        $this->followLocation = $followLocation;
        return $this;
    }

    /**
     * @param $autoReferer
     * @return $this
     */
    public function setAutoReferer($autoReferer)
    {
        $this->autoReferer = $autoReferer;
        return $this;
    }

    /**
     * @param $sslVerifyPeer
     * @return $this
     */
    public function setSslVerifyPeer($sslVerifyPeer)
    {
        $this->sslVerifyPeer = $sslVerifyPeer;
        return $this;
    }

    /**
     * @param $sslVerifyHost
     * @return $this
     */
    public function setSslVerifyHost($sslVerifyHost)
    {
        $this->sslVerifyHost = $sslVerifyHost;
        return $this;
    }

    /**
     * @param $returnTransfer
     * @return $this
     */
    public function setReturnTransfer($returnTransfer)
    {
        $this->returnTransfer = $returnTransfer;
        return $this;
    }

    /**
     * @param $header
     * @return $this
     */
    public function setHeader($header)
    {
        $this->header = $header;
        return $this;
    }

    /**
     * @param $timeout
     * @return $this
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * @param $cookiePath
     * @return $this
     */
    public function setCookiePath($cookiePath)
    {
        $this->cookiePath = $cookiePath;
        return $this;
    }

}