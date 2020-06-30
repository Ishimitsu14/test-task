<?php
include_once 'functions.php';

try {
    echo parseUrl('https://www.somehost.ru/test/index.html?param1=4&param2=3&param3=2&param4=1&param5=3');
} catch (Exception $e) {
    echo $e;
}

echo "\n";

try {
    print_r(loadUsersData('4,5,6,8'));
}catch (Exception $e) {
    echo $e;
}