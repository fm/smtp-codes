<?php

// smtp.codes Build Script

// Create the /docs directory if it doesn’t already exist
$docs_dir = __DIR__.'/../docs';
if(!is_dir($docs_dir)) {
	mkdir($docs_dir, 0755, true);
}

$code_count = count(__DIR__.'/../codes/*');

$html = '<h1>smtp.codes</h1>
<p>Processed '.$code_count.' codes.</p>';

file_put_contents($docs_dir.'/index.html', $html);

echo "Build complete.\n";
