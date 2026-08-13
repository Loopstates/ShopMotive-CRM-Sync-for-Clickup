/* ClickSync WordPress Admin JavaScript */
(function($) {
    'use strict';

    $(document).ready(function() {
        var cloudUrl = 'https://clicksync-connect.apps.loopstates.com';
        var host = window.location.hostname;

        // Fetch ClickUp metadata, lists, custom fields, and sync logs from cloud
        function loadClickSyncConfig() {
            $.ajax({
                url: cloudUrl + '/api/get-config?shop=' + encodeURIComponent(host),
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (res && res.lists && res.lists.length > 0) {
                        populateListSelectors(res.lists, res.account);
                    }
                    if (res && res.customFields && res.customFields.length > 0) {
                        populateCustomFields(res.customFields);
                    }
                    if (res && res.logs && res.logs.length > 0) {
                        renderSyncLogs(res.logs);
                    }
                },
                error: function(err) {
                    console.log('ClickSync Cloud Metadata Status: Offline or initializing.');
                }
            });
        }

        function populateListSelectors(lists, account) {
            var optionsHtml = '<option value="">-- Select ClickUp List --</option>';
            $.each(lists, function(i, l) {
                optionsHtml += '<option value="' + l.id + '">' + l.name + '</option>';
            });

            $('#clicksync-list-orders').html(optionsHtml);
            $('#clicksync-list-customers').html(optionsHtml);
            $('#clicksync-list-checkouts').html(optionsHtml);
            $('#clicksync-list-refunds').html(optionsHtml);
        }

        function populateCustomFields(fields) {
            var optionsHtml = '<option value="">-- Select Target ClickUp Custom Field --</option>';
            $.each(fields, function(i, f) {
                optionsHtml += '<option value="' + f.id + '">' + f.name + ' (' + f.type + ')</option>';
            });
            $('.clicksync-field-target').html(optionsHtml);
        }

        function renderSyncLogs(logs) {
            var html = '';
            $.each(logs, function(i, log) {
                var badgeClass = log.status === 'Success' ? 'badge-success' : 'badge-danger';
                var taskLink = log.clickupTaskId ? '<a href="' + log.clickupTaskId + '" target="_blank" style="color: #7c3aed; text-decoration: underline;">View Task →</a>' : '-';
                html += '<tr>' +
                    '<td style="padding: 10px;"><strong>' + (log.event || 'Sync Event') + '</strong></td>' +
                    '<td style="padding: 10px;"><span class="clicksync-badge ' + badgeClass + '">' + log.status + '</span></td>' +
                    '<td style="padding: 10px;">' + taskLink + '</td>' +
                    '<td style="padding: 10px; text-align: right; font-size: 12px; color: #64748b;">' + (log.createdAt || '') + '</td>' +
                    '</tr>';
            });
            $('#clicksync-logs-body').html(html);
        }

        // Add custom field mapping row
        $('#clicksync-add-field-mapping').on('click', function(e) {
            e.preventDefault();
            var rowHtml = '<tr>' +
                '<td style="padding: 10px;"><input type="text" class="clicksync-select" style="margin:0;" placeholder="e.g. customer.phone, note" /></td>' +
                '<td style="padding: 10px;"><select class="clicksync-select clicksync-field-target" style="margin:0;"><option value="">Select ClickUp Field</option></select></td>' +
                '<td style="padding: 10px; text-align: right;"><button type="button" class="clicksync-btn-secondary clicksync-remove-row" style="height: 28px; padding: 4px 8px; font-size: 12px; color: #dc2626;">Remove</button></td>' +
                '</tr>';
            $('#clicksync-field-mappings-body').append(rowHtml);
        });

        // Remove row handler
        $(document).on('click', '.clicksync-remove-row', function() {
            $(this).closest('tr').remove();
        });

        loadClickSyncConfig();
    });

})(jQuery);
