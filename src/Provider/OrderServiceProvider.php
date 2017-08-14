<?php

namespace Codeages\Biz\Framework\Provider;

use Codeages\Biz\Framework\Order\Status\CloseStatus;
use Codeages\Biz\Framework\Order\Status\ConsignedStatus;
use Codeages\Biz\Framework\Order\Status\CreatedStatus;
use Codeages\Biz\Framework\Order\Status\FinishStatus;
use Codeages\Biz\Framework\Order\Status\OrderContext;
use Codeages\Biz\Framework\Order\Status\PaidStatus;
use Codeages\Biz\Framework\Order\Status\SignedFailStatus;
use Codeages\Biz\Framework\Order\Status\SignedStatus;
use Codeages\Biz\Framework\Order\Status\WaitConsignStatus;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class OrderServiceProvider implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        $biz['migration.directories'][] = dirname(dirname(__DIR__)).'/migrations/order';
        $biz['autoload.aliases']['Order'] = 'Codeages\Biz\Framework\Order';

        $biz['order_context'] = function ($biz) {
            return new OrderContext($biz);
        };

        $biz['order_status.consigned'] = function ($biz) {
            return new ConsignedStatus($biz);
        };

        $biz['order_status.wait_consign'] = function ($biz) {
            return new WaitConsignStatus($biz);
        };

        $biz['order_status.created'] = function ($biz) {
            return new CreatedStatus($biz);
        };

        $biz['order_status.paid'] = function ($biz) {
            return new PaidStatus($biz);
        };

        $biz['order_status.closed'] = function ($biz) {
            return new CloseStatus($biz);
        };

        $biz['order_status.signed'] = function ($biz) {
            return new SignedStatus($biz);
        };

        $biz['order_status.signed_fail'] = function ($biz) {
            return new SignedFailStatus($biz);
        };

        $biz['order_status.finish'] = function ($biz) {
            return new FinishStatus($biz);
        };
    }
}
