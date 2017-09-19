<?php

namespace Codeages\Biz\Framework\Pay\Payment;


use Codeages\Biz\Framework\Util\ArrayToolkit;

class IapGetway extends AbstractGetway
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

        return $this->requestReceiptData($data);
    }

    private function requestReceiptData($notifyData)
    {
        $userId = $notifyData['user_id'];
        $amount = $notifyData['amount'];
        $receipt = $notifyData['receipt'];
        $transactionId = $notifyData['transaction_id'];
        $isSandbox = $notifyData['is_sand_box'];

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
        curl_close($ch);

        if ($errno != 0) {
            return array(
                array(
                    'msg' => '充值失败！'.$errno
                ),
                'failture'
            );
        }

        $data = json_decode($response, true);
        if (empty($data)) {
            return array(
                array(
                    'msg' => '充值验证失败'
                ),
                'failture'
            );
        }

        if ($data['status'] == 21007) {
            $notifyData['is_sand_box'] = true;
            return $this->requestReceiptData($notifyData);
        }

        if (!isset($data['status']) || $data['status'] != 0) {
            return array(
                array(
                    'msg' => '充值失败！状态码 :'.$data['status']
                ),
                'failture'
            );
        }

        if ($data['status'] == 0) {
            if (isset($data['receipt']) && !empty($data['receipt']['in_app'])) {
                $inApp = false;

                if ($transactionId) {
                    foreach ($data['receipt']['in_app'] as $value) {
                        if (ArrayToolkit::requireds($value, array('transaction_id', 'quantity', 'product_id'))
                            && $value['transaction_id'] == $transactionId) {
                            $inApp = $value;
                            break;
                        }
                    }
                } else {
                    $inApp = $data['receipt']['in_app'][0];
                }

                if (!$inApp) {
                    return array(
                        array(
                            'msg' => 'receipt校验失败：找不到对应的transaction_id'
                        ),
                        'failture'
                    );
                }

                return array(
                    array(
                        'status' => 'paid',
                        'pay_amount' => $amount*100,
                        'cash_flow' => $inApp['transaction_id'],
                        'paid_time' => $inApp['purchase_date'],
                        'quantity' => $inApp['quantity'],
                        'product_id' => $inApp['product_id'],
                        'attach' => array(
                            'user_id' => $userId
                        )
                    ),
                    'success'
                );
            }
        }

        return array(
            array(),
            'failture'
        );
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