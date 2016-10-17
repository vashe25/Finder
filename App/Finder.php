<?php
namespace App;

use GuzzleHttp\Client;
use App\Template;

Class Finder {
	// Client object
	protected $client;
	// Template object
	protected $template;
	// Array for URLs
	protected $links = array("http://www.example.com");
	// RegExp string
	protected $pattern = "<a href=\".+\">.+</a>";
	// Matches array
	protected $matches = array();
	// FileName for Links array
	protected $fileName = "links-array.txt";

	public function __construct(Client $client, Template $template) {
		$this->client = $client;
		$this->template = $template;
	}

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

	protected function createFile() {
		file_put_contents($this->fileName, $this->links);
		return TRUE;
	}

	protected function loadFile() {
		$linksArray = file($this->fileName, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
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

	public function run($pattern = false) {
		if (file_exists($this->fileName)) {
            $this->loadFile();
            $this->collectResponse();
            if ($pattern != false) {
                $this->setPattern($pattern);
                $this->matchPattern();
                $view = $this->template->view("html", $this->matches);
                $fileName = "finderResults.html";
            } else {
                $view = $this->template->view("txt", $this->matches);
                $fileName = "finderResults.txt";
            }
            file_put_contents($fileName, $view);
			echo "Check: " . $fileName;
		} else {
			if (!file_exists($this->fileName)) {
				$this->createFile();
				echo "Created: " . $this->fileName . "\n\n";
			}
			echo "reRun App:\n";
			echo "\$ php App.php\n\n";
			echo "reRun App with pattern:\n";
			echo "\$ php script.php \"pattern\"";
		}
	}

}