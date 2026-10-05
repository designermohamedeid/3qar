/* Dara Core – gallery picker (no jQuery dependency in our code). */
(function () {
	"use strict";
	var l10n = window.daraCoreAdmin || {};

	function sync(box) {
		var ids = Array.prototype.map.call(box.querySelectorAll(".dara-gallery__list li"), function (li) {
			return li.getAttribute("data-id");
		});
		box.querySelector("input[type=hidden]").value = ids.join(",");
	}

	function addItem(box, att) {
		var url = att.sizes && att.sizes.thumbnail ? att.sizes.thumbnail.url : att.url;
		var li = document.createElement("li");
		li.setAttribute("data-id", att.id);
		li.draggable = true;
		var img = document.createElement("img");
		img.src = url;
		img.alt = "";
		var btn = document.createElement("button");
		btn.type = "button";
		btn.className = "dara-gallery__remove";
		btn.setAttribute("aria-label", l10n.remove || "Remove");
		btn.textContent = "×";
		li.appendChild(img);
		li.appendChild(btn);
		box.querySelector(".dara-gallery__list").appendChild(li);
	}

	document.addEventListener("click", function (e) {
		var add = e.target.closest(".dara-gallery__add");
		var remove = e.target.closest(".dara-gallery__remove");
		if (remove) {
			var b = remove.closest(".dara-gallery");
			remove.parentNode.remove();
			sync(b);
			return;
		}
		if (!add || !window.wp || !wp.media) {
			return;
		}
		var box = add.closest(".dara-gallery");
		var single = box.hasAttribute("data-single");
		var frame = wp.media({ title: l10n.title, button: { text: l10n.button }, library: { type: "image" }, multiple: single ? false : "add" });
		frame.on("select", function () {
			if (single) {
				box.querySelector(".dara-gallery__list").innerHTML = "";
			}
			frame.state().get("selection").each(function (m) {
				var att = m.toJSON();
				if (!box.querySelector('li[data-id="' + att.id + '"]')) {
					addItem(box, att);
				}
			});
			sync(box);
		});
		frame.open();
	});

	// Drag to reorder.
	var dragged = null;
	document.addEventListener("dragstart", function (e) {
		dragged = e.target.closest && e.target.closest(".dara-gallery__list li");
	});
	document.addEventListener("dragover", function (e) {
		var over = e.target.closest && e.target.closest(".dara-gallery__list li");
		if (dragged && over && over !== dragged && over.parentNode === dragged.parentNode) {
			e.preventDefault();
			var rect = over.getBoundingClientRect();
			var after = (e.clientX - rect.left) / rect.width > 0.5;
			if (document.dir === "rtl") {
				after = !after;
			}
			over.parentNode.insertBefore(dragged, after ? over.nextSibling : over);
		}
	});
	document.addEventListener("dragend", function () {
		if (dragged) {
			sync(dragged.closest(".dara-gallery"));
			dragged = null;
		}
	});
	document.querySelectorAll(".dara-gallery__list li").forEach(function (li) {
		li.draggable = true;
	});
})();
