<?php

if (basename(__DIR__) !== 'api' || basename(dirname(__DIR__)) !== 'key') {
	http_response_code(404);
	exit;
}

require dirname(__DIR__, 2) . '/app/license-register-endpoint.php';