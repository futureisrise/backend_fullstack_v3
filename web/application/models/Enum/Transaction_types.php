<?php
namespace Model\Enum;
use App;
use System\Emerald\Emerald_enum;

class Transaction_types extends Emerald_enum
{
    const INCOME = 'INCOME';//income transaction
    const WITHDROW = 'WITHDROW';//outcome transaction
    const LIKES = 'LIKES';//buy likes transaction
    const BOOSTERPACK = 'BOOSTERPACK';//buy bootstrap transaction


}
