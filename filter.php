<?php
/**
 * AniXoHindi - Legacy filter.php redirected to modern browse.php
 */
$queryString = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
header('Location: /browse.php' . $queryString, true, 301);
exit;
