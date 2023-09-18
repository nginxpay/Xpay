<?php
namespace XXpay;
class Xpay extends Pay
{
    /**
     * @param  int  $amount  
     * @param  string  $order_num  
     * @param  string  $channel  
     * @param  string  $payTye  
     * @param  string  $callbackUrl  
     * @param  string  $product  
     * @return  array ['','','','']
     */
    public function pay($amount, $order_num, $channel, $payTye, $callbackUrl = '', $product = '')
    {
        $data = $this->sendPayInRequest($amount, $order_num, $channel, $payTye, $callbackUrl, $product);
        $code = 0;
        $payUrl = '';
        $passageOrder = '';
        if ($data) {
            if (isset($data['code']) && $data['code'] === 200) {
                $code = 998;
                $msg = 'OK';
                $payUrl = $data['data']['url'];
                $passageOrder = $data['data']['plat_order_id'];
            } else {
                $msg = isset($data['msg']) ? $data['msg'] : 'fail';
            }
        } else {
            $msg = 'Error making request';
        }
        return $this->returnData($code, $msg, $payUrl, $passageOrder);
    }

    /**
     * @param  int  $amount  
     * @param  string  $order_num
     * @param  string  $channel
     * @param  array  $bandInfo
     * @return  array
     */
    public function payOut($amount, $order_num, $bandInfo)
    {
        $data = $this->send_pay_out_request($amount, $order_num, $bandInfo);
        $code = 0;
        $payUrl = '';
        $passageOrder = '';
        if ($data) {
            if (isset($data['code']) && $data['code'] === 200) {
                $code = 998;
                $msg = 'OK';
                $payUrl = '';
                $passageOrder = $data['data']['plat_order_id'];
            } else {
                $msg = isset($data['msg']) ? $data['msg'] : 'fail';
            }
        } else {
            $msg = 'Error making request';
        }
        return $this->returnData($code, $msg, $payUrl, $passageOrder);
    }
    /**
     * 
     * @param  string  $amount  
     * @param  string  $order_num  
     * @param  string  $channel  
     * @param  string  $payTye  
     * @param  string  $callbackUrl  
     * @param  string  $product  
     * @return  array ['','','','']
     */
    public function sendPayInRequest($amount, $order_num, $channel, $payTye, $callbackUrl, $product)
    {
        $api_pay_url = $this->apiHost . '/api/xpay/create/receiveorder';
        if (!$channel) {
            $channel = '101';
        }
        $data = [
            'merchant_id' => $this->merchant_id,
            'order_id' => $order_num,
            'timestamp' => time(),
            'notify_url' => $this->notifyUrl . '/api/paynotify/notify/' . $payTye . 'notify', //
            'callback_url' => $callbackUrl,
            'amount' => $amount . '',
            'phone' => '9' . substr($order_num, -9),
            'product' => 'alc'
            //'code' => $channel,
        ];
        $sign_str = $this->asc_sort($data);
        $sign_str = $sign_str . '&key=' . $this->secret;

        $sign = strtolower(md5($sign_str));
        $data['sign'] = $sign;

        $header = [
            'Content-Type: application/json',
        ];
        $result = $this->http_restfull_curl_timeout($api_pay_url, 'POST', json_encode($data), $header, 30, 30);

        if ($result['curl_code'] != 200) {
            return false;
        }
        $return_data = json_decode($result['curl_response'], true);
        return $return_data;
    }


    public function notify($type)
    {
        $data = $this->payInNotify();
        if ($data['status'] === 998)
            $status = 'success';
        else
            $status = 'fail';
        $msg = isset($data['msg']) ? $data['msg'] : $data['status'];
      
        return $this->returnOrder($status, $msg, $data['amount'], $data['plat_order_id'], $data['mch_order_id'], 'SUCCESS');
    }

    public function payInNotify()
    {
        $data = file_get_contents("php://input");

        if (empty($data)) {
            exit("parameter is empty");
        }
        $return_data = json_decode($data, true);
        $sign = $return_data['sign'];
        unset($return_data['sign']);
        $sign_str = $this->asc_sort($return_data);
        $sign_str = $sign_str . '&key=' . $this->secret;
        if ($sign != md5($sign_str)) {
            exit("incorrect signature");
        }
        return $return_data;
    }

    /**
     * 
     * @param $amount
     * @param $order_num
     * @param $card_info
     * @return bool
     */
    public function send_pay_out_request($amount, $order_num, $card_info)
    {
        $api_pay_url = $this->apiHost . '/api/xpay/create/payout';
        $payType=$card_info['payType'];
        $data = [
            'merchant_id' => $this->merchant_id, //
            'order_id' => $order_num, //
            'amount' => '' . $amount, //
            'timestamp' => time(),
            //'notify_url' => $this->notifyUrl.'/api/paynotify/xpaywithdrawnotify', //
            'notify_url' => $this->notifyUrl . '/api/paynotify/withdrawnotify/' . $payType . 'withdrawnotify',
            'phone' => '9' . substr($order_num, -9),
            'acc_type' => '1',
            'account_name' => $card_info['bank_account_name'], //
            'account' => $card_info['bank_account'], //
            'bank_name' => $card_info['bank_name'],
            'bank_ifsc' => $card_info['ifsc_code'], //
            'email' => 'youmecoffee@gmail.com',
        ];
        $sign_str = $this->asc_sort($data);
        $sign_str = $sign_str . '&key=' . $this->secret;
        $sign = strtolower(md5($sign_str));
        $data['sign'] = $sign;
        //$data['sign_type'] = 'MD5';
        $header = [
            'Content-Type: application/json',
        ];
        $result = $this->http_restfull_curl_timeout($api_pay_url, 'POST', json_encode($data), $header, 30, 30);
        if ($result['curl_code'] != 200) {
            return false;
        } else {
            $return_data = json_decode($result['curl_response'], true);
            return $return_data;
        }
    }

    public function queryBalance(){
        $api_pay_url = $this->apiHost . '/api/xpay/query/balance';
  
        $data = [
            'merchant_id' => $this->merchant_id, //
            'timestamp' => time(),
        ];
        $sign_str = $this->asc_sort($data);
        $sign_str = $sign_str . '&key=' . $this->secret;
        $sign = strtolower(md5($sign_str));
        $data['sign'] = $sign;
        //$data['sign_type'] = 'MD5';
        $header = [
            'Content-Type: application/json',
        ];
        $result = $this->http_restfull_curl_timeout($api_pay_url, 'POST', json_encode($data), $header, 30, 30);
        if ($result['curl_code'] != 200) {
            return false;
        } else {
            $return_data = json_decode($result['curl_response'], true);
            return $return_data;
        }
    }
    public function queryOrder($orderId,$orderType){
        $api_pay_url = $this->apiHost . '/api/xpay/query/order';
  
        $data = [
            'merchant_id' => $this->merchant_id, //
            'timestamp' => time(),
        ];
        $sign_str = $this->asc_sort($data);
        $sign_str = $sign_str . '&key=' . $this->secret;
        $sign = strtolower(md5($sign_str));
        $data['order_id']=$orderId;
        $data['order_type']=$orderType;
        $data['sign'] = $sign;
        //$data['sign_type'] = 'MD5';
        $header = [
            'Content-Type: application/json',
        ];
        $result = $this->http_restfull_curl_timeout($api_pay_url, 'POST', json_encode($data), $header, 30, 30);
        if ($result['curl_code'] != 200) {
            return false;
        } else {
            $return_data = json_decode($result['curl_response'], true);
            return $return_data;
        }
    }
}
