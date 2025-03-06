<?php

use Dotenv\Dotenv;

if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
	die('autoload.php not found');
}
require __DIR__ . '/vendor/autoload.php';




$dotenv = Dotenv::createMutable(__DIR__);
$dotenv->load();

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * This has been slightly modified (to read environment variables) for use in Docker.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

if (!function_exists('getenv_value')) {
	function getenv_value(string $env, string|int|bool|null $default): string|int|bool|null
	{
		$value = isset($_ENV[$env]) ? $_ENV[$env] : null;

		if ($value === '' || !$value) {
			return $default;
		}

		// Обрабатываем логические значения 'true' и 'false' как bool
		$lowerValue = strtolower($value);
		if ($lowerValue === 'true') {
			return true;
		}
		if ($lowerValue === 'false') {
			return false;
		}
		if ($lowerValue === 'null' || $lowerValue === 'NULL') {
			return null;
		}

		// Приведение числа 0 (строкового и числового) к int
		if ($value === '0' || $value === 0) {
			return 0;
		}
		if (is_numeric($value)) {
			$value = (int)$value;
		}
		return $value;
	}
}
if (!function_exists('dd')) {
	function dd()
	{
		header("Content-Type: text/html; charset=UTF-8");
		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
		header("Access-Control-Allow-Headers: Content-Type, Authorization");

		$html = '<style>
		body { background-color: #181818; color: #ddd; font-family: Arial, sans-serif; }
		.debug-container {
			max-width: 90%;
			margin: 20px auto;
			background: #222;
			border-radius: 10px;
			padding: 15px;
			box-shadow: 0 0 10px rgba(255, 255, 255, 0.1);
		}
		pre {
			background-color: #000;
			border-left: 5px solid #00ff00;
			color: #fff;
			padding: 12px;
			font-size: 14px;
			overflow: auto;
			border-radius: 5px;
			font-family: "Courier New", monospace;
		}
	</style>';

		$html .= '<div class="debug-container"><pre>';
		foreach (func_get_args() as $arg) {
			ob_start();
			var_dump($arg);
			$html .= htmlspecialchars(ob_get_clean()) . "\n";
		}

		$html .= '</pre></div>';

		echo $html;
		die;
	}
}

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', getenv_value('WORDPRESS_DB_NAME', 'wordpress'));

/** Database username */
define('DB_USER', getenv_value('WORDPRESS_DB_USER', 'example username'));

/** Database password */
define('DB_PASSWORD', getenv_value('WORDPRESS_DB_PASSWORD', 'example password'));

/**
 * Docker image fallback values above are sourced from the official WordPress installation wizard:
 * https://github.com/WordPress/WordPress/blob/1356f6537220ffdc32b9dad2a6cdbe2d010b7a88/wp-admin/setup-config.php#L224-L238
 * (However, using "example username" and "example password" in your database is strongly discouraged.  Please use strong, random credentials!)
 */

/** Database hostname */
define('DB_HOST', getenv_value('WORDPRESS_DB_HOST', 'mysql'));

/** Database charset to use in creating database tables. */
define('DB_CHARSET', getenv_value('WORDPRESS_DB_CHARSET', 'utf8'));

/** The database collate type. Don't change this if in doubt. */
define('DB_COLLATE', getenv_value('WORDPRESS_DB_COLLATE', ''));


define('WP_REDIS_HOST', getenv_value("WP_REDIS_HOST", "127.0.0.1"));
define('WP_REDIS_PORT', getenv_value("WP_REDIS_PORT", 6379));

define('WP_REDIS_PASSWORD', getenv_value("WP_REDIS_PASSWORD", ""));
define('WP_CACHE', getenv_value("WORDPRESS_CACHE", true));


define('WP_REDIS_TIMEOUT', 1);
define('WP_REDIS_READ_TIMEOUT', 1);

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         getenv_value('WORDPRESS_AUTH_KEY',         '0a226c09e3be1ea3619aa8858fc348b26684ce15'));
define('SECURE_AUTH_KEY',  getenv_value('WORDPRESS_SECURE_AUTH_KEY',  'a757860a7b31bda095988cef585876cea866d8a5'));
define('LOGGED_IN_KEY',    getenv_value('WORDPRESS_LOGGED_IN_KEY',    'd8153f172dbf7cfe264dbbef011770de821ba62b'));
define('NONCE_KEY',        getenv_value('WORDPRESS_NONCE_KEY',        'ba907d5430ccd0f08621a6261f3fd6e7ddf69509'));
define('AUTH_SALT',        getenv_value('WORDPRESS_AUTH_SALT',        '5e91b8f2d663f441eb264dd63449fa2468d1e626'));
define('SECURE_AUTH_SALT', getenv_value('WORDPRESS_SECURE_AUTH_SALT', '9e8f2d82df1ea3dcd1fbaf395e3eacad39f238b4'));
define('LOGGED_IN_SALT',   getenv_value('WORDPRESS_LOGGED_IN_SALT',   '2f5d1c892fc5fb5f3894f5e59b6d15f176bab2d3'));
define('NONCE_SALT',       getenv_value('WORDPRESS_NONCE_SALT',       '268afe0957f5135d8da9cb5eaea8c3c306ca5468'));
// (See also https://wordpress.stackexchange.com/a/152905/199287)

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = getenv_value('WORDPRESS_TABLE_PREFIX', 'wp_');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define('WP_DEBUG', getenv_value("WORDPRESS_DEBUG", false));

/* Add any custom values between this line and the "stop editing" line. */

// If we're behind a proxy server and using HTTPS, we need to alert WordPress of that fact
// see also https://wordpress.org/support/article/administration-over-ssl/#using-a-reverse-proxy
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strpos($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') !== false) {
	$_SERVER['HTTPS'] = 'on';
}
// (we include this by default because reverse proxying is extremely common in container environments)

if ($configExtra = getenv_value('WORDPRESS_CONFIG_EXTRA', '')) {
	eval($configExtra);
}

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if (! defined('ABSPATH')) {
	define('ABSPATH', __DIR__ . '/');
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
