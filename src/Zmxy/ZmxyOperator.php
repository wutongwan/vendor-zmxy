<?php namespace Wutongwan\VendorZmxy;

class  ZmxyOperator
{

    private $appId;
    private $charset = 'UTF-8';
    private $apiVersion = "1.0";

    public function __construct($appId)
    {
        $this->appId = $appId;
    }


    /**
     * 为业务参数生成签名
     * @param $request
     * @return string
     */
    public function generateSign($request, $priKey)
    {
        $paramStr = $this->getBizParamStr($request);
        return \RSAUtil::sign($paramStr, $priKey);
    }

    /**
     * 为业务参数生成签名并进行UrlEncode
     * @param $request
     * @return string
     */
    public function generateSignWithUrlEncode($request, $priKey)
    {
        $paramStr = $this->generateSign($request, $priKey);
        return urlencode($paramStr);
    }

    /**
     * 为业务参数加密
     * @param $request
     * @return string
     */
    public function generateEncryptedParam($request, $pubKey)
    {
        $paramStr = $this->getBizParamStr($request);
        return \RSAUtil::rsaEncrypt($paramStr, $pubKey);
    }

    /**
     * 为业务参数加密
     * @param $request
     * @return string
     */
    public function generateEncryptedParamWithUrlEncode($request, $pubKey)
    {
        $paramStr = $this->generateEncryptedParam($request, $pubKey);
        return urlencode($paramStr);
    }

    /**
     * 从总的返回值中获取业务返回值
     * @param $obj
     * @return null
     */
    public function get_biz_response($obj)
    {
        $attrArray = get_object_vars($obj);
        foreach ($attrArray as $paraKey => $paraValue) {
            //如果属性名以_reponse结尾，该属性对应的值为业务返回值
            if (strrchr($paraKey, "_response") == "_response") {
                return $paraValue;
            }
        }
        return null;
    }

    public function getBizParamStr($request)
    {
        $apiParams = $request->getApiParas();
        $apiParamsQuery = self::buildQueryWithEncode($apiParams);
        return $apiParamsQuery;
    }

    public function getSystemParams($request)
    {
        if (is_empty_string($this->charset)) {
            $this->charset = "UTF-8";
        }

        $iv = null;
        if (!is_empty_string($request->getApiVersion())) {
            $iv = $request->getApiVersion();
        } else {
            $iv = $this->apiVersion;
        }

        //组装系统参数
        $sysParams["app_id"] = $this->appId;
        $sysParams["version"] = $iv;
        $sysParams["method"] = $request->getApiMethodName();
        $sysParams["charset"] = $this->charset;
        $sysParams["scene"] = $request->getScene();
        $sysParams["channel"] = $request->getChannel();
        $sysParams["platform"] = $request->getPlatform();
        $sysParams["ext_params"] = $request->getExtParams();
        return $sysParams;
    }

    /**
     * 将传入的参数组织成key1=value1&key2=value2形式的字符串
     * @param $params
     * @return string
     */
    public static function buildQueryWithoutEncode($params)
    {
        return \ParamSignUtil::buildQuery($params, false);
    }

    public static function buildQueryWithEncode($params)
    {
        return \ParamSignUtil::buildQuery($params, true);
    }

}
