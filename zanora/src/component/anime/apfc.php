<div class="container">
    <div class="anif-blocks">
        <div class="row">
            <?php
            if (!empty($data['topAiring'])) {
                $data['topAiring'] = array_slice($data['topAiring'], 0, 5);
            ?>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 col-xs-12">
                <div class="anif-block anif-block-01">
                    <div class="anif-block-header">Top Airing</div>
                    <div class="anif-block-ul">
                        <ul class="ulclear">
                            <?php foreach ($data['topAiring'] as $index => $anime): ?>
                            <li>
                                <div class="film-poster item-qtip" data-id="<?php echo $anime['data_id']; ?>" data-hasqtip="<?php echo $hasqtipCounter; ?>" aria-describedby="qtip-<?php echo $hasqtipCounter; ?>">
                                    <a href="/<?php echo htmlspecialchars($anime['id']); ?>">
                                        <img data-src="<?php echo htmlspecialchars($anime['poster']); ?>"
                                            class="film-poster-img lazyload"
                                            alt="<?php echo htmlspecialchars($anime['title']); ?>"
                                            loading="lazy" decoding="async">
                                    </a>
                                </div>
                                <div class="film-detail">
                                    <h3 class="film-name">
                                        <a href="/<?php echo $anime['id']; ?>"
                                            title="<?php echo htmlspecialchars($anime['title']); ?>"
                                            class="dynamic-name anime-toggle"
                                            data-title="<?php echo htmlspecialchars($anime['title']); ?>"
                                            data-jname="<?php echo htmlspecialchars($anime['japanese_title']); ?>"
                                            style="opacity: 1;">
                                            <?php echo htmlspecialchars($anime['title']); ?>
                                        </a>
                                    </h3>
                                    <div class="fd-infor">
                                        <div class="tick">
                                            <?php if(isset($anime['tvInfo']['sub'])): ?>
                                            <div class="tick-item tick-sub">
                                                <i
                                                    class="fas fa-closed-captioning mr-1"></i><?php echo $anime['tvInfo']['sub']; ?>
                                            </div>
                                            <?php endif; ?>
                                            <?php if(isset($anime['tvInfo']['dub'])): ?>
                                            <div class="tick-item tick-dub">
                                                <i
                                                    class="fas fa-microphone mr-1"></i><?php echo $anime['tvInfo']['dub']; ?>
                                            </div>
                                            <?php endif; ?>
                                            <?php if (isset($anime['tvInfo']['eps'])): ?>
                                            <div class="tick-item tick-eps"><?php echo $anime['tvInfo']['eps']; ?></div>
                                            <?php endif; ?>
                                            <div>
                                                <span class="dot"></span><?php echo $anime['tvInfo']['showType']; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </li>
                            <?php $hasqtipCounter++; ?>
                            <?php endforeach; ?>
                        </ul>
                        <div class="clearfix"></div>
                    </div>
                    <div class="more"><a href="/top-airing">View more <i class="fas fa-angle-right ml-2"></i></a>
                    </div>
                </div>
            </div>
            <?php } ?>
            <?php
            if (!empty($data['mostPopular'])) {
                $data['mostPopular'] = array_slice($data['mostPopular'], 0, 5);
            ?>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 col-xs-12">
                <div class="anif-block anif-block-04">
                    <div class="anif-block-header">Most Popular</div>
                    <div class="anif-block-ul">
                        <ul class="ulclear">
                            <?php foreach($data['mostPopular'] as $anime): ?>
                            <li>
                                <div class="film-poster item-qtip" data-id="<?php echo $anime['data_id']; ?>" data-hasqtip="<?php echo $hasqtipCounter; ?>" aria-describedby="qtip-<?php echo $hasqtipCounter; ?>">
                                    <a href="/<?php echo htmlspecialchars($anime['id']); ?>">
                                        <img data-src="<?php echo htmlspecialchars($anime['poster']); ?>"
                                            class="film-poster-img lazyload"
                                            alt="<?php echo htmlspecialchars($anime['title']); ?>"
                                            loading="lazy" decoding="async">
                                    </a>
                                </div>
                                <div class="film-detail">
                                    <h3 class="film-name">
                                        <a href="/<?php echo htmlspecialchars($anime['id']); ?>"
                                            title="<?php echo htmlspecialchars($anime['title']); ?>"
                                            class="dynamic-name anime-toggle"
                                            data-title="<?php echo htmlspecialchars($anime['title']); ?>"
                                            data-jname="<?php echo htmlspecialchars($anime['japanese_title']); ?>"
                                            style="opacity: 1;">
                                            <?php echo htmlspecialchars($anime['title']); ?>
                                        </a>
                                    </h3>
                                    <div class="fd-infor">
                                        <div class="tick">
                                            <?php if(isset($anime['tvInfo']['sub'])): ?>
                                            <div class="tick-item tick-sub">
                                                <i
                                                    class="fas fa-closed-captioning mr-1"></i><?php echo htmlspecialchars($anime['tvInfo']['sub']); ?>
                                            </div>
                                            <?php endif; ?>
                                            <?php if(isset($anime['tvInfo']['dub'])): ?>
                                            <div class="tick-item tick-dub">
                                                <i
                                                    class="fas fa-microphone mr-1"></i><?php echo htmlspecialchars($anime['tvInfo']['dub']); ?>
                                            </div>
                                            <?php endif; ?>
                                            <?php if (isset($anime['tvInfo']['eps'])): ?>
                                            <div class="tick-item tick-eps"><?php echo $anime['tvInfo']['eps']; ?></div>
                                            <?php endif; ?>
                                            <div>
                                                <span
                                                    class="dot"></span><?php echo htmlspecialchars($anime['tvInfo']['showType']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </li>
                            <?php $hasqtipCounter++; ?>
                            <?php endforeach; ?>
                        </ul>
                        <div class="clearfix"></div>
                    </div>
                    <div class="more"><a href="/most-popular">View more <i
                                class="fas fa-angle-right ml-2"></i></a></div>
                </div>
            </div>
            <?php } ?>
            <?php
            if (!empty($data['mostFavorite'])) {
                $data['mostFavorite'] = array_slice($data['mostFavorite'], 0, 5);
            ?>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 col-xs-12">
                <div class="anif-block anif-block-04">
                    <div class="anif-block-header">Most Favorite</div>
                    <div class="anif-block-ul">
                        <ul class="ulclear">
                            <?php foreach($data['mostFavorite'] as $anime): ?>
                            <li>
                                <div class="film-poster item-qtip" data-id="<?php echo $anime['data_id']; ?>" data-hasqtip="<?php echo $hasqtipCounter; ?>" aria-describedby="qtip-<?php echo $hasqtipCounter; ?>">
                                    <a href="/<?php echo htmlspecialchars($anime['id']); ?>">
                                        <img data-src="<?php echo htmlspecialchars($anime['poster']); ?>"
                                            class="film-poster-img lazyload"
                                            alt="<?php echo htmlspecialchars($anime['title']); ?>"
                                            loading="lazy" decoding="async">
                                    </a>
                                </div>
                                <div class="film-detail">
                                    <h3 class="film-name">
                                        <a href="/<?php echo htmlspecialchars($anime['id']); ?>"
                                            title="<?php echo htmlspecialchars($anime['title']); ?>"
                                            class="dynamic-name anime-toggle"
                                            data-title="<?php echo htmlspecialchars($anime['title']); ?>"
                                            data-jname="<?php echo htmlspecialchars($anime['japanese_title']); ?>"
                                            style="opacity: 1;">
                                            <?php echo htmlspecialchars($anime['title']); ?>
                                        </a>
                                    </h3>
                                    <div class="fd-infor">
                                        <div class="tick">
                                            <?php if(isset($anime['tvInfo']['sub'])): ?>
                                            <div class="tick-item tick-sub">
                                                <i
                                                    class="fas fa-closed-captioning mr-1"></i><?php echo htmlspecialchars($anime['tvInfo']['sub']); ?>
                                            </div>
                                            <?php endif; ?>
                                            <?php if(isset($anime['tvInfo']['dub'])): ?>
                                            <div class="tick-item tick-dub">
                                                <i
                                                    class="fas fa-microphone mr-1"></i><?php echo htmlspecialchars($anime['tvInfo']['dub']); ?>
                                            </div>
                                            <?php endif; ?>
                                            <?php if (isset($anime['tvInfo']['eps'])): ?>
                                            <div class="tick-item tick-eps"><?php echo $anime['tvInfo']['eps']; ?></div>
                                            <?php endif; ?>
                                            <div>
                                                <span class="dot"></span><?php echo $anime['tvInfo']['showType']; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </li>
                            <?php $hasqtipCounter++; ?>
                            <?php endforeach; ?>
                        </ul>
                        <div class="clearfix"></div>
                    </div>
                    <div class="more">
                        <a href="/most-favorite">View more <i class="fas fa-angle-right ml-2"></i></a>
                    </div>
                </div>
            </div>
            <?php } ?>
            <?php
            if (!empty($data['latestCompleted'])) {
                $data['latestCompleted'] = array_slice($data['latestCompleted'], 0, 5);
            ?>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 col-xs-12">
                <div class="anif-block anif-block-01">
                    <div class="anif-block-header">Latest Completed</div>
                    <div class="anif-block-ul">
                        <ul class="ulclear">
                            <?php foreach($data['latestCompleted'] as $anime): ?>
                            <li>
                                <div class="film-poster item-qtip" data-id="<?php echo $anime['data_id']; ?>" data-hasqtip="<?php echo $hasqtipCounter; ?>" aria-describedby="qtip-<?php echo $hasqtipCounter; ?>">
                                    <a href="/<?php echo htmlspecialchars($anime['id']); ?>">
                                        <img data-src="<?php echo htmlspecialchars($anime['poster']); ?>"
                                            class="film-poster-img lazyload"
                                            alt="<?php echo htmlspecialchars($anime['title']); ?>"
                                            loading="lazy" decoding="async">
                                    </a>
                                </div>
                                <div class="film-detail">
                                    <h3 class="film-name">
                                        <a href="/<?php echo htmlspecialchars($anime['id']); ?>"
                                            title="<?php echo htmlspecialchars($anime['title']); ?>"
                                            class="dynamic-name anime-toggle"
                                            data-title="<?php echo htmlspecialchars($anime['title']); ?>"
                                            data-jname="<?php echo htmlspecialchars($anime['japanese_title']); ?>"
                                            style="opacity: 1;">
                                            <?php echo htmlspecialchars($anime['title']); ?>
                                        </a>
                                    </h3>
                                    <div class="fd-infor">
                                        <div class="tick">
                                            <?php if(isset($anime['tvInfo']['sub'])): ?>
                                            <div class="tick-item tick-sub">
                                                <i
                                                    class="fas fa-closed-captioning mr-1"></i><?php echo htmlspecialchars($anime['tvInfo']['sub']); ?>
                                            </div>
                                            <?php endif; ?>
                                            <?php if(isset($anime['tvInfo']['dub'])): ?>
                                            <div class="tick-item tick-dub">
                                                <i
                                                    class="fas fa-microphone mr-1"></i><?php echo htmlspecialchars($anime['tvInfo']['dub']); ?>
                                            </div>
                                            <?php endif; ?>
                                            <?php if (isset($anime['tvInfo']['eps'])): ?>
                                            <div class="tick-item tick-eps"><?php echo $anime['tvInfo']['eps']; ?></div>
                                            <?php endif; ?>
                                            <div>
                                                <span class="dot"></span><?php echo $anime['tvInfo']['showType']; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </li>
                            <?php $hasqtipCounter++; ?>
                            <?php endforeach; ?>
                        </ul>
                        <div class="clearfix"></div>
                    </div>
                    <div class="more">
                        <a href="/completed">View more <i class="fas fa-angle-right ml-2"></i></a>
                    </div>
                </div>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
    <?php } ?>
</div>