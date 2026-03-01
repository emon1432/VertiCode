(function (window, $) {
    'use strict';

    if (!window || !$ || !$.fn || !$.fn.select2) {
        return;
    }

    if (!window.__select2CommonFocusBound) {
        $(document).on('select2:open', function () {
            var searchField = document.querySelector('.select2-search__field');
            if (searchField) {
                searchField.focus();
            }
        });
        window.__select2CommonFocusBound = true;
    }

    window.initSelect2AjaxOptions = function (selector, options) {
        var config = options || {};
        var endpoint = config.endpoint || '/ajax/select2-options';

        $(selector).select2({
            width: config.width || '100%',
            allowClear: config.allowClear !== false,
            placeholder: config.placeholder || 'Search and select...',
            dropdownParent: config.dropdownParent || undefined,
            ajax: {
                url: endpoint,
                dataType: 'json',
                delay: typeof config.delay === 'number' ? config.delay : 250,
                data: function (params) {
                    var dynamicType = typeof config.type === 'function'
                        ? config.type.call(this, params)
                        : (config.type || $(this).data('type'));

                    var payload = {
                        q: params.term || '',
                        type: dynamicType,
                        page: params.page || 1
                    };

                    if (typeof config.data === 'function') {
                        payload = Object.assign(payload, config.data.call(this, params) || {});
                    }

                    return payload;
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;

                    return {
                        results: data.results || [],
                        pagination: {
                            more: !!(data.pagination && data.pagination.more)
                        }
                    };
                },
                cache: true
            },
            minimumInputLength: typeof config.minimumInputLength === 'number' ? config.minimumInputLength : 0
        });
    };
})(window, window.jQuery);
