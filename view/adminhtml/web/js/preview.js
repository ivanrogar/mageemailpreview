require(
    [
        'jquery',
        'Magento_Ui/js/modal/modal'
    ],
    function ($, modal) {
        'use strict';

        var popupOptions = {
            type: 'popup',
            responsive: true,
            innerScroll: true,
            title: 'Email preview',
            buttons: [],
            clickableOverlay: false
        };

        var select = $('#johnrogar-email-preview-template');

        var previewUrl = $(select).attr('data-preview-url');

        $('#johnrogar_email_preview').on('click', function () {
            $("#johnrogar-email-preview-template-parent").modal(popupOptions).modal('openModal');
        });

        $('.johnrogar-email-preview-template-show').on('click', function () {
            var selected = $('#johnrogar-email-preview-template option:selected').attr('value');

            if (selected !== '' && selected !== undefined) {
                var url = previewUrl + '&template_id=' + selected;

                $.get(url)
                    .done(function( data ) {
                        $('.johnrogar-email-preview-content').html(data);
                    });
            }
        });

        $('.johnrogar-email-preview-template-pdf').on('click', function () {
            var selected = $('#johnrogar-email-preview-template option:selected').attr('value');

            if (selected !== '' && selected !== undefined) {
                var url = previewUrl + '&template_id=' + selected + '&convert_to_pdf=1';
                window.open(url,'_blank');
            }
        });
    }
);
