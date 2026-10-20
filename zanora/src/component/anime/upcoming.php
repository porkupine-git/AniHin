<section class="block_area block_area_category">
    <div class="block_area-header">
        <div class="float-left bah-heading mr-4">
            <h2 class="cat-heading">Top Upcoming</h2>
        </div>
        <div class="float-right viewmore">
            <a class="btn" href="/top-upcoming">View more<i class="fas fa-angle-right ml-2"></i></a>
        </div>
        <div class="clearfix"></div>
    </div>
    <div class="tab-content">
        <?php
            if (!empty($data['topUpcoming'])) {
                $data['topUpcoming'] = array_slice($data['topUpcoming'], 0, 12);
            ?>
        <div class="block_area-content block_area-list film_list film_list-grid film_list-wfeature ">
            <div class="film_list-wrap">
                <?php foreach ($data['topUpcoming'] as $index => $anime): ?>
                <div class="flw-item">
                    <div class="film-poster">
                        <!-- Age Indicator -->
                        <?php if (!empty($anime['adultContent'])) { ?>
                        <div class="tick tick-rate">18+</div>
                        <?php } ?>

                        <!-- Anime Poster -->
                        <img class="film-poster-img lazyload" loading="lazy" decoding="async" data-src="<?= htmlspecialchars($anime['poster']) ?>"
                            alt="<?= htmlspecialchars($anime['title']) ?>">
                        <a class="film-poster-ahref item-qtip" href="/<?= htmlspecialchars($anime['id']) ?>" data-id="<?php echo $anime['data_id']; ?>" data-hasqtip="<?php echo $hasqtipCounter; ?>" aria-describedby="qtip-<?php echo $hasqtipCounter; ?>"
                            title="<?= htmlspecialchars($anime['title']) ?>">
                            <i class="fas fa-play"></i>
                        </a>
                    </div>
                    <div class="film-detail">
                        <h3 class="film-name">
                            <a href="/<?= htmlspecialchars($anime['id']) ?>"
                                title="<?= htmlspecialchars($anime['title'] ?? $anime['japanese_title']) ?>">
                                <span class="dynamic-name" data-title="<?= htmlspecialchars($anime['title']) ?>"
                                    data-jname="<?= htmlspecialchars($anime['japanese_title']) ?>">
                                    <?= htmlspecialchars($anime['title'] ?? $anime['japanese_title']) ?>
                                </span>
                            </a>
                        </h3>
                        <div class="fd-infor">
                            <?php if (!empty($anime['releaseDate'])): ?>
                            <span class="fdi-item"><i class="fas fa-calendar-alt"></i>
                                <?= htmlspecialchars($anime['releaseDate']) ?></span>
                            <?php endif; ?>
                            <span class="fdi-item"><?= htmlspecialchars($anime['tvInfo']['showType'] ?? '') ?></span>
                            <span class="dot"></span>
                            <span class="fdi-item"><?= htmlspecialchars($anime['tvInfo']['duration'] ?? '') ?></span>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <?php $hasqtipCounter++; ?>
                <?php endforeach; ?>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
    <?php } ?>
</section>