<div id="anime-trending">
    <div class="container">
        <section class="block_area block_area_trending">
            <div class="block_area-header">
                <div class="bah-heading">
                    <h2 class="cat-heading">Trending</h2>
                </div>
                <div class="clearfix"></div>
            </div>
            <?php
            if (!empty($data['trending'])) {
                $data['trending'] = array_slice($data['trending'], 0, 10);
            ?>
            <div class="block_area-content">
                <div class="trending-list" id="trending-home" style="display: none;">
                    <div class="swiper-container">
                        <div class="swiper-wrapper">
                            <?php foreach ($data['trending'] as $item): ?>
                            <div class="swiper-slide item-qtip" data-id="<?php echo $item['data_id']; ?>" data-hasqtip="<?php echo $hasqtipCounter; ?>" aria-describedby="qtip-<?php echo $hasqtipCounter; ?>">
                                <div class="item">
                                    <div class="number">
                                        <span><?= htmlspecialchars($item['number']) ?></span>
                                        <div class="film-title dynamic-name"
                                            data-title="<?= htmlspecialchars($item['title']) ?>"
                                            data-jname="<?= htmlspecialchars($item['japanese_title']) ?>">
                                            <?= htmlspecialchars($item['title']) ?>
                                        </div>
                                    </div>
                                    <a href="/<?= htmlspecialchars($item['id']) ?>" class="film-poster">
                                        <img data-src="<?= htmlspecialchars($item['poster']) ?>"
                                            class="film-poster-img lazyload"
                                            alt="<?= htmlspecialchars($item['title']) ?>"
                                            loading="lazy" decoding="async">
                                    </a>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                            <?php $hasqtipCounter++; ?>
                            <?php endforeach; ?>
                        </div>
                        <div class="clearfix"></div>
                        <span class="swiper-notification" aria-live="assertive" aria-atomic="true"></span>
                    </div>
                    <div class="trending-navi">
                        <div class="navi-next" tabindex="0" role="button" aria-label="Next slide" aria-disabled="false">
                            <i class="fas fa-angle-right"></i></div>
                        <div class="navi-prev swiper-button-disabled" tabindex="-1" role="button"
                            aria-label="Previous slide" aria-disabled="true"><i class="fas fa-angle-left"></i></div>
                    </div>
                </div>
            </div>
            <?php } ?>
        </section>
    </div>
</div>