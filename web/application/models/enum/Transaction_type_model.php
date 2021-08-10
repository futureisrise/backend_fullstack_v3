<?php
namespace Model\Enum;
use \System\Emerald\Emerald_enum;

class Transaction_type_model extends Emerald_enum
{
    const WITHDRAW = 0;
    const TOP_UP = 1;
}
