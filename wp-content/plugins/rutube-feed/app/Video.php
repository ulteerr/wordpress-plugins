<?php

namespace App;

require_once dirname(__DIR__) . '/autoload-class.php';

use App\Render\CssRenderer;
use App\Render\HtmlRenderer;
use App\Render\RutubeMetaBoxRenderer;

class Video
{
	private $htmlRenderer;
	private $cssRenderer;
	private $rutubeMetaBoxRenderer;

	public function __construct()
	{
		$this->rutubeMetaBoxRenderer = new RutubeMetaBoxRenderer();
		$this->htmlRenderer = new HtmlRenderer();
		$this->cssRenderer = new CssRenderer();
	}
	public function load_admin_videos()
	{
		$channel_id = sanitize_text_field($_POST['channel']);
		$limit = !empty($_POST['limit']) ? intval($_POST['limit']) : 0;
		$page = !empty($_POST['page']) ? intval($_POST['page']) : 0;

		$post_id = !empty($_POST['post_id']) ? intval($_POST['post_id']) : 0;
		$post = get_post($post_id);

		list('videos' => $videos, 'has_next' => $has_next) = $this->fetch_videos($channel_id, $limit, $page);

		usort($videos, function ($a, $b) {
			return strtotime($b['date']) - strtotime($a['date']);
		});


		wp_send_json_success([
			'html' => $this->render_videos($videos, $post, $page),
			'has_more' => $has_next,
			'count' => count($videos),
		]);
	}
	public function load_shortcode_videos($post)
	{
		$page = 1;
		$limit = get_post_meta($post->ID, 'rutube_video_limit', true);
		$channel_id = get_post_meta($post->ID, 'rutube_channel_id', true);

		list('videos' => $videos, 'has_next' => $has_next) = $this->fetch_videos($channel_id, $limit, $page);

		usort($videos, function ($a, $b) {
			return strtotime($b['date']) - strtotime($a['date']);
		});

		return $this->render_videos($videos, $post, $page);
	}

	private function fetch_videos($channel_id, $limit = 20, $page = 1)
	{
		$cache_key = "rutube_videos_{$channel_id}_{$limit}_{$page}";
		$group_key = "rutube_videos_keys_{$channel_id}";

		$cached_data = wp_cache_get($cache_key, 'rutube');
		$cached_keys = wp_cache_get($group_key, 'rutube') ?: [];


		if ($cached_data !== false) {
			return $cached_data;
		}

		$url = "https://rutube.ru/api/video/person/{$channel_id}/?client=wdp&origin__type=rtb,rst,ifrm,rspa&limit={$limit}&page={$page}";

		$args = [
			'headers' => [
				'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
				'Accept' => 'application/json',
				'Referer' => 'https://rutube.ru/',
				'Connection' => 'keep-alive'
			],
			'timeout' => 15
		];

		$response = wp_remote_get($url, $args);

		if (is_wp_error($response)) {
			error_log('Rutube fetch error: ' . $response->get_error_message());
			return [];
		}

		$body = wp_remote_retrieve_body($response);
		$data = json_decode($body, true);

		if (empty($data) || !isset($data['results'])) {
			error_log("Rutube API вернул некорректные данные (страница {$page})");
			return [];
		}

		$result = [
			'videos' => array_map(function ($video) {
				return [
					'url' => $video['video_url'] ?? '',
					'thumbnail_url' => $video['thumbnail_url'] ?? '',
					'date' => $video['created_ts'] ?? '',
					'title' => $video['title'] ?? '',
					'duration' => $video['duration'] ?? '',
					'pg_rating' => $video['pg_rating']['age'] ?? '',
					'channel' => $video['author']['name'] ?? '',
					'description' => $video['description'] ?? ''
				];
			}, $data['results']),
			'has_next' => $data['has_next'] ?? !empty($data['next']),
		];

		wp_cache_set($cache_key, $result, 'rutube');

		if (!in_array($cache_key, $cached_keys)) {
			$cached_keys[] = $cache_key;
			wp_cache_set($group_key, $cached_keys, 'rutube');
		}

		return $result;
	}

	private function render_videos($videos, $post, $page)
	{
		$template_html = $this->rutubeMetaBoxRenderer->get_template_html($post);
		return $this->htmlRenderer->render($template_html, $videos);
	}
}
