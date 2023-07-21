<?php

function get_last_post_link($channel, $search){

	$url = "https://t.me/s/".$channel."?q=".$search;
	$headers = [
		"User-Agent" => "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:91.0) Gecko/20100101 Firefox/91.0"
	];

	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$response = curl_exec($ch);

	if ($response === false) {
		throw new Exception('CURL Error: ' . curl_error($ch));
	}
	else {
		// Create a new DOM Document
		$doc = new DOMDocument;

		// Properly handle UTF-8 encoding
		@$doc->loadHTML(mb_convert_encoding($response, 'HTML-ENTITIES', 'UTF-8'));

		// Create a new XPath object
		$xpath = new DOMXPath($doc);

		// Query all divs with class "tgme_widget_message"
		$result = $xpath->query('//div[contains(concat(" ", normalize-space(@class), " "), " tgme_widget_message ")]');

		// Get the last match and take an attribute (href for example)
		if($result->length > 0) {
			$lastMatch = $result->item($result->length - 1);
			$data_post = $lastMatch->getAttribute('data-post');
		}
	}

	curl_close($ch);

	return $data_post;
}