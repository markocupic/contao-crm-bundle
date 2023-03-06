:: Run easy-coding-standard (ecs) via this batch file inside your IDE e.g. PhpStorm (Windows only)
:: Install inside PhpStorm the  "Batch Script Support" plugin
cd..
cd..
cd..
cd..
cd..
cd..
php vendor\bin\ecs check vendor/markocupic/contao-crm-bundle/src --fix --config vendor/markocupic/contao-crm-bundle/tools/ecs/config.php
php vendor\bin\ecs check vendor/markocupic/contao-crm-bundle/contao --fix --config vendor/markocupic/contao-crm-bundle/tools/ecs/config.php
php vendor\bin\ecs check vendor/markocupic/contao-crm-bundle/config --fix --config vendor/markocupic/contao-crm-bundle/tools/ecs/config.php
:: php vendor\bin\ecs check vendor/markocupic/contao-crm-bundle/templates --fix --config vendor/markocupic/contao-crm-bundle/tools/ecs/config.php
php vendor\bin\ecs check vendor/markocupic/contao-crm-bundle/tests --fix --config vendor/markocupic/contao-crm-bundle/tools/ecs/config.php


