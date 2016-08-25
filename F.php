<?php
require "vendor/autoload.php";
use GuzzleHttp\Client;

Class Finder {
	function __construct(Client $client) {
		$this->client = $client;
	}
	# Client object
	private $client;
	# Array for URLs
	private $links = array('http://moscow.megafon.ru/', 'http://spb.megafon.ru/tariffs', 'http://spb.megafon.ru/123/');
	# RegExp string
	private $pattern = "<a href=\".+\">.+</a>";
	# Matches array
	private $matches = array();

	private function sendRequest($client, $link) {
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
			# returns error message
			return $e->getMessage();
		}
	}

	private function collectResponse() {
		foreach ($this->links as $link) {
			$response = $this->sendRequest($this->client, $link);
			if (is_string($response)) {
				# returns string: error exception
				$this->matches[$link]['error'] = $response;
			} else {
				# returns integer
				$this->matches[$link]['statusCode'] = $response->getStatusCode();
				# returns array of headers
				$this->matches[$link]['headers'] = $response->getHeaders();
				# returns string = raw content
				$this->matches[$link]['body'] = $response->getBody()->getContents();
			}
		}
		return TRUE;
	}

	private function matchPattern() {
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

	private function createFile(String $filename = "links-array.txt") {
		$fp = fopen($filename, "w");
		$string = "http://www.yandex.ru/";
		fwrite($fp, $string);
		fclose($fp);
		return TRUE;
	}

	private function loadFile(String $filename = "links-array.txt") {
		$linksArray = file($filename, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
		$this->setLinks($linksArray);
		return TRUE;
	}
	# Set pattern
	private function setPattern(String $pattern) {
		$this->pattern = "|".$pattern."|im";
		return TRUE;
	}
	# Set links
	private function setLinks(Array $array) {
		$this->links = $array;
		return TRUE;
	}

	private function printHTML(){
		$string = "<!DOCTYPE html><html><head><title>Finder: Results</title><meta charset=\"utf-8\" /></head><body>\n<h1>".htmlspecialchars($this->pattern)."</h1>\n<table border=\"1px\" cellspacing=\"0px\">\n";
		foreach ($this->matches as $link => $row) {
			$string .= "<tr><td><a target=\"_blank\" href=\"".$link."\"".">".$link."</a></td>";
			if (isset($row['error'])) {
				$string .= "<td colspan=\"2\">".$row['error']."</td></tr>\n";
			} else {
				$string .= "<td><ol>";
				foreach ($row["matches"] as $match) {
					foreach ($match as $value) {
						$string .= "<li>".htmlspecialchars($value)."</li>\n";
					}
				}
				$string .= "</ol></td><td>";
				$string .= "<div>Status Code: ".$row['statusCode']."</div>";
				foreach ($row['headers'] as $header => $hvalue) {
					$string .= "<div>".$header.": ".$hvalue[0]."</div>\n";
				}
				$string .="</td></tr>\n";
			}
		}
		$string .= "</table>\n</body></html>";
		file_put_contents("finderResults.html", $string);
		return TRUE;
	}

	public function run($pattern) {
		if (file_exists("links-array.txt") && isset($pattern)) {
			$this->loadFile();
			$this->setPattern($pattern);
			$this->collectResponse();
			$this->matchPattern();
			$this->printHTML();
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

$F = new Finder(new Client());
$F->run($argv[1]);
exit;