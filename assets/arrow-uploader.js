jQuery(document).ready(function ($) {
  // Handle image upload for both arrows
  $(".pagimore-arrow-upload").on("click", function (e) {
    e.preventDefault();
    const target = $(this).data("target");
    const frame = wp.media({
      title: "Select Arrow Icon",
      button: { text: "Use This Image" },
      multiple: false,
    });

    frame.on("select", function () {
      const attachment = frame.state().get("selection").first().toJSON();
      $("#" + target + "-input").val(attachment.url);
      $("#" + target + "-preview")
        .attr("src", attachment.url)
        .show();
      $('.pagimore-arrow-remove[data-target="' + target + '"]').show();
    });

    frame.open();
  });

  // Handle image removal for both arrows
  $(".pagimore-arrow-remove").on("click", function (e) {
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
