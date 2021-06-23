<?php

namespace Model;

use System\Emerald\Emerald_enum;

class Transaction_type_model extends Emerald_enum
{
    const MONEY_IN  = '1';
    const MONEY_OUT = '0';
}