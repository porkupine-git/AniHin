<div class="modal fade premodal premodal-video" id="modalpromotion" tabindex="-1" role="dialog"
     aria-labelledby="modalpromotiontitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="embed-responsive embed-responsive-16by9">
                    <iframe class="embed-responsive-item" src="" id="if-promotion-src"
                            allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </div>
</div>
                        
<div class="modal fade premodal premodal-login" id="modallogin" tabindex="-1" role="dialog"
    aria-labelledby="modallogintitle" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="tab-content">

                <!-- LOGIN TAB -->
                <div id="modal-tab-login" class="tab-pane active">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modallogintitle">Welcome back!</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger mb-3" id="login-error" style="display: none;"></div>
                        <form class="preform" id="login-form" method="post">
                            <div class="form-group">
                                <label class="prelabel" for="email">Email address</label>
                                <input type="email" class="form-control" id="email" placeholder="name@email.com"
                                    name="email" required>
                            </div>
                            <div class="form-group">
                                <label class="prelabel" for="password">Password</label>
                                <input type="password" class="form-control" id="password" placeholder="Password"
                                    name="password" required>
                            </div>
                            <div class="form-check custom-control custom-checkbox">
                                <div class="float-left">
                                    <input type="checkbox" class="custom-control-input" name="remember" value="1"
                                        id="remember" checked>
                                    <label class="custom-control-label" for="remember">Remember me</label>
                                </div>
                                <div class="float-right">
                                    <a href="javascript:;" class="link-highlight text-forgot forgot-tab-link">Forgot
                                        password?</a>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                            <div data-theme="dark" class="mb-3 mt-3" id="login-recaptcha"></div>

                            <div class="form-group login-btn mb-0">
                                <button id="btn-login" class="btn btn-primary btn-block">Login</button>
                                <div class="loading-relative" id="login-loading" style="display: none;">
                                    <div class="loading">
                                        <div class="span1"></div>
                                        <div class="span2"></div>
                                        <div class="span3"></div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer text-center">
                        Don't have an account? <a class="link-highlight register-tab-link">Register</a>
                        or <a class="link-highlight verify-tab-link">Verify</a>
                    </div>
                </div>

                <!-- REGISTER TAB -->
                <div id="modal-tab-register" class="tab-pane fade">
                    <div class="modal-header">
                        <h5 class="modal-title">Create an Account</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger mb-3" id="register-error" style="display: none;"></div>
                        <div class="alert alert-success mb-3" id="register-success" style="display: none;">
                            Registration successful.
                        </div>
                        <form class="preform" id="register-form" method="post">
                            <div class="form-group">
                                <label class="prelabel" for="re-username">Your name</label>
                                <input type="text" class="form-control" id="re-username" placeholder="Name" name="name"
                                    required>
                            </div>
                            <div class="form-group">
                                <label class="prelabel" for="re-email">Email address</label>
                                <input type="email" class="form-control" id="re-email" placeholder="name@email.com"
                                    name="email" required>
                            </div>
                            <div class="form-group">
                                <label class="prelabel" for="re-password">Password</label>
                                <input type="password" class="form-control" id="re-password" placeholder="Password"
                                    name="password" required>
                            </div>
                            <div class="form-group">
                                <label class="prelabel" for="re-confirmpassword">Confirm Password</label>
                                <input type="password" class="form-control" id="re-confirmpassword"
                                    name="re-confirmpassword" placeholder="Confirm Password" required>
                            </div>
                            <div data-theme="dark" class="mb-3 mt-3" id="register-recaptcha"></div>

                            <div class="form-group login-btn mb-0">
                                <button id="btn-register" class="btn btn-primary btn-block">Register</button>
                                <div class="loading-relative" id="register-loading" style="display: none;">
                                    <div class="loading">
                                        <div class="span1"></div>
                                        <div class="span2"></div>
                                        <div class="span3"></div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer text-center">
                        Have an account? <a class="link-highlight login-tab-link">Login</a>
                    </div>
                </div>

                <!-- FORGOT TAB -->
                <div id="modal-tab-forgot" class="tab-pane fade">
                    <div class="modal-header">
                        <h5 class="modal-title">Reset Password</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body" style="padding-bottom: 20px;">
                        <form class="preform" id="forgot-form" method="post">
                            <div class="alert alert-success mb-3" id="forgot-success" style="display:none"></div>
                            <div class="alert alert-danger mb-3" id="forgot-error" style="display:none"></div>
                            <div class="form-group">
                                <label class="prelabel" for="forgot-email">Your Email</label>
                                <input type="email" class="form-control" id="forgot-email" name="email"
                                    placeholder="name@email.com" required>
                            </div>
                            <div data-theme="dark" class="mb-3 mt-3" id="forgot-recaptcha"></div>

                            <div class="form-group login-btn mb-0">
                                <button class="btn btn-primary btn-block">Submit</button>
                                <div class="loading-relative" id="forgot-loading" style="display: none;">
                                    <div class="loading">
                                        <div class="span1"></div>
                                        <div class="span2"></div>
                                        <div class="span3"></div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer text-center">
                        <a class="link-highlight login-tab-link"><i class="fas fa-angle-left mr-2"></i>Back to
                            Sign-in</a>
                    </div>
                </div>

                <!-- VERIFY TAB -->
                <div id="modal-tab-verify" class="tab-pane fade">
                    <div class="modal-header">
                        <h5 class="modal-title">Send Verification Email</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body" style="padding-bottom: 20px;">
                        <form class="preform" id="verify-form" method="post">
                            <div class="alert alert-success mb-3" id="verify-success" style="display:none"></div>
                            <div class="alert alert-danger mb-3" id="verify-error" style="display:none"></div>
                            <div class="form-group">
                                <label class="prelabel" for="verify-email">Your Email</label>
                                <input type="email" class="form-control" id="verify-email" name="email"
                                    placeholder="name@email.com" required>
                            </div>
                            <div data-theme="dark" class="mb-3 mt-3" id="verify-recaptcha"></div>
                            
                            <div class="form-group login-btn mb-0">
                                <button class="btn btn-primary btn-block">Submit</button>
                                <div class="loading-relative" id="verify-loading" style="display: none;">
                                    <div class="loading">
                                        <div class="span1"></div>
                                        <div class="span2"></div>
                                        <div class="span3"></div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer text-center">
                        <a class="link-highlight login-tab-link"><i class="fas fa-angle-left mr-2"></i>Back to
                            Sign-in</a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<div class="modal fade premodal premodal-characters" id="modalcharacters" tabindex="-1" role="dialog"
     aria-labelledby="modalcharacterstitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-left" id="modalcharacterstitle">Characters & Voice Actors</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="modal-characters">
                    <div id="characters-content"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('hide.bs.modal', function(e) {
    if (document.activeElement && e.target && typeof e.target.contains === 'function' && e.target.contains(document.activeElement)) {
        document.activeElement.blur();
    }
});
</script>