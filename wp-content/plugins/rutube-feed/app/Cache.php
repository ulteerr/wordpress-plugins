<?php

namespace App;

use WP_Object_Cache;

class Cache
{

	public static function is_cache_Working()
	{
		global $wp_object_cache;

		// Проверяем, подключен ли Redis или Memcached
		$cache_type = false;
		if (class_exists('Redis') && $wp_object_cache instanceof WP_Object_Cache && property_exists($wp_object_cache, 'redis')) {
			$cache_type = 'Redis';
		} elseif (class_exists('Memcached') && $wp_object_cache instanceof WP_Object_Cache && property_exists($wp_object_cache, 'm')) {
			$cache_type = 'Memcached';
		}

		// Если не найден Redis или Memcached, возвращаем false
		if (!$cache_type) {
			return false;
		}

		// Тестируем работу кеша
		$test_key = 'cache_test_key';
		$test_value = 'cache_test_value';
		$cache_group = 'test_group';

		wp_cache_set($test_key, $test_value, $cache_group, 600);
		$cached_value = wp_cache_get($test_key, $cache_group);

		return ($cached_value === $test_value) ? $cache_type : false;
	}

	public static function clear_videos_cache()
	{
		$channel_id = sanitize_text_field($_POST['channel']);
		$group_key = "rutube_videos_keys_{$channel_id}";

		$cached_keys = wp_cache_get($group_key, 'rutube') ?: [];
		
		foreach ($cached_keys as $cache_key) {
			wp_cache_delete($cache_key, 'rutube');
		}

		wp_cache_delete($group_key, 'rutube');

		return wp_send_json_success([
			'status' => 'success',
			'message' => 'Кэш успешно очищен.'
		]);
	}
}
