<?php

/**
 * This is XML code for OpenSearch.
 *
 * @deprecated This functionality will be removed in phpMyFAQ 3.1
 *
 * This Source Code Form is subject to the terms of the Mozilla Public License,
 * v. 2.0. If a copy of the MPL was not distributed with this file, You can
 * obtain one at http://mozilla.org/MPL/2.0/.
 *
 * @package phpMyFAQ
 * @author Thorsten Rinne <thorsten@phpmyfaq.de>
 * @copyright 2006-2020 phpMyFAQ Team
 * @license http://www.mozilla.org/MPL/2.0/ Mozilla Public License Version 2.0
 * @link https://www.phpmyfaq.de
 * @since 2006-11-19
 */

define('IS_VALID_PHPMYFAQ', null);

require __DIR__.'/src/Bootstrap.php';
require PMF_LANGUAGE_DIR.'/'.$faqConfig->get('main.language');

$baseUrl = $faqConfig->getDefaultUrl();
$searchUrl = $baseUrl.'index.php?action=search';

$xml = new XMLWriter();
$xml->openMemory();
$xml->setIndent(true);
$xml->startDocument('1.0', 'utf-8');
$xml->startElement('OpenSearchDescription');
$xml->writeAttribute('xmlns', 'http://a9.com/-/spec/opensearch/1.1/');

$xml->writeElement('ShortName', $faqConfig->get('main.titleFAQ'));
$xml->writeElement('Description', $faqConfig->get('main.metaDescription'));
$xml->startElement('Url');
$xml->writeAttribute('type', 'text/html');
$xml->writeAttribute('template', $searchUrl.'&search={searchTerms}');
$xml->endElement();
$xml->writeElement('Language', $PMF_LANG['metaLanguage']);
$xml->writeElement('OutputEncoding', 'utf-8');
$xml->writeElement('Contact', $faqConfig->get('main.administrationMail'));
$xml->startElement('Image');
$xml->writeAttribute('height', 16);
$xml->writeAttribute('width', 16);
$xml->writeAttribute('type', 'image/png');
$xml->text($baseUrl.'/assets/img/pmfsearch.png');

$xml->endDocument();

header('Content-type: text/xml');
print $xml->outputMemory(true);
