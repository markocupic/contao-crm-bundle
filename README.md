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

## Standard Rechnungsdatei updatesicher überschreiben
Um das Standard-Rechnungs-Template in "vendor/markocupic/contao-crm-bundle/src/Resources/contao/templates"
mit einem Custom-Template updatesicher zu ersetzen, muss der Pfad zur docx-Datei mit dem entsprechenden Parameter in der "config/config.yml" angegeben werden.

```
markocupic_contao_crm:
  docx_invoice_template: 'files/crm/templates/docx/my_custom_invoice.docx'

```
