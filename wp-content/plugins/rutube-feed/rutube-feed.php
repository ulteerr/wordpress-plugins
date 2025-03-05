<?php

/**
 * Plugin Name: Rutube Feed
 * Description: Парсинг видео с Rutube API
 * Version: 1.2.0
 * Author: AdAurum
 */

if (!defined('ABSPATH')) {
	exit;
}


require_once plugin_dir_path(__FILE__) . 'autoload-class.php';

use App\Cache;
use App\Render\RutubeMetaBoxRenderer;
use App\Video;

class RutubeFeed
{
	private $rutubeMetaBoxRenderer;
	private $video;


	public function __construct()
	{
		$this->rutubeMetaBoxRenderer = new RutubeMetaBoxRenderer();
		$this->video = new Video();


		add_action('init', [$this, 'register_post_type']);
		add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
		add_action('save_post', [$this, 'save_meta']);
		add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts_and_styles']);

		add_shortcode('rutube-feed', [$this, 'shortcode']);
		add_filter('manage_rutube_feed_posts_columns', [$this, 'custom_columns']);
		add_action('manage_rutube_feed_posts_custom_column', [$this, 'custom_column_content'], 10, 2);


		add_action('wp_ajax_load_admin_videos', [$this->video, 'load_admin_videos']);
		add_action('wp_ajax_clear_videos_cache', [Cache::class, 'clear_videos_cache']);
	}

	public function register_post_type()
	{
		register_post_type('rutube_feed', [
			'labels'      => [
				'name'          => 'Rutube фиды',
				'singular_name' => 'Rutube фид',
				'add_new'       => 'Добавить новый',
				'add_new_item'  => 'Добавить новый Rutube фид',
				'edit_item'     => 'Редактировать Rutube фид',
				'new_item'      => 'Новый Rutube фид',
				'view_item'     => 'Просмотреть Rutube фид',
				'search_items'  => 'Поиск фидов',
				'not_found'     => 'Фиды не найдены',
				'menu_name'     => 'Rutube фиды'
			],
			'public'      => false,
			'show_ui'     => true,
			'menu_icon'   => 'dashicons-video-alt3',
			'supports'    => ['title'],
			'show_in_menu' => true
		]);
	}
	public function add_meta_boxes()
	{
		add_meta_box(
			'rutube_feed_meta',
			'Rutube фид',
			[$this, 'meta_box_callback'],
			'rutube_feed',
			'normal',
			'default'
		);
	}
	public function meta_box_callback($post)
	{
		$this->rutubeMetaBoxRenderer->render($post);
	}

	public function custom_columns($columns)
	{
		return [
			'cb'        => $columns['cb'],
			'title'     => __('Name'),
			'shortcode' => __('Shortcode'),
			'instances' => __('Instances'),
			'actions'   => __('Actions')
		];
	}
	public function custom_column_content($column, $post_id)
	{
		if ($column === 'shortcode') {
			echo '<code>[rutube-feed feed=' . $post_id . ']</code>';
		} elseif ($column === 'instances') {
			echo 'Used in 1 place';
		} elseif ($column === 'actions') {
			echo '<a href="' . get_edit_post_link($post_id) . '">Править</a> | ';
			echo '<a href="' . get_delete_post_link($post_id) . '">Удалить</a>';
		}
	}


	public function enqueue_admin_scripts_and_styles()
	{
		$screen = get_current_screen();

		if ($screen && $screen->post_type === 'rutube_feed') {

			$post_id = ($screen->base === 'post') ? get_the_ID() : 0;

			$plugin_url = plugin_dir_url(__FILE__) . 'resource/';


			// Отключаем jQuery для этой страницы
			wp_deregister_script('jquery');
			wp_dequeue_script('jquery');

			// Подключаем стили и скрипты админки
			wp_enqueue_style('rutube-admin-css', $plugin_url . 'main-admin.css', [], null);
			wp_enqueue_script('rutube-admin-js', $plugin_url . 'dist/main-admin.js', [], null, true);





			wp_localize_script('rutube-admin-js', 'rutubeAdminParams', [
				'ajax_url' => admin_url('admin-ajax.php'),
				'cache_enabled' => Cache::is_cache_Working(),
				'post_id' => $post_id,
			]);

			$this->register_style_action();
		}
	}
	public function save_meta($post_id)
	{
		if (!isset($_POST['rutube_meta_nonce']) || !wp_verify_nonce($_POST['rutube_meta_nonce'], 'save_rutube_meta')) {
			return;
		}
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}

		if (isset($_POST['rutube_channel_id'])) {
			update_post_meta($post_id, 'rutube_channel_id', sanitize_text_field($_POST['rutube_channel_id']));
		}
		if (isset($_POST['rutube_video_limit'])) {
			$video_limit = intval($_POST['rutube_video_limit']);

			if ($video_limit < 1 || $video_limit > 20) {
				add_settings_error(
					'rutube_video_limit',
					'invalid_rutube_video_limit',
					'Лимит на показ должен быть от 1 до 20.',
					'error'
				);
				set_transient('settings_errors', get_settings_errors(), 30);
			} else {
				update_post_meta($post_id, 'rutube_video_limit', $video_limit);
			}
		}

		if (isset($_POST['rutube_video_template_html'])) {
			update_post_meta($post_id, 'rutube_video_template_html', wp_unslash($_POST['rutube_video_template_html']));
		}
		if (isset($_POST['rutube_video_template_css'])) {
			update_post_meta($post_id, 'rutube_video_template_css', wp_unslash($_POST['rutube_video_template_css']));
		}
	}
	public function shortcode($atts)
	{
		$atts = shortcode_atts(['feed' => ''], $atts, 'rutube-feed');
		$feed_id = intval($atts['feed']);
		$channel_id = get_post_meta($feed_id, 'rutube_channel_id', true);

		if (!$channel_id) {
			return '<p>Rutube Feed not found.</p>';
		}
		$post = get_post($feed_id);
		$limit = get_post_meta($post->ID, 'rutube_video_limit', true);;

		add_action('wp_enqueue_scripts',  [$this, 'register_style_action']);
		return  $this->rutubeMetaBoxRenderer->render_video_contaniner_and_modal($channel_id, $limit, $this->video->load_shortcode_videos($post));
	}
	public function register_style_action()
	{
		$post = get_post();
		$template = $this->rutubeMetaBoxRenderer->get_template_css($post);

		$handle = 'rutube-feed-style';
		wp_register_style($handle, false);
		wp_enqueue_style($handle);
		wp_add_inline_style($handle, $template);
	}
}

new RutubeFeed();
