<div id="footer">
    <div id="footer-about">
        <div class="container">
            <div class="footer-top">
                <a href="/" class="footer-logo">
                    <img src="<?=$websiteLogo?>" alt="<?=$websiteTitle?>" width="165" height="40" loading="lazy" decoding="async">
                    <div class="clearfix"></div>
                </a>
                <div class="footer-joingroup">
                    <div class="anw-group">
                        <div class="zrg-title"><span class="top">Join now</span><span
                                class="bottom"><?= $websiteTitle ?> Group</span>
                        </div>
                        <div class="zrg-list">
                            <div class="item"><a href="<?= $discord ?>" target="_blank"
                                    class="zr-social-button dc-btn"><i class="fa-brands fa-discord"></i></a></div>
                            <div class="item"><a href="<?= $telegram ?>" target="_blank"
                                    class="zr-social-button tw-btn"><i class="fab fa-telegram-plane"></i></a></div>
                            <div class="item"><a href="<?= $reddit ?>" target="_blank" 
                                    class="zr-social-button rd-btn"><i class="fab fa-reddit-alien"></i></a></div>
                            <div class="item"><a href="<?= $instagram ?>" target="_blank"
                                    class="zr-social-button in-btn"><i class="fa-brands fa-instagram"></i></a></div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="footer-links">
                <ul class="ulclear">
                    <li><a href="/terms" title="Terms of service">Terms of service</a></li>
                    <li><a href="/dmca" title="DMCA">DMCA</a></li>
                    <li><a href="<?= htmlspecialchars($discord) ?>" title="Join Discord" target="_blank">Join
                            Discord</a></li>
                </ul>
                <div class="clearfix"></div>
            </div>
            <div class="about-text"><?=$websiteTitle?> does not store any files on our server, we only share link to the
                media which is hosted on 3rd party services.</div>
            <p class="copyright">Copyright © <?=date("Y")?> Shinora All Rights Reserved</p>
        </div>
    </div>
</div>
<?php if (!empty($_SESSION['roles']) && $_SESSION['roles'] > 0): 
    $isAuthorBlacklistedSafe = !empty($isAuthorBlacklisted);
?>
<div class="modal fade premodal" id="actionModal" tabindex="-1" aria-labelledby="actionModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="actionModalLabel">
          <?= $isAuthorBlacklistedSafe ? 'Unblacklist User' : 'Blacklist User' ?>
        </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form id="action-form">
        <div class="modal-body">
          <p>
            <?= $isAuthorBlacklistedSafe
                ? 'Do you want to remove this user from the blacklist?'
                : 'Do you want to add this user to the blacklist?'; ?>
          </p>

          <!-- Your JS fills this with data-user-id from .ac-user -->
          <input type="hidden" name="user_id" value="">

          <div id="action-loading" style="display:none;">Processing...</div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">
            <?= $isAuthorBlacklistedSafe ? 'Unblacklist' : 'Blacklist' ?>
          </button>
        </div>
      </form>

    </div>
  </div>
</div>
<?php endif; ?>
<div class="modal fade premodal" id="actionModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"></div>
<div id="totop"><i class="fas fa-chevron-up"></i></div>