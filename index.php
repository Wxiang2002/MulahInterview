<?php

$url = "https://www.theverge.com/";

function fetchHeadlines($url) {
    // Get the HTML content of the URL
    $htmlContent = file_get_contents($url);
    if ($htmlContent === FALSE) {
        return [];
    }

    // Create a new DOMDocument
    $dom = new DOMDocument();

    // Suppress errors due to malformed HTML
    libxml_use_internal_errors(true);

    // Load the HTML into the DOMDocument
    $dom->loadHTML($htmlContent);

    // Restore error handling
    libxml_clear_errors();

    // Create a new DOMXPath
    $xpath = new DOMXPath($dom);

    // Query for headline elements
    $headlineNodes = $xpath->query('//h2[contains(@class, "font-polysans")]/a');
    $datetimeNodes = $xpath->query('//div[contains(@class, "text-gray-63")]/time');

    // Array to hold the headlines
    $headlines = [];

    // Iterate over the headline elements
    foreach ($headlineNodes as $index => $headlineNode) {
        $title = trim($headlineNode->textContent);
        $href = $headlineNode->getAttribute('href');
        $datetime = $datetimeNodes->item($index) ? $datetimeNodes->item($index)->getAttribute('datetime') : null;

        // Convert relative URL to absolute URL
        if (strpos($href, 'http') !== 0) {
            $href = rtrim($url, '/') . '/' . ltrim($href, '/');
        }

        // Filter by date
        if ($datetime && strtotime($datetime) >= strtotime('2022-01-01')) {
            $headlines[] = ['title' => $title, 'url' => $href, 'datetime' => $datetime];
        }
    }

    return $headlines;
}

$headlines = fetchHeadlines($url);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Headlines from The Verge</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            padding: 20px;
        }
        ul {
            list-style-type: none;
        }
        li {
            margin: 10px 0;
            padding: 10px;
            border: 1px solid #ddd;
            background-color: #fff;
        }
        a {
            color: #333;
            text-decoration: none;
        }
        a:hover {
            color: #007BFF;
        }
    </style>
</head>
<body>
    <h1>List of Headlines posted on and after January 1, 2022</h1>
    <ul>
        <?php foreach ($headlines as $headline): ?>
            <li><a href="<?= htmlspecialchars($headline['url']) ?>" target="_blank"><?= htmlspecialchars($headline['title']) ?></a> - <?= htmlspecialchars($headline['datetime']) ?></li>
        <?php endforeach; ?>
    </ul>
</body>
</html>
