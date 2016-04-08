<?php

namespace PeskyCMF;

use DebugBar\DataCollector\PDO\TraceablePDO;

class PeskyOrmPdoTracer extends TraceablePDO {

    protected function profileCall($method, $sql, array $args) {
        if (!preg_match('%^(COMMIT|BEGIN)%i', $sql)) {
            return parent::profileCall($method, $sql, $args);
        } else {
            return call_user_func_array(array($this->pdo, $method), $args);
        }
    }
}