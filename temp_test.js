
    document.addEventListener("DOMContentLoaded", function () {
        $(document).ready(function () {
            const displayToggle = $("#display-comment");
            let commentsLoaded = false;

            $('#display-comment').on('click', function () {
                const isOff = $(this).toggleClass('off').hasClass('off');
                $('#discussion').toggleClass('ds-hide', isOff);
                $('.to-text').text(isOff ? 'Show Comments' : 'Hide Comments');
                updateLocalStorageSetting('show_comments_at_home', isOff ? '0' : '1');
                if (!isOff && !commentsLoaded) {
                    loadComments();
                }
            });

            // Default OFF: a first-time visitor gets no comments request at all.
            // The widget only loads once someone opts in via the toggle, which
            // persists '1' — so the preference still sticks across visits.
            let stored = getUserSetting('show_comments_at_home', '0');
            const isOff = stored === "0";
            displayToggle.toggleClass("off", isOff);
            $('.to-text').text(isOff ? 'Show Comments' : 'Hide Comments');
            $('#discussion').toggleClass("ds-hide", isOff);

            if (!isOff) {
                loadComments();
            }

            var emptyHtml = '<div class="text-center text-muted" style="padding:40px 15px;">No comments yet.</div>';

            function loadComments() {
                if (commentsLoaded) return;
                commentsLoaded = true;
                $.getJSON(user.rest_url + 'home-comments')
                    .done(function (response) {
                        if (response && response.success && response.html) {
                            $('#comment-widget').html(response.html);
                        } else {
                            $('#comment-widget').html(emptyHtml);
                        }
                    })
                    .fail(function () {
                        commentsLoaded = false;
                        $('#comment-widget').html(emptyHtml);
                    });
            }
        });
    });
