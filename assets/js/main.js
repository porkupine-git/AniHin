// Main Interactive Driver for HiAnime / AnixoHindi Clone

document.addEventListener('DOMContentLoaded', function () {
    console.log('HiAnime / AnixoHindi driver initialized.');

    // 0. Initialize Swipers (Slider & Trending)
    if (typeof Swiper !== 'undefined') {
        try {
            const sliderEl = document.querySelector('#slider');
            if (sliderEl && !sliderEl.classList.contains('swiper-initialized')) {
                new Swiper('#slider', {
                    navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
                    pagination: { el: '#slider .swiper-pagination', clickable: true },
                    loop: true,
                    speed: 600,
                    autoplay: { delay: 6000, disableOnInteraction: false }
                });
            }

            const trendingEl = document.querySelector('#trending-home .swiper-container');
            if (trendingEl && !trendingEl.classList.contains('swiper-initialized')) {
                new Swiper(trendingEl, {
                    slidesPerView: 'auto',
                    spaceBetween: 20,
                    navigation: { nextEl: '.trending-navi .navi-next', prevEl: '.trending-navi .navi-prev' },
                    loop: false,
                    autoplay: { delay: 3500, disableOnInteraction: false }
                });
            }
        } catch (e) {
            console.warn('Swiper init error:', e);
        }
    }

    // 1. Back to Top Button
    const backToTop = document.getElementById('back-to-top') || document.querySelector('.btn-scroll-top');
    if (backToTop) {
        window.addEventListener('scroll', function () {
            if (window.scrollY > 400) {
                backToTop.style.display = 'flex';
            } else {
                backToTop.style.display = 'none';
            }
        });
        backToTop.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // 2. Top 10 Tab Switching (Today / Week / Month)
    const tabLinks = document.querySelectorAll('.hianime-top10-widget .nav-link, .bah-tab-min .nav-link');
    tabLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const parentUl = this.closest('ul');
            if (parentUl) {
                parentUl.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
            }
            this.classList.add('active');

            const targetId = this.getAttribute('href') || this.getAttribute('data-target');
            if (targetId && targetId.startsWith('#')) {
                const targetContent = document.querySelector(targetId);
                if (targetContent) {
                    const parentContainer = targetContent.closest('.tab-content');
                    if (parentContainer) {
                        parentContainer.querySelectorAll('.tab-pane').forEach(p => {
                            p.classList.remove('active', 'show');
                        });
                    }
                    targetContent.classList.add('active', 'show');
                }
            }
        });
    });

    // 3. Hamburger Mobile Menu Toggle
    const sidebarToggle = document.getElementById('sidebar_menu_toggle') || document.querySelector('.btn-sidebar');
    const sidebarMenu = document.getElementById('sidebar_menu');
    const sidebarBg = document.getElementById('sidebar_menu_bg');

    if (sidebarToggle && sidebarMenu) {
        sidebarToggle.addEventListener('click', function (e) {
            e.preventDefault();
            sidebarMenu.classList.toggle('active');
            if (sidebarBg) sidebarBg.classList.toggle('active');
        });
    }

    if (sidebarBg && sidebarMenu) {
        sidebarBg.addEventListener('click', function () {
            sidebarMenu.classList.remove('active');
            sidebarBg.classList.remove('active');
        });
    }

    // 4. Spotlight Slider Fallback Controls (if swiper not used)
    const slideItems = document.querySelectorAll('.deslide-item');
    let currentSlide = 0;

    const nextSlideBtn = document.querySelector('.deslide-next, .slide-arrow-next, .btn-next-slide');
    const prevSlideBtn = document.querySelector('.deslide-prev, .slide-arrow-prev, .btn-prev-slide');

    function showSlide(index) {
        if (!slideItems.length) return;
        slideItems.forEach((item, idx) => {
            if (idx === index) {
                item.style.display = 'block';
                item.classList.add('active');
            } else {
                item.style.display = 'none';
                item.classList.remove('active');
            }
        });
    }

    if (slideItems.length > 1 && !document.querySelector('#slider.swiper-initialized')) {
        if (nextSlideBtn) {
            nextSlideBtn.addEventListener('click', function () {
                currentSlide = (currentSlide + 1) % slideItems.length;
                showSlide(currentSlide);
            });
        }
        if (prevSlideBtn) {
            prevSlideBtn.addEventListener('click', function () {
                currentSlide = (currentSlide - 1 + slideItems.length) % slideItems.length;
                showSlide(currentSlide);
            });
        }
    }

    // 5. Watch Page Server & Episode Switching
    const serverButtons = document.querySelectorAll('.btn-server');
    serverButtons.forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const group = this.closest('.ps__-list');
            if (group) {
                group.querySelectorAll('.btn-server').forEach(b => b.classList.remove('active'));
            }
            this.classList.add('active');
            const serverName = this.getAttribute('data-server') || this.innerText;
            console.log('Switched server to:', serverName);
        });
    });

    const episodeButtons = document.querySelectorAll('.ep-item');
    const currentEpDisplay = document.getElementById('current-ep-num');
    episodeButtons.forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            episodeButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const epNum = this.getAttribute('data-number') || this.innerText;
            if (currentEpDisplay) {
                currentEpDisplay.innerText = epNum;
            }
            console.log('Playing Episode:', epNum);
        });
    });

    // 6. Search Filter in Episode Box
    const searchEpInput = document.getElementById('search-ep');
    if (searchEpInput) {
        searchEpInput.addEventListener('input', function () {
            const query = this.value.trim().toLowerCase();
            episodeButtons.forEach(btn => {
                const epNum = (btn.getAttribute('data-number') || btn.innerText).trim().toLowerCase();
                const parentItem = btn.closest('.item');
                if (epNum.includes(query)) {
                    if (parentItem) parentItem.style.display = '';
                    else btn.style.display = '';
                } else {
                    if (parentItem) parentItem.style.display = 'none';
                    else btn.style.display = 'none';
                }
            });
        });
    }

    // 7. Light Switch Toggle
    const lightSwitch = document.getElementById('turn-off-light');
    if (lightSwitch) {
        lightSwitch.addEventListener('click', function () {
            document.body.classList.toggle('theater-mode');
            const isActive = document.body.classList.contains('theater-mode');
            lightSwitch.querySelector('.tb-result').innerText = isActive ? 'ON' : '';
        });
    }

    // 8. Search Header Toggle
    const searchBtn = document.getElementById('search-trigger') || document.querySelector('.search-toggle');
    const searchBox = document.getElementById('search') || document.querySelector('.search-content');
    if (searchBtn && searchBox) {
        searchBtn.addEventListener('click', function (e) {
            e.preventDefault();
            searchBox.classList.toggle('active');
            const input = searchBox.querySelector('input');
            if (input && searchBox.classList.contains('active')) {
                input.focus();
            }
        });
    }
});
