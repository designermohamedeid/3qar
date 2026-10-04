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

	/* ---------- Leaflet on demand ---------- */
	var leafletPromise;
	function loadLeaflet() {
		if (window.L) { return Promise.resolve(window.L); }
		if (leafletPromise) { return leafletPromise; }
		leafletPromise = new Promise(function (resolve, reject) {
			var css = doc.createElement("link");
			css.rel = "stylesheet";
			css.href = data.leafletCss;
			doc.head.appendChild(css);
			var s = doc.createElement("script");
			s.src = data.leafletJs;
			s.async = true;
			s.onload = function () { resolve(window.L); };
			s.onerror = reject;
			doc.head.appendChild(s);
		});
		return leafletPromise;
	}
	function baseMap(el, opts) {
		var L = window.L;
		var map = L.map(el, Object.assign({ scrollWheelZoom: false }, opts || {}));
		L.tileLayer(data.tiles, { maxZoom: 19, attribution: data.attribution }).addTo(map);
		return map;
	}
	function pinIcon(label, active) {
		return window.L.divIcon({
			className: "map-pin" + (active ? " map-pin--active" : ""),
			html: "<span>" + String(label).replace(/[<>&"]/g, "") + "</span>",
			iconSize: null
		});
	}
	function homeIcon() {
		return window.L.divIcon({
			className: "map-home",
			html: '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20V10l8-6 8 6v10"/></svg>',
			iconSize: [48, 48],
			iconAnchor: [24, 24]
		});
	}
	function initSingleMap(el) {
		loadLeaflet().then(function () {
			var ll = [parseFloat(el.getAttribute("data-lat")), parseFloat(el.getAttribute("data-lng"))];
			var map = baseMap(el, { center: ll, zoom: 15 });
			window.L.marker(ll, { icon: homeIcon(), keyboard: false }).addTo(map);
		});
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
				loadLeaflet().then(function (L) {
					var map = baseMap(listingMap, { center: points.length ? [points[0].lat, points[0].lng] : [24.7136, 46.6753], zoom: 11 });
					var group = [];
					points.forEach(function (p) {
						var m = L.marker([p.lat, p.lng], { icon: pinIcon(p.label), title: p.title, riseOnHover: true }).addTo(map);
						var a = doc.createElement("a");
						a.href = p.url;
						a.textContent = p.title;
						m.bindPopup(a);
						group.push([p.lat, p.lng]);
					});
					if (group.length > 1) { map.fitBounds(group, { padding: [40, 40] }); }
				});
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
