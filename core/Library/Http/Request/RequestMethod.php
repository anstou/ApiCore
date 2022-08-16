<?php

namespace ApiCore\Library\Http\Request;

enum RequestMethod
{
    case GET;
    case POST;
    case PUT;
    case DELETE;
}