<?php

namespace App\Enums;

enum HttpStatus
{

  const OK = 200;
  const CREATED = 201;
  const ACCEPTED = 202;
  const NO_CONTENT = 204;
  const MOVED_PERMANENTLY = 301;
  const FOUND = 302;
  const SEE_OTHER = 303;
  const BAD_REQUEST = 400;
  const UNAUTHORIZED = 401;
  const FORBIDDEN = 403;
  const NOT_FOUND = 404;
  const METHOD_NOT_ALLOWED = 405;
  const CONFLICT = 409;
  const INTERNAL_SERVER_ERROR = 500;
  const BAD_GATEWAY = 502;
  const SERVICE_UNAVAILABLE = 503;

}
