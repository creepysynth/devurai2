<?php
require 'Periods.php';

$dates1 = file_get_contents('dates1.json');
$dates2 = file_get_contents('dates2.json');
$dates3 = file_get_contents('dates3.json');
$dates4 = file_get_contents('dates4.json');

echo (new Periods($dates1))->humanReadable();
echo (new Periods($dates2))->humanReadable();
echo (new Periods($dates3))->humanReadable();
echo (new Periods($dates4))->humanReadable();