jQuery(document).ready(function ($) {

    // Add new item
    $("#add-project-content-item").on("click", function () {
        const wrapper = $("#project-content-wrapper");
        const template = $("#project-content-template").html();

        const index = wrapper.children().length;
        const html = template.replace(/__name__/g, `project_content[${index}]`);

        wrapper.append(html);
    });

    // Remove item
    $(document).on("click", ".remove-project-content-item", function () {
        $(this).closest(".project-content-item").remove();
    });

    // Image selector
    $(document).on("click", ".select-project-image", function (e) {
        e.preventDefault();
        const button = $(this);
        const wrapper = button.closest('.image-preview-wrapper');
        const imageField = wrapper.find(".project-image-field");
        const imagePreview = wrapper.find(".image-preview");
        const removeBtn = wrapper.find(".remove-project-image");

        const frame = wp.media({
            title: "Select Image",
            multiple: false
        });

        frame.on("select", function () {
            const attachment = frame.state().get("selection").first().toJSON();
            imageField.val(attachment.url);
            imagePreview.attr('src', attachment.url).show();
            removeBtn.show();
        });

        frame.open();
    });

    // Remove image
    $(document).on("click", ".remove-project-image", function (e) {
        e.preventDefault();
        const wrapper = $(this).closest('.image-preview-wrapper');
        wrapper.find(".project-image-field").val('');
        wrapper.find(".image-preview").hide();
        $(this).hide();
    });
});


jQuery(document).ready(function ($) {
    var endDateInput = $('input[name="end_date"]');
    var checkboxInput = $('input[name="currently_working"]');

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
});