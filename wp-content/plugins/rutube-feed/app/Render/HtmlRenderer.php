<?php

namespace App\Render;

require_once dirname(dirname(__DIR__)) . '/autoload-class.php';

use App\Helper\FormatHelper;


class HtmlRenderer
{

	/**
	 * Рендерит HTML-код на основе шаблона.
	 *
	 * @param string $template - HTML-шаблон с плейсхолдерами
	 * @param array $videos - Массив с видео-данными
	 * @return string - Отрендеренный HTML
	 */
	public function render($template, $videos)
	{
		$output = '';

		foreach ($videos as $video) {
			$output .= $this->replace_placeholders($template, $video);
		}

		return $output;
	}

	/**
	 * Заменяет плейсхолдеры в шаблоне на реальные данные.
	 *
	 * @param string $template - HTML-шаблон
	 * @param array $data - Данные для замены
	 * @return string - Готовый HTML
	 */
	private function replace_placeholders($template, $data)
	{
		$search = [
			'{url}',
			'{thumbnail_url}',
			'{date}',
			'{title}',
			'{duration}',
			'{pg_rating}',
			'{channel}',
			'{description}',
		];
		$template = preg_replace_callback('/\{date(?:\s+([^}]+))?\}/', function ($matches) use ($data) {
			$format = $matches[1] ?? 'd F Y H:i:s'; // Если формат не указан, берем дефолтный
			return !empty($data['date']) ? FormatHelper::format_date($data['date'], $format) : '';
		}, $template);
		if ($data['duration']) {
			$data['duration'] = FormatHelper::format_duration($data['duration']);
		}
		$video_id = basename(parse_url($data['url'], PHP_URL_PATH));
		$data['url'] = "https://rutube.ru/play/embed/{$video_id}";
		$replace = [
			esc_url($data['url'] ?? ''),
			esc_url($data['thumbnail_url'] ?? ''),
			esc_html($data['date'] ?? ''),
			esc_html($data['title'] ?? ''),
			esc_html($data['duration'] ?? ''),
			esc_html($data['pg_rating'] ?? ''),
			esc_html($data['channel'] ?? ''),
			esc_html($data['description'] ?? ''),
		];

		return str_replace($search, $replace, $template);
	}
}
