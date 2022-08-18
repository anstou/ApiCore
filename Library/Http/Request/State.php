<?php

namespace ApiCore\Library\Http\Request;

use ApiCore\Library\InterfaceWarehouse\DataWarehouse;
use ReturnTypeWillChange;

/**
 * 只会存在于当前这次请求中
 * 随着请求结束消失
 */
class State extends DataWarehouse
{


}