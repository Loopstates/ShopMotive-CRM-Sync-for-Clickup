/* ClickSync WordPress Admin JavaScript - Multi-Page Engine */
(function ($) {
    'use strict';

    $(document).ready(function () {
        var cloudUrl = typeof clicksyncData !== 'undefined' ? clicksyncData.cloudUrl : 'https://clicksync-connect.apps.loopstates.com';
        var host = typeof clicksyncData !== 'undefined' ? clicksyncData.host : window.location.hostname;
        var cachedLogs = [];

        // Option Pills Toggle
        $('.clicksync-option-pill').on('click', function (e) {
            e.preventDefault();
            var targetId = $(this).data('target');
            $(this).toggleClass('active');
            $('#' + targetId).slideToggle(200);
        });

        // Add Field Mapping Row
        $('#add-orders-field-mapping').on('click', function (e) {
            e.preventDefault();
            var rowHtml = '<tr>' +
                '<td style="padding: 6px 8px;"><select class="clicksync-select" style="margin: 0;">' +
                '<option value="order_number">Order Number (order_number)</option>' +
                '<option value="total_price">Total Price (total_price)</option>' +
                '<option value="customer.email">Customer Email (customer.email)</option>' +
                '<option value="customer.phone">Customer Phone (customer.phone)</option>' +
                '<option value="billing_address.city">Billing City (billing_address.city)</option>' +
                '<option value="billing_address.country">Billing Country (billing_address.country)</option>' +
                '<option value="note">Customer Note (note)</option>' +
                '</select></td>' +
                '<td style="padding: 6px 8px;"><select class="clicksync-select clicksync-field-target" style="margin: 0;"><option>Select ClickUp Field</option></select></td>' +
                '<td style="padding: 6px 8px; text-align: right;"><button type="button" class="button button-small clicksync-remove-row" style="color: #dc2626;">Remove</button></td>' +
                '</tr>';
            $('#orders-field-mappings-tbody').append(rowHtml);
        });

        $(document).on('click', '.clicksync-remove-row', function () {
            $(this).closest('tr').remove();
        });

        // Load Config & Logs
        function fetchCloudConfig() {
            var connectUrl = $('#clicksync-connection-status-block').data('connect-url') || '';

            $.ajax({
                url: cloudUrl + '/api/get-config?shop=' + encodeURIComponent(host),
                type: 'GET',
                dataType: 'json',
                success: function (res) {
                    // Update Connection Banner dynamically
                    if (res && res.account && res.account.accessToken && res.account.accessToken !== 'pending' && res.account.accessToken !== 'pending_workspace') {
                        var statusHtml = '<div class="clicksync-card" style="background: #ffffff; border: 1px solid #e1e3e5; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">' +
                            '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">' +
                            '<h3 style="margin: 0; font-size: 16px; font-weight: 600; color: #202223; display: flex; align-items: center; gap: 8px;">' +
                            '<svg style="width: 18px; height: 18px; fill: #6A2B8F;" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>' +
                            'ClickSync Status</h3>' +
                            '<span class="clicksync-badge badge-success" style="background: #e3f1df; color: #008060; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600;">Active Connection</span>' +
                            '</div>' +
                            '<p style="font-size: 13px; color: #6d7175; margin-bottom: 16px; line-height: 1.5;">Successfully synced to ClickUp. Background WooCommerce events are intercepted and queued instantly.</p>' +
                            '<a href="' + connectUrl + '" target="_blank" class="clicksync-btn-primary" style="background: #d82c0d; border-color: #bc2205; color: white; padding: 8px 16px; height: 36px; border-radius: 4px; text-decoration: none; display: inline-flex; align-items: center; font-weight: 600; font-size: 13px;">Disconnect Integration</a>' +
                            '</div>';
                        $('#clicksync-connection-status-block').html(statusHtml);

                        // Enable settings main container
                        $('#clicksync-settings-main-container').removeClass('clicksync-settings-disabled');
                    } else {
                        var statusHtml = '<div class="clicksync-card" style="background: #ffffff; border: 1px solid #e1e3e5; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">' +
                            '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">' +
                            '<h3 style="margin: 0; font-size: 16px; font-weight: 600; color: #202223; display: flex; align-items: center; gap: 8px;">' +
                            '<svg style="width: 18px; height: 18px; fill: #d82c0d;" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>' +
                            'ClickSync Status</h3>' +
                            '<span class="clicksync-badge badge-warning" style="background: #fff4e5; color: #b97a00; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600;">Not Connected</span>' +
                            '</div>' +
                            '<p style="font-size: 13px; color: #6d7175; margin-bottom: 16px; line-height: 1.5;">ClickSync is not connected to your ClickUp workspace yet. Click the button below to authorize connection.</p>' +
                            '<a href="' + connectUrl + '" target="_blank" class="clicksync-btn-primary" style="background: #7c3aed; border-color: #6d28d9; color: white; padding: 8px 16px; height: 36px; border-radius: 4px; text-decoration: none; display: inline-flex; align-items: center; font-weight: 600; font-size: 13px;">Connect ClickUp Workspace</a>' +
                            '</div>';
                        $('#clicksync-connection-status-block').html(statusHtml);

                        // Disable settings main container
                        $('#clicksync-settings-main-container').addClass('clicksync-settings-disabled');
                    }

                    // Update Plan details
                    if (res && res.account) {
                        var plan = res.account.planName || 'Free Plan';
                        var syncCount = res.account.monthlySyncCount || 0;
                        var quota = res.account.monthlyQuota || 100;
                        $('#clicksync-usage-count').text(syncCount);
                        $('#clicksync-usage-quota').text(quota);
                        $('.badge-info').text('Active: ' + plan);

                        // Highlight active card
                        $('.plan-active-badge').hide();
                        $('#plan-card-free, #plan-card-growth, #plan-card-pro').css({ border: '1px solid #e1e3e5', background: '#ffffff' });
                        if (plan.toLowerCase().includes('pro')) {
                            $('#plan-card-pro').css({ border: '2px solid #ff007f', background: '#fff0f7' }).find('.plan-active-badge').show();
                        } else if (plan.toLowerCase().includes('growth')) {
                            $('#plan-card-growth').css({ border: '2px solid #4c1d95', background: '#f5f3ff' }).find('.plan-active-badge').show();
                        } else {
                            $('#plan-card-free').css({ border: '2px solid #008060', background: '#f4f6f8' }).find('.plan-active-badge').show();
                        }
                    }

                    // Populate Target Lists
                    if (res && res.lists && res.lists.length > 0) {
                        var optionsHtml = '<option value="">-- Select ClickUp List --</option>';
                        $.each(res.lists, function (i, l) {
                            optionsHtml += '<option value="' + l.id + '">' + l.name + '</option>';
                        });
                        $('#clicksync-list-orders, #clicksync-list-drafts, #clicksync-list-customers, #clicksync-list-checkouts').html(optionsHtml);
                    } else {
                        $('#clicksync-list-orders, #clicksync-list-drafts, #clicksync-list-customers, #clicksync-list-checkouts').html('<option value="">No lists available</option>');
                    }

                    // Populate Custom Fields Mapping options
                    if (res && res.customFields && res.customFields.length > 0) {
                        var fieldsHtml = '<option value="">-- Select ClickUp Custom Field --</option>';
                        $.each(res.customFields, function (i, f) {
                            fieldsHtml += '<option value="' + f.id + '">' + f.name + ' (' + f.type + ')</option>';
                        });
                        $('.clicksync-field-target').html(fieldsHtml);
                    } else {
                        $('.clicksync-field-target').html('<option value="">No custom fields</option>');
                    }

                    // Populate Statuses dynamically
                    if (res && res.statuses && res.statuses.length > 0) {
                        var statusesHtml = '<option value="">-- Choose Status --</option>';
                        $.each(res.statuses, function (i, s) {
                            var statusName = s.status.charAt(0).toUpperCase() + s.status.slice(1);
                            statusesHtml += '<option value="' + s.status + '">' + statusName + '</option>';
                        });
                        $('.clicksync-statuses-dropdown').html(statusesHtml);
                    } else {
                        $('.clicksync-statuses-dropdown').html('<option value="">No statuses available</option>');
                    }

                    // Populate Assignees dynamically from selected workspace members
                    if (res && res.workspaces && res.workspaces.length > 0) {
                        var assigneesHtml = '<option value="">-- Choose Assignee --</option>';
                        var selectedTeamId = res.account ? res.account.teamId : null;
                        var activeTeam = $.grep(res.workspaces, function (t) { return t.id === selectedTeamId; })[0] || res.workspaces[0];
                        if (activeTeam && activeTeam.members) {
                            $.each(activeTeam.members, function (i, m) {
                                if (m.user) {
                                    assigneesHtml += '<option value="' + m.user.id + '">' + m.user.username + ' (' + m.user.email + ')</option>';
                                }
                            });
                        }
                        $('.clicksync-assignees-dropdown').html(assigneesHtml);
                    } else {
                        $('.clicksync-assignees-dropdown').html('<option value="">No members available</option>');
                    }

                    if (res && res.logs) {
                        cachedLogs = res.logs;
                        renderFullLogs(cachedLogs);
                        renderErrorLogs(cachedLogs);
                    }
                },
                error: function (err) {
                    console.error("Failed to connect to ClickSync Connect Cloud Service:", err);
                    var statusHtml = '<div class="clicksync-card" style="background: #fff5f5; border: 1px solid #fed7d7; border-radius: 8px; padding: 20px; margin-bottom: 20px;">' +
                        '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">' +
                        '<h3 style="margin: 0; font-size: 16px; font-weight: 600; color: #c53030; display: flex; align-items: center; gap: 8px;">' +
                        '⚠️ Connection Error</h3>' +
                        '<span class="clicksync-badge" style="background: #fed7d7; color: #c53030; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600;">Offline</span>' +
                        '</div>' +
                        '<p style="font-size: 13px; color: #9b2c2c; margin-bottom: 0; line-height: 1.5;">Could not connect to ClickSync Cloud Service. Please check if your cloud server is live and running.</p>' +
                        '</div>';
                    $('#clicksync-connection-status-block').html(statusHtml);
                }
            });
        }

        function renderFullLogs(logs) {
            if ($('#full-sync-logs-tbody').length === 0) return;
            if (!logs || logs.length === 0) {
                $('#full-sync-logs-tbody').html('<tr><td colSpan="4" style="padding: 30px; text-align: center; color: #64748b;">No recent sync logs recorded yet.</td></tr>');
                return;
            }

            var html = '';
            $.each(logs, function (i, log) {
                var badgeStyle = log.status === 'Success' ? 'background: #dcfce7; color: #15803d;' : 'background: #fee2e2; color: #b91c1c;';
                var taskLink = log.clickupTaskId ? '<a href="' + log.clickupTaskId + '" target="_blank" style="color: #7c3aed; font-weight: 600; text-decoration: underline;">View ClickUp Task →</a>' : '-';
                html += '<tr>' +
                    '<td style="padding: 12px; border-bottom: 1px solid #e2e8f0;"><strong>' + (log.event || 'Sync Event') + '</strong></td>' +
                    '<td style="padding: 12px; border-bottom: 1px solid #e2e8f0;"><span class="clicksync-badge" style="' + badgeStyle + ' padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600;">' + log.status + '</span></td>' +
                    '<td style="padding: 12px; border-bottom: 1px solid #e2e8f0;">' + taskLink + '</td>' +
                    '<td style="padding: 12px; border-bottom: 1px solid #e2e8f0; text-align: right; font-size: 12px; color: #64748b;">' + (log.createdAt || '') + '</td>' +
                    '</tr>';
            });
            $('#full-sync-logs-tbody').html(html);
        }

        // Initialize Fetch
        fetchCloudConfig();
    });
})(jQuery);
