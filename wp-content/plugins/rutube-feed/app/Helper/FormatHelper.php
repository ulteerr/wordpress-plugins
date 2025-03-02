<?php

namespace App\Helper;

use DateTime;

class FormatHelper
{
	public static function format_date($isoString, $format = 'd F Y H:i:s')
	{
		$date = new DateTime($isoString);
		return $date->format($format);
	}

	public static function format_duration($seconds)
	{
		$h = floor($seconds / 3600);
		$m = floor(($seconds % 3600) / 60);
		$s = $seconds % 60;

		if ($h > 0) {
			return sprintf("%d:%02d:%02d", $h, $m, $s);
		} else {
			return sprintf("%d:%02d", $m, $s);
		}
	}
}
