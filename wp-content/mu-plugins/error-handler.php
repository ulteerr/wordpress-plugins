<?php
if (!defined('WP_DEBUG') || !WP_DEBUG) {
	return;
}

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
	$error_types = [
		E_ERROR => ['Fatal Error', '#ffdddd', '#900'],
		E_WARNING => ['Warning', '#fff3cd', '#856404'],
		E_PARSE => ['Parse Error', '#ffdddd', '#900'],
		E_NOTICE => ['Notice', '#fff3cd', '#856404'],
		E_DEPRECATED => ['Deprecated', '#fff3cd', '#856404'],
	];

	$type = $error_types[$errno] ?? ['Unknown Error', '#ffdddd', '#900'];

	echo "<div style='background: {$type[1]}; color: {$type[2]}; padding: 10px; border-left: 5px solid {$type[2]}; margin-bottom: 5px;'>
            <strong>{$type[0]}:</strong> {$errstr} <br>
            <small>File: {$errfile} on line {$errline}</small>
          </div>";
});

set_exception_handler(function ($exception) {
	echo "<div style='background: #ffdddd; color: #900; padding: 10px; border-left: 5px solid #900; margin-bottom: 5px;'>
            <strong>Uncaught Exception:</strong> " . $exception->getMessage() . " <br>
            <small>File: " . $exception->getFile() . " on line " . $exception->getLine() . "</small>
          </div>";
});
