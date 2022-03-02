var massifUsability = (function ($) {
    ('use strict');

    $(document).on('rex:ready', function (event, container) {
        initStatusToggle(container);
        initCustomToggles(container);
        initDuplicateTriggers(container);
    });

    function updateDatasetStatus($this, status, callback) {
        $('#rex-js-ajax-loader').addClass('rex-visible');
        var url = $('<textarea/>').html(rex.ajax_url).text();
        $.post(
            url + '&rex-api-call=massif_usability&method=changeStatus',
            {
                data_id: $this.data('id'),
                table: $this.data('table'),
                status: status,
            },
            function (resp) {
                callback(resp);
                $('#rex-js-ajax-loader').removeClass('rex-visible');
            }
        );
    }

    function initStatusToggle(container) {
        // status toggle
        if (container.find('.status-toggle').length) {
            var statusToggle = function () {
                var $this = $(this);

                updateDatasetStatus(
                    $this,
                    $this.data('status'),
                    function (resp) {
                        var $parent = $this.parent();
                        $parent.html(resp.message.element);
                        $parent.children('a:first').click(statusToggle);
                    }
                );
                return false;
            };
            container.find('.status-toggle').click(statusToggle);
        }

        // status select
        if (container.find('.status-select').length) {
            var statusChange = function () {
                var $this = $(this);

                updateDatasetStatus($this, $this.val(), function (resp) {
                    var $parent = $this.parent();
                    $parent.html(resp.message.element);
                    $parent.children('select:first').change(statusChange);
                });
            };
            container.find('.status-select').change(statusChange);
        }
    }

    function duplicateDataset($this, id, callback) {
        $('#rex-js-ajax-loader').addClass('rex-visible');
        var url = $('<textarea/>').html(rex.ajax_url).text();
        $.post(
            url + '&rex-api-call=massif_usability&method=duplicate',
            {
                data_id: id,
                table: $this.data('table'),
            },
            function (resp) {
                callback(resp);
                $('#rex-js-ajax-loader').removeClass('rex-visible');
            }
        );
    }

    function initDuplicateTriggers(container) {
        // initDuplicateTriggers
        if (container.find('.duplicate-trigger').length) {
            var duplicateTrigger = function () {
                var $this = $(this);

                duplicateDataset($this, $this.data('id'), function (resp) {
                    window.location.href = window.location.href;
                });
                return false;
            };
            container.find('.duplicate-trigger').click(duplicateTrigger);
        }
    }

    function updateDatasetCustom($this, callback) {
        $('#rex-js-ajax-loader').addClass('rex-visible');
        var url = $('<textarea/>').html(rex.ajax_url).text();
        $.post(
            url + '&rex-api-call=massif_usability&method=changeCustom',
            {
                data_id: $this.data('id'),
                name: $this.data('name'),
                table: $this.data('table'),
                value: $this.data('value'),
            },
            function (resp) {
                callback(resp);
                $('#rex-js-ajax-loader').removeClass('rex-visible');
            }
        );
    }

    function initCustomToggles(container) {
        let $toggles = container.find('.custom-toggle');
        $toggles.each(function () {
            let $this = $(this);
            var customToggle = function () {
                var $_this = $(this);

                updateDatasetCustom($_this, function (resp) {
                    var $parent = $_this.parent();
                    $parent.html(resp.message.element);
                    console.log(resp.message.element);
                    $parent.children('a:first').click(customToggle);
                });
                return false;
            };
            $this.click(customToggle);
        });
    }
})(jQuery);
