:: Run easy-coding-standard (ecs) via this batch file inside your IDE e.g. PhpStorm (Windows only)
:: Install inside PhpStorm the  "Batch Script Support" plugin
cd..
cd..
cd..
cd..
cd..
cd..
:: src
vendor\bin\ecs check vendor/markocupic/contao-crm-bundle/src --config vendor/markocupic/contao-crm-bundle/.ecs/config/default.php
:: tests
vendor\bin\ecs check vendor/markocupic/contao-crm-bundle/tests --config vendor/markocupic/contao-crm-bundle/.ecs/config/default.php
:: legacy
vendor\bin\ecs check vendor/markocupic/contao-crm-bundle/src/Resources/contao --config vendor/markocupic/contao-crm-bundle/.ecs/config/legacy.php
:: templates
vendor\bin\ecs check vendor/markocupic/contao-crm-bundle/src/Resources/contao/templates --config vendor/markocupic/contao-crm-bundle/.ecs/config/template.php
::
cd vendor/markocupic/contao-crm-bundle/.ecs./batch/fix
