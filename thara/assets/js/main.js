/**
 * Thara theme scripts.
 */
(function ($) {
	"use strict";

	var $html = $("html");
	var isRtl = $html.attr("dir") === "rtl";
	var isMobile = function () {
		return window.innerWidth < 992;
	};

	/* ---------- Preloader ---------- */
	function hidePreloader() {
		$(".preloader").fadeOut(400, function () {
			$(this).remove();
		});
	}
	$(window).on("load", hidePreloader);
	setTimeout(hidePreloader, 4000); // Safety net if "load" is slow.

	$(function () {
		/* ---------- Side navigation (mobile) ---------- */
		var $sideNav = $(".sideNav");
		var $navOver = $(".navover");

		function openSideNav() {
			$sideNav.addClass("open").attr("aria-hidden", "false");
			$navOver.addClass("open");
			$html.css("overflow", "hidden");
			$(".menuTriger").attr("aria-expanded", "true");
		}

		function closeSideNav() {
			$sideNav.removeClass("open").attr("aria-hidden", "true");
			$navOver.removeClass("open");
			$html.css("overflow", "");
			$(".menuTriger").attr("aria-expanded", "false");
		}

		$(".menuTriger").on("click", function () {
			$sideNav.hasClass("open") ? closeSideNav() : openSideNav();
		});
		$navOver.add(".close1").on("click", closeSideNav);

		// Parent items open their sub menu instead of navigating.
		$sideNav.on("click", ".menu-item-has-children > a, .page_item_has_children > a", function (e) {
			var $li = $(this).parent();
			e.preventDefault();
			$li.children(".dropDown, .children").stop(true).slideToggle();
			$li.siblings().children(".dropDown, .children").slideUp();
		});

		/* ---------- Search overlay ---------- */
		var $search = $(".searchh");

		function openSearch() {
			$search.addClass("open").attr("aria-hidden", "false");
			$html.css("overflow", "hidden");
			setTimeout(function () {
				$search.find("input[name='s']").trigger("focus");
			}, 300);
		}

		function closeSearch() {
			$search.removeClass("open").attr("aria-hidden", "true");
			$html.css("overflow", "");
		}

		$(document).on("click", ".search-toggle, .searchTriger", function (e) {
			e.stopPropagation();
			openSearch();
		});
		$(document).on("keydown", ".search-toggle, .search-close", function (e) {
			if (e.key === "Enter" || e.key === " ") {
				e.preventDefault();
				$(this).hasClass("search-close") ? closeSearch() : openSearch();
			}
		});
		$search.on("click", function (e) {
			if (e.target === this || $(e.target).hasClass("search-close")) {
				closeSearch();
			}
		});
		$(document).on("keydown", function (e) {
			if (e.key === "Escape") {
				closeSearch();
				closeSideNav();
			}
		});

		/* ---------- Sticky navbar ---------- */
		var $nav = $("header .main-nav");
		$(window).on("scroll", function () {
			$nav.toggleClass("scroll", $(window).scrollTop() >= $nav.innerHeight());
		});

		/* ---------- Dropdown background (follow along) ---------- */
		var $dropBg = $(".dropDown-bg");
		var $links = $("#links");
		$links.find("li.drop").on("mouseenter", function () {
			var $li = $(this);
			var $menu = $li.children(".dropDown");
			if (!$menu.length) {
				return;
			}
			var left = $li.position().left;
			$dropBg.addClass("open").css({
				width: $menu.width() + 40,
				height: $menu.innerHeight()
			});
			if (isRtl) {
				$dropBg.css({ right: $links.width() - (left + $li.width() + 55), left: "auto" });
			} else {
				$dropBg.css({ left: left - 25, right: "auto" });
			}
		}).on("mouseleave", function () {
			$dropBg.removeClass("open");
		});

		/* ---------- Hero parallax ---------- */
		var headerImg = document.querySelector("header.home-header > .header-bg");
		if (headerImg && !window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
			$(window).on("scroll", function () {
				headerImg.style.transform = "translateY(" + window.pageYOffset * 0.5 + "px)";
			});
		}

		/* ---------- Counters ---------- */
		var $info = $(".info-section");
		if ($info.length && $.fn.countTo) {
			var counted = false;
			var runCounters = function () {
				if (!counted && $(window).scrollTop() + window.innerHeight > $info.offset().top + 100) {
					counted = true;
					$info.find(".timer").countTo({ speed: 1200 });
				}
			};
			$(window).on("scroll", runCounters);
			runCounters();
		}

		/* ---------- Carousels on mobile ---------- */
		if ($.fn.owlCarousel && isMobile()) {
			$("#properties .projectss").addClass("owl-carousel").owlCarousel({
				rtl: isRtl,
				loop: true,
				margin: 5,
				nav: false,
				dots: true,
				items: 1,
				responsive: { 0: { items: 1 }, 600: { items: 2 }, 767: { items: 3 } }
			});

			$("#news .row").addClass("owl-carousel").css("margin", "0").owlCarousel({
				rtl: isRtl,
				loop: $("#news .row > div").length > 1,
				margin: 5,
				nav: false,
				dots: true,
				items: 1,
				responsive: { 0: { items: 1 }, 767: { items: 2 } }
			});
		}

		/* ---------- Footer accordion (mobile) ---------- */
		var $footBtn = $(".foot-links > button");
		if (isMobile()) {
			$footBtn.attr({ "data-toggle": "collapse", "aria-expanded": "false" });
		}
		$footBtn.on("click", function () {
			if (!isMobile()) {
				return;
			}
			var expanded = $(this).toggleClass("trans").hasClass("trans");
			$(this).attr("aria-expanded", expanded ? "true" : "false");
		});

		/* ---------- Footer fixed background ---------- */
		var $footer = $("footer.site-footer");
		function footerBg() {
			if (!isMobile()) {
				$footer.css("background-position-y", window.innerHeight - ($footer.outerHeight() + 65));
			}
		}
		$(window).on("resize", footerBg);
		footerBg();
	});
})(jQuery);
