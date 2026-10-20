// Reusable Footer Component

export function renderFooter() {
    const letters = "0-9 A B C D E F G H I J K L M N O P Q R S T U V W X Y Z".split(' ');

    return `
    <footer id="footer" style="background:#14131d; padding:40px 0 20px; border-top:1px solid rgba(255,255,255,0.06); margin-top:50px;">
        <div class="container">
            <div class="footer-az mb-4 text-center">
                <span class="text-muted mr-2" style="font-size:12px; font-weight:600;">A-Z LIST:</span>
                <div class="d-inline-flex flex-wrap justify-content-center" style="gap:6px;">
                    ${letters.map(l => `<a href="browse.html?letter=${l}" class="btn btn-sm btn-dark" style="padding:2px 8px; font-size:11px; background:rgba(255,255,255,0.05); border:none; color:#bbb;">${l}</a>`).join('')}
                </div>
            </div>

            <div class="row align-items-center mb-4">
                <div class="col-md-4 text-center text-md-left mb-3 mb-md-0">
                    <img src="assets/images/logo.png" alt="HiAnime" style="height:36px;" class="mb-2">
                    <p class="text-muted mb-0" style="font-size:12px;">Watch anime online in HD quality with Hindi Dubbed & Subbed streams for free.</p>
                </div>
                <div class="col-md-8 text-center text-md-right">
                    <div class="footer-links" style="font-size:13px;">
                        <a href="index.html" class="text-light mr-3">Home</a>
                        <a href="browse.html" class="text-light mr-3">Browse Anime</a>
                        <a href="browse.html?filter=dub" class="text-light mr-3">Hindi Dubbed</a>
                        <a href="browse.html?filter=movies" class="text-light mr-3">Movies</a>
                        <a href="watch.html" class="text-light mr-3">Watch2gether</a>
                        <a href="javascript:;" class="text-light mr-3">Terms of Service</a>
                        <a href="javascript:;" class="text-light">Contact</a>
                    </div>
                </div>
            </div>

            <div class="text-center text-muted pt-3" style="border-top:1px solid rgba(255,255,255,0.04); font-size:11.5px;">
                © 2026 HiAnime / AnixoHindi. All anime content, trademarks and copyrights belong to their respective owners.
            </div>
        </div>
    </footer>
    `;
}
