<?php

declare(strict_types=1);

/*
 * This file is part of Contao CRM Bundle.
 *
 * (c) Marko Cupic 2022 <m.cupic@gmx.ch>
 * @license GPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-crm-bundle
 */

/**
 * Global operations
 */
$GLOBALS['TL_LANG']['tl_crm_service']['new'] = ['Neues Projekt anfangen', 'Legen Sie ein neues Projekt an.'];

/**
 * Operations
 */
$GLOBALS['TL_LANG']['tl_crm_service']['generateInvoiceDocx'] = ['Rechnung/Kostenvoranschlag generieren (docx)', 'Rechnung/Kostenvoranschlag generieren (docx)'];
$GLOBALS['TL_LANG']['tl_crm_service']['generateInvoicePdf'] = ['Rechnung/Kostenvoranschlag generieren (pdf)', 'Rechnung/Kostenvoranschlag generieren (pdf)'];
$GLOBALS['TL_LANG']['tl_crm_service']['edit'] = ['Das Projekt bearbeiten', 'Das Projekt bearbeiten'];
$GLOBALS['TL_LANG']['tl_crm_service']['copy'] = ['Das Projekt duplizieren', 'Das Projekt duplizieren'];
$GLOBALS['TL_LANG']['tl_crm_service']['delete'] = ['Das Projekt löschen', 'Das Projekt löschen'];
$GLOBALS['TL_LANG']['tl_crm_service']['show'] = ['Das Projekt ansehen', 'Das Projekt ansehen'];

/*
 * Legends
 */
$GLOBALS['TL_LANG']['tl_crm_service']['invoice_legend'] = 'Rechnungsdetails';
$GLOBALS['TL_LANG']['tl_crm_service']['state_legend'] = 'Abschluss';
$GLOBALS['TL_LANG']['tl_crm_service']['service_legend'] = 'Leistung/Auftragsdetails';

/*
 * Fields
 */
$GLOBALS['TL_LANG']['tl_crm_service']['title'] = ['Titel', ''];
$GLOBALS['TL_LANG']['tl_crm_service']['projectDateStart'] = ['Projektstart', ''];
$GLOBALS['TL_LANG']['tl_crm_service']['toCustomer'] = ['Kunde', ''];
$GLOBALS['TL_LANG']['tl_crm_service']['description'] = ['Projektinformationen', ''];
$GLOBALS['TL_LANG']['tl_crm_service']['servicePositions'] = ['Aufgaben', ''];
$GLOBALS['TL_LANG']['tl_crm_service']['price'] = ['Preis', ''];
$GLOBALS['TL_LANG']['tl_crm_service']['currency'] = ['Währung', ''];
$GLOBALS['TL_LANG']['tl_crm_service']['invoiceType'] = ['Rechnungsstatus/Typ', ''];
$GLOBALS['TL_LANG']['tl_crm_service']['invoiceDate'] = ['Rechnungsdatum', ''];
$GLOBALS['TL_LANG']['tl_crm_service']['invoiceNumber'] = ['Rechnungsnummer', ''];
$GLOBALS['TL_LANG']['tl_crm_service']['defaultInvoiceText'] = ['Standard-Rechnungstext', ''];
$GLOBALS['TL_LANG']['tl_crm_service']['alternativeInvoiceText'] = ['Alternativer Rechnungstext', ''];
$GLOBALS['TL_LANG']['tl_crm_service']['crmInvoiceTpl'] = ['Rechnungsvorlage/docx-Template', ''];
$GLOBALS['TL_LANG']['tl_crm_service']['paid'] = ['Rechnung vom Kunden bezahlt/Projekt abgeschlossen', ''];
$GLOBALS['TL_LANG']['tl_crm_service']['amountReceivedDate'] = ['Betrag erhalten am', ''];
// Multi column wizard
$GLOBALS['TL_LANG']['tl_crm_service']['position_item'] = ['Leistungsbeschrieb', ''];
$GLOBALS['TL_LANG']['tl_crm_service']['position_quantity'] = ['Anzahl', ''];
$GLOBALS['TL_LANG']['tl_crm_service']['position_price'] = ['Preis', ''];
$GLOBALS['TL_LANG']['tl_crm_service']['position_unit'] = ['Einheit', ''];

/*
 * References
 */
$GLOBALS['TL_LANG']['tl_crm_service']['invoiceTypeReference']['calculation'] = ['Status: Kostenvoranschlag dem Kunden zugestellt.', 'Kostenvoranschlag'];
$GLOBALS['TL_LANG']['tl_crm_service']['invoiceTypeReference']['invoiceDelivered'] = ['Status: Rechnung dem Kunden zugestellt, Betrag aber noch nicht erhalten.', 'Rechnung'];
$GLOBALS['TL_LANG']['tl_crm_service']['invoiceTypeReference']['invoiceNotDelivered'] = ['Status: Rechnung dem Kunden noch nicht zugestellt.', 'Rechnung noch nicht zugestellt'];
