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
                    var account = res ? res.account : null;
                    var isConnected = account && account.accessToken && account.accessToken !== 'pending';

                    if (!isConnected) {
                        // STATE 1: Not Connected
                        var statusHtml = '<div class="clicksync-card" style="border-left: 4px solid #7c3aed;">' +
                            '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">' +
                            '<h3 style="margin: 0; font-size: 16px; font-weight: 600; display: flex; align-items: center; gap: 8px;">' +
                            '🔌 ClickSync Status</h3>' +
                            '<span class="clicksync-badge badge-warning" style="background: #fffbeb; color: #b45309; border: 1px solid #fde68a;">Not Connected</span>' +
                            '</div>' +
                            '<p style="font-size: 13px; color: #64748b; margin-bottom: 16px; line-height: 1.5;">ClickSync is not connected to your ClickUp workspace yet. Authorize ClickSync to connect your store with ClickUp spaces.</p>' +
                            '<a href="' + connectUrl + '" target="_blank" class="clicksync-btn-primary" style="background: #7c3aed; color: white; border: none; text-decoration: none; display: inline-flex; align-items: center;">Connect ClickUp Workspace</a>' +
                            '</div>';
                        $('#clicksync-connection-status-block').html(statusHtml);
                        $('#clicksync-onboarding-container').hide();
                        $('#clicksync-settings-main-container').hide().addClass('clicksync-settings-disabled');
                        return;
                    }

                    // We are connected. Now verify onboarding state.
                    var teamId = account.teamId;
                    var hasRules = account.syncRules && account.syncRules.length > 0;

                    if (!teamId || teamId === 'pending_workspace') {
                        // STATE 2: Onboarding Step 1 - Select Workspace
                        var statusHtml = '<div class="clicksync-card" style="border-left: 4px solid #7c3aed;">' +
                            '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">' +
                            '<h3 style="margin: 0; font-size: 16px; font-weight: 600; display: flex; align-items: center; gap: 8px;">' +
                            '⚡ ClickSync Status</h3>' +
                            '<span class="clicksync-badge badge-warning" style="background: #f3e8ff; color: #7c3aed; border-color: #d8b4fe;">Setup Pending</span>' +
                            '</div>' +
                            '<p style="font-size: 13px; color: #64748b; margin-bottom: 16px; line-height: 1.5;">Successfully authenticated with ClickUp. Please complete the workspace connection steps below.</p>' +
                            '<button class="clicksync-disconnect-btn clicksync-btn-secondary" style="color: #ef4444; border-color: #fecaca; height: 32px; font-size: 12px; padding: 4px 12px;">Disconnect Integration</button>' +
                            '</div>';
                        $('#clicksync-connection-status-block').html(statusHtml);
                        $('#clicksync-settings-main-container').hide().addClass('clicksync-settings-disabled');

                        // Render Step 1 Card
                        var workspacesHtml = '<option value="">-- Choose ClickUp Workspace --</option>';
                        if (res.workspaces && res.workspaces.length > 0) {
                            $.each(res.workspaces, function(i, w) {
                                workspacesHtml += '<option value="' + w.id + '">' + w.name + '</option>';
                            });
                        }
                        var step1Html = '<div class="clicksync-onboarding-card">' +
                            '<div class="clicksync-steps">' +
                            '<div class="clicksync-step active"></div>' +
                            '<div class="clicksync-step"></div>' +
                            '</div>' +
                            '<h2 class="clicksync-onboarding-title">Step 1: Select ClickUp Workspace</h2>' +
                            '<p class="clicksync-onboarding-desc">Choose the ClickUp workspace that contains the spaces, folders, and task lists you wish to synchronize WooCommerce with.</p>' +
                            '<form id="clicksync-setup-workspace-form">' +
                            '<div style="margin-bottom: 20px;">' +
                            '<label class="clicksync-label">Select Workspace</label>' +
                            '<select id="clicksync-setup-team-id" class="clicksync-select" required>' + workspacesHtml + '</select>' +
                            '</div>' +
                            '<button type="submit" class="clicksync-btn-primary" style="width: 100%;">Connect Workspace & Continue</button>' +
                            '</form>' +
                            '</div>';
                        $('#clicksync-onboarding-container').html(step1Html).show();
                        return;
                    }

                    if (!hasRules) {
                        // STATE 3: Onboarding Step 2 - Select Lists
                        var statusHtml = '<div class="clicksync-card" style="border-left: 4px solid #7c3aed;">' +
                            '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">' +
                            '<h3 style="margin: 0; font-size: 16px; font-weight: 600; display: flex; align-items: center; gap: 8px;">' +
                            '⚡ ClickSync Status</h3>' +
                            '<span class="clicksync-badge badge-warning" style="background: #f3e8ff; color: #7c3aed; border-color: #d8b4fe;">Setup Pending</span>' +
                            '</div>' +
                            '<p style="font-size: 13px; color: #64748b; margin-bottom: 16px; line-height: 1.5;">Successfully authenticated. Please choose your synchronization lists to complete onboarding.</p>' +
                            '<button class="clicksync-disconnect-btn clicksync-btn-secondary" style="color: #ef4444; border-color: #fecaca; height: 32px; font-size: 12px; padding: 4px 12px;">Disconnect Integration</button>' +
                            '</div>';
                        $('#clicksync-connection-status-block').html(statusHtml);
                        $('#clicksync-settings-main-container').hide().addClass('clicksync-settings-disabled');

                        // Render Step 2 Card
                        var listsHtml = '<option value="">-- Choose target ClickUp list --</option>';
                        if (res.lists && res.lists.length > 0) {
                            $.each(res.lists, function(i, l) {
                                listsHtml += '<option value="' + l.id + '">' + l.name + '</option>';
                            });
                        }
                        var step2Html = '<div class="clicksync-onboarding-card">' +
                            '<div class="clicksync-steps">' +
                            '<div class="clicksync-step"></div>' +
                            '<div class="clicksync-step active"></div>' +
                            '</div>' +
                            '<h2 class="clicksync-onboarding-title">Step 2: Choose Target Lists</h2>' +
                            '<p class="clicksync-onboarding-desc">Map WooCommerce events to target ClickUp task lists. You can map them all to the same list or choose different lists. You can change this later.</p>' +
                            '<form id="clicksync-setup-lists-form">' +
                            '<div style="display: flex; flex-direction: column; gap: 16px; margin-bottom: 24px;">' +
                            '<div>' +
                            '<label class="clicksync-label">WooCommerce Order Created List</label>' +
                            '<select id="clicksync-setup-orders-list" class="clicksync-select" required>' + listsHtml + '</select>' +
                            '</div>' +
                            '<div>' +
                            '<label class="clicksync-label">WooCommerce Customer Created List</label>' +
                            '<select id="clicksync-setup-customers-list" class="clicksync-select" required>' + listsHtml + '</select>' +
                            '</div>' +
                            '</div>' +
                            '<button type="submit" class="clicksync-btn-primary" style="width: 100%;">Complete Setup & Open Dashboard</button>' +
                            '</form>' +
                            '</div>';
                        $('#clicksync-onboarding-container').html(step2Html).show();
                        return;
                    }

                    // STATE 4: Fully Connected & Configured
                    var pendingCount = res.pendingQueueCount || 0;
                    var statusHtml = '<div class="clicksync-card" style="border-left: 4px solid #10b981;">' +
                        '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">' +
                        '<h3 style="margin: 0; font-size: 16px; font-weight: 600; display: flex; align-items: center; gap: 8px;">' +
                        '✅ ClickSync Status</h3>' +
                        '<div style="display: flex; align-items: center; gap: 8px;">' +
                        '<span class="clicksync-badge badge-info" style="background: #f3e8ff; color: #7c3aed; border-color: #d8b4fe;">' + (account.clickupPlan || 'Free') + ' Workspace</span>' +
                        '<span class="clicksync-badge badge-success" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;">Active Connection</span>' +
                        '</div>' +
                        '</div>' +
                        '<p style="font-size: 13px; color: #64748b; margin-bottom: 16px; line-height: 1.5;">ClickSync is active. Background WooCommerce events are intercepted and synchronized into ClickUp instantly.</p>' +
                        '<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; font-size: 13px; color: #475569; margin-bottom: 16px; line-height: 1.5;">' +
                        'ℹ️ Your integration is subject to <strong>ClickUp\'s plan limits (100 API calls/min)</strong>. If a synchronization fails or experiences delays under heavy load, it is due to ClickUp\'s API rate limits rejecting incoming calls, not our app. ClickSync automatically queues and retries these requests for you.' +
                        '</div>' +
                        '<div style="display: flex; gap: 12px; align-items: center;">' +
                        '<button id="clicksync-process-queue-btn" class="clicksync-btn-secondary" style="height: 36px; font-size: 13px; padding: 8px 16px; display: inline-flex; align-items: center; gap: 6px;">' +
                        '🔄 Process Queue (' + pendingCount + ' pending)' +
                        '</button>' +
                        '<button class="clicksync-disconnect-btn clicksync-btn-secondary" style="color: #ef4444; border-color: #fecaca; height: 36px; font-size: 13px; padding: 8px 16px;">Disconnect Integration</button>' +
                        '</div>' +
                        '</div>';
                    $('#clicksync-connection-status-block').html(statusHtml);
                    $('#clicksync-onboarding-container').hide();
                    $('#clicksync-settings-main-container').show().removeClass('clicksync-settings-disabled');

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
                        $('#plan-card-free, #plan-card-growth, #plan-card-pro').css({ border: '1px solid #e2e8f0', background: '#ffffff' });
                        if (plan.toLowerCase().includes('pro')) {
                            $('#plan-card-pro').css({ border: '2px solid #7c3aed', background: '#f5f3ff' }).find('.plan-active-badge').show();
                        } else if (plan.toLowerCase().includes('growth')) {
                            $('#plan-card-growth').css({ border: '2px solid #7c3aed', background: '#f5f3ff' }).find('.plan-active-badge').show();
                        } else {
                            $('#plan-card-free').css({ border: '2px solid #10b981', background: '#ecfdf5' }).find('.plan-active-badge').show();
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

        // Onboarding Form Submissions
        $(document).on('submit', '#clicksync-setup-workspace-form', function (e) {
            e.preventDefault();
            var btn = $(this).find('button[type="submit"]');
            btn.text('Connecting Workspace...').prop('disabled', true);
            var teamId = $('#clicksync-setup-team-id').val();

            $.ajax({
                url: cloudUrl + '/api/save-config',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    shop: host,
                    actionType: 'save_workspace',
                    payload: { teamId: teamId }
                }),
                success: function (res) {
                    fetchCloudConfig();
                },
                error: function (xhr) {
                    alert('Failed to connect workspace: ' + xhr.responseText);
                    btn.text('Connect Workspace & Continue').prop('disabled', false);
                }
            });
        });

        $(document).on('submit', '#clicksync-setup-lists-form', function (e) {
            e.preventDefault();
            var btn = $(this).find('button[type="submit"]');
            btn.text('Saving destination lists...').prop('disabled', true);
            var ordersListId = $('#clicksync-setup-orders-list').val();
            var customersListId = $('#clicksync-setup-customers-list').val();

            $.ajax({
                url: cloudUrl + '/api/save-config',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    shop: host,
                    actionType: 'save_list_mapping',
                    payload: {
                        ordersListId: ordersListId,
                        customersListId: customersListId,
                        checkoutsListId: ordersListId,
                        draftOrdersListId: ordersListId
                    }
                }),
                success: function (res) {
                    fetchCloudConfig();
                },
                error: function (xhr) {
                    alert('Failed to configure lists: ' + xhr.responseText);
                    btn.text('Complete Setup & Open Dashboard').prop('disabled', false);
                }
            });
        });

        // Trigger manual queue processing sweep
        $(document).on('click', '#clicksync-process-queue-btn', function (e) {
            e.preventDefault();
            var btn = $(this);
            var originalText = btn.html();
            btn.html('🔄 Processing...').prop('disabled', true);

            $.ajax({
                url: cloudUrl + '/api/save-config',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    shop: host,
                    actionType: 'process_queue',
                    payload: {}
                }),
                success: function (res) {
                    alert('Queue sweep complete. Processed ' + (res.processedCount || 0) + ' items.');
                    fetchCloudConfig();
                },
                error: function (xhr) {
                    alert('Failed to process queue: ' + (xhr.responseJSON?.error || xhr.responseText));
                    btn.html(originalText).prop('disabled', false);
                }
            });
        });

        // Trigger manual disconnection
        $(document).on('click', '.clicksync-disconnect-btn', function (e) {
            e.preventDefault();
            if (!confirm("Are you sure you want to disconnect ClickUp? This will reset all your sync rules and mappings.")) {
                return;
            }
            var btn = $(this);
            var originalText = btn.html();
            btn.text('Disconnecting...').prop('disabled', true);

            $.ajax({
                url: cloudUrl + '/api/save-config',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    shop: host,
                    actionType: 'disconnect',
                    payload: {}
                }),
                success: function (res) {
                    fetchCloudConfig();
                },
                error: function (xhr) {
                    alert('Failed to disconnect ClickUp: ' + (xhr.responseJSON?.error || xhr.responseText));
                    btn.html(originalText).prop('disabled', false);
                }
            });
        });

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
