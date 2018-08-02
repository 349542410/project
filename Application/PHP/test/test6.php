<?php
$a = '23.33';
$amount = round(floatval($a) * 100);
// $amount = sprintf("%.2f", $amount);
echo $amount;