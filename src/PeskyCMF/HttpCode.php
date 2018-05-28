<?php

namespace PeskyCMF;

class HttpCode {
    const OK = 200;
    const INVALID = 400;
    const UNAUTHORISED = 401;
    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    const NOT_ACCEPTABLE = 406;
    const CONFLICT = 409;
    const CANNOT_PROCESS = 422;

    const SERVER_ERROR = 500;

    const MOVED_PERMANENTLY = 301;
    const MOVED_TEMPORARILY = 302;
}