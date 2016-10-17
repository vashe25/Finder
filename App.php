<?php
require "vendor/autoload.php";

use GuzzleHttp\Client;
use App\Template;
use App\Finder;

$F = new Finder(new Client(), new Template());
if (isset($argv[1])) {
	$F->run($argv[1]);
} else {
	$F->run();
}
exit;