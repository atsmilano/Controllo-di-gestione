CKEDITOR.dialog.add("lightbox", function(g) {
	var l = CKEDITOR.plugins.link,
		m = function() {
			var a = this.getDialog(),
				k = this.getValue();
		},
		h = function(a) {
			a.advanced && this.setValue(a.advanced[this.id] || "");
		},
		j = function(a) {
			a.advanced || (a.advanced = {});
			a.advanced[this.id] = this.getValue() || ""
		},
		prev = function(a) {
            $('.cke_dialog .ImagePreview').html('<img src="'+this.getValue()+'" style="max-height:100px; max-width:450px;"/>');
		},
		getGal = function(a) {
            a.advanced || (a.advanced = {});
            gal = a.advanced["advCSSClasses"];
            gal = gal.split("ckelightboxgallery");
            gal = gal[1];
            a.advanced && this.setValue(gal || "");
		},
		setGal = function(a) {
            gal = this.getValue() || "";
		},
		c = g.lang.common,
		b = g.lang.link,
		d;
	return {
		title: "Lightbox",
		minWidth: 350,
		minHeight: 300,
		contents: [{
			id: "info",
			label: b.info,
			title: b.info,
			elements: [
			{
				type: "vbox",
				id: "urlOptions",
				children: [{
					type: "hbox",
					widths: "0",
					children: [
					{
						type: "text",
						id: "url",
						label: g.lang.lightbox.url,
						required: !0,
						onLoad: function() {
							this.allowOnChange = !0
						},
						onKeyUp: function() {
							this.allowOnChange = !1;
							var b = this.getValue(),
								k = /^((javascript:)|[#\/\.\?])/i;
                            k.test(b);
							this.allowOnChange = !0;
						},
						onChange: function() {
							if(this.allowOnChange) this.onKeyUp();
                            $('.cke_dialog .ImagePreview').html('<img src="'+this.getValue()+'" style="max-height:100px; max-width:450px;"/>');
						},
						validate: function() {
							var a = this.getDialog();
						    return !g.config.linkJavaScriptLinksAllowed && /javascript\:/.test(this.getValue()) ? (alert(c.invalidValue), !1) : this.getDialog().fakeObj ? !0 : CKEDITOR.dialog.validate.notEmpty(b.noUrl).apply(this);

                        },
						setup: function(a) {
							this.allowOnChange = !1;
							a.url && this.setValue(a.url.protocol != undefined ? a.url.protocol+a.url.url : a.url.url);
							this.allowOnChange = !0;
						},
						onShow: prev,
						commit: function(a) {
                            a.type = "url";
							a.url || (a.url = {});
							a.url.protocol = "";
                            a.advanced["advCSSClasses"] = "ckelightbox ckelightboxgallery"+gal;
							this.onChange();
							a.url || (a.url = {});
							a.url.url = this.getValue();
							this.allowOnChange = !1
						}
					},
                    {
						type: "button",
						id: "browse",
						hidden: "true",
                        style : "margin-top:16px;",
						filebrowser: "info:url",
						label: c.browseServer
					}]
				},
    			{
                    id: "prev",
                    type: "html",
                    html : g.lang.lightbox.preview+'<div class="ImagePreview" style="border:2px solid black; height:100px; text-align:center;"></div>'
                },
                {
					type: "text",
					label: g.lang.lightbox.title,
					requiredContent: "a[title]",
					"default": "",
					id: "advTitle",
					setup: h,
					commit: j
                },
                {
					type: "text",
					label: g.lang.lightbox.gallery,
					"default": "",
					id: "advRel",
					setup: getGal,
					commit: setGal
                }]
			}]
		}],
		onShow: function() {
            $('.cke_dialog .ImagePreview').html('');
			var a =
			this.getParentEditor(),
				b = a.getSelection(),
				c = null;
			(c = l.getSelectedLink(a)) && c.hasAttribute("href") ? b.getSelectedElement() || b.selectElement(c) : c = null;
			a = l.parseLinkAttributes(a, c);
			this._.selectedElement = c;
			this.setupContent(a);
		},
		onOk: function() {
			var a = {};
			this.commitContent(a);
			var b = g.getSelection(),
				c = l.getLinkAttributes(g, a);
			if (this._.selectedElement) {
				var e = this._.selectedElement,
					d = e.data("cke-saved-href"),
					f = e.getHtml();
				e.setAttributes(c.set);
				e.removeAttributes(c.removed);
				if (d == f) e.setHtml(c.set["data-cke-saved-href"]), b.selectElement(e);
				delete this._.selectedElement
			} else b = b.getRanges()[0], b.collapsed && (a = new CKEDITOR.dom.text(c.set["data-cke-saved-href"], g.document), b.insertNode(a), b.selectNodeContents(a)), c = new CKEDITOR.style({
				element: "a",
				attributes: c.set
			}), c.type = CKEDITOR.STYLE_INLINE, c.applyToRange(b, g), b.select()
		}
	}
});