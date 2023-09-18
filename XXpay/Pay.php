<?php
namespace XXpay;

class Pay
{

    protected $merchant_id;
    protected $secret;
    protected $outSecret;
    protected $apiHost;
    protected $notifyUrl;

    /**
     * base sign.
     *
     * @param  string  $mchId
     * @param  string  $key
     * @param  string  $outKey
     * @param  string  $apiHost
     * 
     */
    public function __construct($mchId,$key,$outKey='',$apiHost)
    {
        $this->merchant_id = $mchId; 
        $this->secret = $key; 
        $this->outSecret = $outKey; 
        $this->apiHost = $apiHost;
        $this->notifyUrl='your notiry url';
    }
    public static function  checkMd5($data, $sign, $key)
    {
        $signStr = self::asc_sort($data);
        $signStr = $signStr . '&key=' . $key;
        if ($sign == md5($signStr)) {
            return true;
        } else {
            return false;
        }
    }

    public static function signMd5($data,$key)
    {
        $signStr = self::asc_sort($data);
        $signStr = $signStr . '&key=' . $key;
        return md5($signStr);
    }

    /**ascii
     * @param array $params
     * @return bool|string
     */
    protected static function asc_sort($params = array())
    {
        if (!empty($params)) {
            $p = ksort($params);
            if ($p) {
                $str = '';
                foreach ($params as $k => $val) {
                    if ($val != '') {
                        $str .= $k . '=' . $val . '&';
                    }
                }
                $strs = rtrim($str, '&');
                return $strs;
            }
        }
        return false;
    }

    /**
     * 
     * @param $url
     * @param $method
     * @param string $data
     * @param string $headers
     * @param int $con_timeout
     * @param int $timeout
     * @return mixed
     */
    public static function http_restfull_curl_timeout($url, $method, $data = "", $headers = "", $con_timeout = 3, $timeout = 20)
    {
        if (is_string($data)) {
            $params = $data;
        }
        if (is_array($data)) {
            $params = json_encode($data);
        }
        $ch = curl_init();
        $curl_con_timeout = $con_timeout;       
        $curl_timeout = $timeout;        
        curl_setopt($ch, CURLOPT_URL, $url);
        if (strpos(strtolower($url), "https://") !== false) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        if ($headers == "") {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded')); 
            $params = $data;
        } else {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $curl_con_timeout);  
        curl_setopt($ch, CURLOPT_TIMEOUT, $curl_timeout);  
        $method = strtoupper($method); 
        if ($method == "GET") {
            curl_setopt($ch, CURLOPT_HTTPGET, true);
        } elseif ($method == "POST") {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        } elseif ($method == "PUT") {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        } elseif ($method == "DELETE") {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }
        $ret_contents = curl_exec($ch);
        $curl_errno = curl_errno($ch);  
        $curl_error = curl_error($ch);
        //$curl_info = curl_getinfo($ch);
        //$curl_info["curl_error"] = sprintf("error (%d): %s",$curl_errno,$curl_error);
        curl_close($ch);
        if ($curl_errno > 0) {
            if ($curl_errno == 28) {
                $curl_data["curl_code"] = 408;   //Request Timeout，http
            } else {
                $curl_data["curl_code"] = 201;
            }
            $curl_data["curl_response"] = sprintf("error (%d): %s", $curl_errno, $curl_error);
        } else {
            $curl_data["curl_code"] = 200;
            $curl_data["curl_response"] = $ret_contents;
        }
        return $curl_data;
    }

    /**
     * @param  int  $code  998 success
     * @param  string  $msg
     * @param  string  $payUrl
     * @param  string  $passageOrder
     * @return  array
     */
    protected function returnData($code,$msg,$payUrl='',$passageOrder){
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'url' =>  $payUrl,
            'passageorder' => $passageOrder,
        ];
        return $result;
    }

    /**
     * @param  string  $status  success fail  paying
     * @param  string  $msg   
     * @param  string  $amount   
     * @param  string  $passOrderNum  
     * @param  string  $orderId      
     * @param  string  $ret  
     * @return  array
     */
    protected function returnOrder($status,$msg,$amount,$passOrderNum,$orderId='',$ret){
        $result = [
            'status' => $status,
            'msg'  => $msg,
            'amount' =>  $amount,
            'passOrderNum'=>$passOrderNum,
            'orderId' => $orderId,
            'ret'=>$ret
        ];
        return $result;
    }

    protected function msectime()
    {
        list($msec, $sec) = explode(' ', microtime());
        $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        return intval($msectime);
    }
}
