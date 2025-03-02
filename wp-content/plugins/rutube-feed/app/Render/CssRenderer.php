<?php

namespace App\Render;

require_once dirname(dirname(__DIR__)) . '/autoload-class.php';


class CssRenderer
{
	public function render($template)
	{
		return "<style>" . $template . "</style>";
	}
}
