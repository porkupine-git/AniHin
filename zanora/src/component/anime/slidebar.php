<div class="deslide-wrap">
    <div class="container">
        <div id="slider">
            <div class="swiper-wrapper">
                <?php foreach ($data['spotlights'] as $index => $anime): ?>
                <div class="swiper-slide">
                    <div class="deslide-item">
                        <div class="deslide-cover">
                            <div class="deslide-cover-img">
                                <img class="film-poster-img <?= $index === 0 ? '' : 'lazyload' ?>" 
                                     <?= $index === 0 ? 'src="' . htmlspecialchars($anime['banner'] ?: $anime['poster']) . '" fetchpriority="high"' : 'data-src="' . htmlspecialchars($anime['banner'] ?: $anime['poster']) . '" loading="lazy"' ?>
                                     decoding="async"
                                     alt="<?= htmlspecialchars($anime['title']) ?>">
                            </div>
                        </div>
                        <div class="deslide-item-content">
                            <div class="desi-sub-text">#<?= $index + 1 ?> Spotlight</div>
                            <div class="desi-head-title dynamic-name"
                                data-title="<?= htmlspecialchars($anime['title']) ?>"
                                data-jname="<?= htmlspecialchars($anime['japanese_title']) ?>">
                                <?= htmlspecialchars($anime['title']) ?></div>
                            <div class="sc-detail">
                                <div class="scd-item"><i
                                        class="fas fa-play-circle mr-1"></i>&nbsp;<?= htmlspecialchars($anime['tvInfo']['showType']) ?>
                                </div>
                                <?php if (!empty($anime['tvInfo']['duration'])): ?>
                                <div class="scd-item"><i
                                        class="fas fa-clock mr-1"></i>&nbsp;<?= htmlspecialchars($anime['tvInfo']['duration']) ?>
                                </div>
                                <?php endif; ?>
                                <div class="scd-item m-hide"><i
                                        class="fa-solid fa-calendar-days mr-1"></i>&nbsp;<?= htmlspecialchars($anime['tvInfo']['releaseDate']) ?>
                                </div>
                                <div class="scd-item mr-1"><span
                                        class="quality">&nbsp;<?= htmlspecialchars($anime['tvInfo']['quality']) ?></span>
                                </div>
                                <div class="scd-item">
                                    <div class="tick">
                                        <?php if (!empty($anime['tvInfo']['episodeInfo']['sub'])): ?>
                                        <div class="tick-item tick-sub"><i class="fas fa-closed-captioning mr-1"></i>
                                            <?= htmlspecialchars($anime['tvInfo']['episodeInfo']['sub']) ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($anime['tvInfo']['episodeInfo']['dub'])): ?>
                                        <div class="tick-item tick-dub"><i class="fas fa-microphone mr-1"></i>
                                            <?= htmlspecialchars($anime['tvInfo']['episodeInfo']['dub']) ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($anime['tvInfo']['episodeInfo']['eps'])): ?>
                                        <div class="tick-item tick-eps">
                                            <?php echo htmlspecialchars($anime['tvInfo']['episodeInfo']['eps']); ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                            <div class="desi-description"><?= htmlspecialchars($anime['description']) ?></div>
                            <div class="desi-buttons">
                                <a href="/watch/<?= htmlspecialchars($anime['id']) ?>"
                                    class="btn btn-primary btn-radius mr-2"><i class="fas fa-play-circle mr-2"></i>Watch
                                    Now</a>
                                <a href="/<?= htmlspecialchars($anime['id']) ?>"
                                    class="btn btn-secondary btn-radius">Detail<i
                                        class="fas fa-angle-right ml-2"></i></a>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="swiper-pagination swiper-pagination-clickable swiper-pagination-bullets"></div>
            <div class="swiper-navigation">
                <div class="swiper-button swiper-button-next"><i class="fas fa-angle-right"></i></div>
                <div class="swiper-button swiper-button-prev"><i class="fas fa-angle-left"></i></div>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
</div>