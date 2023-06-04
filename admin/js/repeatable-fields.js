jQuery(function($) {
    let frame,
    documentTable = $('.wpsd-document-table').first();

    let currentChooseMediaButton = null;
    $(documentTable).find('.wpsd-document-choose-media')
    .on('click', function(event) {
        event.preventDefault();
        currentChooseMediaButton = event.target;

        if (frame) {
            frame.open();
            return;
        }

        frame = wp.media({
            title: 'Select or Upload Media',
            button: {
                text: 'Use this media'
            },
            mutiple: false
        });

        frame.on('select', function() {
            let attachement = frame.state().get('selection').first().toJSON();
            const inputs = $(currentChooseMediaButton).closest('.wpsd-document').find('input');
            inputs.first().val(attachement.title);
            inputs.eq(1).val(attachement.link);
        });

        frame.open();
    });
});

jQuery('.wpsd-document-delete').on('click', function() {
    jQuery(this).closest('.wpsd-document').remove();
});

jQuery('.wpsd-document-add-button').on('click', function() {
    let template = jQuery('.wpsd-document-template').clone(true, true);
    jQuery(template).attr('class', 'wpsd-document');
    jQuery(template).find('input').first().attr('name', 'wpsd-document-title[]');
    jQuery(template).find('input').eq(1).attr('name', 'wpsd-document-link[]');

    jQuery('.wpsd-document-table').children('tbody').append(template);
    return false;
});

jQuery('.wpsd-document-table')
.children('tbody')
.sortable({

});
