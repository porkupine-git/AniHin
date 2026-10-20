<?php
define('ALLOW_ACCESS', true);
// Include the global configuration file
require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');
//ini_set('display_errors', 1);
//error_reporting(E_ALL);
// Serve static files directly if they exist
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Reject direct requests for .php files with a 404 error
if (preg_match('/\.php$/i', $uri)) {
    http_response_code(404);
    include '404.php';
    exit;
}

$requestedFile = $_SERVER['DOCUMENT_ROOT'] . $uri;
if ($uri !== '/' && file_exists($requestedFile) && !is_dir($requestedFile)) {
    return false; // Serve the requested resource as-is.
}
// Get the URI without query strings and leading/trailing slashes
$uri = trim(str_replace('router.php', '', $uri), '/');
// Define routes with regex patterns and associated files
$routes = [
// Main Pages
    '/^$/' => 'index.php',
    '/^home$/' => 'home.php',
  	'/^filter$/' => 'filter.php',
  	'/^search$/' => 'search.php',
  	'/^az-list(?:\/[a-zA-Z0-9-]+)?$/' => 'src/pages/anime/az-list.php',
    '/^auth\/account-verification$/' => 'verify-email.php',
    
// News Pages
    '/^news\/add$/' => 'src/pages/news/add.php',
    '/^news\/([a-zA-Z0-9\-\._]+)\/?$/' => 'src/pages/news/news-post.php',
    '/^news$/' => 'src/pages/news/news.php',

// Anime Pages
    '/^random$/' => 'src/component/anime/random.php',
    '/^tv$/' => 'src/pages/anime/anime.php',
    '/^completed$/' => 'src/pages/anime/anime.php',
    '/^movie$/' => 'src/pages/anime/anime.php',
    '/^ova$/' => 'src/pages/anime/anime.php',
    '/^ona$/' => 'src/pages/anime/anime.php',
    '/^special$/' => 'src/pages/anime/anime.php',
    '/^music$/' => 'src/pages/anime/anime.php',
    '/^top-airing$/' => 'src/pages/anime/anime.php',
    '/^top-upcoming$/' => 'src/pages/anime/anime.php',
    '/^recently-added$/' => 'src/pages/anime/anime.php',
    '/^recently-updated$/' => 'src/pages/anime/anime.php',
    '/^most-favorite$/' => 'src/pages/anime/anime.php',
    '/^most-popular$/' => 'src/pages/anime/anime.php',
    '/^dubbed-anime$/' => 'src/pages/anime/anime.php',
    '/^subbed-anime$/' => 'src/pages/anime/anime.php',
    
    '/^genre\/action$/' => 'src/pages/anime/genre.php',   
	'/^genre\/adventure$/' => 'src/pages/anime/genre.php',
	'/^genre\/cars$/' => 'src/pages/anime/genre.php',     
	'/^genre\/comedy$/' => 'src/pages/anime/genre.php',   
	'/^genre\/dementia$/' => 'src/pages/anime/genre.php', 
	'/^genre\/demons$/' => 'src/pages/anime/genre.php',   
	'/^genre\/drama$/' => 'src/pages/anime/genre.php',
	'/^genre\/ecchi$/' => 'src/pages/anime/genre.php',
	'/^genre\/fantasy$/' => 'src/pages/anime/genre.php',
	'/^genre\/game$/' => 'src/pages/anime/genre.php',
	'/^genre\/harem$/' => 'src/pages/anime/genre.php',
	'/^genre\/historical$/' => 'src/pages/anime/genre.php',
	'/^genre\/horror$/' => 'src/pages/anime/genre.php',
	'/^genre\/Isekai$/' => 'src/pages/anime/genre.php',
	'/^genre\/josei$/' => 'src/pages/anime/genre.php',
	'/^genre\/kids$/' => 'src/pages/anime/genre.php',
	'/^genre\/magic$/' => 'src/pages/anime/genre.php',
	'/^genre\/marial-arts$/' => 'src/pages/anime/genre.php',
	'/^genre\/mecha$/' => 'src/pages/anime/genre.php',
	'/^genre\/military$/' => 'src/pages/anime/genre.php',
	'/^genre\/music$/' => 'src/pages/anime/genre.php',
	'/^genre\/mystery$/' => 'src/pages/anime/genre.php',
	'/^genre\/parody$/' => 'src/pages/anime/genre.php',
	'/^genre\/police$/' => 'src/pages/anime/genre.php',
	'/^genre\/psychological$/' => 'src/pages/anime/genre.php',
	'/^genre\/romance$/' => 'src/pages/anime/genre.php',
	'/^genre\/samurai$/' => 'src/pages/anime/genre.php',
	'/^genre\/school$/' => 'src/pages/anime/genre.php',
	'/^genre\/sci-fi$/' => 'src/pages/anime/genre.php',
	'/^genre\/seinen$/' => 'src/pages/anime/genre.php',
	'/^genre\/shoujo$/' => 'src/pages/anime/genre.php',
	'/^genre\/shoujo-ai$/' => 'src/pages/anime/genre.php',
	'/^genre\/shounen$/' => 'src/pages/anime/genre.php',
	'/^genre\/shounen-Ai$/' => 'src/pages/anime/genre.php',
	'/^genre\/slice-of-life$/' => 'src/pages/anime/genre.php',
	'/^genre\/space$/' => 'src/pages/anime/genre.php',
	'/^genre\/sports$/' => 'src/pages/anime/genre.php',
	'/^genre\/super-power$/' => 'src/pages/anime/genre.php',
	'/^genre\/supernatural$/' => 'src/pages/anime/genre.php',
	'/^genre\/thriller$/' => 'src/pages/anime/genre.php',
	'/^genre\/vampire$/' => 'src/pages/anime/genre.php',
    
    '/^([0-9]+)$/' => 'src/pages/anime/details.php',
  	'/^watch\/([a-zA-Z0-9\-\._]+)$/' => 'src/pages/anime/watch.php',
  	'/^producer\/([a-zA-Z0-9\-\.]+)\/?$/' => 'src/pages/anime/producer.php',
  	'/^people\/([a-zA-Z0-9\-\._]+)$/' => 'src/pages/anime/people.php',
  	'/^character\/([a-zA-Z0-9\-\._]+)\/?$/' => 'src/pages/anime/character.php',
    
// User Pages
  	'/^user\/logout$/' => 'src/user/logout.php',
  	'/^user\/profile$/' => 'src/user/profile.php',
  	'/^user\/watch-list$/' => 'src/user/watchlist.php',
    '/^user\/watch-list(?:\?.*)?$/' => 'src/user/watchlist.php',
  	'/^user\/continue-watching$/' => 'src/user/continue-watching.php',
  	'/^user\/notification$/' => 'src/user/notifications.php',
  	'/^user\/settings$/' => 'src/user/settings.php',
  	'/^user\/connections$/' => 'src/user/connections.php',
    '/^auth\/anilist$/' => 'src/user/anilist_auth.php',
    '/^auth\/anilist\/callback$/' => 'src/user/anilist_callback.php',
    '/^user\/anilist\/disconnect$/' => 'src/user/anilist_disconnect.php',
    '/^user\/anilist\/sync$/' => 'src/user/anilist_sync.php',
    '/^auth\/discord$/' => 'src/user/discord_auth.php',
    '/^auth\/discord\/callback$/' => 'src/user/discord_callback.php',
    '/^user\/discord\/disconnect$/' => 'src/user/discord_disconnect.php',
    '/^community\/user\/([0-9]+)$/' => 'src/user/user.php',
       
// Extra Pages
    '/^admin$/' => 'src/pages/admin/index.php',
    '/^admin_panel$/' => 'src/pages/admin/index.php',
    '/^dmca$/' => 'src/pages/extra/dmca.php',
    '/^terms$/' => 'src/pages/extra/terms.php',
    '/^contact$/' => 'src/pages/extra/contact.php',
    '/^kvofjofh\/db-config$/' => 'db-config.php',
    '/^ajax\/contact\/submit$/' => 'src/ajax/contact/submit.php',
    
// Community Pages
    '/^community\/board$/' => 'src/pages/community/board.php',
    '/^community\/new-post$/' => 'src/pages/community/new-post.php',
    '/^community\/my-post$/' => 'src/pages/community/my-post.php',
    '/^community\/my-zone$/' => 'src/pages/community/my-zone.php',
    '/^community\/super-x$/' => 'src/pages/community/super-x.php',
    '/^community\/post\/([a-zA-Z0-9\-\._]+)\/?$/' => 'src/pages/community/post.php',
    '/^community\/user\/(\d+)\/watch-list\/?$/' => 'src/pages/community/user/watch-list.php',

// watch2gether
	'/^watch2gether$/' => 'src/pages/watch2gether/watch2gether.php',
	'/^watch2gether\/create\/([0-9]+)$/' => 'src/pages/watch2gether/create.php',
    
// Backend Files
    // Comment
		'/^ajax\/comment\/widget-home$/' => 'src/ajax/comment/widget-home.php',
    	'/^ajax\/comment\/widget\/([a-zA-Z0-9\-_]+)$/' => 'src/ajax/comment/widget.php',
    	'/^ajax\/comment\/add$/' => 'src/ajax/comment/add.php',
    	'/^ajax\/comment\/vote$/' => 'src/ajax/comment/vote.php',
    	'/^ajax\/comment\/list\/([a-zA-Z0-9\-_]+)$/' => 'src/ajax/comment/list.php',
    	'/^ajax\/comment\/replies\/([a-zA-Z0-9\-_]+)$/' => 'src/ajax/comment/replies.php',
    	'/^ajax\/comment\/report$/' => 'src/ajax/comment/report.php',
    	'/^ajax\/comment\/update$/' => 'src/ajax/comment/update.php',
    
    // Notification
    	'/^ajax\/notification\/latest$/' => 'src/ajax/notification/latest.php',
		'/^ajax\/notification\/panel$/' => 'src/ajax/notification/panel.php',
		'/^ajax\/notification\/list$/' => 'src/ajax/notification/list.php',
		'/^ajax\/notification\/seen-all$/' => 'src/ajax/notification/seen-all.php',
		'/^ajax\/notification\/clear-all$/' => 'src/ajax/notification/clear-all.php',
    
    // Watch-list
		'/^ajax\/watch-list\/add$/' => 'src/ajax/watch-list/add.php',
		'/^ajax\/watch-list\/info$/' => 'src/ajax/watch-list/info.php',
		'/^ajax\/watch-list\/info\/([a-zA-Z0-9\-_]+)$/' => 'src/ajax/watch-list/info.php',
		'/^ajax\/watch-list\/sync-client$/' => 'src/ajax/watch-list/sync-client.php',
    
    // Continue-watch
		'/^ajax\/continue-watch\/remove$/' => 'src/ajax/continue-watch/remove.php',
		'/^ajax\/continue-watch\/home$/' => 'src/ajax/continue-watch/home.php',
		'/^ajax\/continue-watch\/log$/' => 'src/ajax/continue-watch/log.php',
    
    // Settings
		'/^ajax\/user\/settings$/' => 'src/ajax/user/settings.php',
		'/^ajax\/user\/mute$/' => 'src/ajax/user/mute.php',
		'/^ajax\/user\/block$/' => 'src/ajax/user/block.php',
    
    // Episode
		'/^ajax\/v2\/episode\/list\/([a-zA-Z0-9\-_]+)$/' => 'src/ajax/v2/episode/list.php',
    	'/^ajax\/v2\/episode\/servers$/' => 'src/ajax/v2/episode/servers.php',
    	'/^ajax\/v2\/episode\/sources$/' => 'src/ajax/v2/episode/sources.php',
    
    // Auth
		'/^ajax\/login$/' => 'src/ajax/login.php',
		'/^ajax\/register$/' => 'src/ajax/register.php',
		'/^ajax\/reset-password$/' => 'src/ajax/reset-password.php',
		'/^ajax\/send-verification-email$/' => 'src/ajax/send-verification-email.php',
		'/^ajax\/user\/send-email-verify$/' => 'src/ajax/send-verification-email.php',
  
    // Users
    	'/^ajax\/user\/login-state$/' => 'src/ajax/user/login-state.php',
    	'/^ajax\/user\/profile$/' => 'src/ajax/user/profile.php',
    	'/^ajax\/user\/update$/' => 'src/ajax/user/update.php',
    	'/^ajax\/user\/blacklist$/' => 'src/ajax/user/blacklist.php',
    	'/^ajax\/user\/live-status$/' => 'src/ajax/user/live-status.php',
    
    // Schedule
		'/^ajax\/schedule\/widget$/' => 'src/ajax/schedule/widget.php',
    	'/^ajax\/schedule\/list$/' => 'src/ajax/schedule/list.php',
    
    // Character
    	'/^ajax\/character\/list\/([a-zA-Z0-9\-_]+)$/' => 'src/ajax/character/list.php',
    
    // Vote
    	'/^ajax\/vote\/info\/([a-zA-Z0-9\-_]+)$/' => 'src/ajax/vote/info.php',
    	'/^ajax\/vote\/submit$/' => 'src/ajax/vote/submit.php',
    
    // Community
    	'/^ajax\/post\/create$/' => 'src/ajax/post/create.php',
    	'/^ajax\/post\/vote$/' => 'src/ajax/post/vote.php',
    	'/^ajax\/post\/update$/' => 'src/ajax/post/update.php',
    	'/^ajax\/post\/list$/' => 'src/ajax/post/list.php',
    	'/^ajax\/post-comment\/replies\/([a-zA-Z0-9\-_]+)$/' => 'src/ajax/post-comment/replies.php',
    	'/^ajax\/post-comment\/list\/([a-zA-Z0-9\-_]+)$/' => 'src/ajax/post-comment/list.php',
    	'/^ajax\/post-comment\/report$/' => 'src/ajax/post-comment/report.php',
    	'/^ajax\/post-comment\/update$/' => 'src/ajax/post-comment/update.php',
    	'/^ajax\/post-comment\/add$/' => 'src/ajax/post-comment/add.php',
    	'/^ajax\/post-comment\/vote$/' => 'src/ajax/post-comment/vote.php',
    	'/^ajax\/post-comment\/widget\/([a-zA-Z0-9\-_]+)$/' => 'src/ajax/post-comment/widget.php',
    	'/^ajax\/community\/activity\/list$/' => 'src/ajax/community/activity/list.php',
    	'/^ajax\/community\/coin\/history$/' => 'src/ajax/community/coin/history.php',
    	'/^ajax\/community\/activity\/latest\/([0-9]+)$/' => 'src/ajax/community/activity/latest.php',
    	
    
    '/^ajax\/search\/suggest$/' => 'src/ajax/search/suggest.php',
	'/^ajax\/movie\/qtip$/' => 'src/ajax/movie/qtip.php',
    '/^ajax\/movie\/count-view\/([a-zA-Z0-9\-_]+)$/' => 'src/ajax/movie/count-view.php',
    '/^ajax\/og-image\/([a-zA-Z0-9\-_]+)$/' => 'src/ajax/og-image.php',
    '/^ajax\/og-image$/' => 'src/ajax/og-image.php',
];
// Route matching
$handled = false;
foreach ($routes as $pattern => $file) {
    if (preg_match($pattern, $uri, $matches)) {
        // Pass captured groups as GET parameters
        if (!empty($matches[1])) {
            $_GET['slug'] = $matches[1];
        }
        include $file;
        $handled = true;
        break;
    }
}
// Fallback to 404 if no route matches
if (!$handled) {
    http_response_code(404);
    include '404.php';
}
?>