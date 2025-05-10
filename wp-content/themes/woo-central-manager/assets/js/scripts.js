;(($) => {
  // Check if jQuery is loaded
  if (typeof jQuery == "undefined") {
    console.error("jQuery is not loaded.")
    return
  }

  // Check if wcm_ajax is defined
  if (typeof wcm_ajax === "undefined") {
    console.error("wcm_ajax is not defined. Make sure it is properly enqueued.")
    return
  }

  // Toggle sidebar on mobile
  $("#sidebarToggle").on("click", () => {
    $(".wcm-sidebar").toggleClass("active")
    $(".wcm-main-content").toggleClass("active")
  })

  // Toggle submenu
  $(".sidebar-menu .menu-item-has-children > a").on("click", function (e) {
    e.preventDefault()
    $(this).parent().toggleClass("active")
  })

  // Notification read
  $(".notification-item").on("click", function () {
    const notificationId = $(this).data("id")

    $.ajax({
      url: wcm_ajax.ajax_url,
      type: "POST",
      data: {
        action: "wcm_mark_notification_read",
        nonce: wcm_ajax.nonce,
        notification_id: notificationId,
      },
      success: (response) => {
        if (response.success) {
          // Update notification count
          const count = Number.parseInt($("#notificationsDropdown .badge").text())
          $("#notificationsDropdown .badge").text(count - 1)
        }
      },
    })
  })

  // Date range picker initialization
  if ($.fn.daterangepicker) {
    // Check if moment is defined
    if (typeof moment == "undefined") {
      console.error("Moment.js is not loaded. Daterangepicker may not work correctly.")
      return
    }

    $(".wcm-daterange").daterangepicker({
      ranges: {
        "Hôm nay": [moment(), moment()],
        "Hôm qua": [moment().subtract(1, "days"), moment().subtract(1, "days")],
        "7 ngày qua": [moment().subtract(6, "days"), moment()],
        "30 ngày qua": [moment().subtract(29, "days"), moment()],
        "Tháng này": [moment().startOf("month"), moment().endOf("month")],
        "Tháng trước": [moment().subtract(1, "month").startOf("month"), moment().subtract(1, "month").endOf("month")],
      },
      locale: {
        format: "DD/MM/YYYY",
        applyLabel: "Áp dụng",
        cancelLabel: "Hủy",
        customRangeLabel: "Tùy chỉnh",
      },
      alwaysShowCalendars: true,
    })
  }

  // DataTables initialization
  if ($.fn.DataTable) {
    $(".wcm-datatable").DataTable({
      language: {
        url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/Vietnamese.json",
      },
      responsive: true,
    })
  }

  // Select2 initialization
  if ($.fn.select2) {
    $(".wcm-select2").select2({
      theme: "bootstrap-5",
    })
  }
})(jQuery)
