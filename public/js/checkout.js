(function ($) {
	var block = function ($node) {
		if (!is_blocked($node)) {
			$node.addClass("processing").block({
				message: null,
				overlayCSS: {
					background: "#fff",
					opacity: 0.6,
				},
			});
		}
	};
	var is_blocked = function ($node) {
		return $node.is(".processing") || $node.parents(".processing").length;
	};
	var unblock = function ($node) {
		$node.removeClass("processing").unblock();
	};

	$(document).ready(function () {
		$(".wps_wpr_checkout_points_class").remove();
		$("body").on("click", "#pmp-crp-apply-point", function () {
			block($(".woocommerce-cart-form"));
			block($(".woocommerce-checkout"));

			$.ajax({
				url: pmp_crp.ajax_url,
				type: "POST",
				data: {
					action: "pmp_crp_apply_point",
					nonce: pmp_crp.nonce,
				},
				complete: function () {
					unblock($(".woocommerce-cart-form"));
					unblock($(".woocommerce-cart-form"));

					setTimeout(function () {
						location.reload();
					}, 800);
				},
			});
		});
	});
})(jQuery);
