:: Run easy-coding-standard (ecs) via this batch file inside your IDE e.g. PhpStorm (Windows only)
:: Install inside PhpStorm the  "Batch Script Support" plugin
cd..
cd..
cd..
cd..
cd..
cd..
:: templates
vendor\bin\ecs check vendor/markocupic/contao-crm-bundle/src/Resources/contao/templates --config vendor/markocupic/contao-crm-bundle/.ecs/config/template.yaml
::
cd vendor/markocupic/contao-crm-bundle/.ecs./batch/fix