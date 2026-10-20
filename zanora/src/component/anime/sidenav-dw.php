<div id="main-sidebar">
    <?php if (!empty($animeData['relatedAnimes'])): ?>
    <section class="block_area block_area_sidebar block_area-realtime">
        <div class="block_area-header">
            <div class="float-left bah-heading mr-4">
                <h2 class="cat-heading">Related Anime</h2>
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="block_area-content">
            <div class="cbox cbox-list cbox-realtime cbox-collapse">
                <div class="cbox-content">
                    <div class="anif-block-ul">
                        <ul class="ulclear" id="related-anime-list">
                            <?php foreach ($animeData['relatedAnimes'] as $anime): ?>
                            <li>
                                <div class="film-poster item-qtip" data-id="<?= $anime['data_id']; ?>" data-hasqtip="<?php echo $hasqtipCounter; ?>" aria-describedby="qtip-<?php echo $hasqtipCounter; ?>">
                                    <img data-src="<?= htmlspecialchars((string)($anime['poster'] ?? '')) ?>" 
                                         class="film-poster-img lazyload"
                                         loading="lazy" decoding="async"
                                        alt="<?= htmlspecialchars((string)($anime['name'] ?? ($anime['japa'] ?? ''))) ?>">
                                </div>
                                <div class="film-detail">
                                    <h3 class="film-name">
                                        <a href="/<?= htmlspecialchars((string)($anime['id'] ?? '')) ?>"
                                            title="<?= htmlspecialchars((string)($anime['name'] ?? ($anime['japa'] ?? ''))) ?>">
                                            <span class="dynamic-name"
                                                data-title="<?= htmlspecialchars((string)($anime['name'] ?? '')) ?>"
                                                data-jname="<?= htmlspecialchars((string)($anime['japa'] ?? '')) ?>">
                                                <?= htmlspecialchars((string)($anime['name'] ?? ($anime['japa'] ?? ''))) ?>
                                            </span>
                                        </a>
                                    </h3>
                                    <div class="fd-infor mt-2">
                                        <div class="tick">
                                            <?php if (!empty($anime['episodes']['sub'])): ?>
                                            <div class="tick-item tick-sub">
                                                <i
                                                    class="fas fa-closed-captioning mr-1"></i><?= $anime['episodes']['sub'] ?>
                                            </div>
                                            <?php endif; ?>
                                            <?php if (!empty($anime['episodes']['dub'])): ?>
                                            <div class="tick-item tick-dub">
                                                <i class="fas fa-microphone mr-1"></i><?= $anime['episodes']['dub'] ?>
                                            </div>
                                            <?php endif; ?>
                                            <?php if (!empty($anime['episodes']['eps'])): ?>
                                            <div class="tick-item tick-eps"><?= $anime['episodes']['eps'] ?></div>
                                            <?php endif; ?>
                                            <div class="dot"></div>
                                            <?= ucfirst($anime['type']) ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="film-fav wl-item" data-movieid="<?= $anime['id'] ?>">
                                    <i class="fa fa-plus"></i>
                                </div>
                                <div class="clearfix"></div>
                            </li>
                            <?php $hasqtipCounter++; ?>
                            <?php endforeach; ?>
                        </ul>
                        <div class="clearfix"></div>
                    </div>
                    <div class="clearfix"></div>
                    <?php if (count($animeData['relatedAnimes']) > 6): ?>
                    <button class="btn btn-sm btn-block btn-showmore mt-2 mb-3"></button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>
    <section class="block_area block_area_sidebar block_area-realtime">
        <div class="block_area-header">
            <div class="float-left bah-heading mr-4">
                <h2 class="cat-heading">Most Popular</h2>
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="block_area-content">
            <div class="cbox cbox-list cbox-realtime">
                <div class="cbox-content">
                    <div class="anif-block-ul">
                        <ul class="ulclear">
                            <?php foreach ($animeData['popularAnimes'] as $anime): ?>
                            <li>
                                <div class="film-poster item-qtip" data-id="<?= $anime['data_id']; ?>" data-hasqtip="<?php echo $hasqtipCounter; ?>" aria-describedby="qtip-<?php echo $hasqtipCounter; ?>">
                                    <img data-src="<?= htmlspecialchars((string)($anime['poster'] ?? '')) ?>" 
                                         class="film-poster-img lazyload"
                                         loading="lazy" decoding="async"
                                        alt="<?= htmlspecialchars((string)($anime['name'] ?? ($anime['japa'] ?? ''))) ?>">
                                </div>
                                <div class="film-detail">
                                    <h3 class="film-name">
                                        <a href="/<?= htmlspecialchars((string)($anime['id'] ?? '')) ?>"
                                            title="<?= htmlspecialchars((string)($anime['name'] ?? ($anime['japa'] ?? ''))) ?>">
                                            <span class="dynamic-name"
                                                data-title="<?= htmlspecialchars((string)($anime['name'] ?? '')) ?>"
                                                data-jname="<?= htmlspecialchars((string)($anime['japa'] ?? '')) ?>">
                                                <?= htmlspecialchars((string)($anime['name'] ?? ($anime['japa'] ?? ''))) ?>
                                            </span>
                                        </a>
                                    </h3>
                                    <div class="fd-infor mt-2">
                                        <div class="tick">
                                            <?php if (!empty($anime['episodes']['sub'])): ?>
                                            <div class="tick-item tick-sub">
                                                <i
                                                    class="fas fa-closed-captioning mr-1"></i><?= $anime['episodes']['sub'] ?>
                                            </div>
                                            <?php endif; ?>
                                            <?php if (!empty($anime['episodes']['dub'])): ?>
                                            <div class="tick-item tick-dub">
                                                <i class="fas fa-microphone mr-1"></i><?= $anime['episodes']['dub'] ?>
                                            </div>
                                            <?php endif; ?>
                                            <?php if (!empty($anime['episodes']['eps'])): ?>
                                            <div class="tick-item tick-eps"><?= $anime['episodes']['eps'] ?></div>
                                            <?php endif; ?>
                                            <div class="dot"></div>
                                            <?= ucfirst($anime['type']) ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="film-fav wl-item" data-movieid="<?= $anime['id'] ?>">
                                    <i class="fa fa-plus"></i>
                                </div>
                                <div class="clearfix"></div>
                            </li>
                            <?php $hasqtipCounter++; ?>
                            <?php endforeach; ?>
                        </ul>
                        <div class="clearfix"></div>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
    </section>
</div>