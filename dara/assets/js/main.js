/**
 * Dara theme – vanilla JS, no dependencies.
 * Leaflet is fetched on demand only when a map becomes visible.
 */
(function () {
	"use strict";

	var data = window.daraData || {};
	var i18n = data.i18n || {};
	var doc = document;
	var $ = function (sel, ctx) { return (ctx || doc).querySelector(sel); };
	var $$ = function (sel, ctx) { return Array.prototype.slice.call((ctx || doc).querySelectorAll(sel)); };
	var isRtl = doc.documentElement.dir === "rtl";

	/* ---------- Toast ---------- */
	var toastEl;
	function toast(msg) {
		if (!toastEl) {
			toastEl = doc.createElement("div");
			toastEl.className = "toast";
			toastEl.setAttribute("role", "status");
			doc.body.appendChild(toastEl);
		}
		toastEl.textContent = msg;
		toastEl.classList.add("is-visible");
		clearTimeout(toastEl._t);
		toastEl._t = setTimeout(function () { toastEl.classList.remove("is-visible"); }, 2200);
	}

	/* ---------- Header on scroll ---------- */
	var header = $("#site-header");
	if (header) {
		var ticking = false;
		var onScroll = function () {
			header.classList.toggle("is-scrolled", window.scrollY > 24);
			ticking = false;
		};
		window.addEventListener("scroll", function () {
			if (!ticking) { ticking = true; requestAnimationFrame(onScroll); }
		}, { passive: true });
		onScroll();
	}

	/* ---------- Drawer (mobile menu) ---------- */
	var drawer = $("#drawer");
	var menuBtn = $(".menu-toggle");
	function setDrawer(open) {
		if (!drawer) { return; }
		if (open) {
			drawer.hidden = false;
			requestAnimationFrame(function () { drawer.classList.add("is-open"); });
			doc.body.classList.add("no-scroll");
			var first = $("a, button", drawer);
			if (first) { first.focus(); }
		} else {
			drawer.classList.remove("is-open");
			doc.body.classList.remove("no-scroll");
			setTimeout(function () { drawer.hidden = true; }, 250);
			if (menuBtn) { menuBtn.focus(); }
		}
		if (menuBtn) { menuBtn.setAttribute("aria-expanded", open ? "true" : "false"); }
	}
	if (menuBtn) { menuBtn.addEventListener("click", function () { setDrawer(true); }); }
	if (drawer) {
		drawer.addEventListener("click", function (e) {
			if (e.target === drawer || e.target.closest(".drawer__close")) { setDrawer(false); }
		});
	}

	// Sub menu toggles (drawer + keyboard on desktop).
	doc.addEventListener("click", function (e) {
		var t = e.target.closest(".sub-toggle");
		if (!t) { return; }
		var li = t.parentNode;
		var open = !li.classList.contains("is-open");
		li.classList.toggle("is-open", open);
		t.setAttribute("aria-expanded", open ? "true" : "false");
	});

	doc.addEventListener("keydown", function (e) {
		if (e.key !== "Escape") { return; }
		if (drawer && !drawer.hidden) { setDrawer(false); }
		var lb = $(".lightbox[open]");
		if (lb) { lb.close(); }
		doc.body.classList.remove("filters-open");
	});

	/* ---------- Hero search tabs ---------- */
	$$("[data-search]").forEach(function (box) {
		var form = $("form", box);
		var purpose = $("input[name=purpose]", form);
		$$("[role=tab]", box).forEach(function (tab) {
			tab.addEventListener("click", function () {
				$$("[role=tab]", box).forEach(function (t) { t.setAttribute("aria-selected", t === tab ? "true" : "false"); });
				var p = tab.getAttribute("data-purpose");
				var projects = p === "projects";
				form.action = projects ? tab.getAttribute("data-action") : form.getAttribute("data-action");
				purpose.disabled = projects;
				purpose.value = projects ? "" : p;
				$$("[data-hide-projects]", form).forEach(function (el) {
					var only = el.getAttribute("data-purpose-only");
					var show = !projects && (!only || only === p);
					el.hidden = !show;
					$$("select, input", el).forEach(function (i) { i.disabled = !show; });
				});
			});
		});
		var start = box.getAttribute("data-default");
		var startTab = start ? $('[data-purpose="' + start + '"]', box) : null;
		if (startTab) { startTab.click(); }
		// Do not send empty fields: shorter, cache-friendlier URLs.
		form.addEventListener("submit", function () {
			$$("input, select", form).forEach(function (i) { if (!i.value) { i.disabled = true; } });
		});
	});

	/* ---------- Listing: filters drawer, sort, clean URLs ---------- */
	var filtersBtn = $(".filters-toggle");
	if (filtersBtn) {
		filtersBtn.addEventListener("click", function () {
			var open = doc.body.classList.toggle("filters-open");
			filtersBtn.setAttribute("aria-expanded", open ? "true" : "false");
		});
		var filters = $("#filters");
		if (filters) {
			filters.addEventListener("click", function (e) {
				if (e.target === filters) { doc.body.classList.remove("filters-open"); }
			});
		}
	}
	$$("[data-autosubmit] select").forEach(function (s) {
		s.addEventListener("change", function () { s.form.submit(); });
	});
	$$("[data-filters]").forEach(function (form) {
		form.addEventListener("submit", function () {
			$$("input, select", form).forEach(function (i) {
				if ((i.type === "radio" && i.checked && !i.value) || (i.type !== "radio" && i.type !== "checkbox" && !i.value)) { i.disabled = true; }
			});
		});
	});

	/* ---------- Maps on demand (Leaflet / OpenStreetMap or Google Maps) ---------- */
	var mapPromise;
	function loadScript(src, css) {
		return new Promise(function (resolve, reject) {
			if (css) {
				var l = doc.createElement("link");
				l.rel = "stylesheet";
				l.href = css;
				doc.head.appendChild(l);
			}
			var s = doc.createElement("script");
			s.src = src;
			s.async = true;
			s.onerror = reject;
			if (data.mapProvider !== "google") { s.onload = resolve; }
			doc.head.appendChild(s);
		});
	}
	function loadMaps() {
		if (mapPromise) { return mapPromise; }
		if (data.mapProvider === "google") {
			mapPromise = new Promise(function (resolve, reject) {
				window.daraGoogleReady = resolve;
				loadScript(data.googleJs).catch(reject);
			});
		} else {
			mapPromise = window.L ? Promise.resolve() : loadScript(data.leafletJs, data.leafletCss);
		}
		return mapPromise;
	}
	function pinHtml(label) {
		return "<span>" + String(label).replace(/[<>&"]/g, "") + "</span>";
	}
	var homeHtml = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20V10l8-6 8 6v10"/></svg>';

	// Same small API for both providers: addHome(lat, lng), addPin(lat, lng, label, title, link), fit(points).
	function leafletMap(el, lat, lng, zoom) {
		var L = window.L;
		var map = L.map(el, { scrollWheelZoom: false, center: [lat, lng], zoom: zoom });
		L.tileLayer(data.tiles, { maxZoom: 19, attribution: data.attribution }).addTo(map);
		return {
			addHome: function (la, ln) {
				L.marker([la, ln], { keyboard: false, icon: L.divIcon({ className: "map-home", html: homeHtml, iconSize: [48, 48], iconAnchor: [24, 24] }) }).addTo(map);
			},
			addPin: function (la, ln, label, title, link) {
				L.marker([la, ln], { title: title, riseOnHover: true, icon: L.divIcon({ className: "map-pin", html: pinHtml(label), iconSize: null }) }).addTo(map).bindPopup(link);
			},
			fit: function (points) { map.fitBounds(points, { padding: [40, 40] }); }
		};
	}
	function googleMap(el, lat, lng, zoom) {
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
	}
	function createMap(el, lat, lng, zoom) {
		return data.mapProvider === "google" ? googleMap(el, lat, lng, zoom) : leafletMap(el, lat, lng, zoom);
	}
	function initSingleMap(el) {
		loadMaps().then(function () {
			var lat = parseFloat(el.getAttribute("data-lat")), lng = parseFloat(el.getAttribute("data-lng"));
			createMap(el, lat, lng, 15).addHome(lat, lng);
		}).catch(function () { el.hidden = true; });
	}
	var mapObserver = "IntersectionObserver" in window ? new IntersectionObserver(function (entries) {
		entries.forEach(function (en) {
			if (en.isIntersecting) { mapObserver.unobserve(en.target); initSingleMap(en.target); }
		});
	}, { rootMargin: "300px" }) : null;
	$$('[data-map="single"]').forEach(function (el) {
		if (mapObserver) { mapObserver.observe(el); } else { initSingleMap(el); }
	});

	// Listing grid / map toggle.
	var listingMap = $("#listing-map");
	var listingReady = false;
	$$(".view-toggle [data-view]").forEach(function (btn) {
		btn.addEventListener("click", function () {
			var isMap = btn.getAttribute("data-view") === "map";
			$$(".view-toggle [data-view]").forEach(function (b) { b.setAttribute("aria-pressed", b === btn ? "true" : "false"); });
			if (!listingMap) { return; }
			listingMap.hidden = !isMap;
			doc.body.classList.toggle("is-map-view", isMap);
			if (isMap && !listingReady) {
				listingReady = true;
				var points = [];
				try { points = JSON.parse(($("#listing-points") || {}).textContent || "[]"); } catch (err) { points = []; }
				loadMaps().then(function () {
					var map = createMap(listingMap, points.length ? points[0].lat : 24.7136, points.length ? points[0].lng : 46.6753, 11);
					var group = [];
					points.forEach(function (p) {
						var a = doc.createElement("a");
						a.href = p.url;
						a.textContent = p.title;
						map.addPin(p.lat, p.lng, p.label, p.title, a);
						group.push([p.lat, p.lng]);
					});
					if (group.length > 1) { map.fit(group); }
				}).catch(function () { listingReady = false; });
			}
		});
	});

	/* ---------- Gallery lightbox ---------- */
	var gallery = [];
	try { gallery = JSON.parse(($("#gallery-data") || {}).textContent || "[]"); } catch (err) { gallery = []; }
	var lightbox, lbImg, lbCount, lbIndex = 0;
	function showLb(i) {
		lbIndex = (i + gallery.length) % gallery.length;
		lbImg.src = gallery[lbIndex].src;
		lbImg.alt = gallery[lbIndex].alt || "";
		lbCount.textContent = (lbIndex + 1) + " / " + gallery.length;
	}
	function buildLb() {
		lightbox = doc.createElement("dialog");
		lightbox.className = "lightbox";
		lightbox.innerHTML =
			'<button type="button" class="lightbox__close" aria-label="' + (i18n.close || "Close") + '">&times;</button>' +
			'<button type="button" class="lightbox__nav lightbox__prev" aria-label="' + (i18n.prev || "Previous") + '">&#8249;</button>' +
			'<img alt="" decoding="async">' +
			'<button type="button" class="lightbox__nav lightbox__next" aria-label="' + (i18n.next || "Next") + '">&#8250;</button>' +
			'<span class="lightbox__count"></span>';
		doc.body.appendChild(lightbox);
		lbImg = $("img", lightbox);
		lbCount = $(".lightbox__count", lightbox);
		$(".lightbox__close", lightbox).addEventListener("click", function () { lightbox.close(); });
		$(".lightbox__prev", lightbox).addEventListener("click", function () { showLb(lbIndex + (isRtl ? 1 : -1)); });
		$(".lightbox__next", lightbox).addEventListener("click", function () { showLb(lbIndex + (isRtl ? -1 : 1)); });
		lightbox.addEventListener("click", function (e) { if (e.target === lightbox) { lightbox.close(); } });
		lightbox.addEventListener("keydown", function (e) {
			if (e.key === "ArrowLeft") { showLb(lbIndex + (isRtl ? 1 : -1)); }
			if (e.key === "ArrowRight") { showLb(lbIndex + (isRtl ? -1 : 1)); }
		});
		var x0 = null;
		lightbox.addEventListener("touchstart", function (e) { x0 = e.touches[0].clientX; }, { passive: true });
		lightbox.addEventListener("touchend", function (e) {
			if (x0 === null) { return; }
			var dx = e.changedTouches[0].clientX - x0;
			if (Math.abs(dx) > 40) { showLb(lbIndex + ((dx < 0) !== isRtl ? 1 : -1)); }
			x0 = null;
		});
	}
	if (gallery.length && typeof HTMLDialogElement === "function") {
		doc.addEventListener("click", function (e) {
			var item = e.target.closest("[data-gallery] .gallery__item");
			if (!item) { return; }
			e.preventDefault();
			if (!lightbox) { buildLb(); }
			showLb(parseInt(item.getAttribute("data-index"), 10) || 0);
			lightbox.showModal();
		});
	}

	/* ---------- Tabs ---------- */
	$$("[data-tabs]").forEach(function (wrap) {
		var tabs = $$("[role=tab]", wrap);
		tabs.forEach(function (tab) {
			tab.addEventListener("click", function () {
				tabs.forEach(function (t) {
					var on = t === tab;
					t.setAttribute("aria-selected", on ? "true" : "false");
					var panel = doc.getElementById(t.getAttribute("aria-controls"));
					if (panel) { panel.hidden = !on; }
				});
			});
		});
	});

	/* ---------- Video: load the iframe on click only ---------- */
	$$("[data-video]").forEach(function (box) {
		var btn = $(".video__play", box);
		var tpl = $("template", box);
		if (!btn || !tpl) { return; }
		btn.addEventListener("click", function () {
			box.innerHTML = tpl.innerHTML.replace(/src="([^"]+)"/, function (m, src) {
				return 'src="' + src + (src.indexOf("?") > -1 ? "&" : "?") + 'autoplay=1"';
			});
			box.classList.add("is-playing");
		});
	});

	/* ---------- Share & print ---------- */
	doc.addEventListener("click", function (e) {
		var share = e.target.closest("[data-share]");
		if (share) {
			var url = location.href.split("#")[0];
			if (navigator.share) {
				navigator.share({ title: share.getAttribute("data-title") || doc.title, url: url }).catch(function () {});
			} else if (navigator.clipboard) {
				navigator.clipboard.writeText(url).then(function () { toast(i18n.copied || "Copied"); });
			}
		}
		if (e.target.closest("[data-print]")) { window.print(); }
	});

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

	/* ---------- Favorites (localStorage) ---------- */
	var FAV_KEY = "dara_favs";
	function getFavs() {
		try { return JSON.parse(localStorage.getItem(FAV_KEY) || "[]").map(Number).filter(Boolean); } catch (err) { return []; }
	}
	function setFavs(list) {
		try { localStorage.setItem(FAV_KEY, JSON.stringify(list)); } catch (err) { /* private mode */ }
	}
	function paintFavs() {
		var favs = getFavs();
		$$("[data-fav]").forEach(function (b) {
			b.setAttribute("aria-pressed", favs.indexOf(Number(b.getAttribute("data-fav"))) > -1 ? "true" : "false");
		});
		$$(".fav-count").forEach(function (c) {
			c.textContent = favs.length;
			c.hidden = !favs.length;
		});
	}
	doc.addEventListener("click", function (e) {
		var b = e.target.closest("[data-fav]");
		if (!b) { return; }
		e.preventDefault();
		var id = Number(b.getAttribute("data-fav"));
		var favs = getFavs();
		var i = favs.indexOf(id);
		if (i > -1) { favs.splice(i, 1); toast(i18n.removed || "Removed"); } else { favs.push(id); toast(i18n.saved || "Saved"); }
		setFavs(favs);
		paintFavs();
	});
	paintFavs();

	var favWrap = $("[data-favorites]");
	if (favWrap) {
		var favs = getFavs();
		if (!favs.length) {
			favWrap.innerHTML = '<p class="empty">' + (i18n.empty || "") + "</p>";
		} else {
			fetch(data.restUrl + "dara_property?per_page=50&include=" + favs.join(",") + "&_embed=wp:featuredmedia&_fields=id,link,title,meta,_links,_embedded")
				.then(function (r) { return r.json(); })
				.then(function (items) {
					var esc = function (s) { var d = doc.createElement("div"); d.textContent = s || ""; return d.innerHTML; };
					favWrap.innerHTML = items.map(function (p) {
						var media = p._embedded && p._embedded["wp:featuredmedia"] && p._embedded["wp:featuredmedia"][0];
						var img = media && media.media_details && media.media_details.sizes ? (media.media_details.sizes["dara-card"] || media.media_details.sizes.medium_large || media.media_details.sizes.full || {}).source_url : "";
						var price = p.meta && p.meta._dara_price ? Number(p.meta._dara_price).toLocaleString(doc.documentElement.lang) : "";
						var title = doc.createElement("div");
						title.innerHTML = p.title.rendered;
						return '<article class="card card--property"><div class="card__media"><a href="' + esc(p.link) + '">' +
							(img ? '<img src="' + esc(img) + '" alt="" loading="lazy">' : "") + "</a>" +
							'<button type="button" class="fav-btn" data-fav="' + p.id + '" aria-pressed="true">' + ($(".fav-link svg") ? $(".fav-link svg").outerHTML : "♥") + "</button></div>" +
							'<div class="card__body">' + (price ? '<p class="price"><span class="price__amount">' + esc(price) + "</span></p>" : "") +
							'<h2 class="card__title"><a href="' + esc(p.link) + '">' + esc(title.textContent) + "</a></h2></div></article>";
					}).join("");
				})
				.catch(function () { favWrap.innerHTML = ""; });
		}
	}
})();
