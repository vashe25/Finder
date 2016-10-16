<?php
namespace App;
require "vendor/autoload.php";
require "App/Finder.php";
require "App/Template.php";
use GuzzleHttp\Client;
use View\Template;
use Core\Finder;

$F = new Finder(new Client(), new Template());
if (isset($argv[1])) {
	$F->run($argv[1]);
} else {
	$F->run();
}
exit;