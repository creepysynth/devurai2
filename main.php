<?php
require 'periods.php';

$dates1 = file_get_contents('dates1.json');
$dates2 = file_get_contents('dates2.json');
$dates3 = file_get_contents('dates3.json');
$dates4 = file_get_contents('dates4.json');

echo periods($dates1);
echo periods($dates2);
echo periods($dates3);
echo periods($dates4);