<div id="schedule-block">
    <section class="block_area block_area_sidebar block_area-schedule schedule-full">
        <div class="block_area-header">
            <div class="float-left bah-heading mr-4">
                <h2 class="cat-heading">Estimated Schedule</h2>
            </div>
            <div class="float-left bah-time">
                <span class="current-time">
                    <span id="timezone"></span>
                    <span id="current-date"></span>
                    <span id="clock"></span>
                </span>
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="block_area-content">
            <div class="table_schedule">
                <div class="table_schedule-date">
                    <div class="swiper-container swiper-container-initialized swiper-container-horizontal">
                        <div class="swiper-wrapper" id="schedule-dates">
                            <!-- Date slides will be populated dynamically -->
                        </div>
                        <span class="swiper-notification" aria-live="assertive" aria-atomic="true"></span>
                    </div>
                    <div class="ts-navigation">
                        <button class="btn tsn-next" tabindex="0" role="button" aria-label="Next slide"
                            aria-disabled="false"><i class="fas fa-angle-right"></i></button>
                        <button class="btn tsn-prev" tabindex="0" role="button" aria-label="Previous slide"
                            aria-disabled="false"><i class="fas fa-angle-left"></i></button>
                    </div>
                </div>
                <div class="clearfix"></div>
                <ul class="ulclear table_schedule-list limit-8" id="schedule-items">
                    <!-- Schedule items will be populated dynamically -->
                </ul>
                <button id="scl-more" class="btn btn-sm btn-block btn-showmore" style="display: none;"></button>
            </div>
        </div>
    </section>
    <script>
    function formatLocalDateString(date) {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    function generateDatesForMonth(year, month) {
        const dates = [];
        const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const numDays = new Date(year, month + 1, 0).getDate();
        for (let i = 1; i <= numDays; i++) {
            const d = new Date(year, month, i);
            dates.push({
                dayName: dayNames[d.getDay()],
                monthName: monthNames[d.getMonth()],
                date: d.getDate(),
                fullDate: formatLocalDateString(d)
            });
        }
        return dates;
    }

    async function fetchSchedule(date) {
        try {
            const tzOffset = new Date().getTimezoneOffset();
            const response = await fetch(`/src/ajax/schedule.php?date=${date}&tzOffset=${tzOffset}`);
            const data = await response.json();
            const scheduleItems = data.results || [];
            const container = document.getElementById('schedule-items');
            container.innerHTML = '';
            scheduleItems.forEach(item => {
                container.innerHTML += `
                <li>
                    <a href="/${item.id}" class="tsl-link">
                        <div class="time">${item.time}</div>
                        <div class="film-detail">
                            <h3 class="film-name dynamic-name" data-jname="${item.japanese_title}">${item.title}</h3>
                            <div class="fd-play">
                                <button type="button" class="btn btn-sm btn-play">
                                    <i class="fas fa-play mr-2"></i>Episode ${item.episode_no}
                                </button>
                            </div>
                        </div>
                    </a>
                </li>
            `;
            });
            document.getElementById('scl-more').style.display =
                scheduleItems.length > 7 ? 'block' : 'none';
        } catch (error) {
            console.error('Error fetching schedule:', error);
        }
    }

    function updateTime() {
        const now = new Date();
        const currentTime = now.toLocaleTimeString([], {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true
        });
        const timezoneOffset = -now.getTimezoneOffset() / 60;
        const timezone = `GMT${timezoneOffset >= 0 ? '+' : ''}${timezoneOffset}:00`;
        document.getElementById('clock').textContent = currentTime;
        document.getElementById('current-date').textContent = now.toLocaleDateString();
        document.getElementById('timezone').textContent = `(${timezone})`;
    }

    document.addEventListener('DOMContentLoaded', async function() {
        let activeDate = new Date();
        let activeYear = activeDate.getFullYear();
        let activeMonth = activeDate.getMonth();
        let selectedDate = formatLocalDateString(activeDate);

        function renderCalendar(year, month, selected) {
            const container = document.getElementById('schedule-dates');
            container.innerHTML = '';
            const dates = generateDatesForMonth(year, month);
            dates.forEach(date => {
                container.innerHTML += `
                <div class="swiper-slide day-item" data-date="${date.fullDate}" style="width: 163.8px; margin-right: 10px;">
                    <div class="tsd-item ${date.fullDate === selected ? 'active' : ''}">
                        <span>${date.dayName}</span>
                        <div class="date">${date.monthName} ${date.date}</div>
                    </div>
                </div>
            `;
            });

            // Attach event listeners for day click (after render)
            document.querySelectorAll('.day-item').forEach(item => {
                item.onclick = async function() {
                    document.querySelectorAll('.tsd-item').forEach(el => el.classList
                        .remove('active'));
                    this.querySelector('.tsd-item').classList.add('active');
                    selectedDate = this.dataset.date;
                    await fetchSchedule(selectedDate);
                };
            });
        }

        // Month navigation: only ever renders 1..last of shown month!
        document.querySelector('.tsn-prev').onclick = async function() {
            if (activeMonth === 0) {
                activeMonth = 11;
                activeYear -= 1;
            } else {
                activeMonth -= 1;
            }
            selectedDate = formatLocalDateString(new Date(activeYear, activeMonth, 1));
            renderCalendar(activeYear, activeMonth, selectedDate);
            await fetchSchedule(selectedDate);
            setupSwiper();
        };
        document.querySelector('.tsn-next').onclick = async function() {
            if (activeMonth === 11) {
                activeMonth = 0;
                activeYear += 1;
            } else {
                activeMonth += 1;
            }
            selectedDate = formatLocalDateString(new Date(activeYear, activeMonth, 1));
            renderCalendar(activeYear, activeMonth, selectedDate);
            await fetchSchedule(selectedDate);
            setupSwiper();
        };

        setInterval(updateTime, 1000);
        updateTime();

        function setupSwiper() {
            if (window.scheduleSw && window.scheduleSw.destroy) window.scheduleSw.destroy();
            window.scheduleSw = new Swiper('.schedule-full .table_schedule-date .swiper-container', {
                slidesPerView: 7,
                spaceBetween: 10,
                navigation: {
                    nextEl: '.schedule-full .tsn-next',
                    prevEl: '.schedule-full .tsn-prev',
                },
                breakpoints: {
                    320: {
                        slidesPerView: 3,
                        spaceBetween: 10
                    },
                    360: {
                        slidesPerView: 3,
                        spaceBetween: 10
                    },
                    480: {
                        slidesPerView: 3,
                        spaceBetween: 10
                    },
                    640: {
                        slidesPerView: 4,
                        spaceBetween: 10
                    },
                    768: {
                        slidesPerView: 5,
                        spaceBetween: 10
                    },
                    1024: {
                        slidesPerView: 7,
                        spaceBetween: 13
                    },
                },
            });
            // Slide to selected
            const slides = Array.from(document.querySelectorAll('.tsd-item'));
            const activeIndex = slides.findIndex(box => box.classList.contains('active'));
            if (activeIndex > -1) window.scheduleSw.slideTo(activeIndex, 1000);
        }

        // Initial render: ONLY the current month days
        renderCalendar(activeYear, activeMonth, selectedDate);
        await fetchSchedule(selectedDate);
        setupSwiper();

        document.getElementById('scl-more')?.addEventListener('click', function() {
            this.parentElement.querySelector('.limit-8')?.classList.toggle('active');
            this.classList.toggle('active');
        });
    });
    </script>
</div>