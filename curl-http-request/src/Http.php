<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/12
 * Time: 21:29
 */

namespace Lisgroup\Src;


class Http
{
    // 是否记录日志,默认开启
    public $writeLog = 1;
    // 日志类型1:本地 2http，暂支持1
    public $writeLogType = 1;
    // 记录日志是否截取返回内容，大于0位具体字符数，等于0完整，默认0
    public $logResContentLength = 0;

    public function __construct($opts = array())
    {
        if (!empty($opts)) {
            foreach ($opts as $key => $o) {
                $this->$key = $o;
            }
        }
    }

    /**
     * @param $url
     * @param array $params
     * @param string $method
     * @param int $timemout
     * @param array $headers
     * @param string $cookie
     * @return array
     */
    public function request($url, $params = array(), $method = "GET", $timemout = 8, $headers = array(), $cookie = '')
    {
        $method = strtoupper($method);
        // 新增请求方式
        $methodArray = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'];

        if (!in_array($method, $methodArray)) {
            $method = "GET";
        }

        if ($params) {
            if (is_array($params)) {
                $paramsString = http_build_query($params);
            } else {
                $paramsString = $params;
            }
        } else {
            $paramsString = "";
        }

        //$tempUrl = $url;
        if ($method == "GET" && !empty($paramsString)) {
            $url = $url."?".$paramsString;
        }

        // 初始化
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timemout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if (strtolower(substr($url, 0, 8)) == 'https://') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // 从证书中检查SSL加密算法是否存在
        }

        // 请求头
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        if (!empty($cookie)) {
            curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        }

        // 指定请求方式
        switch ($method) {
            case 'GET':
                break;
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $paramsString); //设置请求体，提交数据包
                break;
            case 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($ch, CURLOPT_POSTFIELDS, $paramsString); //设置请求体，提交数据包
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
        }
        /*if ($method == "post") {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $paramsString);
        }*/
        curl_setopt($ch, CURLOPT_URL, $url);

        // 请求网络
        $timeStampBegin = microtime(true);
        //$timeBegin = date("Y-m-d H:i:s");
        $httpContent = curl_exec($ch);
        $timeStampEnd = microtime(true);
        //$timeEnd = date("Y-m-d H:i:s");

        $httpInfo = array();
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
        $curlErrNo = curl_errno($ch);
        $httpError = curl_error($ch);
        $httpCost = round($timeStampEnd - $timeStampBegin, 3);

        // 关闭
        curl_close($ch);

        $curlErrMsg = $this->_curlErrNoMap($curlErrNo);

        return array(
            'httpCode' => $httpCode, // http状态码
            'error' => $httpError, // 错误信息
            'curlErrno' => $curlErrNo, //curl状态码,
            'curlErrMsg' => $curlErrMsg,
            'cost' => $httpCost, // 网络执行时间
            'content' => $httpContent, // 网络返回内容
            'httpInfo' => $httpInfo
        );
    }

    public function _curlErrNoMap($curlErrNo)
    {
        $error_codes = array(
            '0' => 'CURLE_OK',
            '1' => 'CURLE_UNSUPPORTED_PROTOCOL',
            '2' => 'CURLE_FAILED_INIT',
            '3' => 'CURLE_URL_MALFORMAT',
            '4' => 'CURLE_URL_MALFORMAT_USER',
            '5' => 'CURLE_COULDNT_RESOLVE_PROXY',
            '6' => 'CURLE_COULDNT_RESOLVE_HOST',
            '7' => 'CURLE_COULDNT_CONNECT',
            '8' => 'CURLE_FTP_WEIRD_SERVER_REPLY',
            '9' => 'CURLE_REMOTE_ACCESS_DENIED',
            '11' => 'CURLE_FTP_WEIRD_PASS_REPLY',
            '13' => 'CURLE_FTP_WEIRD_PASV_REPLY',
            '14' => 'CURLE_FTP_WEIRD_227_FORMAT',
            '15' => 'CURLE_FTP_CANT_GET_HOST',
            '17' => 'CURLE_FTP_COULDNT_SET_TYPE',
            '18' => 'CURLE_PARTIAL_FILE',
            '19' => 'CURLE_FTP_COULDNT_RETR_FILE',
            '21' => 'CURLE_QUOTE_ERROR',
            '22' => 'CURLE_HTTP_RETURNED_ERROR',
            '23' => 'CURLE_WRITE_ERROR',
            '25' => 'CURLE_UPLOAD_FAILED',
            '26' => 'CURLE_READ_ERROR',
            '27' => 'CURLE_OUT_OF_MEMORY',
            '28' => 'CURLE_OPERATION_TIMEDOUT',
            '30' => 'CURLE_FTP_PORT_FAILED',
            '31' => 'CURLE_FTP_COULDNT_USE_REST',
            '33' => 'CURLE_RANGE_ERROR',
            '34' => 'CURLE_HTTP_POST_ERROR',
            '35' => 'CURLE_SSL_CONNECT_ERROR',
            '36' => 'CURLE_BAD_DOWNLOAD_RESUME',
            '37' => 'CURLE_FILE_COULDNT_READ_FILE',
            '38' => 'CURLE_LDAP_CANNOT_BIND',
            '39' => 'CURLE_LDAP_SEARCH_FAILED',
            '41' => 'CURLE_FUNCTION_NOT_FOUND',
            '42' => 'CURLE_ABORTED_BY_CALLBACK',
            '43' => 'CURLE_BAD_FUNCTION_ARGUMENT',
            '45' => 'CURLE_INTERFACE_FAILED',
            '47' => 'CURLE_TOO_MANY_REDIRECTS',
            '48' => 'CURLE_UNKNOWN_TELNET_OPTION',
            '49' => 'CURLE_TELNET_OPTION_SYNTAX',
            '51' => 'CURLE_PEER_FAILED_VERIFICATION',
            '52' => 'CURLE_GOT_NOTHING',
            '53' => 'CURLE_SSL_ENGINE_NOTFOUND',
            '54' => 'CURLE_SSL_ENGINE_SETFAILED',
            '55' => 'CURLE_SEND_ERROR',
            '56' => 'CURLE_RECV_ERROR',
            '58' => 'CURLE_SSL_CERTPROBLEM',
            '59' => 'CURLE_SSL_CIPHER',
            '60' => 'CURLE_SSL_CACERT',
            '61' => 'CURLE_BAD_CONTENT_ENCODING',
            '62' => 'CURLE_LDAP_INVALID_URL',
            '63' => 'CURLE_FILESIZE_EXCEEDED',
            '64' => 'CURLE_USE_SSL_FAILED',
            '65' => 'CURLE_SEND_FAIL_REWIND',
            '66' => 'CURLE_SSL_ENGINE_INITFAILED',
            '67' => 'CURLE_LOGIN_DENIED',
            '68' => 'CURLE_TFTP_NOTFOUND',
            '69' => 'CURLE_TFTP_PERM',
            '70' => 'CURLE_REMOTE_DISK_FULL',
            '71' => 'CURLE_TFTP_ILLEGAL',
            '72' => 'CURLE_TFTP_UNKNOWNID',
            '73' => 'CURLE_REMOTE_FILE_EXISTS',
            '74' => 'CURLE_TFTP_NOSUCHUSER',
            '75' => 'CURLE_CONV_FAILED',
            '76' => 'CURLE_CONV_REQD',
            '77' => 'CURLE_SSL_CACERT_BADFILE',
            '78' => 'CURLE_REMOTE_FILE_NOT_FOUND',
            '79' => 'CURLE_SSH',
            '80' => 'CURLE_SSL_SHUTDOWN_FAILED',
            '81' => 'CURLE_AGAIN',
            '82' => 'CURLE_SSL_CRL_BADFILE',
            '83' => 'CURLE_SSL_ISSUER_ERROR',
            '84' => 'CURLE_FTP_PRET_FAILED',
            '85' => 'CURLE_RTSP_CSEQ_ERROR',
            '86' => 'CURLE_RTSP_SESSION_ERROR',
            '87' => 'CURLE_FTP_BAD_FILE_LIST',
            '88' => 'CURLE_CHUNK_FAILED'
        );
        // $curlErrNo = (int) $curlErrNo;
        if (isset($error_codes[$curlErrNo])) {
            return $error_codes[$curlErrNo];
        } else {
            return "";
        }
    }
}