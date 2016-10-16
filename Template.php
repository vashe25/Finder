<?php
namespace View;

class Template
{
    protected $templateFolder = __DIR__ . "/templates/";

    protected $templateName = "";

	protected function renderTemplate($data = null)
	{
		ob_start();
		include $this->templateFolder . $this->templateName;
		$result = ob_get_contents();
		ob_end_clean();
		return $result;
	}

    public function view($template, $data = null)
    {
        $this->templateName = $template . ".php";
        return $this->renderTemplate($data);
	}
}