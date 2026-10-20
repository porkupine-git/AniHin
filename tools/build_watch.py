import re

watch_raw_path = r'C:\Users\Ritesh\.gemini\antigravity-ide\brain\f8daebcc-cad5-41b8-8947-1db54d4dffb4\watch_raw.html'
output_path = r'c:\Users\Ritesh\Downloads\anixohindi\watch.html'

with open(watch_raw_path, 'r', encoding='utf-8', errors='ignore') as f:
    html = f.read()

# Replace CSS
html = re.sub(r'<link rel="stylesheet" href="https://hianime\.at/theme/css/app\.min\.css[^"]*">',
              '<link rel="stylesheet" href="assets/css/app.min.css">\n    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">\n    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap">\n    <link rel="stylesheet" href="assets/css/custom.css">', html)

html = re.sub(r'<link rel="stylesheet" href="https://hianime\.at/build/assets/frontend-DDmvDXjb\.css[^"]*"\s*/?>',
              '<link rel="stylesheet" href="assets/css/frontend.css">', html)

# Replace JS
html = re.sub(r'<script data-cfasync="false" src="https://hianime\.at/theme/js/vendor\.min\.js[^"]*"></script>',
              '<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>\n<script src="assets/js/vendor.min.js"></script>', html)

html = re.sub(r'<script data-cfasync="false" src="https://hianime\.at/theme/js/theme\.min\.js[^"]*"></script>',
              '<script src="assets/js/theme.min.js"></script>', html)

html = re.sub(r'<script data-cfasync="false" src="https://hianime\.at/theme/js/jquery\.qtip\.min\.js[^"]*"></script>',
              '<script src="assets/js/jquery.qtip.min.js"></script>\n<script src="assets/js/main.js"></script>', html)

# Remove modulepreload / build script tags
html = re.sub(r'<script type="module" src="https://hianime\.at/build/assets/[^"]*".*?</script>', '', html)
html = re.sub(r'<link rel="modulepreload"[^>]*>', '', html)
html = re.sub(r'<link rel="preload" as="style" href="https://hianime\.at/build/assets/[^"]*" />', '', html)
html = re.sub(r'<script async src="https://www.googletagmanager.com[^"]*"></script>', '', html)
html = re.sub(r'gtag\([^\)]*\);?', '', html)

# Navigation links
html = re.sub(r'href="https://hianime\.at/home"', 'href="index.html"', html)
html = re.sub(r'href="/home"', 'href="index.html"', html)
html = re.sub(r'href="https://hianime\.at/"', 'href="index.html"', html)
html = re.sub(r'href="/"', 'href="index.html"', html)

# Watch links -> watch.html?anime=...
def fix_watch_link(match):
    full_url = match.group(1)
    slug = full_url.rstrip('/').split('/')[-1]
    return f'href="watch.html?anime={slug}"'

html = re.sub(r'href="https://hianime\.at/watch/([^"]+)"', fix_watch_link, html)
html = re.sub(r'href="/watch/([^"]+)"', fix_watch_link, html)

categories = [
    'recently-updated', 'recently-added', 'top-airing', 'most-popular', 'most-favorite', 
    'completed', 'filter', 'subbed-anime', 'dubbed-anime', 'movie', 'tv', 'ova', 'ona', 'special',
    'genre/[^"]+', 'az-list'
]
for cat in categories:
    html = re.sub(rf'href="https://hianime\.at/{cat}"', 'href="browse.html"', html)
    html = re.sub(rf'href="/{cat}"', 'href="browse.html"', html)

html = re.sub(r'https://hianime\.at/theme/images/logo\.png', 'assets/images/logo.png', html)

# Populate video player into .player-frame
player_html = '''
<div class="video-player-container">
    <iframe id="anime-video-player" src="https://www.youtube-nocookie.com/embed/fJ9rUzIMcZQ?autoplay=0&rel=0&modestbranding=1" 
        title="Anime Stream Player" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen>
    </iframe>
</div>
'''
html = html.replace('<div class="loading-relative loading-box" id="embed-loading">', player_html + '<div class="loading-relative loading-box d-none" id="embed-loading">')

# Populate #servers-content
servers_html = '''
<div class="ps_-block ps_-block-sub">
    <div class="ps__-title"><i class="fas fa-closed-captioning mr-1 text-success"></i> SUB:</div>
    <div class="ps__-list">
        <div class="item"><a class="btn btn-server active" href="javascript:;" data-server="hd-1">HD-1</a></div>
        <div class="item"><a class="btn btn-server" href="javascript:;" data-server="hd-2">HD-2</a></div>
        <div class="item"><a class="btn btn-server" href="javascript:;" data-server="megacloud">MegaCloud</a></div>
        <div class="item"><a class="btn btn-server" href="javascript:;" data-server="streamsb">StreamSB</a></div>
    </div>
</div>
<div class="ps_-block ps_-block-dub" style="border-top: 1px solid rgba(255,255,255,0.06); padding-top: 12px; margin-top: 8px;">
    <div class="ps__-title"><i class="fas fa-microphone mr-1 text-info"></i> DUB (Hindi):</div>
    <div class="ps__-list">
        <div class="item"><a class="btn btn-server active" href="javascript:;" data-server="hindi-1" style="background:#ffbade; color:#111; font-weight:700;"><i class="fas fa-play mr-1"></i> Hindi HD-1</a></div>
        <div class="item"><a class="btn btn-server" href="javascript:;" data-server="hindi-2">Hindi HD-2</a></div>
        <div class="item"><a class="btn btn-server" href="javascript:;" data-server="eng-dub">English Dub</a></div>
    </div>
</div>
'''
html = html.replace('<div id="servers-content"></div>', f'<div id="servers-content">{servers_html}</div>')

# Populate #episodes-content
ep_items = ""
for ep in range(1, 25):
    active_cls = " active" if ep == 1 else ""
    ep_items += f'''
    <div class="item" style="display:inline-block; margin: 4px;">
        <a class="btn ep-item{active_cls}" href="javascript:;" data-number="{ep}" title="Episode {ep}" style="min-width:44px; padding:6px 12px; border-radius:4px; font-weight:600; font-size:13px; background:rgba(255,255,255,0.07); color:#fff;">{ep}</a>
    </div>
    '''

episodes_html = f'''
<div class="episodes-list" style="background: #252438; border-radius: 8px; padding: 18px; margin-top: 20px;">
    <div class="episodes-list-header d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="cat-heading mb-0" style="color:#ffbade; font-size:16px; font-weight:600;">List of Episodes: (Currently Playing: Ep <span id="current-ep-num">1</span>)</h4>
        </div>
        <div class="ep-search" style="max-width: 180px;">
            <input type="text" class="form-control form-control-sm" placeholder="Number of Ep" id="search-ep" style="background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.1); color:#fff; border-radius:20px; padding: 4px 14px;">
        </div>
    </div>
    <div class="episodes-ul-wrapper" style="max-height: 250px; overflow-y: auto;">
        <div class="episodes-ul">
            {ep_items}
        </div>
    </div>
</div>
'''
html = html.replace('<div id="episodes-content">\n                        </div>', f'<div id="episodes-content">{episodes_html}</div>')

# Discussion / Comments Box
comments_html = '''
<div class="comments-section mt-4" style="background: #252438; border-radius: 8px; padding: 22px;">
    <h3 class="cat-heading mb-3" style="color:#ffbade; font-size:18px;"><i class="fas fa-comments mr-2"></i>Community Discussion (Hindi & Sub)</h3>
    <div class="comment-input-box mb-4">
        <textarea class="form-control mb-2" rows="3" placeholder="Join the discussion... (Share your thoughts on this episode!)" style="background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.1); color:#fff; border-radius:8px;"></textarea>
        <button class="btn btn-sm" style="background:#ffbade; color:#111; font-weight:600; border-radius:20px; padding:6px 20px;"><i class="fas fa-paper-plane mr-1"></i> Post Comment</button>
    </div>
    <div class="comments-list">
        <div class="comment-item d-flex mb-3 pb-3" style="border-bottom: 1px solid rgba(255,255,255,0.06);">
            <div class="avatar mr-3" style="width:42px; height:42px; border-radius:50%; background:#ffbade; color:#111; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:16px;">R</div>
            <div class="content flex-grow-1">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <strong style="color:#fff; font-size:14px;">Rohit Sharma <span class="badge badge-pill badge-primary ml-2" style="background:#ffbade; color:#111; font-size:10px;">Hindi Fan</span></strong>
                    <span class="text-muted" style="font-size:12px;">15 mins ago</span>
                </div>
                <p class="mb-1 text-light" style="font-size:13.5px;">Bhai Hindi dubbing next level hai! Audio quality and voice acting ekdum mast hai.</p>
                <div class="comment-actions text-muted" style="font-size:12px;">
                    <a href="javascript:;" class="text-muted mr-3"><i class="fas fa-thumbs-up mr-1"></i> 24</a>
                    <a href="javascript:;" class="text-muted"><i class="fas fa-reply mr-1"></i> Reply</a>
                </div>
            </div>
        </div>
        <div class="comment-item d-flex mb-3 pb-3" style="border-bottom: 1px solid rgba(255,255,255,0.06);">
            <div class="avatar mr-3" style="width:42px; height:42px; border-radius:50%; background:#b9ffc6; color:#111; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:16px;">A</div>
            <div class="content flex-grow-1">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <strong style="color:#fff; font-size:14px;">Aarav Sen</strong>
                    <span class="text-muted" style="font-size:12px;">1 hour ago</span>
                </div>
                <p class="mb-1 text-light" style="font-size:13.5px;">The animation and pacing in episode 1 is phenomenal. Can't wait for episode 2!</p>
                <div class="comment-actions text-muted" style="font-size:12px;">
                    <a href="javascript:;" class="text-muted mr-3"><i class="fas fa-thumbs-up mr-1"></i> 18</a>
                    <a href="javascript:;" class="text-muted"><i class="fas fa-reply mr-1"></i> Reply</a>
                </div>
            </div>
        </div>
    </div>
</div>
'''
html = html.replace('<div id="comment-widget"></div>', f'<div id="comment-widget">{comments_html}</div>')

with open(output_path, 'w', encoding='utf-8') as f:
    f.write(html)

print("Created watch.html, size:", len(html))
