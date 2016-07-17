<?php

namespace Codeages\Biz\Framework\Dao;

interface GeneralDaoInterface extends DaoInterface
{
    public function create($fields);

    public function update($id, array $fields);

    public function delete($id);

    public function get($id);

    public function search($conditions, $orderBy, $start, $limit);

    public function count($conditions);

    public function wave(array $ids, array $diffs);
}
