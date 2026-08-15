/* ClickSync WordPress Admin JavaScript - Multi-Page Engine */
(function ($) {
    'use strict';

    $(document).ready(function () {
        var cloudUrl = typeof clicksyncData !== 'undefined' ? clicksyncData.cloudUrl : 'https://clicksync-connect.apps.loopstates.com';
        var host = typeof clicksyncData !== 'undefined' ? clicksyncData.host : window.location.hostname;
        var cachedLogs = [];

        // Global variables to store ClickUp members and statuses
        var workspacesData = [];
        var woocommerceFields = {
            orders: { default_fields: [], meta_fields: [], order_statuses: [] },
            customers: { default_fields: [], meta_fields: [] }
        };

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

            if (isCurrentlyActive) {
                $(this).removeClass('active');
                $('#' + targetId).slideUp(200);
            } else {
                $(this).addClass('active');
                $('#' + targetId).slideDown(200);
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
            btn.html('<svg style="width: 16px; height: 16px; fill: currentColor; animation: spin 1s linear infinite;" viewBox="0 0 24 24"><path d="M12 4V1L8 5l4 4V6c3.31 0 6 2.69 6 6 0 1.01-.25 1.97-.7 2.8l1.46 1.46C19.54 15.03 20 13.57 20 12c0-4.42-3.58-8-8-8zm-6 8c0-1.01.25-1.97.7-2.8L5.24 7.74C4.46 8.97 4 10.43 4 12c0 4.42 3.58 8 8 8v-3l4 4-4-4v3c-3.31 0-6-2.69-6-6z"/></svg> Saving...').prop('disabled', true);

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
                                            action: 'clicksync_save_local_settings',
                                            refunds_enabled: refundsEnabled,
                                            fulfillment_enabled: fulfillmentEnabled
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
                                                    action: 'clicksync_save_user_mappings',
                                                    mappings: userMappings,
                                                    fallback_clickup_user_id: fallbackCuId
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
                    action: 'clicksync_get_wc_fields',
                    nonce: clicksyncData.nonce
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
            $('#clicksync-orders-rule-card .clicksync-assignee-field, #clicksync-orders-rule-card .clicksync-priority-field, #clicksync-orders-rule-card .clicksync-tag-field, #clicksync-orders-rule-card .clicksync-field-field').html(ordersHtml);

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
                            '<svg class="clicksync-title-icon" style="fill: #7c3aed;" viewBox="0 0 24 24"><path d="M16 7v3h2V7h-2zm-5 0v3h2V7h-2zM4 11v6c0 1.1.9 2 2 2h4v3h2v-3h4c1.1 0 2-.9 2-2v-6H4z"/></svg> ClickSync Connection</h3>' +
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
                            '<svg class="clicksync-title-icon" style="fill: #eab308;" viewBox="0 0 24 24"><path d="M7 2v11h3v9l7-12h-4l4-8z"/></svg> ClickSync Connection</h3>' +
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
                            '<svg class="clicksync-title-icon" style="fill: #eab308;" viewBox="0 0 24 24"><path d="M7 2v11h3v9l7-12h-4l4-8z"/></svg> ClickSync Connection</h3>' +
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
                        '<span class="clicksync-badge badge-info" style="background: #f3e8ff; color: #7c3aed; border-color: #d8b4fe; padding: 4px 10px; font-weight: 600; font-size: 11px;">' + clickupPlanStr + '</span>' +
                        '</div>' +
                        '</div>' +
                        '<p style="font-size: 13px; color: #64748b; margin-bottom: 16px; line-height: 1.5;">ClickSync is active. Background WooCommerce events are intercepted and synchronized into ClickUp instantly.</p>' +
                        '<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; font-size: 13px; color: #475569; margin-bottom: 16px; line-height: 1.5;">' +
                        '<svg class="clicksync-title-icon" style="width: 16px; height: 16px; fill: #64748b;" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg> Your integration is subject to <strong>ClickUp\'s plan limits (100 API calls/min)</strong>. If a synchronization fails or experiences delays under heavy load, it is due to ClickUp\'s API rate limits rejecting incoming calls, not our app. ClickSync automatically queues and retries these requests for you.' +
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
                    $('#plan-card-growth, #plan-card-pro').css({ border: '1px solid #cbd5e1', background: '#ffffff' });
                    $('#plan-card-growth a').text('Select Growth').removeClass('clicksync-btn-disabled').css('pointer-events', 'auto');
                    $('#plan-card-pro a').text('Select Pro').removeClass('clicksync-btn-disabled').css('pointer-events', 'auto');

                    // Update Plan details & Lock notices
                    if (res && res.account) {
                        var plan = res.account.planName || 'None';
                        var syncCount = res.account.monthlySyncCount || 0;
                        var quota = res.account.monthlyQuota || 100;
                        $('#clicksync-usage-count').text(syncCount);
                        $('#clicksync-usage-quota').text(quota);
                        
                        // Hide all main billing bar action buttons by default
                        $('#clicksync-activate-free-btn, #clicksync-toggle-upgrade-btn, #clicksync-upgrade-to-pro-btn, #clicksync-custom-quota-btn').hide();
                        $('#clicksync-upgrade-drawer').hide();

                        if (plan === 'None') {
                            $('.badge-info').text('No Plan Selected').css({ background: '#fee2e2', color: '#b91c1c', border: '1px solid #fca5a5' });
                            $('#clicksync-activate-free-btn').show();
                            lockSyncRules();
                        } else if (plan === 'Free Plan') {
                            $('.badge-info').text('Free').css({ background: '#ecfdf5', color: '#047857', border: '1px solid #a7f3d0' });
                            $('#clicksync-toggle-upgrade-btn').show();
                            unlockSyncRules();
                        } else if (plan === 'Growth Plan') {
                            $('.badge-info').text('Paid').css({ background: '#f3e8ff', color: '#7c3aed', border: '1px solid #d8b4fe' });
                            $('#clicksync-upgrade-to-pro-btn').show();
                            
                            // Highlight inside drawer just in case
                            $('#plan-card-growth').css({ border: '2px solid #7c3aed', background: '#f5f3ff' }).find('.plan-active-badge').show();
                            $('#plan-card-growth a').text('Active Plan').addClass('clicksync-btn-disabled').css('pointer-events', 'none');
                            unlockSyncRules();
                        } else if (plan === 'Pro Plan') {
                            $('.badge-info').text('Paid').css({ background: '#f3e8ff', color: '#7c3aed', border: '1px solid #d8b4fe' });
                            
                            // Setup mailto dynamic parameters for custom request
                            var mailtoLink = 'mailto:support@loopstates.com?subject=ClickSync%20Custom%20Quota%20Request%20-%20' + encodeURIComponent(host) + '&body=Hi%20ClickSync%20Team%2C%0A%0AI%20would%20like%20to%20request%20a%20custom%20quota%20for%20my%20store%20' + encodeURIComponent(host) + '.';
                            $('#clicksync-custom-quota-btn').attr('href', mailtoLink).show();

                            // Highlight inside drawer just in case
                            $('#plan-card-pro').css({ border: '2px solid #7c3aed', background: '#f5f3ff' }).find('.plan-active-badge').show();
                            $('#plan-card-pro a').text('Active Plan').addClass('clicksync-btn-disabled').css('pointer-events', 'none');
                            unlockSyncRules();
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
                        $('#clicksync-list-orders, #clicksync-list-customers').html(optionsHtml);
                    } else {
                        $('#clicksync-list-orders, #clicksync-list-customers').html('<option value="">No lists available</option>');
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
                },
                error: function (err) {
                    if (configRetryCount < 3) {
                        configRetryCount++;
                        console.warn("ClickSync Cloud connection failed. Retrying (" + configRetryCount + "/3) in 2 seconds...");
                        setTimeout(fetchCloudConfig, 2000);
                        return;
                    }
                    console.error("Failed to connect to ClickSync Connect Cloud Service:", err);
                    var statusHtml = '<div class="clicksync-card" style="background: #fff5f5; border: 1px solid #fed7d7; border-radius: 8px; padding: 20px; margin-bottom: 20px;">' +
                        '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">' +
                        '<h3 style="margin: 0; font-size: 16px; font-weight: 600; color: #c53030; display: flex; align-items: center; gap: 8px;">' +
                        '<svg class="clicksync-title-icon" style="fill: #c53030;" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg> Connection Error</h3>' +
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

        function renderFullLogs(logs) {
            if ($('#full-sync-logs-tbody').length === 0) return;
            if (!logs || logs.length === 0) {
                $('#full-sync-logs-tbody').html('<tr><td colSpan="4" style="padding: 30px; text-align: center; color: #64748b;">No recent sync logs recorded yet.</td></tr>');
                return;
            }

            var html = '';
            $.each(logs, function (i, log) {
                var badgeStyle = log.status === 'Success' ? 'background: #dcfce7; color: #15803d;' : 'background: #fee2e2; color: #b91c1c;';
                var taskLink = log.clickupTaskId ? '<a href="' + log.clickupTaskId + '" target="_blank" style="color: #7c3aed; font-weight: 600; text-decoration: underline;">View ClickUp Task</a>' : '-';
                html += '<tr>' +
                    '<td style="padding: 12px; border-bottom: 1px solid #e2e8f0;"><strong>' + (log.event || 'Sync Event') + '</strong></td>' +
                    '<td style="padding: 12px; border-bottom: 1px solid #e2e8f0;"><span class="clicksync-badge" style="' + badgeStyle + ' padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600;">' + log.status + '</span></td>' +
                    '<td style="padding: 12px; border-bottom: 1px solid #e2e8f0;">' + taskLink + '</td>' +
                    '<td style="padding: 12px; border-bottom: 1px solid #e2e8f0; text-align: right; font-size: 12px; color: #64748b;">' + (log.createdAt || '') + '</td>' +
                    '</tr>';
            });
            $('#full-sync-logs-tbody').html(html);
        }

        function renderErrorLogs(logs) {
            if ($('#error-sync-logs-tbody').length === 0) return;
            var errorLogs = $.grep(logs, function (l) { return l.status !== 'Success'; });
            if (errorLogs.length === 0) {
                $('#error-sync-logs-tbody').html('<tr><td colSpan="3" style="padding: 30px; text-align: center; color: #64748b;">No error logs recorded. Great job!</td></tr>');
                return;
            }

            var html = '';
            $.each(errorLogs, function (i, log) {
                html += '<tr>' +
                    '<td style="padding: 12px; border-bottom: 1px solid #e2e8f0;"><strong>' + (log.event || 'Sync Event') + '</strong></td>' +
                    '<td style="padding: 12px; border-bottom: 1px solid #e2e8f0; color: #ef4444;">' + (log.error || 'Unknown Error') + '</td>' +
                    '<td style="padding: 12px; border-bottom: 1px solid #e2e8f0; text-align: right; font-size: 12px; color: #64748b;">' + (log.createdAt || '') + '</td>' +
                    '</tr>';
            });
            $('#error-sync-logs-tbody').html(html);
        }

        // ClickSync Sidebar Meta Boxes (Widgets) Controller
        var widgetWrapper = $('.clicksync-widget-wrapper');
        if (widgetWrapper.length > 0) {
            var taskId = widgetWrapper.attr('data-task-id');
            var orderId = widgetWrapper.attr('data-order-id') || 0;
            var customerId = widgetWrapper.attr('data-customer-id') || 0;

            if (taskId) {
                // Fetch latest task details dynamically from ClickUp via signed API request
                $.ajax({
                    url: clicksyncData.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'clicksync_widget_get_task_details',
                        task_id: taskId
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
                                        action: 'clicksync_widget_update_task',
                                        task_id: taskId,
                                        order_id: orderId,
                                        customer_id: customerId,
                                        status: updatedStatus,
                                        priority: updatedPriority,
                                        assignees: updatedAssignees
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
                        action: 'clicksync_widget_force_sync',
                        order_id: orderId,
                        customer_id: customerId
                    },
                    success: function (res) {
                        if (res.success) {
                            alert('Successfully synchronized with ClickUp!');
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
        }

        // Initialize Fetch
        fetchCloudConfig();
    });
})(jQuery);
