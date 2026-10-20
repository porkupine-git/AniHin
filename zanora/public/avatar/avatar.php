<div class="modal fade premodal premodal-avatars" id="modalavatars" tabindex="-1" role="dialog"
    aria-labelledby="modalcharacterstitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Choose Avatar</h5>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs pre-tabs pre-tabs-min pre-tabs-hashtag" style="margin-top: -5px">
                    <li class="nav-item">
                        <a data-toggle="tab" href="#hashtag-1"
                            class="nav-link <?= isActiveTab('dragon_ball', $currentAvatar, $avatarGroups) ?>">#DragonBall</a>
                    </li>
                    <li class="nav-item">
                        <a data-toggle="tab" href="#hashtag-2"
                            class="nav-link <?= isActiveTab('one_piece', $currentAvatar, $avatarGroups) ?>">#OnePiece</a>
                    </li>
                    <li class="nav-item">
                        <a data-toggle="tab" href="#hashtag-3"
                            class="nav-link <?= isActiveTab('zoro_chibi', $currentAvatar, $avatarGroups) ?>">#ZoroChibi</a>
                    </li>
                    <li class="nav-item">
                        <a data-toggle="tab" href="#hashtag-4"
                            class="nav-link <?= isActiveTab('zoro_normal', $currentAvatar, $avatarGroups) ?>">#Zoro</a>
                    </li>
                    <li class="nav-item">
                        <a data-toggle="tab" href="#hashtag-5"
                            class="nav-link <?= isActiveTab('mha', $currentAvatar, $avatarGroups) ?>">#HXH</a>
                    </li>
                    <li class="nav-item">
                        <a data-toggle="tab" href="#hashtag-6"
                            class="nav-link <?= isActiveTab('dragon_ball_chibi', $currentAvatar, $avatarGroups) ?>">#DragonBallChibi</a>
                    </li>
                    <li class="nav-item">
                        <a data-toggle="tab" href="#hashtag-7"
                            class="nav-link <?= isActiveTab('attack_on_titan', $currentAvatar, $avatarGroups) ?>">#AttackOnTitan</a>
                    </li>
                    <li class="nav-item">
                        <a data-toggle="tab" href="#hashtag-8"
                            class="nav-link <?= isActiveTab('conan', $currentAvatar, $avatarGroups) ?>">#Conan</a>
                    </li>
                    <li class="nav-item">
                        <a data-toggle="tab" href="#hashtag-9"
                            class="nav-link <?= isActiveTab('naruto', $currentAvatar, $avatarGroups) ?>">#Naruto</a>
                    </li>
                    <li class="nav-item">
                        <a data-toggle="tab" href="#hashtag-10"
                            class="nav-link <?= isActiveTab('one_punch_man', $currentAvatar, $avatarGroups) ?>">#OnePunchMan</a>
                    </li>
                    <li class="nav-item">
                        <a data-toggle="tab" href="#hashtag-11"
                            class="nav-link <?= isActiveTab('sakura', $currentAvatar, $avatarGroups) ?>">#Sakura</a>
                    </li>
                    <li class="nav-item">
                        <a data-toggle="tab" href="#hashtag-12"
                            class="nav-link <?= isActiveTab('tha', $currentAvatar, $avatarGroups) ?>">#THA</a>
                    </li>
                    <li class="nav-item">
                        <a data-toggle="tab" href="#hashtag-13"
                            class="nav-link <?= isActiveTab('demon_splayer', $currentAvatar, $avatarGroups) ?>">#DemonSlayer</a>
                    </li>
                    <li class="nav-item">
                        <a data-toggle="tab" href="#hashtag-14"
                            class="nav-link <?= isActiveTab('jujutsu_kaisen', $currentAvatar, $avatarGroups) ?>">#JujutsuKaisen</a>
                    </li>
                    <li class="nav-item">
                        <a data-toggle="tab" href="#hashtag-15"
                            class="nav-link <?= isActiveTab('chainsaw', $currentAvatar, $avatarGroups) ?>">#ChainSaw</a>
                    </li>
                    <li class="nav-item">
                        <a data-toggle="tab" href="#hashtag-16"
                            class="nav-link <?= isActiveTab('spy_family', $currentAvatar, $avatarGroups) ?>">#SpyFamily</a>
                    </li>
                    <li class="nav-item">
                        <a data-toggle="tab" href="#hashtag-17"
                            class="nav-link <?= isActiveTab('berserk', $currentAvatar, $avatarGroups) ?>">#Berserk</a>
                    </li>
    				<li class="nav-item">
                        <a data-toggle="tab" href="#hashtag-18"
                            class="nav-link <?= isActiveTab('bleachchibi', $currentAvatar, $avatarGroups) ?>">#BleachChibi</a>
                    </li>
                    <li class="nav-item">
                        <a data-toggle="tab" href="#hashtag-19"
                            class="nav-link <?= isActiveTab('deadnote', $currentAvatar, $avatarGroups) ?>">#DeathNote</a>
                    </li>
                    <li class="nav-item">
                        <a data-toggle="tab" href="#hashtag-20"
                            class="nav-link <?= isActiveTab('haikyu', $currentAvatar, $avatarGroups) ?>">#Haikyu</a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div id="hashtag-1"
                        class="tab-pane fade <?= isActiveTab('dragon_ball', $currentAvatar, $avatarGroups, true) ?>">
                        <div class="avatar-list">
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/dragon_ball/av-db-01.jpeg') !== false ? 'active' : '') ?>"
                                data-id="1">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/dragon_ball/av-db-01.jpeg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/dragon_ball/av-db-02.jpeg') !== false ? 'active' : '') ?>"
                                data-id="2">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/dragon_ball/av-db-02.jpeg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/dragon_ball/av-db-03.jpeg') !== false ? 'active' : '') ?>"
                                data-id="3">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/dragon_ball/av-db-03.jpeg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/dragon_ball/av-db-04.jpeg') !== false ? 'active' : '') ?>"
                                data-id="4">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/dragon_ball/av-db-04.jpeg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/dragon_ball/av-db-05.jpeg') !== false ? 'active' : '') ?>"
                                data-id="5">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/dragon_ball/av-db-05.jpeg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/dragon_ball/av-db-06.jpeg') !== false ? 'active' : '') ?>"
                                data-id="6">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/dragon_ball/av-db-06.jpeg">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="hashtag-2"
                        class="tab-pane fade <?= isActiveTab('one_piece', $currentAvatar, $avatarGroups, true) ?>">
                        <div class="avatar-list">
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/one_piece/user-00.jpeg') !== false ? 'active' : '') ?>"
                                data-id="7">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/one_piece/user-00.jpeg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/one_piece/user-01.jpeg') !== false ? 'active' : '') ?>"
                                data-id="8">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/one_piece/user-01.jpeg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/one_piece/user-02.jpeg') !== false ? 'active' : '') ?>"
                                data-id="9">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/one_piece/user-02.jpeg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/one_piece/user-03.jpeg') !== false ? 'active' : '') ?>"
                                data-id="10">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/one_piece/user-03.jpeg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/one_piece/user-04.jpeg') !== false ? 'active' : '') ?>"
                                data-id="11">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/one_piece/user-04.jpeg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/one_piece/user-05.jpeg') !== false ? 'active' : '') ?>"
                                data-id="12">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/one_piece/user-05.jpeg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/one_piece/user-06.jpeg') !== false ? 'active' : '') ?>"
                                data-id="13">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/one_piece/user-06.jpeg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/one_piece/user-07.jpeg') !== false ? 'active' : '') ?>"
                                data-id="14">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/one_piece/user-07.jpeg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/one_piece/user-08.jpeg') !== false ? 'active' : '') ?>"
                                data-id="15">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/one_piece/user-08.jpeg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/one_piece/user-09.jpeg') !== false ? 'active' : '') ?>"
                                data-id="16">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/one_piece/user-09.jpeg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/one_piece/user-10.jpeg') !== false ? 'active' : '') ?>"
                                data-id="17">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/one_piece/user-10.jpeg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/one_piece/user-11.jpeg') !== false ? 'active' : '') ?>"
                                data-id="18">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/one_piece/user-11.jpeg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/one_piece/03.jpg') !== false ? 'active' : '') ?>"
                                data-id="19">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/one_piece/03.jpg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/one_piece/04.jpg') !== false ? 'active' : '') ?>"
                                data-id="20">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/one_piece/04.jpg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/one_piece/05.jpg') !== false ? 'active' : '') ?>"
                                data-id="21">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/one_piece/05.jpg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/one_piece/18.jpg') !== false ? 'active' : '') ?>"
                                data-id="22">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/one_piece/18.jpg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/one_piece/File1.png') !== false ? 'active' : '') ?>"
                                data-id="23">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/one_piece/File1.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/one_piece/File2.png') !== false ? 'active' : '') ?>"
                                data-id="24">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/one_piece/File2.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/one_piece/File3.png') !== false ? 'active' : '') ?>"
                                data-id="25">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/one_piece/File3.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/one_piece/File4.png') !== false ? 'active' : '') ?>"
                                data-id="26">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/one_piece/File4.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/one_piece/File5.png') !== false ? 'active' : '') ?>"
                                data-id="27">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/one_piece/File5.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/one_piece/File6.png') !== false ? 'active' : '') ?>"
                                data-id="28">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/one_piece/File6.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/one_piece/File7.png') !== false ? 'active' : '') ?>"
                                data-id="29">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/one_piece/File7.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/one_piece/File8.png') !== false ? 'active' : '') ?>"
                                data-id="30">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/one_piece/File8.png">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="hashtag-3"
                        class="tab-pane fade <?= isActiveTab('zoro_chibi', $currentAvatar, $avatarGroups, true) ?>">
                        <div class="avatar-list">
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/zoro_chibi/avatar-02.png') !== false ? 'active' : '') ?>"
                                data-id="31">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/zoro_chibi/avatar-02.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/zoro_chibi/avatar-03.png') !== false ? 'active' : '') ?>"
                                data-id="32">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/zoro_chibi/avatar-03.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/zoro_chibi/avatar-04.png') !== false ? 'active' : '') ?>"
                                data-id="33">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/zoro_chibi/avatar-04.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/zoro_chibi/avatar-05.png') !== false ? 'active' : '') ?>"
                                data-id="34">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/zoro_chibi/avatar-05.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/zoro_chibi/avatar-06.png') !== false ? 'active' : '') ?>"
                                data-id="35">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/zoro_chibi/avatar-06.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/zoro_chibi/avatar-08.png') !== false ? 'active' : '') ?>"
                                data-id="36">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/zoro_chibi/avatar-08.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/zoro_chibi/avatar-09.png') !== false ? 'active' : '') ?>"
                                data-id="37">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/zoro_chibi/avatar-09.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/zoro_chibi/avatar-10.png') !== false ? 'active' : '') ?>"
                                data-id="38">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/zoro_chibi/avatar-10.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/zoro_chibi/avatar-11.png') !== false ? 'active' : '') ?>"
                                data-id="39">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/zoro_chibi/avatar-11.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/zoro_chibi/avatar-12.png') !== false ? 'active' : '') ?>"
                                data-id="40">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/zoro_chibi/avatar-12.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/zoro_chibi/avatar2-01.png') !== false ? 'active' : '') ?>"
                                data-id="41">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/zoro_chibi/avatar2-01.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/zoro_chibi/avatar2-02.png') !== false ? 'active' : '') ?>"
                                data-id="42">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/zoro_chibi/avatar2-02.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/zoro_chibi/avatar2-03.png') !== false ? 'active' : '') ?>"
                                data-id="43">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/zoro_chibi/avatar2-03.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/zoro_chibi/avatar2-04.png') !== false ? 'active' : '') ?>"
                                data-id="44">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/zoro_chibi/avatar2-04.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/zoro_chibi/avatar2-05.png') !== false ? 'active' : '') ?>"
                                data-id="45">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/zoro_chibi/avatar2-05.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/zoro_chibi/avatar2-06.png') !== false ? 'active' : '') ?>"
                                data-id="46">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/zoro_chibi/avatar2-06.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/zoro_chibi/avatar2-07.png') !== false ? 'active' : '') ?>"
                                data-id="47">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/zoro_chibi/avatar2-07.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/zoro_chibi/avatar2-08.png') !== false ? 'active' : '') ?>"
                                data-id="48">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/zoro_chibi/avatar2-08.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/zoro_chibi/avatar2-09.png') !== false ? 'active' : '') ?>"
                                data-id="49">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/zoro_chibi/avatar2-09.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/zoro_chibi/avatar2-10.png') !== false ? 'active' : '') ?>"
                                data-id="50">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/zoro_chibi/avatar2-10.png">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="hashtag-4"
                        class="tab-pane fade <?= isActiveTab('zoro_normal', $currentAvatar, $avatarGroups, true) ?>">
                        <div class="avatar-list">
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/zoro_normal/av-zz-01.jpeg') !== false ? 'active' : '') ?>"
                                data-id="51">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/zoro_normal/av-zz-01.jpeg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/zoro_normal/av-zz-02.jpeg') !== false ? 'active' : '') ?>"
                                data-id="52">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/zoro_normal/av-zz-02.jpeg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/zoro_normal/av-zz-03.jpeg') !== false ? 'active' : '') ?>"
                                data-id="53">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/zoro_normal/av-zz-03.jpeg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/zoro_normal/av-zz-04.jpeg') !== false ? 'active' : '') ?>"
                                data-id="54">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/zoro_normal/av-zz-04.jpeg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/zoro_normal/av-zz-05.jpeg') !== false ? 'active' : '') ?>"
                                data-id="55">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/zoro_normal/av-zz-05.jpeg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/zoro_normal/av-zz-06.jpeg') !== false ? 'active' : '') ?>"
                                data-id="56">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/zoro_normal/av-zz-06.jpeg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/zoro_normal/av-zz-07.jpeg') !== false ? 'active' : '') ?>"
                                data-id="57">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/zoro_normal/av-zz-07.jpeg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/zoro_normal/av-zz-08.jpeg') !== false ? 'active' : '') ?>"
                                data-id="58">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/zoro_normal/av-zz-08.jpeg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/zoro_normal/av-zz-09.jpeg') !== false ? 'active' : '') ?>"
                                data-id="59">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/zoro_normal/av-zz-09.jpeg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/zoro_normal/av-zz-10.jpeg') !== false ? 'active' : '') ?>"
                                data-id="60">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/zoro_normal/av-zz-10.jpeg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/zoro_normal/av-zz-11.jpeg') !== false ? 'active' : '') ?>"
                                data-id="61">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/zoro_normal/av-zz-11.jpeg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/zoro_normal/av-zz-12.jpeg') !== false ? 'active' : '') ?>"
                                data-id="62">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/zoro_normal/av-zz-12.jpeg">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="hashtag-5"
                        class="tab-pane fade <?= isActiveTab('mha', $currentAvatar, $avatarGroups, true) ?>">
                        <div class="avatar-list">
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/mha/avatar-17.png') !== false ? 'active' : '') ?>"
                                data-id="63">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/mha/avatar-17.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/mha/avatar-18.png') !== false ? 'active' : '') ?>"
                                data-id="64">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/mha/avatar-18.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/mha/avatar-19.png') !== false ? 'active' : '') ?>"
                                data-id="65">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/mha/avatar-19.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/mha/avatar-20.png') !== false ? 'active' : '') ?>"
                                data-id="66">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/mha/avatar-20.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/mha/avatar-21.png') !== false ? 'active' : '') ?>"
                                data-id="67">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/mha/avatar-21.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/mha/avatar-22.png') !== false ? 'active' : '') ?>"
                                data-id="68">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/mha/avatar-22.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/mha/avatar-23.png') !== false ? 'active' : '') ?>"
                                data-id="69">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/mha/avatar-23.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/mha/avatar-24.png') !== false ? 'active' : '') ?>"
                                data-id="70">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/mha/avatar-24.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/mha/avatar-25.png') !== false ? 'active' : '') ?>"
                                data-id="71">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/mha/avatar-25.png">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="hashtag-6"
                        class="tab-pane fade <?= isActiveTab('dragon_ball_chibi', $currentAvatar, $avatarGroups, true) ?>">
                        <div class="avatar-list">
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/dragon_ball_chibi/bulma.png') !== false ? 'active' : '') ?>"
                                data-id="72">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/dragon_ball_chibi/bulma.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/dragon_ball_chibi/buumap.png') !== false ? 'active' : '') ?>"
                                data-id="73">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img
                                        src="/avatar/100x100/dragon_ball_chibi/buumap.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/dragon_ball_chibi/cell.png') !== false ? 'active' : '') ?>"
                                data-id="74">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/dragon_ball_chibi/cell.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/dragon_ball_chibi/freezer.png') !== false ? 'active' : '') ?>"
                                data-id="75">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img
                                        src="/avatar/100x100/dragon_ball_chibi/freezer.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/dragon_ball_chibi/gogeta.png') !== false ? 'active' : '') ?>"
                                data-id="76">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img
                                        src="/avatar/100x100/dragon_ball_chibi/gogeta.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/dragon_ball_chibi/gohan.png') !== false ? 'active' : '') ?>"
                                data-id="77">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/dragon_ball_chibi/gohan.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/dragon_ball_chibi/goku.png') !== false ? 'active' : '') ?>"
                                data-id="78">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/dragon_ball_chibi/goku.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/dragon_ball_chibi/goku-saiyan.png') !== false ? 'active' : '') ?>"
                                data-id="79">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img
                                        src="/avatar/100x100/dragon_ball_chibi/goku-saiyan.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/dragon_ball_chibi/gotenk.png') !== false ? 'active' : '') ?>"
                                data-id="80">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img
                                        src="/avatar/100x100/dragon_ball_chibi/gotenk.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/dragon_ball_chibi/kaio.png') !== false ? 'active' : '') ?>"
                                data-id="81">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/dragon_ball_chibi/kaio.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/dragon_ball_chibi/krillin.png') !== false ? 'active' : '') ?>"
                                data-id="82">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img
                                        src="/avatar/100x100/dragon_ball_chibi/krillin.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/dragon_ball_chibi/mabu.png') !== false ? 'active' : '') ?>"
                                data-id="83">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/dragon_ball_chibi/mabu.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/dragon_ball_chibi/mrsatan.png') !== false ? 'active' : '') ?>"
                                data-id="84">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img
                                        src="/avatar/100x100/dragon_ball_chibi/mrsatan.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/dragon_ball_chibi/MutenRoshi.png') !== false ? 'active' : '') ?>"
                                data-id="85">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img
                                        src="/avatar/100x100/dragon_ball_chibi/MutenRoshi.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/dragon_ball_chibi/picolo.png') !== false ? 'active' : '') ?>"
                                data-id="86">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img
                                        src="/avatar/100x100/dragon_ball_chibi/picolo.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/dragon_ball_chibi/tenshihan.png') !== false ? 'active' : '') ?>"
                                data-id="87">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img
                                        src="/avatar/100x100/dragon_ball_chibi/tenshihan.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/dragon_ball_chibi/trunks.png') !== false ? 'active' : '') ?>"
                                data-id="88">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img
                                        src="/avatar/100x100/dragon_ball_chibi/trunks.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/dragon_ball_chibi/vegeta.png') !== false ? 'active' : '') ?>"
                                data-id="89">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img
                                        src="/avatar/100x100/dragon_ball_chibi/vegeta.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/dragon_ball_chibi/videl.png') !== false ? 'active' : '') ?>"
                                data-id="90">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/dragon_ball_chibi/videl.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/dragon_ball_chibi/beerus.png') !== false ? 'active' : '') ?>"
                                data-id="91">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img
                                        src="/avatar/100x100/dragon_ball_chibi/beerus.png">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="hashtag-7"
                        class="tab-pane fade <?= isActiveTab('attack_on_titan', $currentAvatar, $avatarGroups, true) ?>">
                        <div class="avatar-list">
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/attack_on_titan/20.jpg') !== false ? 'active' : '') ?>"
                                data-id="92">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/attack_on_titan/20.jpg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/attack_on_titan/aot_01.png') !== false ? 'active' : '') ?>"
                                data-id="93">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/attack_on_titan/aot_01.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/attack_on_titan/aot_02.png') !== false ? 'active' : '') ?>"
                                data-id="94">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/attack_on_titan/aot_02.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/attack_on_titan/aot_03.png') !== false ? 'active' : '') ?>"
                                data-id="95">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/attack_on_titan/aot_03.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/attack_on_titan/aot_04.png') !== false ? 'active' : '') ?>"
                                data-id="96">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/attack_on_titan/aot_04.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/attack_on_titan/aot_05.png') !== false ? 'active' : '') ?>"
                                data-id="97">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/attack_on_titan/aot_05.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/attack_on_titan/aot_06.png') !== false ? 'active' : '') ?>"
                                data-id="98">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/attack_on_titan/aot_06.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/attack_on_titan/aot_07.png') !== false ? 'active' : '') ?>"
                                data-id="99">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/attack_on_titan/aot_07.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/attack_on_titan/aot_08.png') !== false ? 'active' : '') ?>"
                                data-id="100">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/attack_on_titan/aot_08.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/attack_on_titan/aot_09.png') !== false ? 'active' : '') ?>"
                                data-id="101">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/attack_on_titan/aot_09.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/attack_on_titan/aot_10.png') !== false ? 'active' : '') ?>"
                                data-id="102">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/attack_on_titan/aot_10.png">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="hashtag-8"
                        class="tab-pane fade <?= isActiveTab('conan', $currentAvatar, $avatarGroups, true) ?>">
                        <div class="avatar-list">
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/conan/06.jpg') !== false ? 'active' : '') ?>"
                                data-id="103">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/conan/06.jpg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/conan/07.jpg') !== false ? 'active' : '') ?>"
                                data-id="104">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/conan/07.jpg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/conan/8.png') !== false ? 'active' : '') ?>"
                                data-id="105">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/conan/8.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/conan/9.png') !== false ? 'active' : '') ?>"
                                data-id="106">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/conan/9.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/conan/10.png') !== false ? 'active' : '') ?>"
                                data-id="107">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/conan/10.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/conan/11.png') !== false ? 'active' : '') ?>"
                                data-id="108">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/conan/11.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/conan/12.png') !== false ? 'active' : '') ?>"
                                data-id="109">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/conan/12.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/conan/13.png') !== false ? 'active' : '') ?>"
                                data-id="110">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/conan/13.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/conan/14.png') !== false ? 'active' : '') ?>"
                                data-id="111">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/conan/14.png">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="hashtag-9"
                        class="tab-pane fade <?= isActiveTab('naruto', $currentAvatar, $avatarGroups, true) ?>">
                        <div class="avatar-list">
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/naruto/08.jpg') !== false ? 'active' : '') ?>"
                                data-id="112">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/naruto/08.jpg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/naruto/09.jpg') !== false ? 'active' : '') ?>"
                                data-id="113">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/naruto/09.jpg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/naruto/11.jpg') !== false ? 'active' : '') ?>"
                                data-id="114">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/naruto/11.jpg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/naruto/naruto_01.png') !== false ? 'active' : '') ?>"
                                data-id="115">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/naruto/naruto_01.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/naruto/naruto_02.png') !== false ? 'active' : '') ?>"
                                data-id="116">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/naruto/naruto_02.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/naruto/naruto_03.png') !== false ? 'active' : '') ?>"
                                data-id="117">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/naruto/naruto_03.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/naruto/naruto_04.png') !== false ? 'active' : '') ?>"
                                data-id="118">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/naruto/naruto_04.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/naruto/naruto_05.png') !== false ? 'active' : '') ?>"
                                data-id="119">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/naruto/naruto_05.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/naruto/naruto_06.png') !== false ? 'active' : '') ?>"
                                data-id="120">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/naruto/naruto_06.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/naruto/naruto_07.png') !== false ? 'active' : '') ?>"
                                data-id="121">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/naruto/naruto_07.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/naruto/naruto_08.png') !== false ? 'active' : '') ?>"
                                data-id="122">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/naruto/naruto_08.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/naruto/naruto_09.png') !== false ? 'active' : '') ?>"
                                data-id="123">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/naruto/naruto_09.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/naruto/naruto_010.png') !== false ? 'active' : '') ?>"
                                data-id="124">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/naruto/naruto_010.png">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="hashtag-10"
                        class="tab-pane fade <?= isActiveTab('one_punch_man', $currentAvatar, $avatarGroups, true) ?>">
                        <div class="avatar-list">
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/one_punch_man/14.jpg') !== false ? 'active' : '') ?>"
                                data-id="125">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/one_punch_man/14.jpg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/one_punch_man/opm_01.png') !== false ? 'active' : '') ?>"
                                data-id="126">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/one_punch_man/opm_01.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/one_punch_man/opm_02.png') !== false ? 'active' : '') ?>"
                                data-id="127">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/one_punch_man/opm_02.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/one_punch_man/opm_03.png') !== false ? 'active' : '') ?>"
                                data-id="128">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/one_punch_man/opm_03.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/one_punch_man/opm_04.png') !== false ? 'active' : '') ?>"
                                data-id="129">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/one_punch_man/opm_04.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/one_punch_man/opm_05.png') !== false ? 'active' : '') ?>"
                                data-id="130">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/one_punch_man/opm_05.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/one_punch_man/opm_06.png') !== false ? 'active' : '') ?>"
                                data-id="131">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/one_punch_man/opm_06.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/one_punch_man/opm_07.png') !== false ? 'active' : '') ?>"
                                data-id="132">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/one_punch_man/opm_07.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/one_punch_man/opm_08.png') !== false ? 'active' : '') ?>"
                                data-id="133">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/one_punch_man/opm_08.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/one_punch_man/opm_09.png') !== false ? 'active' : '') ?>"
                                data-id="134">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/one_punch_man/opm_09.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/one_punch_man/opm_010.png') !== false ? 'active' : '') ?>"
                                data-id="135">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/one_punch_man/opm_010.png">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="hashtag-11"
                        class="tab-pane fade <?= isActiveTab('sakura', $currentAvatar, $avatarGroups, true) ?>">
                        <div class="avatar-list">
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/sakura/10.jpg') !== false ? 'active' : '') ?>"
                                data-id="136">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/sakura/10.jpg">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="hashtag-12"
                        class="tab-pane fade <?= isActiveTab('tha', $currentAvatar, $avatarGroups, true) ?>">
                        <div class="avatar-list">
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/tha/01.jpg') !== false ? 'active' : '') ?>"
                                data-id="137">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/tha/01.jpg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/tha/02.jpg') !== false ? 'active' : '') ?>"
                                data-id="138">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/tha/02.jpg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/tha/12.jpg') !== false ? 'active' : '') ?>"
                                data-id="139">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/tha/12.jpg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/tha/13.jpg') !== false ? 'active' : '') ?>"
                                data-id="140">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/tha/13.jpg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/tha/15.jpg') !== false ? 'active' : '') ?>"
                                data-id="141">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/tha/15.jpg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/tha/16.jpg') !== false ? 'active' : '') ?>"
                                data-id="142">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/tha/16.jpg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/tha/17.jpg') !== false ? 'active' : '') ?>"
                                data-id="143">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/tha/17.jpg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/tha/19.jpg') !== false ? 'active' : '') ?>"
                                data-id="144">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/tha/19.jpg">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="hashtag-13"
                        class="tab-pane fade <?= isActiveTab('demon_splayer', $currentAvatar, $avatarGroups, true) ?>">
                        <div class="avatar-list">
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/demon_splayer/File1.jpg') !== false ? 'active' : '') ?>"
                                data-id="145">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/demon_splayer/File1.jpg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/demon_splayer/File2.jpg') !== false ? 'active' : '') ?>"
                                data-id="146">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/demon_splayer/File2.jpg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/demon_splayer/File3.jpg') !== false ? 'active' : '') ?>"
                                data-id="147">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/demon_splayer/File3.jpg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/demon_splayer/File4.jpg') !== false ? 'active' : '') ?>"
                                data-id="148">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/demon_splayer/File4.jpg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/demon_splayer/File5.jpg') !== false ? 'active' : '') ?>"
                                data-id="149">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/demon_splayer/File5.jpg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/demon_splayer/File6.jpg') !== false ? 'active' : '') ?>"
                                data-id="150">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/demon_splayer/File6.jpg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/demon_splayer/File7.jpg') !== false ? 'active' : '') ?>"
                                data-id="151">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/demon_splayer/File7.jpg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/demon_splayer/File8.jpg') !== false ? 'active' : '') ?>"
                                data-id="152">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/demon_splayer/File8.jpg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/demon_splayer/File9.jpg') !== false ? 'active' : '') ?>"
                                data-id="153">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/demon_splayer/File9.jpg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/demon_splayer/File10.jpg') !== false ? 'active' : '') ?>"
                                data-id="154">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/demon_splayer/File10.jpg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/demon_splayer/File11.jpg') !== false ? 'active' : '') ?>"
                                data-id="155">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/demon_splayer/File11.jpg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/demon_splayer/File12.jpg') !== false ? 'active' : '') ?>"
                                data-id="156">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/demon_splayer/File12.jpg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/demon_splayer/File13.jpg') !== false ? 'active' : '') ?>"
                                data-id="157">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/demon_splayer/File13.jpg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/demon_splayer/File14.jpg') !== false ? 'active' : '') ?>"
                                data-id="158">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/demon_splayer/File14.jpg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/demon_splayer/File15.jpg') !== false ? 'active' : '') ?>"
                                data-id="159">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/demon_splayer/File15.jpg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/demon_splayer/File16.jpg') !== false ? 'active' : '') ?>"
                                data-id="160">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/demon_splayer/File16.jpg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/demon_splayer/File17.jpg') !== false ? 'active' : '') ?>"
                                data-id="161">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/demon_splayer/File17.jpg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/demon_splayer/File18.jpg') !== false ? 'active' : '') ?>"
                                data-id="162">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/demon_splayer/File18.jpg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/demon_splayer/File19.jpg') !== false ? 'active' : '') ?>"
                                data-id="163">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/demon_splayer/File19.jpg">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/demon_splayer/File20.jpg') !== false ? 'active' : '') ?>"
                                data-id="164">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/demon_splayer/File20.jpg">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="hashtag-14"
                        class="tab-pane fade <?= isActiveTab('jujutsu_kaisen', $currentAvatar, $avatarGroups, true) ?>">
                        <div class="avatar-list">
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/jujutsu_kaisen/File1.png') !== false ? 'active' : '') ?>"
                                data-id="165">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/jujutsu_kaisen/File1.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/jujutsu_kaisen/File2.png') !== false ? 'active' : '') ?>"
                                data-id="166">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/jujutsu_kaisen/File2.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/jujutsu_kaisen/File3.png') !== false ? 'active' : '') ?>"
                                data-id="167">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/jujutsu_kaisen/File3.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/jujutsu_kaisen/File4.png') !== false ? 'active' : '') ?>"
                                data-id="168">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/jujutsu_kaisen/File4.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/jujutsu_kaisen/File5.png') !== false ? 'active' : '') ?>"
                                data-id="169">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/jujutsu_kaisen/File5.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/jujutsu_kaisen/File6.png') !== false ? 'active' : '') ?>"
                                data-id="170">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/jujutsu_kaisen/File6.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/jujutsu_kaisen/File7.png') !== false ? 'active' : '') ?>"
                                data-id="171">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/jujutsu_kaisen/File7.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/jujutsu_kaisen/File8.png') !== false ? 'active' : '') ?>"
                                data-id="172">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/jujutsu_kaisen/File8.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/jujutsu_kaisen/File9.png') !== false ? 'active' : '') ?>"
                                data-id="173">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/jujutsu_kaisen/File9.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/jujutsu_kaisen/File10.png') !== false ? 'active' : '') ?>"
                                data-id="174">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/jujutsu_kaisen/File10.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/jujutsu_kaisen/File11.png') !== false ? 'active' : '') ?>"
                                data-id="175">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/jujutsu_kaisen/File11.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/jujutsu_kaisen/File12.png') !== false ? 'active' : '') ?>"
                                data-id="176">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/jujutsu_kaisen/File12.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/jujutsu_kaisen/File13.png') !== false ? 'active' : '') ?>"
                                data-id="177">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/jujutsu_kaisen/File13.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/jujutsu_kaisen/File14.png') !== false ? 'active' : '') ?>"
                                data-id="178">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/jujutsu_kaisen/File14.png">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="hashtag-15"
                        class="tab-pane fade <?= isActiveTab('chainsaw', $currentAvatar, $avatarGroups, true) ?>">
                        <div class="avatar-list">
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/chainsaw/01.png') !== false ? 'active' : '') ?>"
                                data-id="179">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/chainsaw/01.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/chainsaw/02.png') !== false ? 'active' : '') ?>"
                                data-id="180">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/chainsaw/02.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/chainsaw/03.png') !== false ? 'active' : '') ?>"
                                data-id="181">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/chainsaw/03.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/chainsaw/04.png') !== false ? 'active' : '') ?>"
                                data-id="182">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/chainsaw/04.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/chainsaw/05.png') !== false ? 'active' : '') ?>"
                                data-id="183">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/chainsaw/05.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/chainsaw/06.png') !== false ? 'active' : '') ?>"
                                data-id="184">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/chainsaw/06.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/chainsaw/07.png') !== false ? 'active' : '') ?>"
                                data-id="185">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/chainsaw/07.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/chainsaw/08.png') !== false ? 'active' : '') ?>"
                                data-id="186">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/chainsaw/08.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/chainsaw/09.png') !== false ? 'active' : '') ?>"
                                data-id="187">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/chainsaw/09.png">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="hashtag-16"
                        class="tab-pane fade <?= isActiveTab('spy_family', $currentAvatar, $avatarGroups, true) ?>">
                        <div class="avatar-list">
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/spy_family/01.png') !== false ? 'active' : '') ?>"
                                data-id="188">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/spy_family/01.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/spy_family/02.png') !== false ? 'active' : '') ?>"
                                data-id="189">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/spy_family/02.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/spy_family/03.png') !== false ? 'active' : '') ?>"
                                data-id="190">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/spy_family/03.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/spy_family/04.png') !== false ? 'active' : '') ?>"
                                data-id="191">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/spy_family/04.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/spy_family/05.png') !== false ? 'active' : '') ?>"
                                data-id="192">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/spy_family/05.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/spy_family/06.png') !== false ? 'active' : '') ?>"
                                data-id="193">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/spy_family/06.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/spy_family/07.png') !== false ? 'active' : '') ?>"
                                data-id="194">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/spy_family/07.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/spy_family/08.png') !== false ? 'active' : '') ?>"
                                data-id="195">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/spy_family/08.png">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="hashtag-17"
                        class="tab-pane fade <?= isActiveTab('berserk', $currentAvatar, $avatarGroups, true) ?>">
                        <div class="avatar-list">
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/berserk/berserk_01.png') !== false ? 'active' : '') ?>"
                                data-id="196">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/berserk/berserk_01.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/berserk/berserk_02.png') !== false ? 'active' : '') ?>"
                                data-id="197">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/berserk/berserk_02.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/berserk/berserk_03.png') !== false ? 'active' : '') ?>"
                                data-id="198">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/berserk/berserk_03.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/berserk/berserk_04.png') !== false ? 'active' : '') ?>"
                                data-id="199">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/berserk/berserk_04.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/berserk/berserk_05.png') !== false ? 'active' : '') ?>"
                                data-id="200">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/berserk/berserk_05.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/berserk/berserk_06.png') !== false ? 'active' : '') ?>"
                                data-id="201">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/berserk/berserk_06.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/berserk/berserk_07.png') !== false ? 'active' : '') ?>"
                                data-id="202">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/berserk/berserk_07.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/berserk/berserk_08.png') !== false ? 'active' : '') ?>"
                                data-id="203">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/berserk/berserk_08.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/berserk/berserk_09.png') !== false ? 'active' : '') ?>"
                                data-id="204">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/berserk/berserk_09.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/berserk/berserk_010.png') !== false ? 'active' : '') ?>"
                                data-id="205">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/berserk/berserk_010.png">
                                </div>
                            </div>
                        </div>
                    </div>
    				<div id="hashtag-18" class="tab-pane fade <?= isActiveTab('bleachchibi', $currentAvatar, $avatarGroups, true) ?>">
						<div class="avatar-list">
    						<div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/bleach/bleanch_01.png') !== false ? 'active' : '') ?>"
                                data-id="206">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/bleach/bleanch_01.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/bleach/bleanch_02.png') !== false ? 'active' : '') ?>"
                                data-id="207">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/bleach/bleanch_02.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/bleach/bleanch_03.png') !== false ? 'active' : '') ?>"
                                data-id="208">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/bleach/bleanch_03.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/bleach/bleanch_04.png') !== false ? 'active' : '') ?>"
                                data-id="209">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/bleach/bleanch_04.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/bleach/bleanch_05.png') !== false ? 'active' : '') ?>"
                                data-id="210">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/bleach/bleanch_05.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/bleach/bleanch_06.png') !== false ? 'active' : '') ?>"
                                data-id="211">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/bleach/bleanch_06.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/bleach/bleanch_07.png') !== false ? 'active' : '') ?>"
                                data-id="212">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/bleach/bleanch_07.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/bleach/bleanch_08.png') !== false ? 'active' : '') ?>"
                                data-id="213">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/bleach/bleanch_08.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/bleach/bleanch_09.png') !== false ? 'active' : '') ?>"
                                data-id="214">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/bleach/bleanch_09.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/bleach/bleanch_010.png') !== false ? 'active' : '') ?>"
                                data-id="215">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/bleach/bleanch_010.png">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="hashtag-19"
                        class="tab-pane fade <?= isActiveTab('deadnote', $currentAvatar, $avatarGroups, true) ?>">
                        <div class="avatar-list">
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/deadnote/dead_note_01.png') !== false ? 'active' : '') ?>"
                                data-id="216">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/deadnote/dead_note_01.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/deadnote/dead_note_02.png') !== false ? 'active' : '') ?>"
                                data-id="217">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/deadnote/dead_note_02.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/deadnote/dead_note_03.png') !== false ? 'active' : '') ?>"
                                data-id="218">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/deadnote/dead_note_03.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/deadnote/dead_note_04.png') !== false ? 'active' : '') ?>"
                                data-id="219">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/deadnote/dead_note_04.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/deadnote/dead_note_05.png') !== false ? 'active' : '') ?>"
                                data-id="220">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/deadnote/dead_note_05.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/deadnote/dead_note_06.png') !== false ? 'active' : '') ?>"
                                data-id="221">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/deadnote/dead_note_06.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/deadnote/dead_note_07.png') !== false ? 'active' : '') ?>"
                                data-id="222">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/deadnote/dead_note_07.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/deadnote/dead_note_08.png') !== false ? 'active' : '') ?>"
                                data-id="223">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/deadnote/dead_note_08.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/deadnote/dead_note_09.png') !== false ? 'active' : '') ?>"
                                data-id="224">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/deadnote/dead_note_09.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/deadnote/dead_note_010.png') !== false ? 'active' : '') ?>"
                                data-id="225">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/deadnote/dead_note_010.png">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="hashtag-20"
                        class="tab-pane fade <?= isActiveTab('haikyu', $currentAvatar, $avatarGroups, true) ?>">
                        <div class="avatar-list">
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/haikyu/haikyu_01.png') !== false ? 'active' : '') ?>"
                                data-id="226">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/haikyu/haikyu_01.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/haikyu/haikyu_02.png') !== false ? 'active' : '') ?>"
                                data-id="227">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/haikyu/haikyu_02.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/haikyu/haikyu_03.png') !== false ? 'active' : '') ?>"
                                data-id="228">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/haikyu/haikyu_03.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/haikyu/haikyu_04.png') !== false ? 'active' : '') ?>"
                                data-id="229">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/haikyu/haikyu_04.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/haikyu/haikyu_05.png') !== false ? 'active' : '') ?>"
                                data-id="230">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/haikyu/haikyu_05.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/haikyu/haikyu_06.png') !== false ? 'active' : '') ?>"
                                data-id="231">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/haikyu/haikyu_06.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/haikyu/haikyu_07.png') !== false ? 'active' : '') ?>"
                                data-id="232">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/haikyu/haikyu_07.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/haikyu/haikyu_08.png') !== false ? 'active' : '') ?>"
                                data-id="233">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/haikyu/haikyu_08.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/haikyu/haikyu_09.png') !== false ? 'active' : '') ?>"
                                data-id="234">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/haikyu/haikyu_09.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/haikyu/haikyu_010.png') !== false ? 'active' : '') ?>"
                                data-id="235">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/haikyu/haikyu_010.png">
                                </div>
                            </div>
                            <div class="item item-avatar <?= (stripos($currentAvatar, '/avatar/100x100/haikyu/haikyu_011.png') !== false ? 'active' : '') ?>"
                                data-id="236">
                                <div class="checked"><i class="fas fa-check"></i></div>
                                <div class="profile-avatar">
                                    <img src="/avatar/100x100/haikyu/haikyu_011.png">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="clearfix"></div>
                <a data-dismiss="modal" aria-label="Close" class="btn btn-block btn-secondary mt-4">Close</a>
            </div>
        </div>
    </div>
</div>