<?php

class Template
{
	function __construct()
	{

	}

	private function renderTemplate()
	{
		ob_start();
		include $templateFolder . $templateName;
		$result = ob_get_contents();
		ob_end_flush();
		return $result;
	}
}