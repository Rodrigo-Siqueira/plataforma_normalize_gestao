<?php
$ano = date('Y');

$rodape = file_get_contents(TEMPLATE_PATH . 'footer.html');
$rodape = str_replace('{ANO}', htmlspecialchars($ano, ENT_QUOTES, 'UTF-8'), $rodape);

print $rodape;