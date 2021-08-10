<?php
namespace Model\Enum;
use \System\Emerald\Emerald_enum;

class Transaction_info_model extends Emerald_enum
{
    const BALANCE = 'Balance';
    const BALANCE_TOP_UP = 'Пополнение баланса';
    const LIKE = 'Like';
}
