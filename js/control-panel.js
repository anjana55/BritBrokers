/*
 * Admin control panel (control_panel.php). Part of the admin bundle (js/admin.min.js), so it does
 * nothing on pages without #cp.
 *
 * Tabs load page fragments into #work_area. The open tab is kept in the address bar (#add, #health ...),
 * so reloading the page or pressing Back keeps your place.
 */
(function ($) {
	"use strict";

	var TABS = {
		dashboard: { url: "stats.php" },
		add: { url: "add_new_property.php" },
		edit: { url: "property_search.php", data: { action: "edit", ru: "edit_property_list.php" } },
		"delete": { url: "property_search.php", data: { action: "delete", ru: "delete_property.php" } },
		health: { url: "server_health.php" }
	};

	/** Screens call this when they finish (saved, deleted, cancelled): back to the dashboard. */
	window.cpDone = function () {
		if (window.history && history.replaceState) {
			history.replaceState(null, "", location.pathname + location.search);
		}
		location.reload();
	};

	$(function () {
		var $cp = $("#cp");
		if (!$cp.length) {
			return;
		}
		var $work = $("#work_area");
		var current = "dashboard";
		var request = null;

		function setActive(tab) {
			current = tab;
			$cp.find("[data-cp-tab]").removeClass("active").filter("[data-cp-tab='" + tab + "']").addClass("active");
		}

		function load(url, data, method) {
			if (request) {
				request.abort();
			}
			$work.addClass("cp-loading").attr("aria-busy", "true");
			request = $.ajax({ url: url, data: data || {}, type: method || "GET", cache: false })
				.done(function (html) {
					// A dialog left open by the previous screen would otherwise leave its dark overlay behind
					$work.find(".modal").modal("hide");
					$(".modal-backdrop").remove();
					$("body").removeClass("modal-open").css("padding-right", "");
					$work.html(html);
					// Bring the top of the panel into view, below the site's fixed menu bar
					var top = $cp.offset().top - ($("#navigation").outerHeight() || 0);
					if (window.scrollY > top) {
						window.scrollTo(0, Math.max(0, top));
					}
				})
				.fail(function (xhr, status) {
					if (status === "abort") {
						return;
					}
					if (xhr.status === 403) {
						location.href = "login.php"; // session timed out
						return;
					}
					$work.html('<div class="alert alert-danger cp-alert">Could not load this screen (' +
						(xhr.status ? "error " + xhr.status : "no connection") + '). <a href="#" data-cp-retry>Try again</a></div>');
					$work.find("[data-cp-retry]").on("click", function (ev) { ev.preventDefault(); load(url, data, method); });
				})
				.always(function () {
					$work.removeClass("cp-loading").removeAttr("aria-busy");
					request = null;
				});
			return request;
		}

		function openTab(tab) {
			if (!TABS.hasOwnProperty(tab)) {
				tab = "dashboard";
			}
			setActive(tab);
			load(TABS[tab].url, TABS[tab].data).done(function () {
				var $h = $work.find(".cp-health");
				if ($h.length) {
					showBadge({ fail: +$h.attr("data-fail"), warn: +$h.attr("data-warn") });
				}
			});
		}

		function tabFromHash() {
			return (location.hash || "").replace(/^#/, "") || "dashboard";
		}

		/** Red badge: things to fix; amber: things to look at. */
		function refreshBadge() {
			$.getJSON("server_health.php", { summary: 1 }).done(showBadge);
		}

		function showBadge(c) {
				var $b = $("#cp_health_badge").removeClass("cp-badge-fail cp-badge-warn");
				if (c.fail > 0) {
					$b.text(c.fail).addClass("cp-badge-fail").attr("title", c.fail + " to fix").prop("hidden", false);
				} else if (c.warn > 0) {
					$b.text(c.warn).addClass("cp-badge-warn").attr("title", c.warn + " to look at").prop("hidden", false);
				} else {
					$b.prop("hidden", true);
				}
		}

		$(window).on("hashchange", function () {
			openTab(tabFromHash());
		});

		// The dashboard is already on the page; open another tab if the address asks for one
		if (tabFromHash() !== "dashboard") {
			openTab(tabFromHash());
		}
		refreshBadge();

		// Edit buttons on the dashboard
		$work.on("click", "[data-cp-edit]", function () {
			if (window.history && history.pushState) {
				history.pushState(null, "", "#edit");
			}
			setActive("edit");
			load("edit_property.php", { pid: $(this).attr("data-cp-edit") }, "POST");
		});

		$work.on("click", "#cp_health_refresh", function () {
			openTab("health");
		});

		$("#btn_logoff").on("click", function () {
			$.ajax({ type: "POST", url: "logoff.php" }).always(function () {
				location.href = "index.php";
			});
		});

		// Search and property forms: a district choice fills the city list
		$work.on("change", "[id^='district']", function () {
			var $city = $("#city");
			$city.prop("disabled", true);
			$.ajax({ url: "ajax_functions.php", data: { tsk: "dbfc", value: $(this).val() } })
				.done(function (options) {
					$city.empty().append(options).prop("disabled", false);
					if ($city.hasClass("selectpicker") && $.fn.selectpicker) {
						$city.selectpicker("refresh");
					}
				})
				.fail(function () {
					$city.prop("disabled", false);
				});
		});

		// Search form: a property ID search ignores the other fields
		$work.on("input change", "[id^='property_id']", function () {
			var byId = $(this).val().length > 0;
			$("#business_type, #property_type, #bedrooms, #bathrooms, #district, #city").prop("disabled", byId);
			var $msg = $("#error_msg");
			if (byId) {
				$msg.removeClass("alert-success").addClass("alert alert-info")
					.text("Searching by property ID: the other fields are ignored. Clear the ID to use them.").show("fast");
			} else {
				$msg.hide();
			}
		});
	});
})(jQuery);
