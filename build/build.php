<?php

// smtp.codes Build Script

// Create the /docs directory if it doesn’t already exist
$docs_dir = __DIR__.'/../docs';
if(!is_dir($docs_dir)) {
	mkdir($docs_dir, 0755, true);
}

$html = <<<HTML
Hello, world!
HTML;

file_put_contents($docs_dir.'/index.html', $html);

echo "Build complete.\n";
