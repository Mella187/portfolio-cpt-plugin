jQuery(document).ready(function ($) {

    $('.project-rich-editor').each(function () {
        initRichEditor(this.id);
    });

    function updateFullWidthVisibility() {
        $('#project-content-wrapper .repeater-item').each(function (i) {
            $(this).find('.full-width-field').toggle(i === 0);
        });
    }

    function renumberProjectItems() {
        $('#project-content-wrapper .repeater-item').each(function (i) {
            $(this).find('> .flex-row.space-between h4').first().text('Item ' + (i + 1));
        });
    }

    updateFullWidthVisibility();

    // Drag & drop reorder
    if ($.fn.sortable) {
        $('#project-content-wrapper').sortable({
            handle: '.drag-handle',
            items: '> .repeater-item',
            placeholder: 'repeater-item-placeholder',
            forcePlaceholderSize: true,
            start: function () {
                $('#project-content-wrapper iframe').css('pointer-events', 'none');
            },
            stop: function () {
                $('#project-content-wrapper iframe').css('pointer-events', '');
            },
            update: function () {
                updateFullWidthVisibility();
                renumberProjectItems();
            }
        });
    }

    // Add new item
    $("#add-project-content-item").on("click", function () {
        const wrapper = $("#project-content-wrapper");
        const template = $("#project-content-template")[0].innerHTML;

        const index = window.projectContentCounter;
        window.projectContentCounter++;

        const html = template
            .replace(/__name__/g, `project_content[${index}]`)
            .replace(/__INDEX__/g, index);

        wrapper.append(html);
        initRichEditor('prcontent' + index);
        updateFullWidthVisibility();
        renumberProjectItems();
    });

    // Remove item 
    $(document).on("click", ".btn-remove", function () {
        if ($(this).closest('.image-preview-wrapper').length > 0) {
            var wrapper = $(this).closest('.image-preview-wrapper');
            wrapper.find('input[type="hidden"]').val('');
            wrapper.find('.image-preview').attr('src', '').hide();
            $(this).hide();
        } else {
            var item = $(this).closest(".repeater-item");
            item.find('.project-rich-editor').each(function () {
                destroyRichEditor(this.id);
            });
            item.remove();
            updateFullWidthVisibility();
            renumberProjectItems();
        }
    });


    // Add CTA pair (max 2)
    $(document).on('click', '.btn-add-cta', function () {
        const section = $(this).closest('.cta-section');
        const pairsWrapper = section.find('.cta-pairs-wrapper');
        const currentCount = pairsWrapper.find('.cta-pair').length;
        if (currentCount >= 2) return;

        const repeaterItem = section.closest('.repeater-item');
        const baseName = repeaterItem.find('input[name$="[title]"]').first().attr('name').replace('[title]', '');
        const ctaTemplate = $('#cta-pair-template')[0].innerHTML;
        const html = ctaTemplate.replace(/__ctaname__/g, `${baseName}[ctas][${currentCount}]`);

        pairsWrapper.append(html);

        if (pairsWrapper.find('.cta-pair').length >= 2) {
            $(this).hide();
        } else {
            $(this).text('+ Add second CTA');
        }
    });

    // Remove CTA pair
    $(document).on('click', '.btn-remove-cta', function () {
        const section = $(this).closest('.cta-section');
        const pairsWrapper = section.find('.cta-pairs-wrapper');
        $(this).closest('.cta-pair').remove();

        const remaining = pairsWrapper.find('.cta-pair').length;
        const addBtn = section.find('.btn-add-cta');
        addBtn.text(remaining === 0 ? '+ Add CTA' : '+ Add second CTA').show();

        // re-indexar names
        const baseName = section.closest('.repeater-item').find('input[name$="[title]"]').first().attr('name').replace('[title]', '');
        pairsWrapper.find('.cta-pair').each(function (i) {
            $(this).find('input').each(function () {
                $(this).attr('name', $(this).attr('name').replace(/\[ctas\]\[\d+\]/, `[ctas][${i}]`));
            });
        });
    });

    // Gallery
    function toggleLayoutVisibility(repeaterItem) {
        const count = repeaterItem.find('.gallery-item').length;
        const layoutSelect = repeaterItem.find('select[name*="[layout]"]');
        if (count > 1) {
            layoutSelect.prop('disabled', true).val('stack');
        } else {
            layoutSelect.prop('disabled', false);
        }
    }

    // Inicializar estado de layout en items existentes
    $('#project-content-wrapper .repeater-item').each(function () {
        toggleLayoutVisibility($(this));
    });

    $(document).on('click', '.add-gallery-images', function () {
        const btn = $(this);
        const wrapper = btn.prev('.gallery-wrapper');
        const repeaterItem = btn.closest('.repeater-item');
        const titleInput = repeaterItem.find('input[name$="[title]"]').first();
        const baseName = titleInput.attr('name').replace('[title]', '');

        const frame = wp.media({ title: 'Select Images or Videos', multiple: true });

        frame.on('select', function () {
            frame.state().get('selection').each(function (attachment) {
                const data = attachment.toJSON();
                const url = data.url;
                const media = data.type === 'video'
                    ? `<video src="${url}" style="width:120px; height:80px; object-fit:cover; display:block;" controls muted></video>`
                    : `<img src="${url}" style="width:120px; height:80px; object-fit:cover; display:block;">`;
                wrapper.append(`
                <div class="gallery-item" style="position:relative;">
                    ${media}
                    <input type="hidden" name="${baseName}[gallery][]" value="${url}">
                    <button type="button" class="button gallery-remove-image" style="position:absolute; top:2px; right:2px; padding:0 4px;">✕</button>
                </div>`);
            });
            toggleLayoutVisibility(btn.closest('.repeater-item'));
        });

        frame.open();
    });

    $(document).on('click', '.gallery-remove-image', function () {
        const item = $(this).closest('.repeater-item');
        $(this).closest('.gallery-item').remove();
        toggleLayoutVisibility(item);
    });

    $('form#post').on('submit', function () {
        $('.project-rich-editor').each(function () {
            var editorId = this.id;
            var tmce = typeof tinymce !== 'undefined' ? tinymce.get(editorId) : null;
            if (tmce && tmce.isHidden()) {
            } else if (tmce) {
                tmce.save();
            }
        });
    });

});

function initRichEditor(editorId) {
    if (!editorId || typeof wp === 'undefined' || typeof wp.editor === 'undefined') return;
    if (typeof tinymce !== 'undefined' && tinymce.get(editorId)) {
        tinymce.get(editorId).remove();
    }
    wp.editor.initialize(editorId, {
        tinymce: {
            wpautop: true,
            plugins: 'lists paste',
            toolbar1: 'bold italic | bullist numlist | removeformat',
            valid_elements: '*[*]',
            extended_valid_elements: '*[*]',
            paste_as_text: false,
            paste_webkit_styles: 'none',
            paste_remove_styles_if_webkit: true,
        },
        quicktags: true,
        mediaButtons: false,
    });
}

function destroyRichEditor(editorId) {
    if (!editorId) return;
    if (typeof wp !== 'undefined' && typeof wp.editor !== 'undefined') {
        wp.editor.remove(editorId);
    }
}


jQuery(document).ready(function ($) {
    var endDateInput = $('input[name="end_date"]');
    var checkboxInput = $('input[name="currently_working"]');

    if (endDateInput.length && checkboxInput.length) {
        function toggleInputs() {
            if (endDateInput.val().trim() !== '') {
                checkboxInput.prop('disabled', true);
                checkboxInput.prop('checked', false);
            }
            else if (checkboxInput.is(':checked')) {
                endDateInput.prop('disabled', true);
                endDateInput.val('');
            }
            else {
                endDateInput.prop('disabled', false);
                checkboxInput.prop('disabled', false);
            }
        }

        toggleInputs();
        endDateInput.on('input', toggleInputs);
        checkboxInput.on('change', toggleInputs);
    }
});


// About Image Upload
jQuery(document).ready(function ($) {

    var aboutMediaUploader;

    $('.about-select-image').on('click', function (e) {
        e.preventDefault();

        var button = $(this);
        var wrapper = button.closest('.image-preview-wrapper');
        var imagePreview = wrapper.find('.image-preview');
        var imageField = wrapper.find('#visor_image');
        var removeBtn = wrapper.find('.btn-remove');

        if (aboutMediaUploader) {
            aboutMediaUploader.open();
            return;
        }

        aboutMediaUploader = wp.media({
            title: 'Select Image',
            button: { text: 'Use this image' },
            multiple: false
        });

        aboutMediaUploader.on('select', function () {
            var attachment = aboutMediaUploader.state().get('selection').first().toJSON();
            imageField.val(attachment.url);
            imagePreview.attr('src', attachment.url).show();
            removeBtn.show();
        });

        aboutMediaUploader.open();
    });
});


// About Repeaters
jQuery(document).ready(function ($) {

    // Links
    var linkIndex = $('#about-links-wrapper .repeater-item').length;
    $('#add-about-link').on('click', function () {
        var template = $('#about-link-template').html();
        var newItem = template.replace(/__name__/g, 'about_links[' + linkIndex + ']');
        $('#about-links-wrapper').append(newItem);
        linkIndex++;
    });

    // About Items
    $('#add-about-item').on('click', function () {
        var wrapper = $('#about-items-wrapper');
        var template = $('#about-item-template').html();
        var index = wrapper.find('.repeater-item').length;
        var newItem = template.replace(/__name__/g, 'about_items[' + index + ']');
        wrapper.append(newItem);
    });

    // Languages
    $('#add-language').on('click', function () {
        var wrapper = $('#about-languages-wrapper');
        var template = $('#language-template').html();
        var index = wrapper.find('.repeater-item').length;
        var newItem = template.replace(/__name__/g, 'about_languages[' + index + ']');
        wrapper.append(newItem);
    });

    // Education
    $('#add-education').on('click', function () {
        var wrapper = $('#about-education-wrapper');
        var template = $('#education-template').html();
        var index = wrapper.find('.repeater-item').length;
        var newItem = template.replace(/__name__/g, 'about_education[' + index + ']');
        wrapper.append(newItem);
    });

});


jQuery(document).ready(function ($) {
    // form de alta
    $('#is_main_skill_new').on('change', function () {
        $('#skill_order_field_new').toggle(this.checked);
    });

    // form de edición
    $('#is_main_skill_edit').on('change', function () {
        $('#skill_order_field_edit').toggle(this.checked);
    });
});