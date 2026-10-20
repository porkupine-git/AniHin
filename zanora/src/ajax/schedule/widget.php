<?php
header('Content-Type: application/json');

$tzOffset = intval($_GET['tzOffset'] ?? 0);

$timezone = timezone_name_from_abbr('', -$tzOffset * 60, 0);
if (!$timezone) $timezone = 'UTC';
date_default_timezone_set($timezone);

$today = date('Y-m-d');

$start = new DateTime(date('Y-m-01'));
$end = new DateTime(date('Y-m-t'));

ob_start();
?>

<section class="block_area block_area_sidebar block_area-schedule schedule-full">

<div class="block_area-header">
    <div class="float-left bah-heading mr-4">
        <h2 class="cat-heading">Estimated Schedule</h2>
    </div>
    <div class="float-left bah-time">
        <span class="current-time">
            <span class="timezone"></span> 
            <span class="current-date"></span> 
            <span class="clock"></span>
        </span>
    </div>
    <div class="clearfix"></div>
</div>

<div class="block_area-content">
<div class="table_schedule">

<div class="table_schedule-date">
<div class="swiper-container">
<div class="swiper-wrapper">

<?php while ($start <= $end): ?>
<?php $dateStr = $start->format('Y-m-d'); ?>
<div class="swiper-slide day-item" data-date="<?= $dateStr ?>">
    <div class="tsd-item <?= $dateStr === $today ? 'active' : '' ?>">
        <span><?= $start->format('D') ?></span>
        <div class="date"><?= $start->format('M j') ?></div>
    </div>
</div>
<?php $start->modify('+1 day'); endwhile; ?>

</div>
</div>

<div class="ts-navigation">
    <button class="btn tsn-next"><i class="fas fa-angle-right"></i></button>
    <button class="btn tsn-prev"><i class="fas fa-angle-left"></i></button>
</div>

</div>

<div class="clearfix"></div>

<ul class="ulclear table_schedule-list limit-8" id="schedule-items"></ul>

<button class="btn btn-sm btn-block btn-showmore scl-more" style="display:none;">
</button>

</div>
</div>

</section>

<script>
(function () {

    let clockInterval;

    async function loadSchedule(date) {
        try {
            const tzOffset = new Date().getTimezoneOffset();

            const res = await fetch(`/ajax/schedule/list?date=${date}&tzOffset=${tzOffset}`);
            const data = await res.json();

            const container = document.querySelector("#schedule-block #schedule-items");
            container.innerHTML = data.html || "<li>No schedule</li>";

            const items = container.querySelectorAll("li");
            const btn = document.querySelector("#schedule-block .scl-more");
            const list = document.querySelector("#schedule-block .limit-8");

            list.classList.remove("active");
            btn.classList.remove("active");

            if (items.length > 7) {
                btn.style.display = "block";
            } else {
                btn.style.display = "none";
            }

        } catch (err) {
            console.error(err);
        }
    }

    function initClock() {
        if (clockInterval) clearInterval(clockInterval);

        function updateTime() {
            const now = new Date();

            const time = now.toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            });

            const tzOffset = -now.getTimezoneOffset() / 60;
            const tz = `GMT${tzOffset >= 0 ? '+' : ''}${tzOffset}:00`;

            document.querySelector("#schedule-block .clock").textContent = time;
            document.querySelector("#schedule-block .current-date").textContent = now.toLocaleDateString();
            document.querySelector("#schedule-block .timezone").textContent = `(${tz})`;
        }

        updateTime();
        clockInterval = setInterval(updateTime, 1000);
    }

    function initEvents() {
        document.querySelectorAll("#schedule-block .day-item").forEach(item => {
            item.onclick = function () {
                document.querySelectorAll("#schedule-block .tsd-item")
                    .forEach(el => el.classList.remove("active"));

                this.querySelector(".tsd-item").classList.add("active");

                const date = this.dataset.date;
                loadSchedule(date);
            };
        });

        document.querySelector("#schedule-block .scl-more")?.addEventListener("click", function () {
            document.querySelector("#schedule-block .limit-8")?.classList.toggle("active");
            this.classList.toggle("active");
        });
    }

    function initSwiper() {
        const activeIndex = [...document.querySelectorAll('#schedule-block .day-item')]
            .findIndex(i => i.querySelector('.tsd-item.active'));

        new Swiper('#schedule-block .swiper-container', {
            slidesPerView: 7,
            spaceBetween: 10,
            initialSlide: Math.max(activeIndex - Math.floor((window.innerWidth < 640 ? 3 : window.innerWidth < 768 ? 4 : window.innerWidth < 1024 ? 5 : 7) / 2), 0),
            navigation: {
                nextEl: '#schedule-block .tsn-next',
                prevEl: '#schedule-block .tsn-prev'
            },
            breakpoints: {
                320: { slidesPerView: 3 },
                640: { slidesPerView: 4 },
                768: { slidesPerView: 5 },
                1024: { slidesPerView: 7 }
            }
        });
    }

    initClock();
    initSwiper();
    initEvents();

    const today = new Date().toISOString().split('T')[0];
    loadSchedule(today);

})();
</script>

<?php
$html = ob_get_clean();

echo json_encode([
    "html" => $html
]);