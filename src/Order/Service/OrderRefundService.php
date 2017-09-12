<?php

namespace Codeages\Biz\Framework\Order\Service;

interface OrderRefundService
{
    public function searchRefunds($conditions, $orderby, $start, $limit);

    public function countRefunds($conditions);

<<<<<<< HEAD
    public function getById($id);
=======
    public function getOrderRefundById($id);
>>>>>>> b25661c6a11085df0643f7a396c769d13c3090af
}