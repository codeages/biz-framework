<?php

namespace Codeages\Biz\Framework\Pay\Payment;


use Codeages\Biz\Framework\Util\ArrayToolkit;

class AppleGetway extends AbstractGetway
{
    public function converterNotify($data)
    {
        $data = ArrayToolkit::parts($data, array(
            'user_id',
            'amount',
            'receipt',
            'transaction_id',
            'is_sand_box'
        ));

        $this->requestReceiptData($data);
    }

    private function requestReceiptData($data)
    {
        $userId = $data['user_id'];
        $amount = $data['amount'];
        $receipt = $data['receipt'];
        $transactionId = $data['transaction_id'];
        $isSandbox = $data['is_sand_box'];

        if ($isSandbox) {
            $endpoint = 'https://sandbox.itunes.apple.com/verifyReceipt';
        } else {
            $endpoint = 'https://buy.itunes.apple.com/verifyReceipt';
        }

        $postData = json_encode(
            array('receipt-data' => $receipt)
        );

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        $response = curl_exec($ch);
        $errno = curl_errno($ch);
        $errmsg = curl_error($ch);
        curl_close($ch);

        if ($errno != 0) {
            return $this->createErrorResponse('error', '充值失败！'.$errno);
        }

        $data = json_decode($response, true);
        if (empty($data)) {
            return $this->createErrorResponse('error', '充值验证失败');
        }

        if ($data['status'] == 21007) {
            //sandbox receipt
            return $this->requestReceiptData($userId, $amount, $receipt, $transactionId, true);
        }

        if (!isset($data['status']) || $data['status'] != 0) {
            return $this->createErrorResponse('error', '充值失败！状态码 :'.$data['status']);
        }

        if ($data['status'] == 0) {
            if (isset($data['receipt']) && !empty($data['receipt']['in_app'])) {
                $inApp = false;

                if ($transactionId) {
                    foreach ($data['receipt']['in_app'] as $value) {
                        if (ArrayToolkit::requireds($value, array('transaction_id', 'quantity', 'product_id')) && $value['transaction_id'] == $transactionId) {
                            $inApp = $value;
                            break;
                        }
                    }
                } else {
                    //兼容没有transactionId的模式
                    $inApp = $data['receipt']['in_app'][0];
                }

                if (!$inApp) {
                    return $this->createErrorResponse('error', 'receipt校验失败：找不到对应的transaction_id');
                }

                $token = 'iap-'.$inApp['transaction_id'];
                $quantity = $inApp['quantity'];
                $productId = $inApp['product_id'];

                try {
                    $calculatedAmount = $this->calculateBoughtAmount($productId, $quantity);

                    // if ($calculatedAmount != $amount) {
                    //     throw new \RuntimeException("金额校验错误，充值失败");
                    // }

                    $status = $this->buyCoinByIAP($userId, $calculatedAmount, 'none', $token);
                } catch (\Exception $e) {
                    return $this->createErrorResponse('error', $e->getMessage());
                }

                return array(
                    'status' => $status,
                );
            }
        }

        return array(
            array(
                'status' => 'paid',
                'cash_flow' => $data['trade_no'],
                'paid_time' => $this->getPaidTime($data),
                'pay_amount' => (int)($data['total_fee']*100),
                'cash_type' => 'RMB',
                'trade_sn' => $data['out_trade_no'],
                'attach' => !empty($data['extra_common_param']) ? json_decode($data['extra_common_param'], true) : array(),
                'notify_data' => $data,
            ),
            'success'
        );
    }

    private function calculateBoughtAmount($productId, $quantity)
    {
        $registeredProducts = $this->getSettingService()->get('mobile_iap_product', array());
        if (empty($registeredProducts[$productId])) {
            throw new \RuntimeException('该商品信息未与苹果服务器同步，充值失败');
        }

        return $registeredProducts[$productId]['price'] * $quantity;
    }

    public function createTrade($data)
    {
        // TODO: Implement createTrade() method.
    }

    public function applyRefund($data)
    {
        // TODO: Implement applyRefund() method.
    }

    public function queryTrade($trade)
    {
        // TODO: Implement queryTrade() method.
    }

    public function converterRefundNotify($data)
    {
        // TODO: Implement converterRefundNotify() method.
    }
}