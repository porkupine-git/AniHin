<section class="block_area block_area_category">
    <div class="block_area-header">
        <div class="float-left bah-heading mr-4">
            <h2 class="cat-heading">Recommended for you</h2>
        </div>
        <div class="clearfix"></div>
    </div>
    <div class="tab-content">
        <div class="block_area-content block_area-list film_list film_list-grid film_list-wfeature">
            <div class="film_list-wrap">
                <?php if (!empty($animeData['recommendedAnimes'])): ?>
                <?php foreach ($animeData['recommendedAnimes'] as $recommendedAnime): ?>
                <div class="flw-item">
                    <div class="film-poster">
                        <?php if ($recommendedAnime['adultContent']) { ?>
                        <div class="tick tick-rate">18+</div>
                        <?php } ?>
                        <div class="tick ltr">
                            <?php if (!empty($recommendedAnime['episodes']['sub'])): ?>
                            <div class="tick-item tick-sub"><i
                                    class="fas fa-closed-captioning mr-1"></i><?= htmlspecialchars((string)$recommendedAnime['episodes']['sub']) ?>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($recommendedAnime['episodes']['dub'])): ?>
                            <div class="tick-item tick-dub"><i
                                    class="fas fa-microphone mr-1"></i><?= htmlspecialchars((string)$recommendedAnime['episodes']['dub']) ?>
                            </div>
                            <?php endif; ?>
                            <?php if (isset($recommendedAnime['episodes']['eps']) && $recommendedAnime['episodes']['eps'] !== null): ?>
                            <div class="tick-item tick-eps">
                                <?php echo htmlspecialchars((string)$recommendedAnime['episodes']['eps']); ?></div>
                            <?php endif; ?>
                        </div>
                        <img data-src="<?= htmlspecialchars((string)($recommendedAnime['poster'] ?? '')) ?>"
                            class="film-poster-img lazyload"
                            loading="lazy" decoding="async"
                            alt="<?= htmlspecialchars((string)($recommendedAnime['name'] ?? ($recommendedAnime['japa'] ?? ''))) ?>">
                        <a href="/<?= htmlspecialchars((string)($recommendedAnime['id'] ?? '')) ?>"
                            class="film-poster-ahref item-qtip"
                            data-id="<?= htmlspecialchars((string)($recommendedAnime['data_id'] ?? '')) ?>" data-hasqtip="<?php echo $hasqtipCounter; ?>" 
                            oldtitle="<?= htmlspecialchars((string)($recommendedAnime['name'] ?? ($recommendedAnime['japa'] ?? ''))) ?>"
                            title="" aria-describedby="qtip-<?php echo $hasqtipCounter; ?>"><i class="fas fa-play"></i></a>
                    </div>
                    <div class="film-detail">
                        <h3 class="film-name">
                            <a href="/<?= htmlspecialchars((string)($recommendedAnime['id'] ?? '')) ?>"
                                title="<?= htmlspecialchars((string)($recommendedAnime['name'] ?? ($recommendedAnime['japa'] ?? ''))) ?>">
                                <span class="dynamic-name"
                                    data-title="<?= htmlspecialchars((string)($recommendedAnime['name'] ?? '')) ?>"
                                    data-jname="<?= htmlspecialchars((string)($recommendedAnime['japa'] ?? '')) ?>">
                                    <?= htmlspecialchars((string)($recommendedAnime['name'] ?? ($recommendedAnime['japa'] ?? ''))) ?>
                                </span>
                            </a>
                        </h3>
                        <div class="fd-infor">
                            <span class="fdi-item"><?= htmlspecialchars((string)($recommendedAnime['type'] ?? '')) ?></span>
                            <span class="dot"></span>
                            <span
                                class="fdi-item fdi-duration"><?= htmlspecialchars((string)($recommendedAnime['duration'] ?? '')) ?></span>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
				<?php $hasqtipCounter++; ?>
                <?php endforeach; ?>
                <?php else: ?>
                <p>No recommended animes available</p>
                <?php endif; ?>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
</section>