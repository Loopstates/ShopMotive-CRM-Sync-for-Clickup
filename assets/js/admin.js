/* ClickSync WordPress Admin JavaScript - Multi-Page Engine */
(function($) {
    'use strict';

    $(document).ready(function() {
        var cloudUrl = 'https://clicksync-connect.apps.loopstates.com';
        var host = window.location.hostname;
        var cachedLogs = [];

        // Option Pills Toggle
        $('.clicksync-pill').on('click', function(e) {
            e.preventDefault();
            var targetId = $(this).data('target');
            $(this).toggleClass('active');
            $('#' + targetId).slideToggle(200);
        });

        // Add Field Mapping Row
        $('#add-orders-field-mapping').on('click', function(e) {
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

        $(document).on('click', '.clicksync-remove-row', function() {
            $(this).closest('tr').remove();
        });

        // Load Config & Logs
        function fetchCloudConfig() {
            $.ajax({
                url: cloudUrl + '/api/get-config?shop=' + encodeURIComponent(host),
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (res && res.lists && res.lists.length > 0) {
                        var optionsHtml = '<option value="">-- Select ClickUp List --</option>';
                        $.each(res.lists, function(i, l) {
                            optionsHtml += '<option value="' + l.id + '">' + l.name + '</option>';
                        });
                        $('#clicksync-list-orders, #clicksync-list-drafts, #clicksync-list-customers, #clicksync-list-checkouts').html(optionsHtml);
                    }
                    if (res && res.customFields && res.customFields.length > 0) {
                        var fieldsHtml = '<option value="">-- Select ClickUp Custom Field --</option>';
                        $.each(res.customFields, function(i, f) {
                            fieldsHtml += '<option value="' + f.id + '">' + f.name + ' (' + f.type + ')</option>';
                        });
                        $('.clicksync-field-target').html(fieldsHtml);
                    }
                    if (res && res.logs) {
                        cachedLogs = res.logs;
                        renderFullLogs(cachedLogs);
                        renderErrorLogs(cachedLogs);
                    }
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
            $.each(logs, function(i, log) {
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

        function renderErrorLogs(logs) {
            if ($('#sync-errors-tbody').length === 0) return;
            var errorLogs = $.grep(logs, function(l) { return l.status === 'Failure'; });
            if (errorLogs.length === 0) {
                $('#sync-errors-tbody').html('<tr><td colSpan="3" style="padding: 30px; text-align: center; color: #64748b;">No error traces recorded. All events are running 100% clean!</td></tr>');
                return;
            }

            var html = '';
            $.each(errorLogs, function(i, log) {
                html += '<tr>' +
                    '<td style="padding: 12px; border-bottom: 1px solid #e2e8f0;"><strong>' + log.event + '</strong></td>' +
                    '<td style="padding: 12px; border-bottom: 1px solid #e2e8f0; color: #b91c1c; font-family: monospace; font-size: 12px;">' + (log.error || 'Rate Limit Timeout') + '</td>' +
                    '<td style="padding: 12px; border-bottom: 1px solid #e2e8f0; text-align: right;"><button type="button" class="button button-small button-primary" style="background: #7c3aed;">Retry Sync</button></td>' +
                    '</tr>';
            });
            $('#sync-errors-tbody').html(html);
        }

        $('#clicksync-refresh-logs').on('click', function() {
            fetchCloudConfig();
        });

        fetchCloudConfig();
    });
})(jQuery);
