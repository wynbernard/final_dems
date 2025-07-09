<?php
header('Content-Type: application/json');

// Function to scrape disaster news from a sample news website
function fetch_disaster_news()
{
	$url = 'https://news.abs-cbn.com/list/tag/disaster'; // Replace with a page that lists disaster-related articles

	// Initialize cURL
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0'); // Some websites block curl without user-agent
	$html = curl_exec($ch);
	curl_close($ch);

	// If the HTML couldn't be fetched
	if (!$html) {
		return [
			"status" => "error",
			"message" => "Failed to fetch news content."
		];
	}

	// Load HTML into DOM
	libxml_use_internal_errors(true); // Avoid DOM parsing errors from malformed HTML
	$dom = new DOMDocument();
	$dom->loadHTML($html);
	libxml_clear_errors();

	$xpath = new DOMXPath($dom);

	// XPath to extract news titles/descriptions
	$articles = $xpath->query('//div[contains(@class, "node__content")]');

	$news_items = [];
	foreach ($articles as $article) {
		$titleNode = $xpath->query('.//h3', $article)->item(0);
		$descNode = $xpath->query('.//p', $article)->item(0);

		$title = $titleNode ? trim($titleNode->textContent) : '';
		$desc = $descNode ? trim($descNode->textContent) : '';

		if ($title !== '') {
			$news_items[] = $title . ' - ' . $desc;
		}

		// Limit to 5 headlines
		if (count($news_items) >= 5) {
			break;
		}
	}

	return [
		"status" => "success",
		"latest" => $news_items ?: ["No disaster news found."]
	];
}

// Output the result
echo json_encode(fetch_disaster_news());
