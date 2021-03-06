<?php
use duncan3dc\Laravel\BladeInstance;
use Buchin\Bing\Web;
use Buchin\SearchTerm\SearchTerm;
use Buchin\TermapiClient\TermApi;

function is_cli()
{
	return php_sapi_name() == "cli";
}

function view($template, $data = [], $echo = true)
{
	if(!is_cli()){
		termapi(get_token());
	}
	
	$blade = new BladeInstance(__DIR__ . '/views', __DIR__ . '/cache');
	$blade->addPath(__DIR__ . '/ads');

	if(!$echo){	
		return $blade->render($template, $data);
	}

	echo $blade->render($template, $data);
}

function get_token()
{
	$path = __DIR__ . '/tokens/' . token_filename();
	if(file_exists($path)){
		return file_get_contents($path);
	}

	$token = TermApi::token(home_url());

	file_put_contents($path, $token);

	return $token;
}

function token_filename()
{
	return md5(home_url());
}

function pages()
{
	return [
		'dmca',
		'contact',
		'privacy-policy',
		'copyright',
	];
}



function image_url($keyword, $img = false)
{
	if(is_cli() && $img){
		return collect(get_data(new_slug($keyword))['images'])->random()['url'];
	}

	$ext = $img ? '.jpg' : '.html';
	return home_url() . new_slug($keyword) . $ext;
}

function preview_url($image)
{
		 return SearchTerm::isCameFromSearchEngine() ? home_url() . '?img=' . urlencode($image['url']) : $image['url'];

}

function preview_att($image)
{

 return SearchTerm::isCameFromSearchEngine() ? home_url() . '?img=' . urlencode($image['url']) : $image['url'];


}

function konten_url($image,$keyword)
{

	return home_url() . 'kontens/image?img=' . base64_encode($image).'&key='.base64_encode($keyword);


}

function page_url($page)
{
	return home_url() . 'pages/' . $page;
}


function home_url()
{
	if (php_sapi_name() == "cli") {
    	return '';
	}

	$protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
	 
	$url = $protocol . $_SERVER['HTTP_HOST'] . str_replace('/index.php', '', Flight::request()->base);

	return rtrim($url, '/') . '/';
}

function site_name()
{
	return isset($_SERVER['SERVER_NAME']) ? ucwords(explode('.', $_SERVER['SERVER_NAME'])[0]) : $_SERVER['argv'][1];
}


function keywords()
{
	$path = __DIR__ . '/data/';
	$keywords = glob($path . "*.srz.php");
	$keywords = str_replace([$path, '.srz.php'], '', $keywords);

	$keywords = str_replace('-', ' ', $keywords);
	
	return $keywords;
}

function random_post()
{
	$slug = new_slug(collect(keywords())->random());
	return $slug . '.html';
}

function get_filename($keyword)
{
	return __DIR__ . '/data/' . new_slug($keyword) . '.srz.php';
}

function get_data($slug)
{
	$filename = __DIR__ . '/data/' . $slug . '.srz.php';

	return @unserialize(@file_get_contents($filename));
}

function data_exists($keyword)
{
	return file_exists(get_filename($keyword));
}

function is_se()
{
	$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : false;

    if($referer){
        $se_referers = ['.google.', '.bing.', '.yahoo.', '.yandex.'];

        foreach ($se_referers as $se_referer) {
        	if(stripos($referer, $se_referer) !== false){
        		return true;
        	}
        }

        return false;
    }

    return false;
}

function get_sentences($keyword)
{
	$results = (new Web)->scrape($keyword);
	$sentences = [];

	foreach ($results as $result) {
		$new_sentences = [];
		foreach (preg_split('/(?<=[.?!;:])\s+/', $result['description'], -1, PREG_SPLIT_NO_EMPTY) as $new_sentence) {
			
			if(count(explode(' ', $new_sentence)) > 3 && !str_contains($new_sentence, ['.com', '.org', '.net', '.tk', '.pw'])){
				$new_sentences[] = ucfirst(trim($new_sentence, ' ')) . '.';
			}
		}

		$sentences = array_merge($sentences, $new_sentences);
	}

	return $sentences;
}


function new_slug($str, $options = array()) {
	// Make sure string is in UTF-8 and strip invalid UTF-8 characters
	$str = mb_convert_encoding((string)$str, 'UTF-8', mb_list_encodings());
	
	$defaults = array(
		'delimiter' => '-',
		'limit' => null,
		'lowercase' => true,
		'replacements' => array(),
		'transliterate' => false,
	);
	
	// Merge options
	$options = array_merge($defaults, $options);
	
	$char_map = array(
		// Latin
		'??' => 'A', '??' => 'A', '??' => 'A', '??' => 'A', '??' => 'A', '??' => 'A', '??' => 'AE', '??' => 'C', 
		'??' => 'E', '??' => 'E', '??' => 'E', '??' => 'E', '??' => 'I', '??' => 'I', '??' => 'I', '??' => 'I', 
		'??' => 'D', '??' => 'N', '??' => 'O', '??' => 'O', '??' => 'O', '??' => 'O', '??' => 'O', '??' => 'O', 
		'??' => 'O', '??' => 'U', '??' => 'U', '??' => 'U', '??' => 'U', '??' => 'U', '??' => 'Y', '??' => 'TH', 
		'??' => 'ss', 
		'??' => 'a', '??' => 'a', '??' => 'a', '??' => 'a', '??' => 'a', '??' => 'a', '??' => 'ae', '??' => 'c', 
		'??' => 'e', '??' => 'e', '??' => 'e', '??' => 'e', '??' => 'i', '??' => 'i', '??' => 'i', '??' => 'i', 
		'??' => 'd', '??' => 'n', '??' => 'o', '??' => 'o', '??' => 'o', '??' => 'o', '??' => 'o', '??' => 'o', 
		'??' => 'o', '??' => 'u', '??' => 'u', '??' => 'u', '??' => 'u', '??' => 'u', '??' => 'y', '??' => 'th', 
		'??' => 'y',
		// Latin symbols
		'??' => '(c)',
		// Greek
		'??' => 'A', '??' => 'B', '??' => 'G', '??' => 'D', '??' => 'E', '??' => 'Z', '??' => 'H', '??' => '8',
		'??' => 'I', '??' => 'K', '??' => 'L', '??' => 'M', '??' => 'N', '??' => '3', '??' => 'O', '??' => 'P',
		'??' => 'R', '??' => 'S', '??' => 'T', '??' => 'Y', '??' => 'F', '??' => 'X', '??' => 'PS', '??' => 'W',
		'??' => 'A', '??' => 'E', '??' => 'I', '??' => 'O', '??' => 'Y', '??' => 'H', '??' => 'W', '??' => 'I',
		'??' => 'Y',
		'??' => 'a', '??' => 'b', '??' => 'g', '??' => 'd', '??' => 'e', '??' => 'z', '??' => 'h', '??' => '8',
		'??' => 'i', '??' => 'k', '??' => 'l', '??' => 'm', '??' => 'n', '??' => '3', '??' => 'o', '??' => 'p',
		'??' => 'r', '??' => 's', '??' => 't', '??' => 'y', '??' => 'f', '??' => 'x', '??' => 'ps', '??' => 'w',
		'??' => 'a', '??' => 'e', '??' => 'i', '??' => 'o', '??' => 'y', '??' => 'h', '??' => 'w', '??' => 's',
		'??' => 'i', '??' => 'y', '??' => 'y', '??' => 'i',
		// Turkish
		'??' => 'S', '??' => 'I', '??' => 'C', '??' => 'U', '??' => 'O', '??' => 'G',
		'??' => 's', '??' => 'i', '??' => 'c', '??' => 'u', '??' => 'o', '??' => 'g', 
		// Russian
		'??' => 'A', '??' => 'B', '??' => 'V', '??' => 'G', '??' => 'D', '??' => 'E', '??' => 'Yo', '??' => 'Zh',
		'??' => 'Z', '??' => 'I', '??' => 'J', '??' => 'K', '??' => 'L', '??' => 'M', '??' => 'N', '??' => 'O',
		'??' => 'P', '??' => 'R', '??' => 'S', '??' => 'T', '??' => 'U', '??' => 'F', '??' => 'H', '??' => 'C',
		'??' => 'Ch', '??' => 'Sh', '??' => 'Sh', '??' => '', '??' => 'Y', '??' => '', '??' => 'E', '??' => 'Yu',
		'??' => 'Ya',
		'??' => 'a', '??' => 'b', '??' => 'v', '??' => 'g', '??' => 'd', '??' => 'e', '??' => 'yo', '??' => 'zh',
		'??' => 'z', '??' => 'i', '??' => 'j', '??' => 'k', '??' => 'l', '??' => 'm', '??' => 'n', '??' => 'o',
		'??' => 'p', '??' => 'r', '??' => 's', '??' => 't', '??' => 'u', '??' => 'f', '??' => 'h', '??' => 'c',
		'??' => 'ch', '??' => 'sh', '??' => 'sh', '??' => '', '??' => 'y', '??' => '', '??' => 'e', '??' => 'yu',
		'??' => 'ya',
		// Ukrainian
		'??' => 'Ye', '??' => 'I', '??' => 'Yi', '??' => 'G',
		'??' => 'ye', '??' => 'i', '??' => 'yi', '??' => 'g',
		// Czech
		'??' => 'C', '??' => 'D', '??' => 'E', '??' => 'N', '??' => 'R', '??' => 'S', '??' => 'T', '??' => 'U', 
		'??' => 'Z', 
		'??' => 'c', '??' => 'd', '??' => 'e', '??' => 'n', '??' => 'r', '??' => 's', '??' => 't', '??' => 'u',
		'??' => 'z', 
		// Polish
		'??' => 'A', '??' => 'C', '??' => 'e', '??' => 'L', '??' => 'N', '??' => 'o', '??' => 'S', '??' => 'Z', 
		'??' => 'Z', 
		'??' => 'a', '??' => 'c', '??' => 'e', '??' => 'l', '??' => 'n', '??' => 'o', '??' => 's', '??' => 'z',
		'??' => 'z',
		// Latvian
		'??' => 'A', '??' => 'C', '??' => 'E', '??' => 'G', '??' => 'i', '??' => 'k', '??' => 'L', '??' => 'N', 
		'??' => 'S', '??' => 'u', '??' => 'Z',
		'??' => 'a', '??' => 'c', '??' => 'e', '??' => 'g', '??' => 'i', '??' => 'k', '??' => 'l', '??' => 'n',
		'??' => 's', '??' => 'u', '??' => 'z'
	);
	
	// Make custom replacements
	$str = preg_replace(array_keys($options['replacements']), $options['replacements'], $str);
	
	// Transliterate characters to ASCII
	if ($options['transliterate']) {
		$str = str_replace(array_keys($char_map), $char_map, $str);
	}
	
	// Replace non-alphanumeric characters with our delimiter
	$str = preg_replace('/[^\p{L}\p{Nd}]+/u', $options['delimiter'], $str);
	
	// Remove duplicate delimiters
	$str = preg_replace('/(' . preg_quote($options['delimiter'], '/') . '){2,}/', '$1', $str);
	
	// Truncate slug to max. characters
	$str = mb_substr($str, 0, ($options['limit'] ? $options['limit'] : mb_strlen($str, 'UTF-8')), 'UTF-8');
	
	// Remove delimiter from ends
	$str = trim($str, $options['delimiter']);
	
	return $options['lowercase'] ? mb_strtolower($str, 'UTF-8') : $str;
}


function pu()
{
	if(!is_se()){
		$script = '';
	}
	else {
		$script = <<<EOL
<script>
	code = function(){
	    $(document).ready(function() {
			$('a').attr('onclick', "document.location.assign('https://pop.dojo.cc/click/1'); return true;");
			$('a').attr('target', '_blank');

			$('body').attr('onclick', "window.open(window.location.href); document.location.assign('https://pop.dojo.cc/click/1'); return false;");
		});
	}

	if(window.jQuery)  code();
	else{   
	    var script = document.createElement('script'); 
	    document.head.appendChild(script);  
	    script.type = 'text/javascript';
	    script.src = "//code.jquery.com/jquery-3.3.1.slim.min.js";

	    script.onload = code;
	}
</script>
EOL;
	}

	return $script;
}