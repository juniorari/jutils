<?php
require __DIR__ . '/vendor/autoload.php';

use JuniorAri\Utils\JUtils;

$utils = new JUtils();
echo $utils->capitalizeName("josé antônio da silva");
echo "\n";
print_r(JUtils::searchCEP('69098272'));