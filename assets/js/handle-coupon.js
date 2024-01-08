(function ($) {
  $(document.body).on("updated_checkout", function () {
    $(".woocommerce-remove-coupon").on("click", function () {
      const coupon = $(this).data("coupon");
      $('input[value="' + coupon + '"]').prop("checked", false);
      //console.log('coupon', coupon);
    });

    // 綠界加字
    const text = '<br><span style="color:red;">⚠ 請務必選擇取貨門市<span>';
    $('label[for^="shipping_method_0_wooecpay"]').after(text);
  });
  $(document).ready(function () {
    let old_coupon = {
      required_reward_coupon: "",
      normal_coupon: "",
    };
    $(".woocommerce-remove-coupon").each(function () {
      $('input[value="' + $(this).data("coupon") + '"]').prop("checked", true);
      const type = $('input[value="' + $(this).data("coupon") + '"]').data("type");
      old_coupon[type] = $(this).data("coupon");
    });
    //console.log('old_coupon', old_coupon);

    $(".required_reward_coupon, .normal_coupon").on("change", function () {
      console.log("origin", old_coupon[$(this).data("type")]);
      console.log("change to", $(this).val());
      yf_handle_coupon(old_coupon[$(this).data("type")], $(this).val());
      old_coupon[$(this).data("type")] = $(this).val();
    });

    function yf_apply_coupon(newCoupon) {
      const newData = {
        security: wc_checkout_params.apply_coupon_nonce,
        coupon_code: newCoupon,
      };
      //console.log('coupon_code', newData);
      $.ajax({
        type: "POST",
        url: wc_checkout_params.wc_ajax_url.toString().replace("%%endpoint%%", "apply_coupon"),
        data: newData,
        success: function (code) {
          $(".woocommerce-error, .woocommerce-message").remove();

          if (code) {
            $("form.checkout_coupon").before(code);
            $("form.checkout_coupon").slideUp();

            $(document.body).trigger("update_checkout", {
              update_shipping_method: false,
            });
            $(document.body).trigger("applied_coupon_in_checkout", [newData.coupon_code]);
          }
        },
        dataType: "html",
      });
    }

    function yf_handle_coupon(oldCoupon, newCoupon) {
      if (oldCoupon === "") {
        yf_apply_coupon(newCoupon);
      } else {
        const oldData = {
          security: wc_checkout_params.remove_coupon_nonce,
          coupon: oldCoupon,
        };

        $.ajax({
          type: "POST",
          url: wc_checkout_params.wc_ajax_url.toString().replace("%%endpoint%%", "remove_coupon"),
          data: oldData,
          success: function (code) {
            $(".woocommerce-error, .woocommerce-message").remove();

            if (code) {
              $("form.woocommerce-checkout").before(code);

              $(document.body).trigger("removed_coupon_in_checkout", [oldData.coupon]);
              $(document.body).trigger("update_checkout", {
                update_shipping_method: false,
              });

              // Remove coupon code from coupon field
              $("form.checkout_coupon").find('input[name="coupon_code"]').val("");
              setTimeout(() => {
                yf_apply_coupon(newCoupon);
              }, 500);
            }
          },
          error: function (jqXHR) {
            if (wc_checkout_params.debug_mode) {
              /* jshint devel: true */
              console.log(jqXHR.responseText);
            }
          },
          dataType: "html",
        });
      }
    }
  });
})(jQuery);
