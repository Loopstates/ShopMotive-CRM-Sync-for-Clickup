/* ClickSync WordPress Admin JavaScript - Multi-Page Engine */
(function ($) {
    'use strict';

    $(document).ready(function () {
        var cloudUrl = typeof clicksyncData !== 'undefined' ? clicksyncData.cloudUrl : 'https://shopmotive.apps.loopstates.com';
        var host = typeof clicksyncData !== 'undefined' ? clicksyncData.host : window.location.hostname;
        var cachedLogs = [];
        var activePlanName = 'Free Plan';

        // Global variables to store ClickUp members and statuses
        var workspacesData = [];
        var woocommerceFields = {
            orders: { default_fields: [], meta_fields: [], order_statuses: [] },
            customers: { default_fields: [], meta_fields: [] }
        };

        // Append premium toast container
        $('body').append('<div id="clicksync-toast-container" style="position: fixed; bottom: 24px; right: 24px; z-index: 100000; display: flex; flex-direction: column; gap: 12px; pointer-events: none;"></div>');

        // Onboarding Lock Helpers (WordPress Compliant Notice & Disabled Inputs)
        function lockSyncRules() {
            $('#clicksync-rules-lock-banner').slideDown(200);
            $('.clicksync-card').not('#billing-section').not('#clicksync-connection-status-block').each(function () {
                var card = $(this);
                card.css('opacity', '0.6');
                card.find('input, select, button').prop('disabled', true);
            });
        }

        function unlockSyncRules() {
            $('#clicksync-rules-lock-banner').slideUp(200);
            $('.clicksync-card').not('#billing-section').not('#clicksync-connection-status-block').each(function () {
                var card = $(this);
                card.css('opacity', '1');
                card.find('input, select, button').prop('disabled', false);
            });
        }

        // Toggle Upgrade Drawer
        $(document).on('click', '#clicksync-toggle-upgrade-btn', function (e) {
            e.preventDefault();
            $('#clicksync-upgrade-drawer').slideToggle(200);
        });

        // Dynamic Rule Lists Rendering Helper
        function getFriendlyFieldName(key, isCustomer) {
            var list = isCustomer ? 
                (woocommerceFields.customers.default_fields || []).concat(woocommerceFields.customers.meta_fields || []) : 
                (woocommerceFields.orders.default_fields || []).concat(woocommerceFields.orders.meta_fields || []);
            
            var found = $.grep(list, function (f) { return f.key === key; })[0];
            return found ? found.label : key;
        }

        function getFriendlyOperator(op) {
            var map = {
                'equals': 'is equal to',
                'not_equals': 'is not equal to',
                'contains': 'contains',
                'not_contains': 'does not contain',
                'starts_with': 'starts with',
                'greater_than_or_equal': 'is greater than or equal to',
                'less_than_or_equal': 'is less than or equal to',
                'greater_than': 'is greater than',
                'less_than': 'is less than'
            };
            return map[op] || op;
        }

        function getPriorityLabel(level) {
            var map = {
                1: 'Urgent',
                2: 'High',
                3: 'Normal',
                4: 'Low'
            };
            return map[level] || 'Priority ' + level;
        }

        function getMemberName(userId, selectedTeamId) {
            var activeTeam = $.grep(workspacesData, function (t) { return String(t.id) === String(selectedTeamId); })[0];
            if (activeTeam && activeTeam.members) {
                var found = $.grep(activeTeam.members, function (m) { return m.user && String(m.user.id) === String(userId); })[0];
                if (found && found.user) {
                    return found.user.username || found.user.email;
                }
            }
            return 'User ' + userId;
        }

        function getCustomFieldName(fieldId, cardId) {
            var cachedFields = $('#' + cardId).data('custom-fields') || [];
            var found = $.grep(cachedFields, function (f) { return String(f.id) === String(fieldId); })[0];
            return found ? found.name : fieldId;
        }

        function getFriendlyWooStatus(slug) {
            var found = $.grep(woocommerceFields.orders.order_statuses || [], function (s) { return s.slug === slug; })[0];
            return found ? found.label : slug;
        }

        function renderRuleRows(rule, targetTeamId) {
            var isCustomer = rule.shopifyEvent === 'customers/create';
            var eventSuffix = isCustomer ? 'customers' : 'orders';
            var cardId = isCustomer ? 'clicksync-customers-rule-card' : 'clicksync-orders-rule-card';

            // 1. Assignees
            var assigneeHtml = '';
            if (rule.assigneeRules && rule.assigneeRules.length > 0) {
                $.each(rule.assigneeRules, function (i, r) {
                    assigneeHtml += '<div class="clicksync-rule-row" style="display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13px; margin-bottom: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">' +
                        '<span>If <strong>' + getFriendlyFieldName(r.shopifyPropertyPath, isCustomer) + '</strong> ' + getFriendlyOperator(r.operator) + ' <strong>"' + r.value + '"</strong> then assign to <strong>' + getMemberName(r.clickupAssigneeId, targetTeamId) + '</strong></span>' +
                        '<button type="button" class="clicksync-delete-rule-btn" data-rule-id="' + r.id + '" data-action="delete_assignee_rule" style="background: none; border: none; color: #ef4444; cursor: pointer; display: flex; align-items: center; padding: 4px; transition: color 0.15s ease;">' +
                        '<svg style="width: 15px; height: 15px; fill: currentColor;" viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>' +
                        '</button>' +
                        '</div>';
                });
            } else {
                assigneeHtml = '<p style="font-size: 12px; color: #6d7175; margin-bottom: 12px; font-style: italic;">No assignee routing rules configured yet. Order tasks will remain unassigned by default.</p>';
            }
            $('#' + eventSuffix + '-assignee-rules-list').html(assigneeHtml);

            // 2. Priorities
            var priorityHtml = '';
            if (rule.priorityRules && rule.priorityRules.length > 0) {
                $.each(rule.priorityRules, function (i, r) {
                    priorityHtml += '<div class="clicksync-rule-row" style="display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13px; margin-bottom: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">' +
                        '<span>If <strong>' + getFriendlyFieldName(r.shopifyPropertyPath, isCustomer) + '</strong> ' + getFriendlyOperator(r.operator) + ' <strong>"' + r.value + '"</strong> then set priority to <strong>' + getPriorityLabel(r.priorityLevel) + '</strong></span>' +
                        '<button type="button" class="clicksync-delete-rule-btn" data-rule-id="' + r.id + '" data-action="delete_priority_rule" style="background: none; border: none; color: #ef4444; cursor: pointer; display: flex; align-items: center; padding: 4px; transition: color 0.15s ease;">' +
                        '<svg style="width: 15px; height: 15px; fill: currentColor;" viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>' +
                        '</button>' +
                        '</div>';
                });
            } else {
                priorityHtml = '<p style="font-size: 12px; color: #6d7175; margin-bottom: 12px; font-style: italic;">No priority routing rules configured yet. Order tasks will default to no priority.</p>';
            }
            $('#' + eventSuffix + '-priority-rules-list').html(priorityHtml);

            // 3. Tagging
            var taggingHtml = '';
            if (rule.tagRules && rule.tagRules.length > 0) {
                $.each(rule.tagRules, function (i, r) {
                    taggingHtml += '<div class="clicksync-rule-row" style="display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13px; margin-bottom: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">' +
                        '<span>If <strong>' + getFriendlyFieldName(r.shopifyPropertyPath, isCustomer) + '</strong> ' + getFriendlyOperator(r.operator) + ' <strong>"' + r.value + '"</strong> then apply tag <span class="clicksync-badge" style="background: #efe6fc; color: #6d28d9; border: 1px solid #d8b4fe; font-size: 11px; padding: 2px 8px; border-radius: 12px;">' + r.clickupTag + '</span></span>' +
                        '<button type="button" class="clicksync-delete-rule-btn" data-rule-id="' + r.id + '" data-action="delete_tag_rule" style="background: none; border: none; color: #ef4444; cursor: pointer; display: flex; align-items: center; padding: 4px; transition: color 0.15s ease;">' +
                        '<svg style="width: 15px; height: 15px; fill: currentColor;" viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>' +
                        '</button>' +
                        '</div>';
                });
            } else {
                taggingHtml = '<p style="font-size: 12px; color: #6d7175; margin-bottom: 12px; font-style: italic;">No tagging rules configured yet. Add rules below to automatically assign tags in ClickUp.</p>';
            }
            $('#' + eventSuffix + '-tagging-rules-list').html(taggingHtml);

            // 4. Custom fields
            var cfHtml = '';
            if (rule.fieldMappings && rule.fieldMappings.length > 0) {
                $.each(rule.fieldMappings, function (i, r) {
                    cfHtml += '<div class="clicksync-rule-row" style="display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13px; margin-bottom: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">' +
                        '<span>Map WooCommerce <strong>' + getFriendlyFieldName(r.shopifyPropertyPath, isCustomer) + '</strong> to ClickUp custom field <strong>' + getCustomFieldName(r.clickupFieldId, cardId) + '</strong></span>' +
                        '<button type="button" class="clicksync-delete-rule-btn" data-rule-id="' + r.id + '" data-action="delete_field_mapping" style="background: none; border: none; color: #ef4444; cursor: pointer; display: flex; align-items: center; padding: 4px; transition: color 0.15s ease;">' +
                        '<svg style="width: 15px; height: 15px; fill: currentColor;" viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>' +
                        '</button>' +
                        '</div>';
                });
            } else {
                cfHtml = '<p style="font-size: 12px; color: #6d7175; margin-bottom: 12px; font-style: italic;">No custom field mappings defined yet for this rule.</p>';
            }
            $('#' + eventSuffix + '-customfields-rules-list').html(cfHtml);

            // 4b. Regional List Routing rules (orders only)
            if (rule.shopifyEvent === 'orders/create') {
                var listRoutingHtml = '';
                if (rule.listRules && rule.listRules.length > 0) {
                    $.each(rule.listRules, function (i, r) {
                        listRoutingHtml += '<div class="clicksync-rule-row" style="display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13px; margin-bottom: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">' +
                            '<span>If <strong>' + getFriendlyFieldName(r.shopifyPropertyPath, false) + '</strong> ' + getFriendlyOperator(r.operator) + ' <strong>"' + r.value + '"</strong> then route to ClickUp list <strong>' + getListName(r.clickupListId) + '</strong></span>' +
                            '<button type="button" class="clicksync-delete-rule-btn" data-rule-id="' + r.id + '" data-action="delete_list_rule" style="background: none; border: none; color: #ef4444; cursor: pointer; display: flex; align-items: center; padding: 4px; transition: color 0.15s ease;">' +
                            '<svg style="width: 15px; height: 15px; fill: currentColor;" viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>' +
                            '</button>' +
                            '</div>';
                    });
                } else {
                    listRoutingHtml = '<p style="font-size: 12px; color: #6d7175; margin-bottom: 12px; font-style: italic;">No regional list routing rules configured yet. Tasks will route to the default ClickUp list.</p>';
                }
                $('#orders-list-routing-rules-list').html(listRoutingHtml);
            }

            // 5. Status actions (orders only)
            if (rule.shopifyEvent === 'orders/create') {
                var statusHtml = '';
                if (rule.statusMappings && rule.statusMappings.length > 0) {
                    $.each(rule.statusMappings, function (i, r) {
                        var actionText = 'Update WooCommerce order status to ' + getFriendlyWooStatus(r.shopifyAction);
                        statusHtml += '<div class="clicksync-rule-row" style="display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13px; margin-bottom: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">' +
                            '<span>When ClickUp task status updates to <strong>' + String(r.clickupStatus).toUpperCase() + '</strong>, then execute action <strong>' + actionText + '</strong></span>' +
                            '<button type="button" class="clicksync-delete-rule-btn" data-rule-id="' + r.id + '" data-action="delete_status_mapping" style="background: none; border: none; color: #ef4444; cursor: pointer; display: flex; align-items: center; padding: 4px; transition: color 0.15s ease;">' +
                            '<svg style="width: 15px; height: 15px; fill: currentColor;" viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>' +
                            '</button>' +
                            '</div>';
                    });
                } else {
                    statusHtml = '<p style="font-size: 12px; color: #6d7175; margin-bottom: 12px; font-style: italic;">No status action mappings configured.</p>';
                }
                $('#orders-status-rules-list').html(statusHtml);
            }
        }

        // Option Pills Toggle Click Handlers
        $(document).on('click', '.clicksync-option-pill', function (e) {
            e.preventDefault();
            var targetId = $(this).data('target');
            var isCurrentlyActive = $(this).hasClass('active');

            // Gating rules to match features matrix:
            // - Regional List Routing: Pro only
            // - Assignee Routing (Conditional Tags & Assignees): Pro only
            // - Priority Rules: Pro only
            // - Tagging Rules: Pro only
            // - Custom Fields (Custom Field Mapping): Growth or Pro
            var isGated = false;
            var tierRequired = '';

            if (targetId.indexOf('list-routing') !== -1 || targetId.indexOf('assignee') !== -1 || targetId.indexOf('priority') !== -1 || targetId.indexOf('tagging') !== -1) {
                if (activePlanName !== 'Pro Plan') {
                    isGated = true;
                    tierRequired = 'Pro Plan';
                }
            } else if (targetId.indexOf('customfields') !== -1) {
                if (activePlanName === 'Free Plan') {
                    isGated = true;
                    tierRequired = 'Growth Plan';
                }
            }

            if (isGated) {
                showClickSyncToast(
                    $(this).text() + ' Locked',
                    $(this).text() + ' is a ' + tierRequired + ' feature. Please upgrade your plan to unlock this advanced sync capability.'
                );
            }

            if (isCurrentlyActive) {
                $(this).removeClass('active');
                $('#' + targetId).slideUp(200);
            } else {
                $(this).addClass('active');
                $('#' + targetId).slideDown(200);
            }

            if (isGated) {
                return;
            }

            // Immediately save options status
            var cardId = $(this).closest('.clicksync-card').attr('id');
            var eventType = cardId === 'clicksync-orders-rule-card' ? 'orders/create' : 'customers/create';
            saveOptionTogglesForEvent(eventType);
        });

        function saveOptionTogglesForEvent(eventType) {
            var cardSelector = eventType === 'orders/create' ? '#clicksync-orders-rule-card' : '#clicksync-customers-rule-card';
            var card = $(cardSelector);
            var ruleId = card.data('rule-id');
            if (!ruleId) return;

            var splitRouting = $('#orders-split-routing').is(':checked');

            // Map button states to variables
            var assigneeEnabled = card.find('.clicksync-option-pill[data-target*="assignee"]').hasClass('active');
            var priorityEnabled = card.find('.clicksync-option-pill[data-target*="priority"]').hasClass('active');
            var taggingEnabled = card.find('.clicksync-option-pill[data-target*="tagging"]').hasClass('active');
            var fieldMappingsEnabled = card.find('.clicksync-option-pill[data-target*="customfields"]').hasClass('active');

            $.ajax({
                url: cloudUrl + '/api/save-config',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    shop: host,
                    actionType: 'save_option_toggles',
                    payload: {
                        ruleId: ruleId,
                        splitRouting: splitRouting,
                        assigneeRulesEnabled: assigneeEnabled,
                        priorityRulesEnabled: priorityEnabled,
                        tagRulesEnabled: taggingEnabled,
                        fieldMappingsEnabled: fieldMappingsEnabled
                    }
                }),
                success: function (res) {
                    console.log('Saved options toggles status successfully.');
                },
                error: function (xhr) {
                    console.error('Failed to auto-save toggle state: ' + xhr.responseText);
                }
            });
        }

        // Save target lists and active rule toggles (Save Settings button)
        $(document).on('click', '#clicksync-save-all-settings', function (e) {
            e.preventDefault();
            var btn = $(this);
            var originalHtml = btn.html();
            btn.html('<svg style="width: 16px; height: 16px; fill: none; stroke: currentColor; stroke-width: 3; stroke-linecap: round; animation: spin 1s linear infinite; display: inline-block; vertical-align: middle; margin-right: 8px;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="rgba(255,255,255,0.2)"></circle><path d="M4 12a8 8 0 018-8" fill="none" stroke="currentColor"></path></svg> Saving...').prop('disabled', true);

            var ordersListId = $('#clicksync-list-orders').val();
            var customersListId = $('#clicksync-list-customers').val();

            // First save target list mapping
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
                    // Update active statuses for rules as well
                    var ordersRuleId = $('#clicksync-orders-rule-card').data('rule-id');
                    var customersRuleId = $('#clicksync-customers-rule-card').data('rule-id');

                    var ordersActive = $('#orders-toggle').is(':checked');
                    var customersActive = $('#customers-toggle').is(':checked');

                    // Call toggle rule for orders
                    $.ajax({
                        url: cloudUrl + '/api/save-config',
                        type: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify({
                            shop: host,
                            actionType: 'toggle_rule',
                            payload: { ruleId: ordersRuleId, active: ordersActive }
                        }),
                        success: function () {
                            // Call toggle rule for customers
                            $.ajax({
                                url: cloudUrl + '/api/save-config',
                                type: 'POST',
                                contentType: 'application/json',
                                data: JSON.stringify({
                                    shop: host,
                                    actionType: 'toggle_rule',
                                    payload: { ruleId: customersRuleId, active: customersActive }
                                }),
                                success: function () {
                                    // Save local WordPress settings (Refunds & Fulfillment toggles) via WP AJAX
                                    var refundsEnabled = $('#orders-sync-refunds').is(':checked') ? '1' : '0';
                                    var fulfillmentEnabled = $('#orders-sync-fulfillment').is(':checked') ? '1' : '0';

                                     $.ajax({
                                         url: clicksyncData.ajaxUrl,
                                         type: 'POST',
                                         data: {
                                             action: 'shopmotive_save_local_settings',
                                             refunds_enabled: refundsEnabled,
                                             fulfillment_enabled: fulfillmentEnabled,
                                             security: clicksyncData.nonce
                                         },
                                         success: function (wpRes) {
                                             // Collect and save User Identity Mappings
                                             var userMappings = {};
                                             $('.clicksync-user-mapping-row').each(function() {
                                                 var row = $(this);
                                                 var wpId = row.attr('data-wp-user-id');
                                                 var cuId = row.find('.clicksync-member-mapping-select').val();
                                                 if (wpId && cuId) {
                                                     userMappings[wpId] = cuId;
                                                 }
                                             });
                                             var fallbackCuId = $('#clicksync-fallback-member-select').val();
 
                                             $.ajax({
                                                 url: clicksyncData.ajaxUrl,
                                                 type: 'POST',
                                                 data: {
                                                     action: 'shopmotive_save_user_mappings',
                                                     mappings: userMappings,
                                                     fallback_clickup_user_id: fallbackCuId,
                                                     security: clicksyncData.nonce
                                                 },
                                                 success: function() {
                                                    // Save toggles (pills) for both rules
                                                    saveOptionTogglesForEvent('orders/create');
                                                    saveOptionTogglesForEvent('customers/create');

                                                    alert('Configurations saved successfully.');
                                                    btn.html(originalHtml).prop('disabled', false);
                                                    fetchCloudConfig();
                                                },
                                                error: function() {
                                                    alert('Failed to save user identity mappings.');
                                                    btn.html(originalHtml).prop('disabled', false);
                                                }
                                            });
                                        },
                                        error: function () {
                                            alert('Failed to save local WordPress settings.');
                                            btn.html(originalHtml).prop('disabled', false);
                                        }
                                    });
                                },
                                error: function (err) {
                                    alert('Failed to save customer rule active status.');
                                    btn.html(originalHtml).prop('disabled', false);
                                }
                            });
                        },
                        error: function (err) {
                            alert('Failed to save order rule active status.');
                            btn.html(originalHtml).prop('disabled', false);
                        }
                    });
                },
                error: function (xhr) {
                    alert('Failed to configure target lists: ' + xhr.responseText);
                    btn.html(originalHtml).prop('disabled', false);
                }
            });
        });

        // Add assignee rule
        $(document).on('click', '.clicksync-add-assignee-rule-btn', function (e) {
            e.preventDefault();
            var btn = $(this);
            var eventType = btn.data('event');
            var container = btn.closest('.clicksync-card');

            var field = container.find('.clicksync-assignee-field').val();
            var operator = container.find('.clicksync-assignee-operator').val();
            var value = container.find('.clicksync-assignee-value').val();
            var assignee = container.find('.clicksync-assignees-dropdown').val();

            if (!value) {
                alert('Please input a compare value.');
                return;
            }
            if (!assignee) {
                alert('Please select an assignee.');
                return;
            }

            btn.text('Adding...').prop('disabled', true);

            $.ajax({
                url: cloudUrl + '/api/save-config',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    shop: host,
                    actionType: 'add_assignee_rule',
                    payload: {
                        event: eventType,
                        shopifyPropertyPath: field,
                        operator: operator,
                        value: value,
                        clickupAssigneeId: assignee
                    }
                }),
                success: function (res) {
                    container.find('.clicksync-assignee-value').val('');
                    btn.text('Add Rule').prop('disabled', false);
                    fetchCloudConfig();
                },
                error: function (xhr) {
                    alert('Failed to add rule: ' + xhr.responseText);
                    btn.text('Add Rule').prop('disabled', false);
                }
            });
        });

        // Add priority rule
        $(document).on('click', '.clicksync-add-priority-rule-btn', function (e) {
            e.preventDefault();
            var btn = $(this);
            var eventType = btn.data('event');
            var container = btn.closest('.clicksync-card');

            var field = container.find('.clicksync-priority-field').val();
            var operator = container.find('.clicksync-priority-operator').val();
            var value = container.find('.clicksync-priority-value').val();
            var priority = container.find('.clicksync-priority-level').val();

            if (!value) {
                alert('Please input a compare value.');
                return;
            }

            btn.text('Adding...').prop('disabled', true);

            $.ajax({
                url: cloudUrl + '/api/save-config',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    shop: host,
                    actionType: 'add_priority_rule',
                    payload: {
                        event: eventType,
                        shopifyPropertyPath: field,
                        operator: operator,
                        value: value,
                        priorityLevel: priority
                    }
                }),
                success: function (res) {
                    container.find('.clicksync-priority-value').val('');
                    btn.text('Add Rule').prop('disabled', false);
                    fetchCloudConfig();
                },
                error: function (xhr) {
                    alert('Failed to add rule: ' + xhr.responseText);
                    btn.text('Add Rule').prop('disabled', false);
                }
            });
        });

        // Add tag rule
        $(document).on('click', '.clicksync-add-tag-rule-btn', function (e) {
            e.preventDefault();
            var btn = $(this);
            var eventType = btn.data('event');
            var container = btn.closest('.clicksync-card');

            var field = container.find('.clicksync-tag-field').val();
            var operator = container.find('.clicksync-tag-operator').val();
            var value = container.find('.clicksync-tag-value').val();
            var tag = container.find('.clicksync-tag-tag').val();

            if (!value) {
                alert('Please input a compare value.');
                return;
            }
            if (!tag) {
                alert('Please specify the tag to apply.');
                return;
            }

            btn.text('Adding...').prop('disabled', true);

            $.ajax({
                url: cloudUrl + '/api/save-config',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    shop: host,
                    actionType: 'add_tag_rule',
                    payload: {
                        event: eventType,
                        shopifyPropertyPath: field,
                        operator: operator,
                        value: value,
                        clickupTag: tag
                    }
                }),
                success: function (res) {
                    container.find('.clicksync-tag-value').val('');
                    container.find('.clicksync-tag-tag').val('');
                    btn.text('Add Rule').prop('disabled', false);
                    fetchCloudConfig();
                },
                error: function (xhr) {
                    alert('Failed to add rule: ' + xhr.responseText);
                    btn.text('Add Rule').prop('disabled', false);
                }
            });
        });

        // Add custom field mapping
        $(document).on('click', '.clicksync-add-field-mapping-btn', function (e) {
            e.preventDefault();
            var btn = $(this);
            var eventType = btn.data('event');
            var container = btn.closest('.clicksync-card');

            var field = container.find('.clicksync-field-field').val();
            var target = container.find('.clicksync-field-target').val();

            if (!target) {
                alert('Please select a target ClickUp custom field.');
                return;
            }

            btn.text('Mapping...').prop('disabled', true);

            $.ajax({
                url: cloudUrl + '/api/save-config',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    shop: host,
                    actionType: 'add_field_mapping',
                    payload: {
                        event: eventType,
                        shopifyPropertyPath: field,
                        clickupFieldId: target
                    }
                }),
                success: function (res) {
                    btn.text('Add Mapping').prop('disabled', false);
                    fetchCloudConfig();
                },
                error: function (xhr) {
                    alert('Failed to add mapping: ' + (xhr.responseJSON?.error || xhr.responseText));
                    btn.text('Add Mapping').prop('disabled', false);
                }
            });
        });

        // Add status mapping action
        $(document).on('click', '.clicksync-add-status-mapping-btn', function (e) {
            e.preventDefault();
            var btn = $(this);
            var eventType = btn.data('event');
            var container = btn.closest('.clicksync-card');

            var status = container.find('.clicksync-statuses-dropdown').val();
            var action = container.find('.clicksync-status-action').val();

            if (!status) {
                alert('Please select a ClickUp status.');
                return;
            }

            btn.text('Adding...').prop('disabled', true);

            $.ajax({
                url: cloudUrl + '/api/save-config',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    shop: host,
                    actionType: 'add_status_mapping',
                    payload: {
                        event: eventType,
                        clickupStatus: status,
                        shopifyAction: action
                    }
                }),
                success: function (res) {
                    btn.text('Add Action').prop('disabled', false);
                    fetchCloudConfig();
                },
                error: function (xhr) {
                    alert('Failed to add status mapping: ' + xhr.responseText);
                    btn.text('Add Action').prop('disabled', false);
                }
            });
        });

        // Handle rule removal click
        $(document).on('click', '.clicksync-delete-rule-btn', function (e) {
            e.preventDefault();
            var btn = $(this);
            var ruleId = btn.data('rule-id');
            var action = btn.data('action');

            if (!confirm('Are you sure you want to delete this configuration rule?')) {
                return;
            }

            btn.prop('disabled', true).css('opacity', 0.5);

            $.ajax({
                url: cloudUrl + '/api/save-config',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    shop: host,
                    actionType: action,
                    payload: { ruleId: ruleId }
                }),
                success: function (res) {
                    fetchCloudConfig();
                },
                error: function (xhr) {
                    alert('Failed to delete rule: ' + xhr.responseText);
                    btn.prop('disabled', false).css('opacity', 1);
                }
            });
        });

        // Dynamic List-Specific ClickUp Fields and Statuses Fetching
        function fetchListMetadata(listId, cardId, callback) {
            var card = $('#' + cardId);
            if (!listId) {
                card.find('.clicksync-field-target').html('<option value="">-- Select a target list first --</option>');
                card.find('.clicksync-statuses-dropdown').html('<option value="">-- Select a target list first --</option>');
                if (callback) callback();
                return;
            }

            card.find('.clicksync-field-target').html('<option value="">Loading custom fields...</option>');
            card.find('.clicksync-statuses-dropdown').html('<option value="">Loading statuses...</option>');

            $.ajax({
                url: cloudUrl + '/api/get-list-metadata?shop=' + encodeURIComponent(host) + '&listId=' + encodeURIComponent(listId),
                type: 'GET',
                dataType: 'json',
                cache: false,
                success: function (res) {
                    // Cache list custom fields locally in DOM to resolve names in renderRuleRows
                    card.data('custom-fields', res.customFields || []);

                    // 1. Populate custom fields inside this card
                    if (res.customFields && res.customFields.length > 0) {
                        var fieldsHtml = '<option value="">-- Select ClickUp Custom Field --</option>';
                        $.each(res.customFields, function (i, f) {
                            fieldsHtml += '<option value="' + f.id + '">' + f.name + ' (' + f.type + ')</option>';
                        });
                        card.find('.clicksync-field-target').html(fieldsHtml);
                    } else {
                        card.find('.clicksync-field-target').html('<option value="">No custom fields available</option>');
                    }

                    // 2. Populate statuses inside this card
                    if (res.statuses && res.statuses.length > 0) {
                        var statusesHtml = '<option value="">-- Choose Status --</option>';
                        $.each(res.statuses, function (i, s) {
                            var statusVal = typeof s === 'string' ? s : (s.status || '');
                            if (!statusVal) return;
                            var statusName = statusVal.charAt(0).toUpperCase() + statusVal.slice(1);
                            statusesHtml += '<option value="' + statusVal + '">' + statusName + '</option>';
                        });
                        card.find('.clicksync-statuses-dropdown').html(statusesHtml);
                    } else {
                        card.find('.clicksync-statuses-dropdown').html('<option value="">No statuses available</option>');
                    }

                    if (callback) callback();
                },
                error: function (err) {
                    console.error("Failed to fetch ClickUp list metadata:", err);
                    card.find('.clicksync-field-target').html('<option value="">Failed to load custom fields</option>');
                    card.find('.clicksync-statuses-dropdown').html('<option value="">Failed to load statuses</option>');
                    if (callback) callback();
                }
            });
        }

        // Listen for list changes to dynamically reload list custom fields and statuses
        $(document).on('change', '#clicksync-list-orders', function () {
            fetchListMetadata($(this).val(), 'clicksync-orders-rule-card');
        });

        $(document).on('change', '#clicksync-list-customers', function () {
            fetchListMetadata($(this).val(), 'clicksync-customers-rule-card');
        });

        // Dynamic WooCommerce Fields Retrieval
        function fetchWooCommerceFields(callback) {
            $.ajax({
                url: clicksyncData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'shopmotive_get_wc_fields',
                    security: clicksyncData.nonce
                },
                success: function (res) {
                    if (res.success && res.data) {
                        woocommerceFields = res.data;
                        populateWooCommerceFieldsDropdowns();
                    }
                    if (callback) callback();
                },
                error: function (err) {
                    console.error("Failed to fetch dynamic WooCommerce fields:", err);
                    if (callback) callback();
                }
            });
        }

        function populateWooCommerceFieldsDropdowns() {
            // Orders Field Selectors
            var ordersHtml = '';
            if (woocommerceFields.orders.default_fields && woocommerceFields.orders.default_fields.length > 0) {
                ordersHtml += '<optgroup label="Default Fields">';
                $.each(woocommerceFields.orders.default_fields, function (i, f) {
                    ordersHtml += '<option value="' + f.key + '">' + f.label + '</option>';
                });
                ordersHtml += '</optgroup>';
            }
            if (woocommerceFields.orders.meta_fields && woocommerceFields.orders.meta_fields.length > 0) {
                ordersHtml += '<optgroup label="Custom Meta Fields">';
                $.each(woocommerceFields.orders.meta_fields, function (i, f) {
                    ordersHtml += '<option value="' + f.key + '">' + f.label + '</option>';
                });
                ordersHtml += '</optgroup>';
            }
            $('#clicksync-orders-rule-card .clicksync-assignee-field, #clicksync-orders-rule-card .clicksync-priority-field, #clicksync-orders-rule-card .clicksync-tag-field, #clicksync-orders-rule-card .clicksync-field-field, #clicksync-orders-rule-card .clicksync-list-routing-field').html(ordersHtml);

            // Customers Field Selectors
            var customersHtml = '';
            if (woocommerceFields.customers.default_fields && woocommerceFields.customers.default_fields.length > 0) {
                customersHtml += '<optgroup label="Default Fields">';
                $.each(woocommerceFields.customers.default_fields, function (i, f) {
                    customersHtml += '<option value="' + f.key + '">' + f.label + '</option>';
                });
                customersHtml += '</optgroup>';
            }
            if (woocommerceFields.customers.meta_fields && woocommerceFields.customers.meta_fields.length > 0) {
                customersHtml += '<optgroup label="Custom Meta Fields">';
                $.each(woocommerceFields.customers.meta_fields, function (i, f) {
                    customersHtml += '<option value="' + f.key + '">' + f.label + '</option>';
                });
                customersHtml += '</optgroup>';
            }
            $('#clicksync-customers-rule-card .clicksync-tag-field, #clicksync-customers-rule-card .clicksync-field-field').html(customersHtml);

            // Status Mappings WooCommerce Action Selector
            var statusActionsHtml = '';
            if (woocommerceFields.orders.order_statuses && woocommerceFields.orders.order_statuses.length > 0) {
                $.each(woocommerceFields.orders.order_statuses, function (i, s) {
                    statusActionsHtml += '<option value="' + s.slug + '">Update order status to: ' + s.label + '</option>';
                });
            } else {
                statusActionsHtml += '<option value="processing">Update order status to: Processing</option>' +
                                    '<option value="completed">Update order status to: Completed</option>' +
                                    '<option value="on-hold">Update order status to: On Hold</option>' +
                                    '<option value="cancelled">Update order status to: Cancelled</option>';
            }
            $('.clicksync-status-action').html(statusActionsHtml);
        }

        var configRetryCount = 0;
        function fetchCloudConfig() {
            var connectUrl = $('#clicksync-connection-status-block').data('connect-url') || '';

            $.ajax({
                url: cloudUrl + '/api/get-config?shop=' + encodeURIComponent(host),
                type: 'GET',
                dataType: 'json',
                cache: false,
                success: function (res) {
                    configRetryCount = 0; // Reset retry counter on success
                    var account = res ? res.account : null;
                    var isConnected = account && account.accessToken && account.accessToken !== 'pending';

                    if (!isConnected) {
                        // STATE 1: Not Connected
                        var statusHtml = '<div class="clicksync-card" style="border-left: 4px solid #7c3aed;">' +
                            '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">' +
                            '<h3 style="margin: 0; font-size: 16px; font-weight: 600; display: flex; align-items: center; gap: 8px;">' +
                            '<svg class="clicksync-title-icon" style="fill: #7c3aed;" viewBox="0 0 24 24"><path d="M2 18.439l3.69-2.828c1.961 2.56 4.044 3.739 6.363 3.739 2.307 0 4.33-1.166 6.203-3.704L22 18.405C19.298 22.065 15.941 24 12.053 24c-3.875 0-7.265-1.922-10.053-5.561zM12.04 6.15L5.472 11.81l-3.036-3.52L12.055 0l9.543 8.296-3.05 3.509z"/></svg> ShopMotive Connection</h3>' +
                            '<span class="clicksync-badge badge-warning" style="background: #fffbeb; color: #b45309; border: 1px solid #fde68a;">Not Connected</span>' +
                            '</div>' +
                            '<p style="font-size: 13px; color: #64748b; margin-bottom: 16px; line-height: 1.5;">ClickSync is not connected to your ClickUp workspace yet. Authorize ClickSync to connect your store with ClickUp spaces.</p>' +
                            '<a href="' + connectUrl + '" target="_blank" class="clicksync-btn-primary" style="background: #008060; color: white; border: none; text-decoration: none; display: inline-flex; align-items: center;">Connect ClickUp Workspace</a>' +
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
                            '<svg class="clicksync-title-icon" style="fill: #7c3aed;" viewBox="0 0 24 24"><path d="M2 18.439l3.69-2.828c1.961 2.56 4.044 3.739 6.363 3.739 2.307 0 4.33-1.166 6.203-3.704L22 18.405C19.298 22.065 15.941 24 12.053 24c-3.875 0-7.265-1.922-10.053-5.561zM12.04 6.15L5.472 11.81l-3.036-3.52L12.055 0l9.543 8.296-3.05 3.509z"/></svg> ShopMotive Connection</h3>' +
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
                            '<svg class="clicksync-title-icon" style="fill: #7c3aed;" viewBox="0 0 24 24"><path d="M2 18.439l3.69-2.828c1.961 2.56 4.044 3.739 6.363 3.739 2.307 0 4.33-1.166 6.203-3.704L22 18.405C19.298 22.065 15.941 24 12.053 24c-3.875 0-7.265-1.922-10.053-5.561zM12.04 6.15L5.472 11.81l-3.036-3.52L12.055 0l9.543 8.296-3.05 3.509z"/></svg> ShopMotive Connection</h3>' +
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

                    // STATE 4: Fully Connected
                    var pendingCount = res.pendingQueueCount || 0;
                    var clickupPlanStr = account.clickupPlan || 'Free';
                    var clickupRateLimit = account.clickupLimit;
                    if (!clickupRateLimit) {
                        var planLower = clickupPlanStr.toLowerCase();
                        if (planLower.indexOf('enterprise') !== -1) {
                            clickupRateLimit = 10000;
                        } else if (planLower.indexOf('business plus') !== -1 || planLower.indexOf('business-plus') !== -1) {
                            clickupRateLimit = 1000;
                        } else {
                            clickupRateLimit = 100;
                        }
                    }
                    
                    // Disable Process Queue button if nothing is in the queue
                    var isQueueEmpty = pendingCount === 0;
                    var queueDisabledAttr = isQueueEmpty ? 'disabled="disabled"' : '';
                    var queueDisabledStyle = isQueueEmpty ? 'opacity: 0.65; cursor: not-allowed; pointer-events: none;' : '';

                    var statusHtml = '<div class="clicksync-card" style="border-left: 4px solid #10b981;">' +
                        '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 12px;">' +
                        // LHS: Title + Active Connection Badge
                        '<div style="display: flex; align-items: center; gap: 12px;">' +
                        '<h3 style="margin: 0; font-size: 16px; font-weight: 600; display: flex; align-items: center; gap: 8px;">' +
                        '<svg class="clicksync-title-icon" style="fill: #10b981;" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg> ClickUp Connection</h3>' +
                        '<span class="clicksync-badge badge-success" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; padding: 4px 10px; font-weight: 600; font-size: 11px;">Active Connection</span>' +
                        '</div>' +
                        // RHS: ClickUp Subscription logo + Plan
                        '<div style="display: flex; align-items: center; gap: 8px;">' +
                        '<span style="font-size: 13px; font-weight: 600; color: #475569; display: inline-flex; align-items: center; gap: 4px;">' +
                        '<svg style="width: 14px; height: 14px; fill: #7c3aed; margin-right: 4px;" viewBox="0 0 24 24"><path d="M2 18.439l3.69-2.828c1.961 2.56 4.044 3.739 6.363 3.739 2.307 0 4.33-1.166 6.203-3.704L22 18.405C19.298 22.065 15.941 24 12.053 24c-3.875 0-7.265-1.922-10.053-5.561zM12.04 6.15L5.472 11.81l-3.036-3.52L12.055 0l9.543 8.296-3.05 3.509z"/></svg>' +
                        'ClickUp Subscription:</span> ' +
                        '<span class="clicksync-badge clicksync-clickup-plan-badge" style="background: #f3e8ff; color: #7c3aed; border-color: #d8b4fe; padding: 4px 10px; font-weight: 600; font-size: 11px;">' + clickupPlanStr + '</span>' +
                        '</div>' +
                        '</div>' +
                        '<p style="font-size: 13px; color: #64748b; margin-bottom: 16px; line-height: 1.5;">ClickSync is active. Background WooCommerce events are intercepted and synchronized into ClickUp instantly.</p>' +
                        '<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; font-size: 13px; color: #475569; margin-bottom: 16px; line-height: 1.5;">' +
                        '<svg class="clicksync-title-icon" style="width: 16px; height: 16px; fill: #64748b;" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg> Your integration is subject to the API rate limits of your active ClickUp <strong>' + clickupPlanStr + ' Plan</strong> (which restricts traffic to <strong>' + clickupRateLimit + ' API calls/min</strong>). If a synchronization fails or experiences delays under heavy load, it is due to ClickUp\'s API rate limits rejecting incoming calls, not our app. ClickSync automatically queues and retries these requests for you.' +
                        '</div>' +
                        '<div style="display: flex; gap: 12px; align-items: center;">' +
                        '<button id="clicksync-process-queue-btn" class="clicksync-btn-primary clicksync-btn-green" style="background: #10b981; color: white; border: none; height: 36px; font-size: 13px; padding: 8px 16px; display: inline-flex; align-items: center; gap: 6px; border-radius: 6px; font-weight: 500; cursor: pointer; ' + queueDisabledStyle + '" ' + queueDisabledAttr + '>' +
                        '<svg class="clicksync-title-icon" style="width: 14px; height: 14px; fill: currentColor; margin-right: 6px; margin-left: 0;" viewBox="0 0 24 24"><path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/></svg> Process Queue (' + pendingCount + ' pending)' +
                        '</button>' +
                        '<button class="clicksync-disconnect-btn clicksync-btn-primary clicksync-btn-red" style="background: #ef4444; color: white; border: none; height: 36px; font-size: 13px; padding: 8px 16px; border-radius: 6px; font-weight: 500; cursor: pointer;">Disconnect Integration</button>' +
                        '</div>' +
                        '</div>';
                    $('#clicksync-connection-status-block').html(statusHtml);
                    $('#clicksync-onboarding-container').hide();
                    $('#clicksync-settings-main-container').show().removeClass('clicksync-settings-disabled');

                    // Reset plan active badges inside drawer & visual cards styles
                    $('.plan-active-badge').hide();
                    $('#plan-card-growth, #plan-card-pro').removeClass('active clicksync-btn-disabled').css('pointer-events', 'auto');
                    $('#plan-card-growth .plan-action').html('Upgrade &rarr;');
                    $('#plan-card-pro .plan-action').html('Upgrade &rarr;');

                    if (res && res.account) {
                        var plan = res.account.planName || 'None';
                        activePlanName = plan;
                        var syncCount = res.account.monthlySyncCount || 0;
                        var boostQuota = res.account.boostQuota || 0;
                        var boostMonths = res.account.boostIterationsLeft || 0;
                        var baseQuota = plan.toLowerCase().indexOf('pro') !== -1 ? 10000 : (plan.toLowerCase().indexOf('growth') !== -1 ? 1000 : 100);
                        var quota = res.account.monthlyQuota || 100;
                        
                        $('#clicksync-usage-count').text(syncCount);
                        if (boostQuota > 0 && boostMonths > 0) {
                            $('#clicksync-usage-quota').html(baseQuota + ' <span style="color: #7c3aed; font-weight: bold;">+ ' + boostQuota + '</span> <span style="font-size: 11px; color: #6d7175; font-weight: normal; margin-left: 4px;">(⚡ Boost: ' + boostMonths + 'mo left)</span>');
                        } else {
                            $('#clicksync-usage-quota').text(quota);
                        }

                        var pct = quota > 0 ? Math.min(100, Math.max(0, (syncCount / quota) * 100)) : 0;
                        var barColor = '#10b981';
                        if (pct >= 80) {
                            barColor = '#ef4444';
                        } else if (pct >= 50) {
                            barColor = '#f59e0b';
                        }
                        $('#clicksync-quota-progress-bar').css({
                            'width': pct + '%',
                            'background-color': barColor
                        });

                        if (res.account.lastSyncReset) {
                            var resetDate = new Date(res.account.lastSyncReset);
                            if (!isNaN(resetDate.getTime())) {
                                var pad = function (num) { return (num < 10 ? '0' : '') + num; };
                                var formattedReset = resetDate.getFullYear() + '-' + 
                                    pad(resetDate.getMonth() + 1) + '-' + 
                                    pad(resetDate.getDate()) + ' ' + 
                                    pad(resetDate.getHours()) + ':' + 
                                    pad(resetDate.getMinutes()) + ':' + 
                                    pad(resetDate.getSeconds());
                                $('#clicksync-usage-reset').text(formattedReset);
                            }
                        }
                        
                        // Hide all main billing bar action buttons by default
                        $('#clicksync-activate-free-btn, #clicksync-toggle-upgrade-btn, #clicksync-upgrade-to-pro-btn, #clicksync-custom-quota-btn').hide();
                        $('#clicksync-upgrade-drawer').hide();

                        // Silent Contact Info Sync to Cloud
                        if (!res.account.email || !res.account.ownerName) {
                            var statusBlock = $('#clicksync-connection-status-block');
                            var emailVal = statusBlock.data('site-email') || '';
                            var siteTitleVal = statusBlock.data('site-title') || '';
                            var ownerNameVal = statusBlock.data('site-owner') || '';
                            
                            if (emailVal) {
                                $.ajax({
                                    url: cloudUrl + '/api/save-config',
                                    type: 'POST',
                                    contentType: 'application/json',
                                    data: JSON.stringify({
                                        shop: host,
                                        actionType: 'update_contact_info',
                                        payload: {
                                            email: emailVal,
                                            siteTitle: siteTitleVal,
                                            ownerName: ownerNameVal
                                        }
                                    })
                                });
                            }
                        }

                        var activeBadge = $('#clicksync-active-plan-badge');
                        activeBadge.removeClass('badge-info clicksync-plan-none clicksync-plan-free clicksync-plan-growth clicksync-plan-pro').attr('style', 'padding: 2px 8px; border-radius: 12px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;');
                        var badgeIcon = '';

                        if (plan === 'None') {
                            badgeIcon = '<svg style="width: 12px; height: 12px; fill: #b91c1c; margin-right: 4px; vertical-align: middle;" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>';
                            activeBadge.addClass('clicksync-plan-none').html(badgeIcon + 'No Plan Selected');
                            $('#clicksync-activate-free-btn').show();
                            lockSyncRules();
                        } else if (plan === 'Free Plan') {
                            badgeIcon = '<svg style="width: 12px; height: 12px; fill: #047857; margin-right: 4px; vertical-align: middle;" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>';
                            activeBadge.addClass('clicksync-plan-free').html(badgeIcon + 'Free Plan');
                            $('#clicksync-toggle-upgrade-btn').show();
                            unlockSyncRules();
                        } else if (plan === 'Growth Plan') {
                            badgeIcon = '<svg style="width: 16px; height: 16px; fill: #7c3aed; margin-right: 6px; vertical-align: middle; display: inline-block;" viewBox="0 0 24 24"><path d="M16 2H8L3.25 8.5 12 22 20.75 8.5 16 2zM7.5 7L10 3.3v3.7H7.5zm4.5-3.6l2.3 3.6h-4.6l2.3-3.6zM14 7V3.3l2.5 3.7H14zm-4 2h4v10.5l-4-10.5z"/></svg>';
                            activeBadge.addClass('clicksync-plan-growth').html(badgeIcon + 'Growth Plan');
                            $('#clicksync-toggle-upgrade-btn').show();
                            
                            // Highlight inside drawer just in case
                            $('#plan-card-growth').addClass('active clicksync-btn-disabled').css('pointer-events', 'none').find('.plan-active-badge').show();
                            $('#plan-card-growth .plan-action').html('Active');
                            unlockSyncRules();
                        } else if (plan === 'Pro Plan') {
                            badgeIcon = '<svg style="width: 16px; height: 16px; fill: #b45309; margin-right: 6px; vertical-align: middle; display: inline-block;" viewBox="0 0 24 24"><path d="M2 4l3 12h14l3-12-6 7-4-7-4 7-6-7zm3 14h14v2H5v-2z"/></svg>';
                            activeBadge.addClass('clicksync-plan-pro').html(badgeIcon + 'Pro Plan');
                            
                            // Prevent mailto link, open custom quota modal
                            $('#clicksync-custom-quota-btn').off('click').on('click', function(e) {
                                e.preventDefault();
                                $('#clicksync-quota-modal').css('display', 'flex');
                            }).show();

                            // Highlight inside drawer just in case
                            $('#plan-card-pro').addClass('active clicksync-btn-disabled').css('pointer-events', 'none').find('.plan-active-badge').show();
                            $('#plan-card-pro .plan-action').html('Active');
                            unlockSyncRules();
                        }
                        
                        if (res.account.boostQuota > 0 && res.account.boostIterationsLeft > 0) {
                            activeBadge.append(' <span style="font-size: 10px; background: rgba(124, 58, 237, 0.1); color: #7c3aed; padding: 1.5px 6px; border-radius: 8px; font-weight: 700; margin-left: 6px; display: inline-flex; align-items: center; gap: 2px; vertical-align: middle;">⚡ Boost Active</span>');
                        }
                    }

                    // Store workspaces globally for member resolution
                    workspacesData = res.workspaces || [];

                    // Populate Target Lists Dropdowns
                    if (res && res.lists && res.lists.length > 0) {
                        var optionsHtml = '<option value="">-- Select ClickUp List --</option>';
                        $.each(res.lists, function (i, l) {
                            optionsHtml += '<option value="' + l.id + '">' + l.name + '</option>';
                        });
                        $('#clicksync-list-orders, #clicksync-list-customers, .clicksync-list-routing-list').html(optionsHtml);
                    } else {
                        $('#clicksync-list-orders, #clicksync-list-customers, .clicksync-list-routing-list').html('<option value="">No lists available</option>');
                    }

                    // Populate Assignees dynamically from selected workspace members
                    if (res && res.workspaces && res.workspaces.length > 0) {
                        var assigneesHtml = '<option value="">-- Choose Assignee --</option>';
                        var selectedTeamId = res.account ? res.account.teamId : null;
                        var activeTeam = $.grep(res.workspaces, function (t) { return String(t.id) === String(selectedTeamId); })[0] || res.workspaces[0];
                        if (activeTeam && activeTeam.members) {
                            $.each(activeTeam.members, function (i, m) {
                                if (m.user) {
                                    assigneesHtml += '<option value="' + m.user.id + '">' + m.user.username + ' (' + m.user.email + ')</option>';
                                }
                            });
                        }
                        $('.clicksync-assignees-dropdown').html(assigneesHtml);

                        // Populate member identity mapping dropdowns inside mapping card
                        var memberMappingHtml = '<option value="">-- Choose Workspace Member --</option>';
                        if (activeTeam && activeTeam.members) {
                            $.each(activeTeam.members, function (i, m) {
                                if (m.user) {
                                    memberMappingHtml += '<option value="' + m.user.id + '">' + m.user.username + ' (' + m.user.email + ')</option>';
                                }
                            });
                        }
                        
                        $('.clicksync-member-mapping-select, #clicksync-fallback-member-select').each(function() {
                            var select = $(this);
                            var selectedVal = select.attr('data-selected');
                            select.html(memberMappingHtml).val(selectedVal);
                        });
                    } else {
                        $('.clicksync-assignees-dropdown').html('<option value="">No members available</option>');
                        $('.clicksync-member-mapping-select, #clicksync-fallback-member-select').html('<option value="">No members available</option>');
                    }

                    // Fetch dynamic WooCommerce fields from WP
                    fetchWooCommerceFields(function () {
                        // Map Saved syncRules configurations to the settings view
                        if (account && account.syncRules && account.syncRules.length > 0) {
                            $.each(account.syncRules, function (i, rule) {
                                if (rule.shopifyEvent === 'orders/create') {
                                    var card = $('#clicksync-orders-rule-card');
                                    card.data('rule-id', rule.id);
                                    $('#clicksync-list-orders').val(rule.clickupListId);
                                    $('#orders-toggle').prop('checked', rule.active);
                                    $('#orders-split-routing').prop('checked', rule.splitRouting);

                                    // Map options pill active states and show/hide blocks
                                    card.find('.clicksync-option-pill[data-field="assigneeRulesEnabled"]').toggleClass('active', rule.assigneeRulesEnabled);
                                    $('#orders-assignee-block').toggle(rule.assigneeRulesEnabled);

                                    card.find('.clicksync-option-pill[data-field="priorityRulesEnabled"]').toggleClass('active', rule.priorityRulesEnabled);
                                    $('#orders-priority-block').toggle(rule.priorityRulesEnabled);

                                    card.find('.clicksync-option-pill[data-field="tagRulesEnabled"]').toggleClass('active', rule.tagRulesEnabled);
                                    $('#orders-tagging-block').toggle(rule.tagRulesEnabled);

                                    card.find('.clicksync-option-pill[data-field="fieldMappingsEnabled"]').toggleClass('active', rule.fieldMappingsEnabled);
                                    $('#orders-customfields-block').toggle(rule.fieldMappingsEnabled);
                                    
                                    var hasListRules = rule.listRules && rule.listRules.length > 0;
                                    card.find('.clicksync-option-pill[data-target="orders-list-routing-block"]').toggleClass('active', hasListRules);
                                    $('#orders-list-routing-block').toggle(hasListRules);

                                    // Load list-specific metadata (custom fields and statuses) for Order list
                                    fetchListMetadata(rule.clickupListId, 'clicksync-orders-rule-card', function () {
                                        renderRuleRows(rule, account.teamId);
                                    });
                                }

                                if (rule.shopifyEvent === 'customers/create') {
                                    var card = $('#clicksync-customers-rule-card');
                                    card.data('rule-id', rule.id);
                                    $('#clicksync-list-customers').val(rule.clickupListId);
                                    $('#customers-toggle').prop('checked', rule.active);

                                    card.find('.clicksync-option-pill[data-field="tagRulesEnabled"]').toggleClass('active', rule.tagRulesEnabled);
                                    $('#customers-tagging-block').toggle(rule.tagRulesEnabled);

                                    card.find('.clicksync-option-pill[data-field="fieldMappingsEnabled"]').toggleClass('active', rule.fieldMappingsEnabled);
                                    $('#customers-customfields-block').toggle(rule.fieldMappingsEnabled);

                                    // Load list-specific metadata (custom fields) for Customer list
                                    fetchListMetadata(rule.clickupListId, 'clicksync-customers-rule-card', function () {
                                        renderRuleRows(rule, account.teamId);
                                    });
                                }
                            });
                        } else {
                            // If no rules are saved yet, load metadata for whatever lists are default-selected
                            var oList = $('#clicksync-list-orders').val();
                            if (oList) fetchListMetadata(oList, 'clicksync-orders-rule-card');
                            
                            var cList = $('#clicksync-list-customers').val();
                            if (cList) fetchListMetadata(cList, 'clicksync-customers-rule-card');
                        }
                    });

                    if (res && res.logs) {
                        cachedLogs = res.logs;
                        renderFullLogs(cachedLogs);
                        renderErrorLogs(cachedLogs);
                    }
                    enforcePlanLocks();
                },
                error: function (err) {
                    if (configRetryCount < 3) {
                        configRetryCount++;
                        console.warn("ShopMotive Cloud connection failed. Retrying (" + configRetryCount + "/3) in 2 seconds...");
                        setTimeout(fetchCloudConfig, 2000);
                        return;
                    }
                    console.error("Failed to connect to ShopMotive Connect Cloud Service:", err);
                    var statusHtml = '<div class="clicksync-card" style="background: #fff5f5; border: 1px solid #fed7d7; border-radius: 8px; padding: 20px; margin-bottom: 20px;">' +
                        '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">' +
                        '<h3 style="margin: 0; font-size: 16px; font-weight: 600; color: #c53030; display: flex; align-items: center; gap: 8px;">' +
                        '<svg class="clicksync-title-icon" style="fill: #c53030;" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg> Connection Error</h3>' +
                        '<span class="clicksync-badge" style="background: #fed7d7; color: #c53030; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600;">Offline</span>' +
                        '</div>' +
                        '<p style="font-size: 13px; color: #9b2c2c; margin-bottom: 0; line-height: 1.5;">Could not connect to ShopMotive Cloud Service. Please check if your cloud server is live and running.</p>' +
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

            var statusBlock = $('#clicksync-connection-status-block');
            var emailVal = statusBlock.data('site-email') || '';
            var siteTitleVal = statusBlock.data('site-title') || '';
            var ownerNameVal = statusBlock.data('site-owner') || '';

            $.ajax({
                url: cloudUrl + '/api/save-config',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    shop: host,
                    actionType: 'save_workspace',
                    payload: {
                        teamId: teamId,
                        email: emailVal,
                        siteTitle: siteTitleVal,
                        ownerName: ownerNameVal
                    }
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
            btn.html('<svg class="clicksync-title-icon" style="width: 14px; height: 14px; fill: currentColor; margin-right: 6px; animation: spin 1s linear infinite;" viewBox="0 0 24 24"><path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/></svg> Processing...').prop('disabled', true);

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

        window.clicksyncAllLogs = [];
        window.currentLogsPage = 1;
        window.currentErrorsPage = 1;

        function applyLogsFiltersAndRender() {
            var searchVal = ($('#logs-search').val() || '').toLowerCase().trim();
            var statusFilter = $('#logs-status-filter').val() || '';
            var perPage = parseInt($('#logs-per-page').val() || '25', 10);
            
            var logs = window.clicksyncAllLogs || [];
            var filtered = $.grep(logs, function(log) {
                var matchesSearch = true;
                var matchesStatus = true;

                if (searchVal) {
                    var event = (log.event || '').toLowerCase();
                    var taskId = (log.clickupTaskId || '').toLowerCase();
                    var details = (log.details || '').toLowerCase();
                    var error = (log.error || '').toLowerCase();
                    matchesSearch = event.indexOf(searchVal) !== -1 || 
                                    taskId.indexOf(searchVal) !== -1 || 
                                    details.indexOf(searchVal) !== -1 || 
                                    error.indexOf(searchVal) !== -1;
                }

                if (statusFilter) {
                    matchesStatus = log.status === statusFilter;
                }

                return matchesSearch && matchesStatus;
            });

            var totalItems = filtered.length;
            var totalPages = Math.ceil(totalItems / perPage) || 1;
            if (window.currentLogsPage > totalPages) {
                window.currentLogsPage = totalPages;
            }
            if (window.currentLogsPage < 1) {
                window.currentLogsPage = 1;
            }

            var start = (window.currentLogsPage - 1) * perPage;
            var end = start + perPage;
            var pageItems = filtered.slice(start, end);

            var tbody = $('#full-sync-logs-tbody');
            if (tbody.length === 0) return;

            if (pageItems.length === 0) {
                tbody.html('<tr><td colSpan="4" style="padding: 30px; text-align: center; color: #64748b;">No matching sync logs found.</td></tr>');
            } else {
                var html = '';
                $.each(pageItems, function (i, log) {
                    var badgeStyle = log.status === 'Success' ? 'background: #dcfce7; color: #15803d;' : 'background: #fee2e2; color: #b91c1c;';
                    var taskLink = '-';
                    if (log.clickupTaskId) {
                        var taskUrl = log.clickupTaskId.startsWith('http') ? log.clickupTaskId : 'https://app.clickup.com/t/' + log.clickupTaskId;
                        taskLink = '<a href="' + taskUrl + '" target="_blank" style="color: #008060; font-weight: 600; text-decoration: underline; display: inline-flex; align-items: center; gap: 4px;">View ClickUp Task <svg style="width: 12px; height: 12px; fill: #008060;" viewBox="0 0 24 24"><path d="M19 19H5V5h7V3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2v-7h-2v7zM14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3h-7z"/></svg></a>';
                    }
                    html += '<tr>' +
                        '<td style="padding: 12px 24px; border-bottom: 1px solid #e2e8f0;"><strong>' + (log.event || 'Sync Event') + '</strong></td>' +
                        '<td style="padding: 12px 24px; border-bottom: 1px solid #e2e8f0;"><span class="clicksync-badge" style="' + badgeStyle + ' padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600;">' + log.status + '</span></td>' +
                        '<td style="padding: 12px 24px; border-bottom: 1px solid #e2e8f0;">' + taskLink + '</td>' +
                        '<td style="padding: 12px 24px; border-bottom: 1px solid #e2e8f0; text-align: right; font-size: 12px; color: #64748b;">' + (log.createdAt || '') + '</td>' +
                        '</tr>';
                });
                tbody.html(html);
            }

            var controlsDiv = $('#logs-pagination-controls');
            if (controlsDiv.length > 0) {
                var paginationHtml = '';
                var itemStart = totalItems ? start + 1 : 0;
                var itemEnd = Math.min(end, totalItems);
                paginationHtml += '<span style="font-size: 12px; color: #64748b; margin-right: 8px;">' + itemStart + '-' + itemEnd + ' of ' + totalItems + '</span>';

                var backDisabled = window.currentLogsPage === 1 ? 'disabled="disabled" style="opacity: 0.5; cursor: not-allowed;"' : '';
                paginationHtml += '<button type="button" class="clicksync-btn-secondary logs-page-prev" ' + backDisabled + ' style="padding: 4px 10px; height: 28px; font-size: 12px; font-weight: 600;">&laquo; Prev</button>';

                var nextDisabled = window.currentLogsPage === totalPages ? 'disabled="disabled" style="opacity: 0.5; cursor: not-allowed;"' : '';
                paginationHtml += '<button type="button" class="clicksync-btn-secondary logs-page-next" ' + nextDisabled + ' style="padding: 4px 10px; height: 28px; font-size: 12px; font-weight: 600; margin-left: 4px;">Next &raquo;</button>';

                controlsDiv.html(paginationHtml);
            }
        }

        function applyErrorsFiltersAndRender() {
            var searchVal = ($('#errors-search').val() || '').toLowerCase().trim();
            var perPage = parseInt($('#errors-per-page').val() || '25', 10);
            
            var logs = window.clicksyncAllLogs || [];
            var errorLogs = $.grep(logs, function (l) { return l.status !== 'Success'; });
            
            var filtered = $.grep(errorLogs, function(log) {
                if (searchVal) {
                    var event = (log.event || '').toLowerCase();
                    var error = (log.error || '').toLowerCase();
                    return event.indexOf(searchVal) !== -1 || error.indexOf(searchVal) !== -1;
                }
                return true;
            });

            var totalItems = filtered.length;
            var totalPages = Math.ceil(totalItems / perPage) || 1;
            if (window.currentErrorsPage > totalPages) {
                window.currentErrorsPage = totalPages;
            }
            if (window.currentErrorsPage < 1) {
                window.currentErrorsPage = 1;
            }

            var start = (window.currentErrorsPage - 1) * perPage;
            var end = start + perPage;
            var pageItems = filtered.slice(start, end);

            var tbody = $('#sync-errors-tbody');
            if (tbody.length === 0) return;

            if (pageItems.length === 0) {
                tbody.html('<tr><td colSpan="3" style="padding: 30px; text-align: center; color: #64748b;">No matching error traces found.</td></tr>');
            } else {
                var html = '';
                $.each(pageItems, function (i, log) {
                    var retryBtn = '<button type="button" class="clicksync-btn-secondary clicksync-retry-event-btn" data-topic="' + log.event + '" data-payload=\'' + JSON.stringify(log.payload || {}) + '\' style="padding: 4px 10px; height: 28px; font-size: 12px; font-weight: 600;">Retry Now</button>';
                    html += '<tr>' +
                        '<td style="padding: 12px 24px; border-bottom: 1px solid #e2e8f0;"><strong>' + (log.event || 'Sync Event') + '</strong></td>' +
                        '<td style="padding: 12px 24px; border-bottom: 1px solid #e2e8f0; color: #ef4444; font-family: monospace; font-size: 11px;">' + (log.error || 'Unknown Error') + '</td>' +
                        '<td style="padding: 12px 24px; border-bottom: 1px solid #e2e8f0; text-align: right;">' + retryBtn + '</td>' +
                        '</tr>';
                });
                tbody.html(html);
            }

            var controlsDiv = $('#errors-pagination-controls');
            if (controlsDiv.length > 0) {
                var paginationHtml = '';
                var itemStart = totalItems ? start + 1 : 0;
                var itemEnd = Math.min(end, totalItems);
                paginationHtml += '<span style="font-size: 12px; color: #64748b; margin-right: 8px;">' + itemStart + '-' + itemEnd + ' of ' + totalItems + '</span>';

                var backDisabled = window.currentErrorsPage === 1 ? 'disabled="disabled" style="opacity: 0.5; cursor: not-allowed;"' : '';
                paginationHtml += '<button type="button" class="clicksync-btn-secondary errors-page-prev" ' + backDisabled + ' style="padding: 4px 10px; height: 28px; font-size: 12px; font-weight: 600;">&laquo; Prev</button>';

                var nextDisabled = window.currentErrorsPage === totalPages ? 'disabled="disabled" style="opacity: 0.5; cursor: not-allowed;"' : '';
                paginationHtml += '<button type="button" class="clicksync-btn-secondary errors-page-next" ' + nextDisabled + ' style="padding: 4px 10px; height: 28px; font-size: 12px; font-weight: 600; margin-left: 4px;">Next &raquo;</button>';

                controlsDiv.html(paginationHtml);
            }
        }

        function renderFullLogs(logs) {
            window.clicksyncAllLogs = logs || [];
            
            if ($('#full-sync-logs-tbody').length > 0 && $('#logs-search').length === 0) {
                var miniLogs = window.clicksyncAllLogs.slice(0, 5);
                var html = '';
                $.each(miniLogs, function (i, log) {
                    var badgeStyle = log.status === 'Success' ? 'background: #dcfce7; color: #15803d;' : 'background: #fee2e2; color: #b91c1c;';
                    var taskLink = '-';
                    if (log.clickupTaskId) {
                        var taskUrl = log.clickupTaskId.startsWith('http') ? log.clickupTaskId : 'https://app.clickup.com/t/' + log.clickupTaskId;
                        taskLink = '<a href="' + taskUrl + '" target="_blank" style="color: #008060; font-weight: 600; text-decoration: underline; display: inline-flex; align-items: center; gap: 4px;">View Task <svg style="width: 12px; height: 12px; fill: #008060;" viewBox="0 0 24 24"><path d="M19 19H5V5h7V3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2v-7h-2v7zM14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3h-7z"/></svg></a>';
                    }
                    html += '<tr>' +
                        '<td style="padding: 12px; border-bottom: 1px solid #e2e8f0;"><strong>' + (log.event || 'Sync Event') + '</strong></td>' +
                        '<td style="padding: 12px; border-bottom: 1px solid #e2e8f0;"><span class="clicksync-badge" style="' + badgeStyle + ' padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600;">' + log.status + '</span></td>' +
                        '<td style="padding: 12px; border-bottom: 1px solid #e2e8f0;">' + taskLink + '</td>' +
                        '<td style="padding: 12px; border-bottom: 1px solid #e2e8f0; text-align: right; font-size: 12px; color: #64748b;">' + (log.createdAt || '') + '</td>' +
                        '</tr>';
                });
                $('#full-sync-logs-tbody').html(html || '<tr><td colspan="4" style="padding: 20px; text-align: center;">No sync logs yet.</td></tr>');
            }

            if ($('#logs-search').length > 0) {
                applyLogsFiltersAndRender();
            }
        }

        function renderErrorLogs(logs) {
            window.clicksyncAllLogs = logs || [];
            
            if ($('#error-sync-logs-tbody').length > 0) {
                var errorLogs = $.grep(window.clicksyncAllLogs, function (l) { return l.status !== 'Success'; });
                var miniErrors = errorLogs.slice(0, 5);
                if (miniErrors.length === 0) {
                    $('#error-sync-logs-tbody').html('<tr><td colSpan="3" style="padding: 30px; text-align: center; color: #64748b;">No error logs recorded. Great job!</td></tr>');
                } else {
                    var html = '';
                    $.each(miniErrors, function (i, log) {
                        html += '<tr>' +
                            '<td style="padding: 12px; border-bottom: 1px solid #e2e8f0;"><strong>' + (log.event || 'Sync Event') + '</strong></td>' +
                            '<td style="padding: 12px; border-bottom: 1px solid #e2e8f0; color: #ef4444;">' + (log.error || 'Unknown Error') + '</td>' +
                            '<td style="padding: 12px; border-bottom: 1px solid #e2e8f0; text-align: right; font-size: 12px; color: #64748b;">' + (log.createdAt || '') + '</td>' +
                            '</tr>';
                    });
                    $('#error-sync-logs-tbody').html(html);
                }
            }

            if ($('#errors-search').length > 0 || $('#sync-errors-tbody').length > 0) {
                applyErrorsFiltersAndRender();
            }
        }

        // Attach pagination click and filter event handlers
        $(document).on('click', '.logs-page-prev', function() {
            if (window.currentLogsPage > 1) {
                window.currentLogsPage--;
                applyLogsFiltersAndRender();
            }
        });
        $(document).on('click', '.logs-page-next', function() {
            window.currentLogsPage++;
            applyLogsFiltersAndRender();
        });
        $(document).on('click', '.errors-page-prev', function() {
            if (window.currentErrorsPage > 1) {
                window.currentErrorsPage--;
                applyErrorsFiltersAndRender();
            }
        });
        $(document).on('click', '.errors-page-next', function() {
            window.currentErrorsPage++;
            applyErrorsFiltersAndRender();
        });

        $(document).on('input', '#logs-search', function() {
            window.currentLogsPage = 1;
            applyLogsFiltersAndRender();
        });
        $(document).on('change', '#logs-status-filter', function() {
            window.currentLogsPage = 1;
            applyLogsFiltersAndRender();
        });
        $(document).on('change', '#logs-per-page', function() {
            window.currentLogsPage = 1;
            applyLogsFiltersAndRender();
        });

        $(document).on('input', '#errors-search', function() {
            window.currentErrorsPage = 1;
            applyErrorsFiltersAndRender();
        });
        $(document).on('change', '#errors-per-page', function() {
            window.currentErrorsPage = 1;
            applyErrorsFiltersAndRender();
        });

        // Client-side CSV Exporter Helper
        function downloadCSV(filename, headers, rows) {
            var csv = "\uFEFF"; // BOM for Excel Compatibility
            csv += headers.join(",") + "\r\n";
            
            $.each(rows, function(i, row) {
                var escapedRow = $.map(row, function(val) {
                    var str = (val === null || val === undefined) ? '' : String(val);
                    str = str.replace(/"/g, '""'); // Escape inner double quotes
                    if (str.indexOf(',') !== -1 || str.indexOf('"') !== -1 || str.indexOf('\n') !== -1 || str.indexOf('\r') !== -1) {
                        str = '"' + str + '"';
                    }
                    return str;
                });
                csv += escapedRow.join(",") + "\r\n";
            });
            
            var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            var link = document.createElement("a");
            link.setAttribute("href", window.URL.createObjectURL(blob));
            link.setAttribute("download", filename);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Export Logs CSV Trigger
        $(document).on('click', '#btn-export-logs', function(e) {
            e.preventDefault();
            var searchVal = ($('#logs-search').val() || '').toLowerCase().trim();
            var statusFilter = $('#logs-status-filter').val() || '';
            var logs = window.clicksyncAllLogs || [];
            
            var filtered = $.grep(logs, function(log) {
                var matchesSearch = true;
                var matchesStatus = true;

                if (searchVal) {
                    var event = (log.event || '').toLowerCase();
                    var taskId = (log.clickupTaskId || '').toLowerCase();
                    var details = (log.details || '').toLowerCase();
                    var error = (log.error || '').toLowerCase();
                    matchesSearch = event.indexOf(searchVal) !== -1 || 
                                    taskId.indexOf(searchVal) !== -1 || 
                                    details.indexOf(searchVal) !== -1 || 
                                    error.indexOf(searchVal) !== -1;
                }

                if (statusFilter) {
                    matchesStatus = log.status === statusFilter;
                }

                return matchesSearch && matchesStatus;
            });

            var headers = ["Event & Entity", "Status", "ClickUp Task", "Details", "Error Trace", "Timestamp"];
            var rows = $.map(filtered, function(log) {
                return [[
                    log.event || '',
                    log.status || '',
                    log.clickupTaskId || '',
                    log.details || '',
                    log.error || '',
                    log.createdAt || ''
                ]];
            });

            downloadCSV("clicksync-audit-logs.csv", headers, rows);
        });

        // Export Errors CSV Trigger
        $(document).on('click', '#btn-export-errors', function(e) {
            e.preventDefault();
            var searchVal = ($('#errors-search').val() || '').toLowerCase().trim();
            var logs = window.clicksyncAllLogs || [];
            var errorLogs = $.grep(logs, function(l) { return l.status !== 'Success'; });
            
            var filtered = $.grep(errorLogs, function(log) {
                if (searchVal) {
                    var event = (log.event || '').toLowerCase();
                    var error = (log.error || '').toLowerCase();
                    return event.indexOf(searchVal) !== -1 || error.indexOf(searchVal) !== -1;
                }
                return true;
            });

            var headers = ["Failed Event", "Error Traceback", "Payload JSON", "Timestamp"];
            var rows = $.map(filtered, function(log) {
                return [[
                    log.event || '',
                    log.error || '',
                    log.payload ? JSON.stringify(log.payload) : '{}',
                    log.createdAt || ''
                ]];
            });

            downloadCSV("clicksync-error-traces.csv", headers, rows);
        });


        // Support Form Submission
        $(document).on('submit', '#clicksync-support-contact-form', function(e) {
            e.preventDefault();
            var form = $(this);
            var btn = form.find('button[type="submit"]');
            var originalText = btn.html();
            btn.html('<svg style="width: 16px; height: 16px; fill: none; stroke: currentColor; stroke-width: 3; stroke-linecap: round; animation: spin 1s linear infinite; display: inline-block; vertical-align: middle; margin-right: 8px;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="rgba(255,255,255,0.2)"></circle><path d="M4 12a8 8 0 018-8" fill="none" stroke="currentColor"></path></svg> Sending...').prop('disabled', true);

            $.ajax({
                url: clicksyncData.ajaxUrl,
                type: 'POST',
                data: form.serialize() + '&action=shopmotive_contact_submit&security=' + clicksyncData.nonce,
                success: function(res) {
                    if (res.success) {
                        alert(res.data.message);
                        form[0].reset();
                    } else {
                        alert('Failed to send message: ' + res.data);
                    }
                    btn.html(originalText).prop('disabled', false);
                },
                error: function(xhr) {
                    alert('Error sending message: ' + (xhr.responseJSON?.data || 'Server error.'));
                    btn.html(originalText).prop('disabled', false);
                }
            });
        });

        // Error Center - Retry Event Click Handler
        $(document).on('click', '.clicksync-retry-event-btn', function(e) {
            e.preventDefault();
            var btn = $(this);
            var originalText = btn.text();
            btn.text('Retrying...').prop('disabled', true);

            var topic = btn.attr('data-topic');
            var payload = JSON.parse(btn.attr('data-payload') || '{}');

            $.ajax({
                url: clicksyncData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'shopmotive_widget_force_sync',
                    order_id: payload.id || payload.order_id || 0,
                    customer_id: payload.customer_id || 0,
                    security: clicksyncData.nonce
                },
                success: function(res) {
                    if (res.success) {
                        alert('Event retried and synced successfully!');
                        window.location.reload();
                    } else {
                        alert('Retry failed: ' + res.data);
                        btn.text(originalText).prop('disabled', false);
                    }
                },
                error: function(xhr) {
                    alert('Error retrying event: ' + (xhr.responseJSON?.data || 'Server error.'));
                    btn.text(originalText).prop('disabled', false);
                }
            });
        });

        // ClickSync Sidebar Meta Boxes (Widgets) Controller
        $('.clicksync-widget-wrapper').each(function () {
            var widgetWrapper = $(this);
            var taskId = widgetWrapper.attr('data-task-id');
            var orderId = widgetWrapper.attr('data-order-id') || 0;
            var customerId = widgetWrapper.attr('data-customer-id') || 0;

            if (taskId) {
                // Fetch latest task details dynamically from ClickUp via signed API request
                $.ajax({
                    url: clicksyncData.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'shopmotive_widget_get_task_details',
                        task_id: taskId,
                        security: clicksyncData.nonce
                    },
                    success: function (res) {
                        if (res.success && res.data) {
                            var task = res.data.task;
                            var statuses = res.data.statuses;
                            var priorities = res.data.priorities;
                            var members = res.data.members;

                            // 1. Populate statuses select
                            var statusSelect = widgetWrapper.find('.clicksync-widget-status-select');
                            var statusHtml = '';
                            $.each(statuses, function (i, s) {
                                statusHtml += '<option value="' + s + '">' + s.toUpperCase() + '</option>';
                            });
                            statusSelect.html(statusHtml).val(task.status);

                            // 2. Populate priorities select
                            var prioritySelect = widgetWrapper.find('.clicksync-widget-priority-select');
                            if (prioritySelect.length > 0) {
                                var priorityHtml = '';
                                $.each(priorities, function (i, p) {
                                    priorityHtml += '<option value="' + p.score + '">' + p.name + '</option>';
                                });
                                prioritySelect.html(priorityHtml).val(task.priority);
                            }

                            // 3. Populate assignees list
                            var assigneesList = widgetWrapper.find('.clicksync-widget-assignees-list');
                            if (assigneesList.length > 0) {
                                var assigneesHtml = '';
                                $.each(members, function (i, m) {
                                    var isChecked = task.assignees.indexOf(m.id) !== -1 ? 'checked' : '';
                                    assigneesHtml += '<label style="display: block; margin-bottom: 6px; font-weight: normal; cursor: pointer;">';
                                    assigneesHtml += '<input type="checkbox" class="clicksync-widget-assignee-checkbox" value="' + m.id + '" ' + isChecked + ' /> ';
                                    assigneesHtml += m.username;
                                    assigneesHtml += '</label>';
                                });
                                assigneesList.html(assigneesHtml || 'No members available');
                            }

                            // Attach change listeners to save updates dynamically
                            widgetWrapper.on('change', '.clicksync-widget-status-select, .clicksync-widget-priority-select, .clicksync-widget-assignee-checkbox', function () {
                                widgetWrapper.css({ opacity: 0.6, 'pointer-events': 'none' });

                                var updatedStatus = widgetWrapper.find('.clicksync-widget-status-select').val();
                                var updatedPriority = widgetWrapper.find('.clicksync-widget-priority-select').val() || '';
                                var updatedAssignees = [];
                                widgetWrapper.find('.clicksync-widget-assignee-checkbox:checked').each(function () {
                                    updatedAssignees.push($(this).val());
                                });

                                $.ajax({
                                    url: clicksyncData.ajaxUrl,
                                    type: 'POST',
                                    data: {
                                        action: 'shopmotive_widget_update_task',
                                        task_id: taskId,
                                        order_id: orderId,
                                        customer_id: customerId,
                                        status: updatedStatus,
                                        priority: updatedPriority,
                                        assignees: updatedAssignees,
                                        security: clicksyncData.nonce
                                    },
                                    success: function (updateRes) {
                                        widgetWrapper.css({ opacity: 1, 'pointer-events': 'auto' });
                                    },
                                    error: function (xhr) {
                                        alert('Failed to save changes to ClickUp: ' + (xhr.responseJSON?.data || 'Unknown error'));
                                        widgetWrapper.css({ opacity: 1, 'pointer-events': 'auto' });
                                    }
                                });
                            });

                        } else {
                            widgetWrapper.html('<p style="color: #ef4444; margin: 0; font-size: 11px;">Error loading task details.</p>');
                        }
                    },
                    error: function () {
                        widgetWrapper.html('<p style="color: #ef4444; margin: 0; font-size: 11px;">Failed to fetch task details.</p>');
                    }
                });
            }

            // Sync Now Click Handler
            widgetWrapper.on('click', '.clicksync-manual-sync-btn', function (e) {
                e.preventDefault();
                var btn = $(this);
                var originalHtml = btn.html();
                btn.html('Syncing...').prop('disabled', true);
                $.ajax({
                    url: clicksyncData.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'shopmotive_widget_force_sync',
                        order_id: orderId,
                        customer_id: customerId,
                        security: clicksyncData.nonce
                    },
                    success: function (res) {
                        if (res.success) {
                            alert(res.data.message || 'Successfully synchronized with ClickUp!');
                            window.location.reload();
                        } else {
                            alert('Sync failure: ' + res.data);
                            btn.html(originalHtml).prop('disabled', false);
                        }
                    },
                    error: function (xhr) {
                        alert('Sync error: ' + (xhr.responseJSON?.data || 'Server error occurred during sync.'));
                        btn.html(originalHtml).prop('disabled', false);
                    }
                });
            });
        });

        // Enforce cloud-controlled plan features and toggle locks in the WordPress UI
        function enforcePlanLocks() {
            if (activePlanName === 'None') return;

            // 1. Gating Split Routing: Pro only
            if (activePlanName !== 'Pro Plan') {
                $('#orders-split-routing').prop('checked', false).prop('disabled', true);
                $('#orders-split-routing').closest('.clicksync-switch').css({ opacity: '0.55', 'pointer-events': 'auto', cursor: 'not-allowed' });
                $('#orders-split-routing').closest('label').addClass('clicksync-switch-disabled');
                $('#clicksync-split-routing-lock-tooltip').css('display', 'inline-flex');
            } else {
                $('#orders-split-routing').prop('disabled', false);
                $('#orders-split-routing').closest('.clicksync-switch').css({ opacity: '1', 'pointer-events': 'auto', cursor: '' });
                $('#orders-split-routing').closest('label').removeClass('clicksync-switch-disabled');
                $('#split-routing-lock-notice').remove();
                $('#clicksync-split-routing-lock-tooltip').hide();
            }

            // 2. Gating Sync Refunds: Growth or Pro (requires Growth or Pro)
            if (activePlanName === 'Free Plan') {
                $('#orders-sync-refunds').prop('checked', false).prop('disabled', true);
                $('#orders-sync-refunds').closest('.clicksync-switch').css({ opacity: '0.55', 'pointer-events': 'auto', cursor: 'not-allowed' });
                $('#orders-sync-refunds').closest('label').addClass('clicksync-switch-disabled-refunds');
                $('#clicksync-refunds-lock-tooltip').css('display', 'inline-flex');
            } else {
                $('#orders-sync-refunds').prop('disabled', false);
                $('#orders-sync-refunds').closest('.clicksync-switch').css({ opacity: '1', 'pointer-events': 'auto', cursor: '' });
                $('#orders-sync-refunds').closest('label').removeClass('clicksync-switch-disabled-refunds');
                $('#refunds-lock-notice').remove();
                $('#clicksync-refunds-lock-tooltip').hide();
            }

            // 3. Gating Status Mapping: Always enabled for configuration (runs in unidirectional mode on Free)
            var statusSection = $('.clicksync-add-status-mapping-btn').closest('div[style*="background: #f9fafb"]');
            if (statusSection.length > 0) {
                statusSection.css('opacity', '1');
                statusSection.find('input, select, button').prop('disabled', false);
                $('#status-mapping-lock-notice').remove();

                var headerTitle = statusSection.find('div[style*="font-size: 14px"]').first();
                if (activePlanName === 'Free Plan') {
                    if (headerTitle.find('.clicksync-unidirectional-tooltip').length === 0) {
                        headerTitle.append(
                            '<span class="clicksync-unidirectional-tooltip" tabindex="0" style="display: inline-flex; align-items: center; color: #3b82f6; cursor: help; margin-left: 6px;" data-tooltip="This currently syncs from your store to ClickUp. To enable bidirectional sync you need a paid plan.">' +
                                '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle;"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 9.9-1"></path></svg>' +
                            '</span>'
                        );
                    }
                } else {
                    headerTitle.find('.clicksync-unidirectional-tooltip').remove();
                }
            }

            // Option Pills Locking styling: Removed dynamic prepend overlays to maintain clean layout styling.
            $('.clicksync-option-pill').each(function() {
                var pill = $(this);
                var targetId = pill.data('target');
                pill.find('.clicksync-pill-lock-icon').remove();
                pill.removeClass('clicksync-pill-locked clicksync-pill-unlocked').css({ opacity: '' });

                var isLocked = false;
                if (activePlanName === 'Free Plan') {
                    if (targetId.indexOf('list-routing') !== -1 || targetId.indexOf('assignee') !== -1 || targetId.indexOf('priority') !== -1 || targetId.indexOf('tagging') !== -1 || targetId.indexOf('customfields') !== -1) {
                        isLocked = true;
                    }
                } else if (activePlanName === 'Growth Plan') {
                    if (targetId.indexOf('list-routing') !== -1 || targetId.indexOf('assignee') !== -1 || targetId.indexOf('priority') !== -1 || targetId.indexOf('tagging') !== -1) {
                        isLocked = true;
                    }
                }

                var targetBlock = $('#' + targetId);
                if (isLocked) {
                    pill.addClass('clicksync-pill-locked').removeClass('active');
                    targetBlock.addClass('clicksync-pill-locked-block').hide(); // Collapse on page load
                    targetBlock.find('input, select, button').prop('disabled', true);
                } else {
                    pill.addClass('clicksync-pill-unlocked');
                    targetBlock.removeClass('clicksync-pill-locked-block');
                    targetBlock.find('input, select, button').prop('disabled', false);
                }
            });

             // 4. Render Multi-Store Connections Card
            $('#clicksync-multistore-card').show();
            $('#clicksync-multistore-card').find('.clicksync-multistore-lock-overlay').remove();

            if (activePlanName === 'Pro Plan') {
                $('#clicksync-multistore-lock-tooltip').hide();
                
                var multistoreHtml = '';
                if (resSiblings && resSiblings.length > 0) {
                    $.each(resSiblings, function(i, sib) {
                        var isCurrent = sib.shop === host;
                        var domainLabel = isCurrent ? '<strong>' + sib.shop + ' (This Store)</strong>' : sib.shop;
                        var statusBadge = '<span class="clicksync-badge" style="background: #ecfdf5; color: #047857; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 600;">Active</span>';
                        var roleLabel = isCurrent ? 'Primary Node' : 'Linked Store';
                        
                        multistoreHtml += '<tr style="border-bottom: 1px solid #f1f5f9;">' +
                            '<td style="padding: 10px 12px 10px 0; color: #0f172a;">' + domainLabel + '</td>' +
                            '<td style="padding: 10px 12px;">' + statusBadge + '</td>' +
                            '<td style="padding: 10px 0 10px 12px; text-align: right; color: #64748b; font-weight: 500;">' + roleLabel + '</td>' +
                            '</tr>';
                    });
                } else {
                    multistoreHtml = '<tr><td colspan="3" style="padding: 10px 0; color: #6d7175; font-style: italic;">No sibling connections detected.</td></tr>';
                }
                $('#clicksync-multistore-list').html(multistoreHtml);
            } else {
                $('#clicksync-multistore-lock-tooltip').css('display', 'inline-flex');
                
                var currentStoreHtml = '<tr style="border-bottom: 1px solid #f1f5f9;">' +
                    '<td style="padding: 10px 12px 10px 0; color: #0f172a;"><strong>' + host + ' (This Store)</strong></td>' +
                    '<td style="padding: 10px 12px;"><span class="clicksync-badge" style="background: #ecfdf5; color: #047857; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 600;">Active</span></td>' +
                    '<td style="padding: 10px 0 10px 12px; text-align: right; color: #64748b; font-weight: 500;">Single Store Mode</td>' +
                    '</tr>';
                $('#clicksync-multistore-list').html(currentStoreHtml);
            }
        }

        var resSiblings = [];
        // Inject siblings from fetch config response
        var oldFetchCloudConfig = fetchCloudConfig;
        fetchCloudConfig = function() {
            var oldSuccess = null;
            $.ajaxSetup({
                beforeSend: function(xhr, settings) {
                    if (settings.url.indexOf('/api/get-config') !== -1) {
                        oldSuccess = settings.success;
                        settings.success = function(res) {
                            resSiblings = res.siblings || [];
                            if (oldSuccess) oldSuccess(res);
                        };
                    }
                }
            });
            oldFetchCloudConfig();
            // Restore default ajax setup
            $.ajaxSetup({ beforeSend: null });
        };

        function getListName(id) {
            var option = $('.clicksync-list-routing-list option[value="' + id + '"]');
            if (option.length > 0) {
                return option.text();
            }
            return 'List ID: ' + id;
        }

        // Add regional list routing rule
        $(document).on('click', '.clicksync-add-list-routing-rule-btn', function (e) {
            e.preventDefault();
            var btn = $(this);
            var eventType = btn.data('event');
            var container = btn.closest('.clicksync-card');

            var field = container.find('.clicksync-list-routing-field').val();
            var operator = container.find('.clicksync-list-routing-operator').val();
            var value = container.find('.clicksync-list-routing-value').val();
            var targetList = container.find('.clicksync-list-routing-list').val();

            if (!field || !value || !targetList) {
                alert('Please fill out all rule fields and select a target ClickUp list.');
                return;
            }

            btn.text('Adding...').prop('disabled', true);

            $.ajax({
                url: cloudUrl + '/api/save-config',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    shop: host,
                    actionType: 'add_list_rule',
                    payload: {
                        event: eventType,
                        shopifyPropertyPath: field,
                        operator: operator,
                        value: value,
                        clickupListId: targetList
                    }
                }),
                success: function (res) {
                    container.find('.clicksync-list-routing-value').val('');
                    btn.text('Add Rule').prop('disabled', false);
                    fetchCloudConfig();
                },
                error: function (xhr) {
                    alert('Failed to add list routing rule: ' + xhr.responseText);
                    btn.text('Add Rule').prop('disabled', false);
                }
            });
        });

        // Render premium Polaris-like toast notification drawer
        function showClickSyncToast(title, message, type) {
            type = type || 'warning';
            var toastId = 'cs-toast-' + Date.now();
            
            var borderColor = '#db2777';
            var iconSvg = '<svg width="14" height="14" viewBox="0 0 24 24" fill="#db2777" style="vertical-align: middle;"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>';
            var footerHtml = '<div style="margin-top: 6px; display: flex; gap: 12px; align-items: center;">' +
                                '<a href="#" class="clicksync-open-upgrade-btn" style="font-size: 12px; font-weight: 600; color: #db2777; text-decoration: none; cursor: pointer;">Upgrade subscription &rarr;</a>' +
                             '</div>';
            
            if (type === 'success') {
                borderColor = '#10b981';
                iconSvg = '<svg width="14" height="14" viewBox="0 0 24 24" fill="#10b981" style="vertical-align: middle;"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>';
                footerHtml = '';
            }
            
            var toastHtml = 
                '<div id="' + toastId + '" class="clicksync-toast" style="pointer-events: auto; min-width: 320px; max-width: 400px; background: #ffffff; border: 1px solid #e1e3e5; border-left: 4px solid ' + borderColor + '; border-radius: 6px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); padding: 14px 16px; display: flex; flex-direction: column; gap: 4px; transition: all 0.3s ease; transform: translateY(50px); opacity: 0; margin-top: 10px;">' +
                    '<div style="display: flex; justify-content: space-between; align-items: flex-start;">' +
                        '<span style="font-size: 13px; font-weight: 600; color: #202223; display: flex; align-items: center; gap: 6px;">' +
                            iconSvg +
                            title +
                        '</span>' +
                        '<button type="button" class="clicksync-toast-close" style="background: none; border: none; font-size: 16px; color: #8c9196; cursor: pointer; padding: 0 4px; line-height: 1;">&times;</button>' +
                    '</div>' +
                    '<p style="font-size: 12px; color: #6d7175; margin: 0; line-height: 1.4;">' + message + '</p>' +
                    footerHtml +
                '</div>';
            
            $('#clicksync-toast-container').append(toastHtml);
            
            setTimeout(function() {
                $('#' + toastId).css({ transform: 'translateY(0)', opacity: '1' });
            }, 10);

            setTimeout(function() {
                var toast = $('#' + toastId);
                if (toast.length > 0) {
                    toast.css({ transform: 'translateY(-20px)', opacity: '0' });
                    setTimeout(function() {
                        toast.remove();
                    }, 300);
                }
            }, 6000);
        }

        // Intercept locked toggle switches and billing prompts clicks
        $(document).on('click', '.clicksync-switch-disabled, .clicksync-switch-disabled-refunds', function (e) {
            e.preventDefault();
            e.stopPropagation();
            
            var isRefunds = $(this).hasClass('clicksync-switch-disabled-refunds');
            var featureTitle = isRefunds ? 'Refund Syncing' : 'Split Order Routing';
            var featureMessage = isRefunds 
                ? 'Refund updates syncing is a Growth Plan feature. Upgrade to automatically post detailed refund comments and line items in ClickUp when refunds occur.' 
                : 'Split Order Routing is a Pro Plan feature. Upgrade to automatically split multi-product orders into individual ClickUp tasks.';
            
            showClickSyncToast(featureTitle, featureMessage);
        });

        // Intercept clicks on locked configuration blocks to show the upgrade toast
        $(document).on('click', '.clicksync-pill-locked-block', function (e) {
            e.preventDefault();
            e.stopPropagation();

            var blockId = $(this).attr('id');
            var featureTitle = '';
            var featureMessage = '';

            if (blockId.indexOf('list-routing') !== -1) {
                featureTitle = 'Regional List Routing';
                featureMessage = 'Regional List Routing is a Pro Plan feature. Upgrade your subscription to enable routing rules for different lists.';
            } else if (blockId.indexOf('assignee') !== -1) {
                featureTitle = 'Assignee Routing';
                featureMessage = 'Assignee Routing is a Pro Plan feature. Upgrade your subscription to enable conditional tags and assignees routing rules.';
            } else if (blockId.indexOf('priority') !== -1) {
                featureTitle = 'Priority Rules';
                featureMessage = 'Priority Rules routing is a Pro Plan feature. Upgrade your subscription to enable dynamic priority assignment.';
            } else if (blockId.indexOf('tagging') !== -1) {
                featureTitle = 'Tagging Rules';
                featureMessage = 'Tagging Rules is a Pro Plan feature. Upgrade your subscription to enable tag assignment rules.';
            } else if (blockId.indexOf('customfields') !== -1) {
                featureTitle = 'Custom Fields';
                featureMessage = 'Custom Field Mapping is a Growth Plan feature. Upgrade your subscription to map WooCommerce custom fields to ClickUp fields.';
            }

            if (featureTitle) {
                showClickSyncToast(featureTitle, featureMessage);
            }
        });

        $(document).on('click', '.clicksync-toast-close', function() {
            var toast = $(this).closest('.clicksync-toast');
            toast.css({ transform: 'translateY(-20px)', opacity: '0' });
            setTimeout(function() {
                toast.remove();
            }, 300);
        });

        // Close Custom Quota modal
        $(document).on('click', '#clicksync-close-quota-modal, #clicksync-cancel-quota-modal', function() {
            $('#clicksync-quota-modal').hide();
        });

        // Auto-trigger cancellation modal if query action parameter is present
        var urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('clicksync_action') === 'cancel_subscription') {
            var checkInterval = setInterval(function() {
                if (activePlanName) {
                    clearInterval(checkInterval);
                    if (activePlanName !== 'None') {
                        $('#clicksync-cancel-modal').css('display', 'flex');
                    }
                }
            }, 200);
        }

        // Close Cancel modal
        $(document).on('click', '#clicksync-close-cancel-modal, #clicksync-cancel-keep-btn', function() {
            $('#clicksync-cancel-modal').hide();
        });

        // Submit Cancel Subscription Form to Cloud
        $(document).on('submit', '#clicksync-cancel-subscription-form', function(e) {
            e.preventDefault();
            var submitBtn = $(this).find('button[type="submit"]');
            var reason = $('#clicksync-cancel-reason').val();
            var feedback = $('#clicksync-cancel-feedback').val();

            submitBtn.prop('disabled', true).text('Canceling...');

            $.ajax({
                url: cloudUrl + '/api/save-config',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    shop: host,
                    actionType: 'cancel_subscription',
                    payload: {
                        reason: reason,
                        feedback: feedback
                    }
                }),
                success: function(res) {
                    submitBtn.prop('disabled', false).text('Confirm Cancellation');
                    if (res.success) {
                        $('#clicksync-cancel-modal').hide();
                        fetchCloudConfig();
                        showClickSyncToast('Subscription Canceled', 'Your subscription was successfully canceled.', 'success');
                    } else {
                        alert(res.error || 'Failed to cancel subscription.');
                    }
                },
                error: function() {
                    submitBtn.prop('disabled', false).text('Confirm Cancellation');
                    alert('An error occurred during cancellation. Please try again.');
                }
            });
        });

        // Submit Custom Quota Request Form
        $(document).on('submit', '#clicksync-quota-request-form', function(e) {
            e.preventDefault();
            var form = $(this);
            var submitBtn = form.find('button[type="submit"]');
            var message = $('#clicksync-quota-message').val();

            submitBtn.prop('disabled', true).text('Sending...');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'shopmotive_contact_submit',
                    contact_name: 'Store Manager (' + host + ')',
                    contact_email: $('#clicksync-connection-status-block').data('site-email') || 'billing@' + host,
                    contact_subject: 'Custom Quota Request',
                    contact_message: message
                },
                success: function(res) {
                    submitBtn.prop('disabled', false).text('Submit Request');
                    if (res.success) {
                        $('#clicksync-quota-modal').hide();
                        $('#clicksync-quota-message').val('');
                        showClickSyncToast('Request Submitted', 'Your custom quota request has been sent to our support desk.', 'success');
                    } else {
                        alert(res.data || 'Failed to submit quota request. Please try again.');
                    }
                },
                error: function() {
                    submitBtn.prop('disabled', false).text('Submit Request');
                    alert('An error occurred. Please try again.');
                }
            });
        });

        $(document).on('click', '.clicksync-open-upgrade-btn', function (e) {
            e.preventDefault();
            $('#clicksync-upgrade-drawer').slideDown(200);
            $('html, body').animate({
                scrollTop: $("#clicksync-usage-count").offset().top - 100
            }, 500);
        });

        // Initialize Fetch
        fetchCloudConfig();
    });
})(jQuery);
