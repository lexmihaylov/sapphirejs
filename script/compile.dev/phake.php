<?php
$phar = new Phar('../compile.phar',0 , 'compile.phar');
$phar->buildFromDirectory('./src');

$phar->setStub(file_get_contents('stub.php'));
?>
