<?php

// smtp.codes Build Script

// Create the /docs directory if it doesn’t already exist
$docs_dir = __DIR__.'/../docs';
if(!is_dir($docs_dir)) {
	mkdir($docs_dir, 0755, true);
}

function clear_directory($dir) {
	if (!is_dir($dir)) return;
	$items = array_diff(scandir($dir), ['.', '..']);
	foreach ($items as $item) {
		$path = $dir . DIRECTORY_SEPARATOR . $item;
		if (is_dir($path)) {
			clear_directory($path);
			rmdir($path);
		}
		else {
			unlink($path);
		}
	}
}

$start = microtime(1);
echo 'Building!'."\n";

// Check JSON formatting
foreach(glob(__DIR__.'/../data/codes/*') as $file) {
	$code = json_decode(file_get_contents($file), 1);
	if(!json_validate(file_get_contents($file))) {
		die("The file $file is not valid JSON. Please correct the format and try again.\n");
	}
}

$file = __DIR__.'/../data/contributors.json';
if(!json_validate(file_get_contents($file))) {
	die("The file $file is not valid JSON. Please correct the format and try again.\n");
}

$file = __DIR__.'/../data/providers.json';
if(!json_validate(file_get_contents($file))) {
	die("The file $file is not valid JSON. Please correct the format and try again.\n");
}

clear_directory($docs_dir);
echo 'Cleared existing /docs directory.'."\n";

mkdir($docs_dir.'/assets', 0755, true);

foreach(glob(__DIR__.'/../site/*') as $file) {
	$filepath = explode('/', $file);
	copy($file, __DIR__.'/../docs/assets/'.end($filepath));
}

// Load template
$template = file_get_contents(__DIR__.'/../site/template.html');

// Initialize some variables
$all_codes = [];
$all_providers = [];

// Build code pages
foreach(glob(__DIR__.'/../data/codes/*') as $file) {
	
	// Initialize our content
	$content = null;
	
	// Load the code JSON
	$data = json_decode(file_get_contents($file), 1);
	
	// Sort providers alphabetically
	array_multisort(array_column($data['providers'], 'name'), SORT_ASC, $data['providers']);
	
	$code = $data['code'];
	$all_codes[] = $code;
	
	if(substr($code, 0, 1) == 4 || substr($code, 0, 1) == 5) {
		$code_type = 'Error ';
	}
	else {
		$code_type = null;
	}
	
	// Set title and description
	$title = 'SMTP '.$code_type.'Code '.$code;
	$description = $data['description'];
	
	// Define page content
	$content .= '<h1>'.$title.'</h1>'."\n";
	$content .= '<p>'.$description.'</p>'."\n";
	
	$provider_list = [];
	foreach($data['providers'] as $provider) {
		$provider_list[] = '<a href="#'.$provider['id'].'">'.$provider['name'].'</a>';
	}
	
	$provider_list = implode(' &#183; ', $provider_list);
	$content .= '<h2>Providers</h2>'."\n";
	$content .= '<p>'.$provider_list.'</p>'."\n";
	
	$content .= '<dl>'."\n";
	
	foreach($data['providers'] as $provider) {
		$content .= '<div class="card">'."\n";
		$content .= '<dt id="'.$provider['id'].'">'.$provider['name'].'<span class="top"><a href="#">↑</a></span></dt>'."\n";
		
		foreach($provider['responses'] as $response) {
			if(isset($response['description']) && $response['description'] !== '') {
				$description = '<p class="info">'.$response['description'].'</p>';
			}
			else {
				$description = null;
			}
			
			if(isset($response['links']) && count($response['links']) > 0) {
				$links = '<ul>'."\n";
				foreach($response['links'] as $link) {
					$links .= '<li><a href="'.$link.'">'.$link.'</a></li>'."\n";
				}
				$links .= '</ul>'."\n";
				
			}
			else {
				$links = null;
			}
			
			if(is_null($description) && is_null($links)) {
				$content .= '<dd>'.htmlentities($response['response']).$description.$links.'</dd>'."\n";
			}
			else {
				$content .= '<dd><details><summary>'.htmlentities($response['response']).'</summary>'.$description.$links.'</details></dd>'."\n";
			}
		}
		
		$content .= '</div>'."\n";
	}
	
	$content .= '</dl>'."\n";
	
	// Perform template substitutions and prepare the output
	$output = $template;
	$output = str_replace('{title}', $title, $output);
	$output = str_replace('{description}', $title, $output);
	$output = str_replace('{content}', $content, $output);
	
	// Create this code’s directory if it doesn’t exist
	if(!is_dir(__DIR__.'/../docs/'.$code)) {
		mkdir(__DIR__.'/../docs/'.$code, 0755, true);
	}
	
	// Write the code page
	file_put_contents(__DIR__.'/../docs/'.$code.'/index.html', $output);
	
}

// Build provider pages

$providers = file_get_contents(__DIR__.'/../data/providers.json');
$providers = json_decode($providers, 1);

foreach($providers as $provider) {
	
	// Initialize our content
	$content = null;
	
	$all_providers[$provider['id']] = $provider['name'];
	
	// Set title and description
	$title = $provider['name'];
	$description = $provider['description'];
	
	// Define page content
	$content .= '<h1>'.$title.'</h1>'."\n";
	$content .= '<p>'.$description.'</p>'."\n";
	
	if(isset($provider['links']) && count($provider['links']) > 0) {
		$content .= '<h2>Links</h2>'."\n";
		$content .= '<ul>'."\n";
		foreach($provider['links'] as $link) {
			$content .= '<li><a href="'.$link.'">'.$link.'</a></li>'."\n";
		}
		$content .= '</ul>'."\n";
	}
	
	if(isset($provider['domains']) && count($provider['domains']) > 0) {
		$content .= '<h2>Domains</h2>'."\n";
		$content .= '<ul>'."\n";
		foreach($provider['domains'] as $domain) {
			$content .= '<li>'.$domain.'</li>'."\n";
		}
		$content .= '</ul>'."\n";
	}
	
	$codes = [];
	
	// Add codes
	foreach(glob(__DIR__.'/../data/codes/*') as $file) {
		$code = json_decode(file_get_contents($file), 1);
		$code_id = $code['code'];
		
		foreach($code['providers'] as $code_provider) {
			if($code_provider['id'] == $provider['id']) {
				$codes[$code_id] = $code_provider['responses'];
			}
		}
	}
	
	$code_list = [];
	foreach($codes as $code => $data) {
		$code_list[] = '<a href="#'.$code.'">'.$code.'</a>';
	}
	
	$code_list = implode(' &#183; ', $code_list);
	$content .= '<h2>Codes</h2>'."\n";
	$content .= '<p>'.$code_list.'</p>'."\n";
	
	if(count($codes) > 0) {
		
		$content .= '<dl>'."\n";
		
		foreach($codes as $code => $data) {
			$content .= '<div class="card">'."\n";
			$content .= '<dt id="'.$code.'"><a href="/'.$code.'">'.$code.'</a><span class="top"><a href="#">↑</a></span></dt>'."\n";
			foreach($data as $response) {
				if(isset($response['description']) && $response['description'] !== '') {
					$description = '<p class="info">'.$response['description'].'</p>';
				}
				else {
					$description = null;
				}
				
				if(isset($response['links']) && count($response['links']) > 0) {
					$links = '<ul>'."\n";
					foreach($response['links'] as $link) {
						$links .= '<li><a href="'.$link.'">'.$link.'</a></li>'."\n";
					}
					$links .= '</ul>'."\n";
				}
				else {
					$links = null;
				}
				
				if(is_null($description) && is_null($links)) {
					$content .= '<dd>'.htmlentities($response['response']).$description.$links.'</dd>'."\n";
				}
				else {
					$content .= '<dd><details><summary>'.htmlentities($response['response']).'</summary>'.$description.$links.'</details></dd>'."\n";
				}
			}
			$content .= '</div>'."\n";
		}
		
		$content .= '</dl>'."\n";
	}
	else {
		$content .= '<p>There are no known response codes for this provider.</p>'."\n";
	}
	
	
	// Perform template substitutions and prepare the output
	$output = $template;
	$output = str_replace('{title}', $title, $output);
	$output = str_replace('{description}', $title, $output);
	$output = str_replace('{content}', $content, $output);
	$output = str_replace('{timestamp}', time(), $output);
	
	// Create this providers’s directory if it doesn’t exist
	if(!is_dir(__DIR__.'/../docs/'.$provider['id'])) {
		mkdir(__DIR__.'/../docs/'.$provider['id'], 0755, true);
	}
	
	// Write the code page
	file_put_contents(__DIR__.'/../docs/'.$provider['id'].'/index.html', $output);
	
}

// Build contributors page

$content = null;

$title = 'Contributors';
$description = 'Contributors to the SMTP Codes website';

$content = '<h1>Contributors</h1>'."\n";

$contributors = file_get_contents(__DIR__.'/../data/contributors.json');
$contributors = json_decode($contributors, 1);

$content .= '<ul>'."\n";

foreach($contributors as $contributor) {
	if($contributor['url'] !== '') {
		$content .= '<li><a href="'.$contributor['url'].'">'.$contributor['name'].'</a></li>'."\n";
	}
	else {
		$content .= '<li>'.$contributor['name'].'</li>'."\n";
	}
}

$content .= '</ul>'."\n";

// Perform template substitutions and prepare the output
$output = $template;
$output = str_replace('{title}', $title, $output);
$output = str_replace('{description}', $description, $output);
$output = str_replace('{content}', $content, $output);
$output = str_replace('{timestamp}', time(), $output);

// Create the contributors directory if it doesn’t exist
if(!is_dir(__DIR__.'/../docs/contributors')) {
	mkdir(__DIR__.'/../docs/contributors', 0755, true);
}

// Write the contributors page
file_put_contents(__DIR__.'/../docs/contributors/index.html', $output);

// Build landing page

$content = null;

$title = 'SMTP Codes';
$description = 'Your source for everything about where and how SMTP codes are used.';

$content = file_get_contents(__DIR__.'/../site/landing.html');

$content .= '<h2>Codes</h2>'."\n";
$content .= '<ul>'."\n";
foreach($all_codes as $code) {
	$content .= '<li><a href="/'.$code.'">'.$code.'</a>'."\n";
}
$content .= '</ul>'."\n";

$content .= '<h2>Providers</h2>'."\n";
$content .= '<ul>'."\n";
foreach($all_providers as $provider_id => $provider_name) {
	$content .= '<li><a href="/'.$provider_id.'">'.$provider_name.'</a>'."\n";
}
$content .= '</ul>'."\n";

// Perform template substitutions and prepare the output
$output = $template;
$output = str_replace('{title}', $title, $output);
$output = str_replace('{description}', $title, $output);
$output = str_replace('{content}', $content, $output);
$output = str_replace('{timestamp}', time(), $output);

// Write the landing page
file_put_contents(__DIR__.'/../docs/index.html', $output);

// Write the CNAME file
file_put_contents(__DIR__.'/../docs/CNAME', 'smtp.codes');

$end = microtime(1);

echo 'Built smtp.codes in '.round(($end - $start), 3).' seconds. View the site: https://smtp.codes"\n";';
