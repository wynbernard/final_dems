<?php
$url = 'https://bagong.pagasa.dost.gov.ph/weather/weather-advisory';

$html = file_get_contents($url);

libxml_use_internal_errors(true);
$doc = new DOMDocument();
$doc->loadHTML($html);
libxml_clear_errors();

$xpath = new DOMXPath($doc);
$items = $xpath->query('//div[contains(@class, "panel-body")]//p');

$data = [];

$keywords = ['typhoon', 'storm', 'signal', 'tropical', 'low pressure', 'rainfall', 'gale warning'];

foreach ($items as $item) {
	$text = trim($item->textContent);
	if (!empty($text)) {
		foreach ($keywords as $word) {
			if (stripos($text, $word) !== false) {
				$data[] = $text;
				break;
			}
		}
	}
}

header('Content-Type: application/json');
echo json_encode([
	"status" => "success",
	"type" => "Phenomenal Report",
	"latest" => $data,
	"source" => $url
]);
