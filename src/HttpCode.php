<?php

declare(strict_types=1);

namespace PeskyCMF;

class HttpCode {
    public const OK = 200;
    public const INVALID = 400;
    public const UNAUTHORISED = 401;
    public const FORBIDDEN = 403;
    public const NOT_FOUND = 404;
    public const NOT_ALLOWED = 405;
    public const NOT_ACCEPTABLE = 406;
    public const TIMEOUT = 408;
    public const CONFLICT = 409;
    public const CANNOT_PROCESS = 422;
    public const UPGRADE_REQUIRED = 426;
    public const TOO_MANY_REQUESTS = 429;

    public const SERVER_ERROR = 500;
    public const NOT_IMPLEMENTED = 501;
    public const BAD_GATEWAY = 502;
    public const MAINTENANCE = 503;
    public const GATEWAY_TIMEOUT = 504;
    public const INSUFFICIENT_STORAGE = 507;

    public const MOVED_PERMANENTLY = 301;
    public const MOVED_TEMPORARILY = 302;
    public const NOT_MODIFIED = 304;
    public const TEMPORARY_REDIRECT = 307;
    public const PERMANENT_REDIRECT = 308;
}