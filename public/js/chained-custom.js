(function ($, window, document, undefined) {
  "use strict";

  $.fn.chained = function (parentSelector) {
    return this.each(function () {
      var child = this;
      var backup = $(child).clone();

      $(parentSelector).each(function () {
        $(this).on("change", updateChildren);

        if (!$("option:selected", this).length) {
          $("option", this).first().attr("selected", "selected");
        }

        updateChildren();
      });

      function updateChildren() {
        var triggerChange = true;
        var currentlySelectedValue = $("option:selected", child).data("parent");

        $(child).html(backup.html());

        var selected = "";
        $(parentSelector).each(function () {
          var selectedValue = $("option:selected", this).data("parent");
          if (selectedValue) {
            if (selected.length > 0) {
              selected += "+";
            }
            selected += selectedValue;
          }
        });

        var first = $.isArray(parentSelector)
          ? $(parentSelector[0]).first()
          : $(parentSelector).first();
        var selectedFirst = $("option:selected", first).data("parent");

        $("option", child).each(function () {
          if ($(this).data("child") === "") {
            return;
          }
          var matches = String($(this).data("child")).split(" ");
          if (
            matches.indexOf(selected) > -1 ||
            matches.indexOf(selectedFirst) > -1
          ) {
            if ($(this).val() === currentlySelectedValue) {
              $(this).prop("selected", true);
              triggerChange = false;
            }
          } else {
            $(this).remove();
          }
        });

        if ($("option", child).length === 1 && $(child).val() === "") {
          $(child).prop("disabled", true);
        } else {
          $(child).prop("disabled", false);
        }
        if (triggerChange) {
          $(child).trigger("change");
        }
      }
    });
  };

  $.fn.chainedTo = $.fn.chained;
  $.fn.chained.defaults = {};
})(window.jQuery || window.Zepto, window, document);

(function ($) {
  // Inisialisasi chained untuk elemen select saat dokumen siap
  $(document).ready(function () {
    $("#cp_city").chained("#cp_province");

    // Populate the selects and re-initialize chained plugin
    populateSelect("cp_province", "stateData");
    populateSelect("cp_city", "cityData");

    // Re-initialize chained plugin after populating selects
    $("#cp_city").chained("#cp_province");
  });
})(jQuery);
