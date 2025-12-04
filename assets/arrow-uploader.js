jQuery(document).ready(function ($) {
  // Common upload handler for BOTH arrow & preloader buttons
  $(".pagimore-arrow-upload, .pagimore-gif-upload").on("click", function (e) {
    e.preventDefault();

    const target = $(this).data("target");
    const frame = wp.media({
      title: "Select Image",
      button: { text: "Use This Image" },
      multiple: false,
    });

    frame.on("select", function () {
      const attachment = frame.state().get("selection").first().toJSON();
      $("#" + target + "-input").val(attachment.url);
      $("#" + target + "-preview")
        .attr("src", attachment.url)
        .show();

      $(
        '.pagimore-arrow-remove[data-target="' +
          target +
          '"], .pagimore-gif-remove[data-target="' +
          target +
          '"]'
      ).show();
    });

    frame.open();
  });

  // Common remove handler
  $(".pagimore-arrow-remove, .pagimore-gif-remove").on("click", function (e) {
    e.preventDefault();

    const target = $(this).data("target");
    $("#" + target + "-input").val("");
    $("#" + target + "-preview")
      .attr("src", "")
      .hide();

    $(this).hide();
  });

  $(".pagi-color-picker").wpColorPicker();
});
