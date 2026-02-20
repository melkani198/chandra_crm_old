<?php

class RequestContext {
    private static $traceId = null;

    public static function setTraceId($traceId) {
        self::$traceId = $traceId;
    }

    public static function getTraceId() {
        return self::$traceId;
    }
}
