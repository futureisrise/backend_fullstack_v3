<?php


function lang($line)
{
    $line = get_instance()->lang->line($line);
    return $line;
}

