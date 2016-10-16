<?php
namespace App;
require "vendor/autoload.php";
require "Template.php";
use GuzzleHttp\Client;
use View\Template;

Class Finder {
	function __construct(Client $client, Template $template) {
		$this->client = $client;
        $this->template = $template;
	}
	// Client object
	protected $client;
    // Template object
    protected $template;
	// Array for URLs
	protected $links = array('http://moscow.megafon.ru/', 'http://spb.megafon.ru/tariffs', 'http://spb.megafon.ru/123/');
	// RegExp string
	protected $pattern = "<a href=\".+\">.+</a>";
	// Matches array
	protected $matches = array();

	protected function sendRequest($client, $link) {
		try {
			$response = $client->request("GET", $link, [
				'allow_redirects' => [
					'max' => 10, // allow at most 10 redirects.
					'strict' => true, // use "strict" RFC compliant redirects.
					'referer' => true, // add a Referer header

					'protocols' => ['https', 'http'], // allow https|http URLs
					'track_redirects' => true
				],
				'http_errors' => false
			]);
			return $response;
		} catch(\Exception $e) {
			// returns error message
			return $e->getMessage();
		}
	}

	protected function collectResponse() {
		foreach ($this->links as $link) {
			$response = $this->sendRequest($this->client, $link);
			if (is_string($response)) {
				// returns string: error exception
				$this->matches[$link]['error'] = $response;
			} else {
				// returns integer
				$this->matches[$link]['statusCode'] = $response->getStatusCode();
				// returns array of headers
				$this->matches[$link]['headers'] = $response->getHeaders();
				// returns string = raw content
				$this->matches[$link]['body'] = $response->getBody()->getContents();
			}
		}
		return TRUE;
	}

	protected function matchPattern() {
		foreach ($this->matches as $link => $element) {
			if ($element['statusCode'] >= 300 or isset($element['error'])) {
				$this->matches[$link]['matches'][0][0] = "Nothing";
				continue;
			} else {
				preg_match_all($this->pattern, $element['body'], $this->matches[$link]['matches'], PREG_SET_ORDER);
			}
		}
		return TRUE;
	}

	protected function createFile($filename = "links-array.txt") {
		$fp = fopen($filename, "w");
		$string = "http://www.yandex.ru/";
		fwrite($fp, $string);
		fclose($fp);
		return TRUE;
	}

	protected function loadFile($filename = "links-array.txt") {
		$linksArray = file($filename, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
		$this->setLinks($linksArray);
		return TRUE;
	}
	// Set pattern
	protected function setPattern($pattern) {
		$this->pattern = "|".$pattern."|im";
		return TRUE;
	}
	// Set links
	protected function setLinks(Array $array) {
		$this->links = $array;
		return TRUE;
	}

	public function run($pattern) {
		if (file_exists("links-array.txt") && isset($pattern)) {
			$this->loadFile();
			$this->setPattern($pattern);
			$this->collectResponse();
			$this->matchPattern();
            $view = $this->template->view("html", $this->matches);
            file_put_contents("finderResults.html", $view);
			echo "Check: finderResults.html";
		} else {
			if (!file_exists("links-array.txt")) {
				$this->createFile();
				echo "links-array.txt created\n";
			}
			echo "reRun finder with parameter:\n";
			echo "	php script.php \"pattern\"";
		}
	}

}

$F = new Finder(new Client(), new Template());
$F->run($argv[1]);
exit;