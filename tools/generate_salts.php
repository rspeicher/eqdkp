<?php

define('EQDKP_INC', true);

require_once('./../upload/includes/functions.php');

for ( $i = 0; $i < 30; $i++ )
{
    echo generate_salt() . PHP_EOL;
}