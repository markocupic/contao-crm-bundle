![Alt text](src/Resources/public/logo.png?raw=true "logo")


# Contao CRM Bundle
Dieses Contao Bundle bietet eine minimale CRM Funktionalität und wird für den privaten Gebrauch verwendet. 

Es können Kunden erfasst werden und diesen Aufträge zugewiesen werden. 
Nach Auftragsende kann eine Rechnung im docx- oder pdf-Format ([Cloudconvert](https://cloudconvert.com/api/v2#overview) API key nötig) gedruckt werden. 

Cloudconvert verlangt einen API Key, welcher in der Datei config/config.yml eingetragen werden sollte. Danach unbedingt den Cache neu aufbauen.

```
markocupic_contao_crm:
  cloudconvert_api_key: 'Nrccfwsdfsdfsdfsdf5gSTprM0REEKjqg'

```

