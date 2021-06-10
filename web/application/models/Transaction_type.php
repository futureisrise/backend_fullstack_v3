<?php

namespace Model;

use System\Emerald\Emerald_enum;

class Transaction_type extends Emerald_enum
{
    const MONEY_IN = 'money_in';
    const MONEY_OUT = 'money_out';
}