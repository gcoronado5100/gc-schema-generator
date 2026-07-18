/* GC Schema Generator — settings screen media picker. @author Gabriel Coronado */
jQuery(function ($) {
    $(document).on('click', '.gcp-schema-media-pick', function (e) {
        e.preventDefault();
        var $input = $($(this).data('target'));
        var frame = wp.media({
            title: 'Select image',
            button: { text: 'Use this' },
            multiple: false
        });
        frame.on('select', function () {
            var att = frame.state().get('selection').first().toJSON();
            $input.val(att.url).trigger('change'); // store the URL string (matches the string contract)
        });
        frame.open();
    });
    $(document).on('click', '.gcp-schema-media-remove', function (e) {
        e.preventDefault();
        $($(this).data('target')).val('').trigger('change');
    });
});
