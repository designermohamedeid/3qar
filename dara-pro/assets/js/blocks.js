/**
 * Dara blocks – editor UI (no build step: plain wp.element calls).
 * Each block shows a live server-rendered preview and its settings in the sidebar.
 */
(function (wp, config) {
	"use strict";
	if (!wp || !config) {
		return;
	}

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var be = wp.blockEditor;
	var c = wp.components;
	var SSR = wp.serverSideRender;
	var i18n = config.i18n || {};

	function imageControl(control, value, set) {
		return el(
			be.MediaUploadCheck,
			{ key: control.key },
			el(be.MediaUpload, {
				allowedTypes: ["image"],
				value: value,
				onSelect: function (media) { set(media.id); },
				render: function (obj) {
					return el(
						"div",
						{ className: "dara-image-control", style: { marginBottom: "16px" } },
						el("p", { style: { margin: "0 0 8px", fontWeight: 600 } }, control.label),
						value ? el("p", { style: { margin: "0 0 8px", color: "#757575" } }, "#" + value) : null,
						el(c.Button, { variant: "secondary", onClick: obj.open }, value ? i18n.replace : i18n.choose),
						value ? el(c.Button, { variant: "link", isDestructive: true, onClick: function () { set(undefined); }, style: { marginInlineStart: "8px" } }, i18n.remove) : null
					);
				}
			})
		);
	}

	function field(control, value, set) {
		switch (control.type) {
			case "toggle":
				return el(c.ToggleControl, { key: control.key, label: control.label, checked: !!value, onChange: set });
			case "number":
				return el(c.RangeControl, { key: control.key, label: control.label, value: value, min: control.min || 1, max: control.max || 12, allowReset: true, onChange: set });
			case "amount":
				return el(c.TextControl, { key: control.key, label: control.label, type: "number", min: 0, value: value === undefined ? "" : value, onChange: function (v) { set(v === "" ? undefined : Number(v)); } });
			case "select":
				return el(c.SelectControl, { key: control.key, label: control.label, value: value || "", options: control.options, onChange: set });
			case "textarea":
				return el(c.TextareaControl, { key: control.key, label: control.label, help: control.help, value: value || "", onChange: set });
			case "image":
				return imageControl(control, value, set);
			case "repeater":
				return repeater(control, value, set);
			default:
				return el(c.TextControl, { key: control.key, label: control.label, value: value || "", onChange: set });
		}
	}

	function repeater(control, value, set) {
		var rows = Array.isArray(value) ? value : [];
		var update = function (index, key, v) {
			var next = rows.map(function (r) { return Object.assign({}, r); });
			next[index][key] = v;
			set(next);
		};
		var move = function (index, dir) {
			var next = rows.slice();
			var target = index + dir;
			if (target < 0 || target >= next.length) { return; }
			var tmp = next[target];
			next[target] = next[index];
			next[index] = tmp;
			set(next);
		};
		return el(
			"div",
			{ key: control.key, className: "dara-repeater" },
			el("p", { style: { fontWeight: 600, margin: "0 0 8px" } }, control.label),
			rows.map(function (row, index) {
				return el(
					c.PanelBody,
					{ key: index, title: (row.title || i18n.item + " " + (index + 1)), initialOpen: false },
					control.fields.map(function (f) {
						return field(f, row[f.key], function (v) { update(index, f.key, v); });
					}),
					el(
						"div",
						{ style: { display: "flex", gap: "8px", flexWrap: "wrap" } },
						el(c.Button, { variant: "secondary", size: "small", disabled: index === 0, onClick: function () { move(index, -1); } }, i18n.up),
						el(c.Button, { variant: "secondary", size: "small", disabled: index === rows.length - 1, onClick: function () { move(index, 1); } }, i18n.down),
						el(c.Button, { variant: "link", isDestructive: true, onClick: function () { set(rows.filter(function (r, i) { return i !== index; })); } }, i18n.remove)
					)
				);
			}),
			el(c.Button, { variant: "primary", onClick: function () { set(rows.concat([{ icon: "home", title: "", text: "", url: "" }])); }, style: { marginTop: "8px" } }, i18n.add)
		);
	}

	Object.keys(config.controls).forEach(function (name) {
		var controls = config.controls[name];
		wp.blocks.registerBlockType(name, {
			edit: function (props) {
				var blockProps = be.useBlockProps();
				return el(
					Fragment,
					null,
					el(
						be.InspectorControls,
						null,
						el(
							c.PanelBody,
							{ title: i18n.settings, initialOpen: true },
							el("p", { style: { color: "#757575", marginTop: 0 } }, i18n.fallback),
							controls.map(function (control) {
								return field(control, props.attributes[control.key], function (v) {
									var o = {};
									o[control.key] = v;
									props.setAttributes(o);
								});
							})
						)
					),
					el(
						"div",
						blockProps,
						el(c.Disabled, null, el(SSR, { block: name, attributes: props.attributes, httpMethod: "POST" }))
					)
				);
			},
			save: function () {
				return null;
			}
		});
	});
})(window.wp, window.daraBlocks);
