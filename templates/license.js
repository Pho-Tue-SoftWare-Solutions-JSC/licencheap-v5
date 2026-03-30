$(function () {
    var root = $('#licensecheapv5-data');

    if (!root.length) {
        return;
    }

    var accountId = $('#account_id').val();
    var loaderSrc = typeof template_dir !== 'undefined' ? template_dir + 'img/ajax-loading.gif' : '';
    var loaderHtml = loaderSrc ? '<div style="text-align: center"><img src="' + loaderSrc + '"/></div>' : '<div style="text-align: center; padding: 10px 0;">Loading...</div>';

    function loadLicense(refresh) {
        root.html(loaderHtml);

        var payload = {
            id: accountId
        };

        if (refresh) {
            payload.refresh = 1;
        }

        $.post('?cmd=licensecheapv5&action=license', payload).done(function (data) {
            data = parse_response(data);
            root.html(data);
        }).fail(function () {
            root.text('Failed to load license details');
        });
    }

    function copyText(text, callback) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function () {
                if (callback) {
                    callback();
                }
            }).catch(function () {
                window.prompt('Copy command:', text);
                if (callback) {
                    callback();
                }
            });
            return;
        }

        window.prompt('Copy command:', text);
        if (callback) {
            callback();
        }
    }

    loadLicense(false);

    root.on('click', 'a.lic-change-ip', function () {
        var form = $('#licensecheapv5-changeip-form');
        form.bootboxform();
        form.trigger('show');
        return false;
    });

    root.on('click', 'a.lic-renew', function () {
        var form = $('#licensecheapv5-renew-form');
        form.bootboxform();
        form.trigger('show');
        return false;
    });

    root.on('click', 'a.lic-reset-ip-count', function () {
        var form = $('#licensecheapv5-reset-ip-count-form');
        form.bootboxform();
        form.trigger('show');
        return false;
    });

    root.on('click', 'a.lic-refresh-details', function () {
        loadLicense(true);
        return false;
    });

    root.on('click', 'a.lic-copy-command', function () {
        var card = $(this).closest('.lic-command-card');
        var feedback = card.find('.lic-copy-feedback');
        copyText(card.find('code').text(), function () {
            feedback.stop(true, true).fadeIn(120).delay(1200).fadeOut(250);
        });
        return false;
    });
});
