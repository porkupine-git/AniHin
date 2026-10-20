<section class="block_area block_area_category lazy-component" data-component="category">

    <div class="block_area-header">

        <div class="float-left bah-heading mr-4">
            <h2 class="cat-heading">
                Last Season
            </h2>
        </div>

        <div class="float-right viewmore">
            <a class="btn" href="/last-season">
                View more
                <i class="fas fa-angle-right ml-2"></i>
            </a>
        </div>

        <div class="clearfix"></div>
    </div>

    <div class="tab-content">

        <?php
        if (!empty($data['lastSeason'])) {

            $data['lastSeason'] =
                array_slice(
                    $data['lastSeason'],
                    0,
                    12
                );
        ?>

        <div class="block_area-content block_area-list film_list film_list-grid film_list-wfeature">

            <div class="film_list-wrap">

                <?php foreach ($data['lastSeason'] as $index => $anime): ?>

                <div class="flw-item">

                    <div class="film-poster">

                        <!-- Adult -->
                        <?php if (!empty($anime['tvInfo']['rating'])) { ?>
                        <div class="tick tick-rate">
                            18+
                        </div>
                        <?php } ?>

                        <!-- Episode Info -->
                        <div class="tick ltr">

                            <?php if (!empty($anime['tvInfo']['sub'])): ?>
                            <div class="tick-item tick-sub amp-algn" style="text-align: left;">
                                <i class="fas fa-closed-captioning"></i>
                                &nbsp;
                                <?= htmlspecialchars($anime['tvInfo']['sub']) ?>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($anime['tvInfo']['dub'])): ?>
                            <div class="tick-item tick-dub amp-algn" style="text-align: left;">
                                <i class="fas fa-microphone"></i>
                                &nbsp;
                                <?= htmlspecialchars($anime['tvInfo']['dub']) ?>
                            </div>
                            <?php endif; ?>

                            <?php if (isset($anime['tvInfo']['eps'])): ?>
                            <div class="tick-item tick-eps">
                                <?= $anime['tvInfo']['eps']; ?>
                            </div>
                            <?php endif; ?>

                        </div>

                        <!-- Poster -->
                        <img
                            class="film-poster-img lazyload"
                            loading="lazy"
                            decoding="async"
                            data-src="<?= $anime['poster'] ?>"
                            alt="<?= htmlspecialchars($anime['title']) ?>"
                        >

                        <a
                            href="/watch/<?= $anime['id'] ?>"
                            class="film-poster-ahref item-qtip"
                            title="<?= htmlspecialchars($anime['title']) ?>"
                            data-id="<?= $anime['data_id']; ?>"
                            data-hasqtip="<?php echo $hasqtipCounter; ?>"
                            aria-describedby="qtip-<?php echo $hasqtipCounter; ?>"
                            data-jname="<?= htmlspecialchars($anime['title']) ?>"
                        >
                            <i class="fas fa-play"></i>
                        </a>

                    </div>

                    <div class="film-detail">

                        <h3 class="film-name">

                            <a
                                href="/<?= $anime['id'] ?>"
                                class="dynamic-name"
                                data-title="<?= htmlspecialchars($anime['title']) ?>"
                                data-jname="<?= htmlspecialchars($anime['japanese_title']) ?>"
                            >
                                <?= htmlspecialchars($anime['title']) ?>
                            </a>

                        </h3>

                        <div class="fd-infor">

                            <?php if (!empty($anime['tvInfo']['showType'])): ?>
                            <span class="fdi-item">
                                <?= htmlspecialchars($anime['tvInfo']['showType']) ?>
                            </span>
                            <?php endif; ?>

                            <?php if (!empty($anime['tvInfo']['duration'])): ?>
                            <span class="dot"></span>

                            <span class="fdi-item">
                                <?= htmlspecialchars($anime['tvInfo']['duration']) ?>
                            </span>
                            <?php endif; ?>

                        </div>

                    </div>

                    <div class="clearfix"></div>
                </div>

                <?php $hasqtipCounter++; ?>

                <?php endforeach; ?>

            </div>

            <div class="clearfix"></div>
        </div>

        <?php } ?>

    </div>
</section>