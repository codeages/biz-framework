<?php

namespace Codeages\Biz\Framework\Scheduler\Service;

interface SchedulerService
{
    public function schedule($jobDetail);

    public function execute();

    public function deleteJobDetailByPoolAndName($pool, $name);

    public function deleteJobDetail($id);

    public function clearJobDetails();
}
