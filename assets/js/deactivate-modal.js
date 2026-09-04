jQuery(document).ready(function($) {
    // Target the deactivate link specifically for ShopMotive
    var $deactivateLink = $('tr[data-slug="shopmotive-crm-sync-for-clickup"] .deactivate a, tr[data-slug="clicksync-connect-clickup-crm-sync-for-woocommerce"] .deactivate a');
    
    if (!$deactivateLink.length) {
        return;
    }

    var deactivateUrl = $deactivateLink.attr('href');
    var logoUrl = (typeof clicksyncData !== 'undefined' && clicksyncData.logoUrl) ? clicksyncData.logoUrl : '';

    // Inject Deactivation Modal HTML into body
    var modalHtml = `
    <div id="shopmotive-deactivate-modal" style="display:none; position:fixed; z-index:999999; left:0; top:0; width:100%; height:100%; background:rgba(15, 23, 42, 0.7); backdrop-filter:blur(4px); align-items:center; justify-content:center;">
        <div style="background:#ffffff; width:100%; max-width:520px; border-radius:12px; box-shadow:0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; overflow:hidden; border:1px solid #e2e8f0; animation: shopmotive-fade-in 0.2s ease-out; box-sizing:border-box;">
            
            <div style="padding:20px 24px; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; background:#f8fafc;">
                <h3 style="margin:0; font-size:16px; font-weight:700; color:#0f172a; display:flex; align-items:center; gap:10px; line-height:1.2;">
                    ${logoUrl ? `<img src="${logoUrl}" alt="ShopMotive" style="width:24px; height:24px; border-radius:6px; display:inline-block; flex-shrink:0; vertical-align:middle;" />` : ''}
                    <span style="display:inline-block; vertical-align:middle;">Deactivate ShopMotive?</span>
                </h3>
                <button type="button" id="shopmotive-modal-close" style="background:none; border:none; font-size:20px; color:#64748b; cursor:pointer; line-height:1;">&times;</button>
            </div>

            <div style="padding:24px; box-sizing:border-box;">
                <p style="margin:0 0 16px 0; font-size:14px; color:#334155; line-height:1.5;">
                    Before you go, please let us know why you are deactivating. If you have an active paid plan, you can also cancel your subscription below so you are not charged.
                </p>

                <div style="margin-bottom:16px; width:100%; box-sizing:border-box;">
                    <label style="display:block; font-size:12px; font-weight:600; color:#475569; margin-bottom:8px; text-transform:uppercase; letter-spacing:0.5px;">Reason for deactivating</label>
                    <select id="shopmotive-exit-reason" style="width:100% !important; max-width:100% !important; min-width:100% !important; box-sizing:border-box !important; margin:0 !important; padding:10px 36px 10px 14px; border:1px solid #cbd5e1; border-radius:6px; font-size:13px; color:#1e293b; background:#ffffff url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E&quot;) no-repeat right 12px center / 16px 16px; appearance:none; -webkit-appearance:none; -moz-appearance:none; cursor:pointer; outline:none; display:block !important;">
                        <option value="">Select reason...</option>
                        <option value="Temporary troubleshooting">Temporary troubleshooting</option>
                        <option value="Too expensive">Too expensive</option>
                        <option value="Switching tools">Switching tools</option>
                        <option value="Missing a feature">Missing a feature</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div style="margin-bottom:20px; width:100%; box-sizing:border-box;">
                    <label style="display:block; font-size:12px; font-weight:600; color:#475569; margin-bottom:6px; text-transform:uppercase; letter-spacing:0.5px;">Additional Feedback (Optional)</label>
                    <textarea id="shopmotive-exit-message" rows="3" placeholder="Tell us how we can improve ShopMotive..." style="width:100% !important; max-width:100% !important; min-width:100% !important; box-sizing:border-box !important; margin:0 !important; padding:10px 14px; border:1px solid #cbd5e1; border-radius:6px; font-size:13px; color:#1e293b; resize:vertical; outline:none; font-family:inherit; display:block !important;"></textarea>
                </div>

                <div style="background:#f1f5f9; padding:12px 16px; border-radius:8px; margin-bottom:20px; display:flex; align-items:center; gap:10px; border:1px solid #e2e8f0; box-sizing:border-box;">
                    <input type="checkbox" id="shopmotive-cancel-sub-check" style="margin:0; width:16px; height:16px; accent-color:#008060; cursor:pointer;" />
                    <label for="shopmotive-cancel-sub-check" style="font-size:13px; font-weight:600; color:#0f172a; cursor:pointer; margin:0;">
                        Cancel my active subscription
                    </label>
                </div>

                <div style="display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap; border-top:1px solid #e2e8f0; padding-top:16px; box-sizing:border-box;">
                    <a href="${deactivateUrl}" id="shopmotive-skip-deactivate" style="font-size:12px; color:#64748b; text-decoration:underline; cursor:pointer;">
                        Skip & Deactivate
                    </a>

                    <div style="display:flex; gap:8px;">
                        <button type="button" id="shopmotive-submit-deactivate" style="padding:8px 16px; font-size:13px; font-weight:600; background:#008060; color:#ffffff; border:none; border-radius:6px; cursor:pointer;">
                            Submit & Deactivate
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <style>
        @keyframes shopmotive-fade-in {
            from { opacity: 0; transform: scale(0.96); }
            to { opacity: 1; transform: scale(1); }
        }
        #shopmotive-exit-reason:focus, #shopmotive-exit-message:focus {
            border-color: #008060 !important;
            box-shadow: 0 0 0 2px rgba(0, 128, 96, 0.15) !important;
        }
    </style>
    `;

    $('body').append(modalHtml);

    // Intercept click on deactivate link
    $deactivateLink.on('click', function(e) {
        e.preventDefault();
        $('#shopmotive-deactivate-modal').css('display', 'flex');
    });

    // Modal Close buttons
    $('#shopmotive-modal-close').on('click', function() {
        $('#shopmotive-deactivate-modal').hide();
    });

    // Submit & Deactivate handler
    $('#shopmotive-submit-deactivate').on('click', function(e) {
        e.preventDefault();
        var $btn = $(this);
        $btn.text('Processing...').prop('disabled', true);

        var reason = $('#shopmotive-exit-reason').val();
        var message = $('#shopmotive-exit-message').val();
        var canceledSub = $('#shopmotive-cancel-sub-check').is(':checked');

        var data = {
            action: 'shopmotive_submit_exit_feedback',
            security: clicksyncData.nonce,
            reason: reason,
            message: message,
            canceledSubscription: canceledSub ? 1 : 0
        };

        $.post(clicksyncData.ajaxurl, data, function() {
            window.location.href = deactivateUrl;
        }).fail(function() {
            window.location.href = deactivateUrl;
        });
    });
});
