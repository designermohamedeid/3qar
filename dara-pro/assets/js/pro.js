/**
 * Dara Pro: mortgage calculator, property comparison and Google Maps markers.
 * Runs after the theme script (window.Dara holds its shared helpers).
 */
(function () {
	"use strict";

	var Dara = window.Dara;
	if (!Dara || !Dara.$) { return; }
	var doc = document;
	var data = Dara.data, i18n = Dara.i18n, toast = Dara.toast, $ = Dara.$, $$ = Dara.$$;
	var homeHtml = Dara.homeHtml, pinHtml = Dara.pinHtml;

	Dara.googleMap = function (el, lat, lng, zoom) {
		var g = window.google.maps;
		var map = new g.Map(el, { center: { lat: lat, lng: lng }, zoom: zoom, gestureHandling: "cooperative", mapTypeControl: false, streetViewControl: false });
		var info = new g.InfoWindow();
		// HTML markers via OverlayView, so the pins look the same as with Leaflet.
		function HtmlMarker(la, ln, cls, html, title, link) {
			this.pos = new g.LatLng(la, ln);
			this.div = doc.createElement("div");
			this.div.className = cls;
			this.div.innerHTML = html;
			this.div.style.position = "absolute";
			if (title) { this.div.title = title; }
			if (link) {
				var self = this;
				this.div.style.cursor = "pointer";
				this.div.addEventListener("click", function () { info.setContent(link); info.setPosition(self.pos); info.open({ map: map }); });
			}
			this.setMap(map);
		}
		HtmlMarker.prototype = new g.OverlayView();
		HtmlMarker.prototype.onAdd = function () { this.getPanes().overlayMouseTarget.appendChild(this.div); };
		HtmlMarker.prototype.draw = function () {
			var p = this.getProjection().fromLatLngToDivPixel(this.pos);
			this.div.style.left = p.x + "px";
			this.div.style.top = p.y + "px";
		};
		HtmlMarker.prototype.onRemove = function () { this.div.remove(); };
		return {
			addHome: function (la, ln) {
				var m = new HtmlMarker(la, ln, "map-home map-home--g", homeHtml);
				return m;
			},
			addPin: function (la, ln, label, title, link) { return new HtmlMarker(la, ln, "map-pin", pinHtml(label), title, link); },
			fit: function (points) {
				var b = new g.LatLngBounds();
				points.forEach(function (p) { b.extend({ lat: p[0], lng: p[1] }); });
				map.fitBounds(b, 40);
			}
		};
	};

	/* ---------- Mortgage calculator ---------- */
	$$("[data-mortgage]").forEach(function (box) {
		var form = $("form", box);
		var method = box.getAttribute("data-method");
		var cur = ($("[data-currency]", box) || {}).textContent || "";
		var nf;
		try { nf = new Intl.NumberFormat((doc.documentElement.lang || "en") + "-u-nu-latn", { maximumFractionDigits: 0 }); } catch (err) { nf = new Intl.NumberFormat("en", { maximumFractionDigits: 0 }); }
		var fmt = function (n) { return nf.format(Math.round(n)); };
		var out = function (key, text) { $$('[data-out="' + key + '"]', box).forEach(function (o) { o.textContent = text; }); };
		var yearsInput = form.elements.years;
		var yearLabel = function (n) {
			var key = n === 1 ? "one" : n === 2 ? "two" : (n >= 3 && n <= 10 ? "few" : "many");
			return (yearsInput.getAttribute("data-unit-" + key) || "%s").replace("%s", nf.format(n));
		};
		var last = null;
		function calc() {
			var price = Math.max(0, parseFloat(form.elements.price.value) || 0);
			var downPct = Math.min(90, Math.max(0, parseFloat(form.elements.down.value) || 0));
			var years = Math.max(1, parseInt(yearsInput.value, 10) || 1);
			var rate = Math.max(0, parseFloat(form.elements.rate.value) || 0) / 100;
			var down = price * downPct / 100, loan = price - down, n = years * 12, monthly, profit;
			if (method === "flat") {
				profit = loan * rate * years;
				monthly = (loan + profit) / n;
			} else {
				var m = rate / 12;
				monthly = m > 0 ? loan * m / (1 - Math.pow(1 + m, -n)) : loan / n;
				profit = monthly * n - loan;
			}
			profit = Math.max(0, profit);
			var total = down + loan + profit;
			out("down-pct", nf.format(downPct) + "%");
			out("years", yearLabel(years));
			out("monthly", fmt(monthly));
			out("down", fmt(down) + " " + cur);
			out("loan", fmt(loan) + " " + cur);
			out("profit", fmt(profit) + " " + cur);
			out("total", fmt(total) + " " + cur);
			["down", "loan", "profit"].forEach(function (k) {
				var bar = $('[data-bar="' + k + '"]', box);
				if (bar) { bar.style.width = (total > 0 ? (100 * ({ down: down, loan: loan, profit: profit })[k] / total) : 0) + "%"; }
			});
			last = { price: fmt(price) + " " + cur, down: fmt(down) + " " + cur + " (" + nf.format(downPct) + "%)", years: nf.format(years), monthly: fmt(monthly) + " " + cur };
		}
		form.addEventListener("input", calc);
		form.addEventListener("submit", function (e) { e.preventDefault(); });
		calc();

		// "Request a consultation": prefill the page's lead form with the numbers.
		var apply = $("[data-mortgage-apply]", box);
		if (apply) {
			apply.addEventListener("click", function (e) {
				var target = $("#lead-form textarea[name=lead_message]");
				if (!target || !last) { return; }
				e.preventDefault();
				target.value = (box.getAttribute("data-msg") || "").replace(/\{(\w+)\}/g, function (m, k) { return last[k] || ""; });
				var formEl = target.closest("form");
				formEl.scrollIntoView({ behavior: "smooth", block: "center" });
				var nameInput = $("input[name=lead_name]", formEl);
				setTimeout(function () { (nameInput || target).focus({ preventScroll: true }); }, 400);
			});
		}
	});

	/* ---------- Compare (localStorage, max 4) ---------- */
	var CMP_KEY = "dara_compare";
	var CMP_MAX = 4;
	function getCmp() {
		try { return (JSON.parse(localStorage.getItem(CMP_KEY) || "[]") || []).filter(function (x) { return x && x.id; }); } catch (err) { return []; }
	}
	function setCmp(list) {
		try { localStorage.setItem(CMP_KEY, JSON.stringify(list.slice(0, CMP_MAX))); } catch (err) { /* private mode */ }
	}
	function cmpUrl(list) {
		if (!data.compareUrl) { return "#"; }
		var u = new URL(data.compareUrl, location.href);
		u.searchParams.delete("ids");
		var q = u.search ? u.search + "&" : "?";
		return u.origin + u.pathname + q + "ids=" + list.map(function (x) { return Number(x.id); }).join(",") + u.hash;
	}
	var cmpBar;
	function paintCmp() {
		var list = getCmp();
		var ids = list.map(function (x) { return Number(x.id); });
		$$("[data-compare]").forEach(function (b) {
			b.setAttribute("aria-pressed", ids.indexOf(Number(b.getAttribute("data-compare"))) > -1 ? "true" : "false");
		});
		if (!data.compareUrl || $("[data-compare-page]")) { return; }
		if (!cmpBar) {
			cmpBar = doc.createElement("div");
			cmpBar.className = "cmp-bar";
			cmpBar.setAttribute("role", "region");
			cmpBar.setAttribute("aria-label", i18n.cmpTitle || "Compare");
			doc.body.appendChild(cmpBar);
			cmpBar.addEventListener("click", function (e) {
				var rm = e.target.closest("[data-cmp-remove]");
				if (rm) {
					setCmp(getCmp().filter(function (x) { return String(x.id) !== rm.getAttribute("data-cmp-remove"); }));
					paintCmp();
				}
				if (e.target.closest("[data-cmp-clear]")) { setCmp([]); paintCmp(); }
			});
		}
		doc.body.classList.toggle("has-cmp-bar", list.length > 0);
		cmpBar.hidden = !list.length;
		if (!list.length) { cmpBar.innerHTML = ""; return; }
		var esc = function (s) { var d = doc.createElement("div"); d.textContent = s || ""; return d.innerHTML; };
		var items = list.map(function (x) {
			return '<li><span class="cmp-bar__thumb">' + (x.img ? '<img src="' + esc(x.img) + '" alt="" width="44" height="44">' : "") + "</span>" +
				'<span class="cmp-bar__name">' + esc(x.title) + "</span>" +
				'<button type="button" class="cmp-bar__x" data-cmp-remove="' + esc(String(x.id)) + '" aria-label="' + esc((i18n.remove || "Remove") + " " + x.title) + '">&times;</button></li>';
		}).join("");
		var ready = list.length > 1;
		cmpBar.innerHTML =
			'<div class="container cmp-bar__inner">' +
			'<p class="cmp-bar__title"><strong>' + esc(i18n.cmpTitle) + "</strong> <span>" + list.length + " / " + CMP_MAX + "</span></p>" +
			'<ul class="cmp-bar__list">' + items + "</ul>" +
			'<div class="cmp-bar__actions">' +
			(ready ? '<a class="btn btn--primary btn--sm" href="' + esc(cmpUrl(list)) + '">' + esc(i18n.cmpNow) + "</a>" : '<span class="cmp-bar__hint">' + esc(i18n.cmpMin) + "</span>") +
			'<button type="button" class="btn btn--outline btn--sm" data-cmp-clear>' + esc(i18n.cmpClear) + "</button></div></div>";
	}
	doc.addEventListener("click", function (e) {
		var b = e.target.closest("[data-compare]");
		if (!b) { return; }
		e.preventDefault();
		var id = Number(b.getAttribute("data-compare"));
		var list = getCmp();
		var idx = list.map(function (x) { return Number(x.id); }).indexOf(id);
		if (idx > -1) {
			list.splice(idx, 1);
			toast(i18n.cmpRemoved || "Removed");
		} else if (list.length >= CMP_MAX) {
			toast(i18n.cmpMax || "Max 4");
			return;
		} else {
			list.push({ id: id, title: b.getAttribute("data-title") || "", img: b.getAttribute("data-img") || "" });
			toast(i18n.cmpAdded || "Added");
		}
		setCmp(list);
		paintCmp();
	});
	paintCmp();

	// Compare page: open the saved list when the URL has no ids, keep the list in sync on remove.
	var cmpPage = $("[data-compare-page]");
	if (cmpPage) {
		var params = new URLSearchParams(location.search);
		var saved = getCmp();
		if (!params.get("ids") && saved.length) {
			location.replace(cmpUrl(saved));
		}
		cmpPage.addEventListener("click", function (e) {
			var rm = e.target.closest("[data-remove]");
			if (rm) {
				setCmp(getCmp().filter(function (x) { return String(x.id) !== rm.getAttribute("data-remove"); }));
			}
		});
	}

})();
