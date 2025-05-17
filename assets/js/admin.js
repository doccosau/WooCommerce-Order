/**
 * WooCenter Admin JavaScript
 */
;(($) => {
  // Khi tài liệu đã sẵn sàng
  $(document).ready(() => {
    // Xử lý kiểm tra kết nối API
    $(".woocenter-test-connection").on("click", function (e) {
      e.preventDefault()

      var $button = $(this)
      var originalText = $button.text()

      $button.text("Đang kiểm tra...").prop("disabled", true)

      var data = {
        action: "woocenter_test_connection",
        site_id: $button.data("site-id"),
        nonce: woocenterAdmin.nonce,
      }

      $.post(woocenterAdmin.ajaxUrl, data, (response) => {
        if (response.success) {
          alert("Kết nối thành công!")
        } else {
          alert("Lỗi kết nối: " + response.data.message)
        }

        $button.text(originalText).prop("disabled", false)
      }).fail(() => {
        alert("Đã xảy ra lỗi khi kiểm tra kết nối.")
        $button.text(originalText).prop("disabled", false)
      })
    })

    // Xử lý đồng bộ dữ liệu
    $(".woocenter-sync-now").on("click", function (e) {
      e.preventDefault()

      var $button = $(this)
      var originalText = $button.text()

      $button.text("Đang đồng bộ...").prop("disabled", true)

      var data = {
        action: "woocenter_sync_data",
        site_id: $button.data("site-id"),
        sync_type: $button.data("sync-type"),
        nonce: woocenterAdmin.nonce,
      }

      $.post(woocenterAdmin.ajaxUrl, data, (response) => {
        if (response.success) {
          alert("Đồng bộ thành công!")

          // Tải lại trang để hiển thị kết quả mới nhất
          window.location.reload()
        } else {
          alert("Lỗi đồng bộ: " + response.data.message)
        }

        $button.text(originalText).prop("disabled", false)
      }).fail(() => {
        alert("Đã xảy ra lỗi khi đồng bộ dữ liệu.")
        $button.text(originalText).prop("disabled", false)
      })
    })

    // Xác nhận xóa
    $(".woocenter-delete-confirm").on("click", (e) => {
      if (!confirm("Bạn có chắc chắn muốn xóa mục này?")) {
        e.preventDefault()
      }
    })
  })
})(jQuery)
