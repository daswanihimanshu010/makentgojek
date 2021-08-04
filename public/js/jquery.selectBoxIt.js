!function(t){"use strict";!function(t,e,s,i){t.widget("selectBox.selectBoxIt",{VERSION:"3.8.1",options:{showEffect:"none",showEffectOptions:{},showEffectSpeed:"medium",hideEffect:"none",hideEffectOptions:{},hideEffectSpeed:"medium",showFirstOption:!0,defaultText:"",defaultIcon:"",downArrowIcon:"",theme:"default",keydownOpen:!0,isMobile:function(){var t=navigator.userAgent||navigator.vendor||e.opera;return/iPhone|iPod|iPad|Silk|Android|BlackBerry|Opera Mini|IEMobile/.test(t)},native:!1,aggressiveChange:!1,selectWhenHidden:!0,viewport:t(e),similarSearch:!1,copyAttributes:["title","rel"],copyClasses:"button",nativeMousedown:!1,customShowHideEvent:!1,autoWidth:!0,html:!0,populate:"",dynamicPositioning:!0,hideCurrent:!1},getThemes:function(){var e=t(this.element).attr("data-theme")||"c";return{bootstrap:{focus:"active",hover:"",enabled:"enabled",disabled:"disabled",arrow:"caret",button:"btn",list:"dropdown-menu",container:"bootstrap",open:"open"},jqueryui:{focus:"ui-state-focus",hover:"ui-state-hover",enabled:"ui-state-enabled",disabled:"ui-state-disabled",arrow:"ui-icon ui-icon-triangle-1-s",button:"ui-widget ui-state-default",list:"ui-widget ui-widget-content",container:"jqueryui",open:"selectboxit-open"},jquerymobile:{focus:"ui-btn-down-"+e,hover:"ui-btn-hover-"+e,enabled:"ui-enabled",disabled:"ui-disabled",arrow:"ui-icon ui-icon-arrow-d ui-icon-shadow",button:"ui-btn ui-btn-icon-right ui-btn-corner-all ui-shadow ui-btn-up-"+e,list:"ui-btn ui-btn-icon-right ui-btn-corner-all ui-shadow ui-btn-up-"+e,container:"jquerymobile",open:"selectboxit-open"},default:{focus:"selectboxit-focus",hover:"selectboxit-hover",enabled:"selectboxit-enabled",disabled:"selectboxit-disabled",arrow:"selectboxit-default-arrow",button:"selectboxit-btn",list:"selectboxit-list",container:"selectboxit-container",open:"selectboxit-open"}}},isDeferred:function(e){return t.isPlainObject(e)&&e.promise&&e.done},_create:function(e){var i=this.options.populate,o=this.options.theme;if(this.element.is("select"))return this.widgetProto=t.Widget.prototype,this.originalElem=this.element[0],this.selectBox=this.element,this.options.populate&&this.add&&!e&&this.add(i),this.selectItems=this.element.find("option"),this.firstSelectItem=this.selectItems.slice(0,1),this.documentHeight=t(s).height(),this.theme=t.isPlainObject(o)?t.extend({},this.getThemes().default,o):this.getThemes()[o]?this.getThemes()[o]:this.getThemes().default,this.currentFocus=0,this.blur=!0,this.textArray=[],this.currentIndex=0,this.currentText="",this.flipped=!1,e||(this.selectBoxStyles=this.selectBox.attr("style")),this._createDropdownButton()._createUnorderedList()._copyAttributes()._replaceSelectBox()._addClasses(this.theme)._eventHandlers(),this.originalElem.disabled&&this.disable&&this.disable(),this._ariaAccessibility&&this._ariaAccessibility(),this.isMobile=this.options.isMobile(),this._mobile&&this._mobile(),this.options.native&&this._applyNativeSelect(),this.triggerEvent("create"),this},_createDropdownButton:function(){var e=this.originalElemId=this.originalElem.id||"",s=this.originalElemValue=this.originalElem.value||"",i=this.originalElemName=this.originalElem.name||"",o=this.options.copyClasses,n=this.selectBox.attr("class")||"";return this.dropdownText=t("<span/>",{id:e&&e+"SelectBoxItText",class:"selectboxit-text",unselectable:"on",text:this.firstSelectItem.text()}).attr("data-val",s),this.dropdownImageContainer=t("<span/>",{class:"selectboxit-option-icon-container"}),this.dropdownImage=t("<i/>",{id:e&&e+"SelectBoxItDefaultIcon",class:"selectboxit-default-icon",unselectable:"on"}),this.dropdown=t("<span/>",{id:e&&e+"SelectBoxIt",class:"selectboxit "+("button"===o?n:"")+" "+(this.selectBox.prop("disabled")?this.theme.disabled:this.theme.enabled),name:i,tabindex:this.selectBox.attr("tabindex")||"0",unselectable:"on"}).append(this.dropdownImageContainer.append(this.dropdownImage)).append(this.dropdownText),this.dropdownContainer=t("<span/>",{id:e&&e+"SelectBoxItContainer",class:"selectboxit-container "+this.theme.container+" "+("container"===o?n:"")}).append(this.dropdown),this},_createUnorderedList:function(){var e,s,i,o,n,r,a,l,d,c,h,u,p,b=this,f="",m=b.originalElemId||"",x=t("<ul/>",{id:m&&m+"SelectBoxItOptions",class:"selectboxit-options",tabindex:-1});if(b.options.showFirstOption||(b.selectItems.first().attr("disabled","disabled"),b.selectItems=b.selectBox.find("option").slice(1)),b.selectItems.each(function(m){u=t(this),s="",i="",e=u.prop("disabled"),o=u.attr("data-icon")||"",n=u.attr("data-iconurl")||"",r=n?"selectboxit-option-icon-url":"",a=n?"style=\"background-image:url('"+n+"');\"":"",l=u.attr("data-selectedtext"),d=u.attr("data-text"),h=d||u.text(),(p=u.parent()).is("optgroup")&&(s="selectboxit-optgroup-option",0===u.index()&&(i='<span class="selectboxit-optgroup-header '+p.first().attr("class")+'"data-disabled="true">'+p.first().attr("label")+"</span>")),u.attr("value",this.value),f+=i+'<li data-id="'+m+'" data-val="'+this.value+'" data-disabled="'+e+'" class="'+s+" selectboxit-option "+(t(this).attr("class")||"")+'"><a class="selectboxit-option-anchor"><span class="selectboxit-option-icon-container"><i class="selectboxit-option-icon '+o+" "+(r||b.theme.container)+'"'+a+"></i></span>"+(b.options.html?h:b.htmlEscape(h))+"</a></li>",c=u.attr("data-search"),b.textArray[m]=e?"":c||h,this.selected&&(b._setText(b.dropdownText,l||h),b.currentFocus=m)}),b.options.defaultText||b.selectBox.attr("data-text")){var g=b.options.defaultText||b.selectBox.attr("data-text");b._setText(b.dropdownText,g),b.options.defaultText=g}return x.append(f),b.list=x,b.dropdownContainer.append(b.list),b.listItems=b.list.children("li"),b.listAnchors=b.list.find("a"),b.listItems.first().addClass("selectboxit-option-first"),b.listItems.last().addClass("selectboxit-option-last"),b.list.find("li[data-disabled='true']").not(".optgroupHeader").addClass(b.theme.disabled),b.dropdownImage.addClass(b.selectBox.attr("data-icon")||b.options.defaultIcon||b.listItems.eq(b.currentFocus).find("i").attr("class")),b.dropdownImage.attr("style",b.listItems.eq(b.currentFocus).find("i").attr("style")),b},_replaceSelectBox:function(){var e,s,o=this.originalElem.id||"",n=this.selectBox.attr("data-size"),r=this.listSize=n===i?"auto":"0"===n?"auto":+n;return this.selectBox.css("display","none").after(this.dropdownContainer),this.dropdownContainer.appendTo("body").addClass("selectboxit-rendering"),this.dropdown.height(),this.downArrow=t("<i/>",{id:o&&o+"SelectBoxItArrow",class:"selectboxit-arrow",unselectable:"on"}),this.downArrowContainer=t("<span/>",{id:o&&o+"SelectBoxItArrowContainer",class:"selectboxit-arrow-container",unselectable:"on"}).append(this.downArrow),this.dropdown.append(this.downArrowContainer),this.listItems.removeClass("selectboxit-selected").eq(this.currentFocus).addClass("selectboxit-selected"),e=this.downArrowContainer.outerWidth(!0),s=this.dropdownImage.outerWidth(!0),this.options.autoWidth&&(this.dropdown.css({width:"auto"}).css({width:this.list.outerWidth(!0)+e+s}),this.list.css({"min-width":this.dropdown.width()})),this.dropdownText.css({"max-width":this.dropdownContainer.outerWidth(!0)-(e+s)}),this.selectBox.after(this.dropdownContainer),this.dropdownContainer.removeClass("selectboxit-rendering"),"number"===t.type(r)&&(this.maxHeight=this.listAnchors.outerHeight(!0)*r),this},_scrollToView:function(t){var e=this.listItems.eq(this.currentFocus),s=this.list.scrollTop(),i=e.height(),o=e.position().top,n=Math.abs(o),r=this.list.height();return"search"===t?r-o<i?this.list.scrollTop(s+(o-(r-i))):o<-1&&this.list.scrollTop(o-i):"up"===t?o<-1&&this.list.scrollTop(s-n):"down"===t&&r-o<i&&this.list.scrollTop(s+(n-r+i)),this},_callbackSupport:function(e){return t.isFunction(e)&&e.call(this,this.dropdown),this},_setText:function(t,e){return this.options.html?t.html(e):t.text(e),this},open:function(t){var e=this,s=e.options.showEffect,i=e.options.showEffectSpeed,o=e.options.showEffectOptions,n=e.options.native,r=e.isMobile;return!e.listItems.length||e.dropdown.hasClass(e.theme.disabled)?e:(n||r||this.list.is(":visible")||(e.triggerEvent("open"),e._dynamicPositioning&&e.options.dynamicPositioning&&e._dynamicPositioning(),"none"===s?e.list.show():"show"===s||"slideDown"===s||"fadeIn"===s?e.list[s](i):e.list.show(s,o,i),e.list.promise().done(function(){e._scrollToView("search"),e.triggerEvent("opened")})),e._callbackSupport(t),e)},close:function(t){var e=this,s=e.options.hideEffect,i=e.options.hideEffectSpeed,o=e.options.hideEffectOptions,n=e.options.native,r=e.isMobile;return n||r||!e.list.is(":visible")||(e.triggerEvent("close"),"none"===s?e.list.hide():"hide"===s||"slideUp"===s||"fadeOut"===s?e.list[s](i):e.list.hide(s,o,i),e.list.promise().done(function(){e.triggerEvent("closed")})),e._callbackSupport(t),e},toggle:function(){var t=this.list.is(":visible");t?this.close():t||this.open()},_keyMappings:{38:"up",40:"down",13:"enter",8:"backspace",9:"tab",32:"space",27:"esc"},_keydownMethods:function(){var t=this,e=t.list.is(":visible")||!t.options.keydownOpen;return{down:function(){t.moveDown&&e&&t.moveDown()},up:function(){t.moveUp&&e&&t.moveUp()},enter:function(){var e=t.listItems.eq(t.currentFocus);t._update(e),"true"!==e.attr("data-preventclose")&&t.close(),t.triggerEvent("enter")},tab:function(){t.triggerEvent("tab-blur"),t.close()},backspace:function(){t.triggerEvent("backspace")},esc:function(){t.close()}}},_eventHandlers:function(){var e,s,i=this,o=i.options.nativeMousedown,n=i.options.customShowHideEvent,r=i.focusClass,a=i.hoverClass,l=i.openClass;return this.dropdown.on({"click.selectBoxIt":function(){i.dropdown.trigger("focus",!0),i.originalElem.disabled||(i.triggerEvent("click"),o||n||i.toggle())},"mousedown.selectBoxIt":function(){t(this).data("mdown",!0),i.triggerEvent("mousedown"),o&&!n&&i.toggle()},"mouseup.selectBoxIt":function(){i.triggerEvent("mouseup")},"blur.selectBoxIt":function(){i.blur&&(i.triggerEvent("blur"),i.close(),t(this).removeClass(r))},"focus.selectBoxIt":function(e,s){var o=t(this).data("mdown");t(this).removeData("mdown"),o||s||setTimeout(function(){i.triggerEvent("tab-focus")},0),s||(t(this).hasClass(i.theme.disabled)||t(this).addClass(r),i.triggerEvent("focus"))},"keydown.selectBoxIt":function(t){var e=i._keyMappings[t.keyCode],s=i._keydownMethods()[e];s&&(s(),!i.options.keydownOpen||"up"!==e&&"down"!==e||i.open()),s&&"tab"!==e&&t.preventDefault()},"keypress.selectBoxIt":function(t){var e=t.charCode||t.keyCode,s=i._keyMappings[t.charCode||t.keyCode],o=String.fromCharCode(e);i.search&&(!s||s&&"space"===s)&&i.search(o,!0,!0),"space"===s&&t.preventDefault()},"mouseenter.selectBoxIt":function(){i.triggerEvent("mouseenter")},"mouseleave.selectBoxIt":function(){i.triggerEvent("mouseleave")}}),i.list.on({"mouseover.selectBoxIt":function(){i.blur=!1},"mouseout.selectBoxIt":function(){i.blur=!0},"focusin.selectBoxIt":function(){i.dropdown.trigger("focus",!0)}}),i.list.on({"mousedown.selectBoxIt":function(){i._update(t(this)),i.triggerEvent("option-click"),"false"===t(this).attr("data-disabled")&&"true"!==t(this).attr("data-preventclose")&&i.close(),setTimeout(function(){i.dropdown.trigger("focus",!0)},0)},"focusin.selectBoxIt":function(){i.listItems.not(t(this)).removeAttr("data-active"),t(this).attr("data-active","");var e=i.list.is(":hidden");(i.options.searchWhenHidden&&e||i.options.aggressiveChange||e&&i.options.selectWhenHidden)&&i._update(t(this)),t(this).addClass(r)},"mouseup.selectBoxIt":function(){o&&!n&&(i._update(t(this)),i.triggerEvent("option-mouseup"),"false"===t(this).attr("data-disabled")&&"true"!==t(this).attr("data-preventclose")&&i.close())},"mouseenter.selectBoxIt":function(){"false"===t(this).attr("data-disabled")&&(i.listItems.removeAttr("data-active"),t(this).addClass(r).attr("data-active",""),i.listItems.not(t(this)).removeClass(r),t(this).addClass(r),i.currentFocus=+t(this).attr("data-id"))},"mouseleave.selectBoxIt":function(){"false"===t(this).attr("data-disabled")&&(i.listItems.not(t(this)).removeClass(r).removeAttr("data-active"),t(this).addClass(r),i.currentFocus=+t(this).attr("data-id"))},"blur.selectBoxIt":function(){t(this).removeClass(r)}},".selectboxit-option"),i.list.on({"click.selectBoxIt":function(t){t.preventDefault()}},"a"),i.selectBox.on({"change.selectBoxIt, internal-change.selectBoxIt":function(t,o){var n,r;o||(n=i.list.find('li[data-val="'+i.originalElem.value+'"]')).length&&(i.listItems.eq(i.currentFocus).removeClass(i.focusClass),i.currentFocus=+n.attr("data-id")),n=i.listItems.eq(i.currentFocus),r=n.attr("data-selectedtext"),e=n.attr("data-text"),s=e||n.find("a").text(),i._setText(i.dropdownText,r||s),i.dropdownText.attr("data-val",i.originalElem.value),n.find("i").attr("class")&&(i.dropdownImage.attr("class",n.find("i").attr("class")).addClass("selectboxit-default-icon"),i.dropdownImage.attr("style",n.find("i").attr("style"))),i.triggerEvent("changed")},"disable.selectBoxIt":function(){i.dropdown.addClass(i.theme.disabled)},"enable.selectBoxIt":function(){i.dropdown.removeClass(i.theme.disabled)},"open.selectBoxIt":function(){var t,e=i.list.find("li[data-val='"+i.dropdownText.attr("data-val")+"']");e.length||(e=i.listItems.not("[data-disabled=true]").first()),i.currentFocus=+e.attr("data-id"),t=i.listItems.eq(i.currentFocus),i.dropdown.addClass(l).removeClass(a).addClass(r),i.listItems.removeClass(i.selectedClass).removeAttr("data-active").not(t).removeClass(r),t.addClass(i.selectedClass).addClass(r),i.options.hideCurrent&&(i.listItems.show(),t.hide())},"close.selectBoxIt":function(){i.dropdown.removeClass(l)},"blur.selectBoxIt":function(){i.dropdown.removeClass(r)},"mouseenter.selectBoxIt":function(){t(this).hasClass(i.theme.disabled)||i.dropdown.addClass(a)},"mouseleave.selectBoxIt":function(){i.dropdown.removeClass(a)},destroy:function(t){t.preventDefault(),t.stopPropagation()}}),i},_update:function(t){var e,s=this.options.defaultText||this.selectBox.attr("data-text"),i=this.listItems.eq(this.currentFocus);"false"===t.attr("data-disabled")&&(this.listItems.eq(this.currentFocus).attr("data-selectedtext"),e=i.attr("data-text"),e||i.text(),(s&&this.options.html?this.dropdownText.html()===s:this.dropdownText.text()===s)&&this.selectBox.val()===t.attr("data-val")?this.triggerEvent("change"):(this.selectBox.val(t.attr("data-val")),this.currentFocus=+t.attr("data-id"),this.originalElem.value!==this.dropdownText.attr("data-val")&&this.triggerEvent("change")))},_addClasses:function(t){this.focusClass=t.focus,this.hoverClass=t.hover;var e=t.button,s=t.list,i=t.arrow,o=t.container;this.openClass=t.open;return this.selectedClass="selectboxit-selected",this.downArrow.addClass(this.selectBox.attr("data-downarrow")||this.options.downArrowIcon||i),this.dropdownContainer.addClass(o),this.dropdown.addClass(e),this.list.addClass(s),this},refresh:function(t,e){return this._destroySelectBoxIt()._create(!0),e||this.triggerEvent("refresh"),this._callbackSupport(t),this},htmlEscape:function(t){return String(t).replace(/&/g,"&amp;").replace(/"/g,"&quot;").replace(/'/g,"&#39;").replace(/</g,"&lt;").replace(/>/g,"&gt;")},triggerEvent:function(t){var e=this.options.showFirstOption?this.currentFocus:this.currentFocus-1>=0?this.currentFocus:0;return this.selectBox.trigger(t,{selectbox:this.selectBox,selectboxOption:this.selectItems.eq(e),dropdown:this.dropdown,dropdownOption:this.listItems.eq(this.currentFocus)}),this},_copyAttributes:function(){return this._addSelectBoxAttributes&&this._addSelectBoxAttributes(),this},_realOuterWidth:function(t){if(t.is(":visible"))return t.outerWidth(!0);var e,s=t.clone();return s.css({visibility:"hidden",display:"block",position:"absolute"}).appendTo("body"),e=s.outerWidth(!0),s.remove(),e}});var o=t.selectBox.selectBoxIt.prototype;o.add=function(e,s){this._populate(e,function(e){var i,o,n=this,r=t.type(e),a=0,l=[],d=n._isJSON(e),c=d&&n._parseJSON(e);if(e&&("array"===r||d&&c.data&&"array"===t.type(c.data))||"object"===r&&e.data&&"array"===t.type(e.data)){for(n._isJSON(e)&&(e=c),e.data&&(e=e.data),o=e.length;a<=o-1;a+=1)i=e[a],t.isPlainObject(i)?l.push(t("<option/>",i)):"string"===t.type(i)&&l.push(t("<option/>",{text:i,value:i}));n.selectBox.append(l)}else e&&"string"===r&&!n._isJSON(e)?n.selectBox.append(e):e&&"object"===r?n.selectBox.append(t("<option/>",e)):e&&n._isJSON(e)&&t.isPlainObject(n._parseJSON(e))&&n.selectBox.append(t("<option/>",n._parseJSON(e)));return n.dropdown?n.refresh(function(){n._callbackSupport(s)},!0):n._callbackSupport(s),n})},o._parseJSON=function(e){return JSON&&JSON.parse&&JSON.parse(e)||t.parseJSON(e)},o._isJSON=function(t){try{return this._parseJSON(t),!0}catch(t){return!1}},o._populate=function(e,s){var i=this;return e=t.isFunction(e)?e.call():e,i.isDeferred(e)?e.done(function(t){s.call(i,t)}):s.call(i,e),i},o._ariaAccessibility=function(){var e=this,s=t("label[for='"+e.originalElem.id+"']");return e.dropdownContainer.attr({role:"combobox","aria-autocomplete":"list","aria-haspopup":"true","aria-expanded":"false","aria-owns":e.list[0].id}),e.dropdownText.attr({"aria-live":"polite"}),e.dropdown.on({"disable.selectBoxIt":function(){e.dropdownContainer.attr("aria-disabled","true")},"enable.selectBoxIt":function(){e.dropdownContainer.attr("aria-disabled","false")}}),s.length&&e.dropdownContainer.attr("aria-labelledby",s[0].id),e.list.attr({role:"listbox","aria-hidden":"true"}),e.listItems.attr({role:"option"}),e.selectBox.on({"open.selectBoxIt":function(){e.list.attr("aria-hidden","false"),e.dropdownContainer.attr("aria-expanded","true")},"close.selectBoxIt":function(){e.list.attr("aria-hidden","true"),e.dropdownContainer.attr("aria-expanded","false")}}),e},o._addSelectBoxAttributes=function(){var e=this;return e._addAttributes(e.selectBox.prop("attributes"),e.dropdown),e.selectItems.each(function(s){e._addAttributes(t(this).prop("attributes"),e.listItems.eq(s))}),e},o._addAttributes=function(e,s){var i=this.options.copyAttributes;return e.length&&t.each(e,function(e,o){var n=o.name.toLowerCase(),r=o.value;"null"===r||-1===t.inArray(n,i)&&-1===n.indexOf("data")||s.attr(n,r)}),this},o.destroy=function(t){return this._destroySelectBoxIt(),this.widgetProto.destroy.call(this),this._callbackSupport(t),this},o._destroySelectBoxIt=function(){return this.dropdown.off(".selectBoxIt"),t.contains(this.dropdownContainer[0],this.originalElem)&&this.dropdownContainer.before(this.selectBox),this.dropdownContainer.remove(),this.selectBox.removeAttr("style").attr("style",this.selectBoxStyles),this.triggerEvent("destroy"),this},o.disable=function(t){return this.options.disabled||(this.close(),this.selectBox.attr("disabled","disabled"),this.dropdown.removeAttr("tabindex").removeClass(this.theme.enabled).addClass(this.theme.disabled),this.setOption("disabled",!0),this.triggerEvent("disable")),this._callbackSupport(t),this},o.disableOption=function(e,s){var i,o,n,r=t.type(e);return"number"===r&&(this.close(),i=this.selectBox.find("option").eq(e),this.triggerEvent("disable-option"),i.attr("disabled","disabled"),this.listItems.eq(e).attr("data-disabled","true").addClass(this.theme.disabled),this.currentFocus===e&&(o=this.listItems.eq(this.currentFocus).nextAll("li").not("[data-disabled='true']").first().length,n=this.listItems.eq(this.currentFocus).prevAll("li").not("[data-disabled='true']").first().length,o?this.moveDown():n?this.moveUp():this.disable())),this._callbackSupport(s),this},o._isDisabled=function(t){return this.originalElem.disabled&&this.disable(),this},o._dynamicPositioning=function(){if("number"===t.type(this.listSize))this.list.css("max-height",this.maxHeight||"none");else{var e=this.dropdown.offset().top,s=this.list.data("max-height")||this.list.outerHeight(),i=this.dropdown.outerHeight(),o=this.options.viewport,n=o.height(),r=t.isWindow(o.get(0))?o.scrollTop():o.offset().top,a=e+i+s<=n+r,l=!a;if(this.list.data("max-height")||this.list.data("max-height",this.list.outerHeight()),l)if(this.dropdown.offset().top-r>=s)this.list.css("max-height",s),this.list.css("top",this.dropdown.position().top-this.list.outerHeight());else{var d=Math.abs(e+i+s-(n+r)),c=Math.abs(this.dropdown.offset().top-r-s);d<c?(this.list.css("max-height",s-d-i/2),this.list.css("top","auto")):(this.list.css("max-height",s-c-i/2),this.list.css("top",this.dropdown.position().top-this.list.outerHeight()))}else this.list.css("max-height",s),this.list.css("top","auto")}return this},o.enable=function(t){return this.options.disabled&&(this.triggerEvent("enable"),this.selectBox.removeAttr("disabled"),this.dropdown.attr("tabindex",0).removeClass(this.theme.disabled).addClass(this.theme.enabled),this.setOption("disabled",!1),this._callbackSupport(t)),this},o.enableOption=function(e,s){var i,o=t.type(e);return"number"===o&&(i=this.selectBox.find("option").eq(e),this.triggerEvent("enable-option"),i.removeAttr("disabled"),this.listItems.eq(e).attr("data-disabled","false").removeClass(this.theme.disabled)),this._callbackSupport(s),this},o.moveDown=function(t){this.currentFocus+=1;var e="true"===this.listItems.eq(this.currentFocus).attr("data-disabled"),s=this.listItems.eq(this.currentFocus).nextAll("li").not("[data-disabled='true']").first().length;if(this.currentFocus===this.listItems.length)this.currentFocus-=1;else{if(e&&s)return this.listItems.eq(this.currentFocus-1).blur(),void this.moveDown();e&&!s?this.currentFocus-=1:(this.listItems.eq(this.currentFocus-1).blur().end().eq(this.currentFocus).focusin(),this._scrollToView("down"),this.triggerEvent("moveDown"))}return this._callbackSupport(t),this},o.moveUp=function(t){this.currentFocus-=1;var e="true"===this.listItems.eq(this.currentFocus).attr("data-disabled"),s=this.listItems.eq(this.currentFocus).prevAll("li").not("[data-disabled='true']").first().length;if(-1===this.currentFocus)this.currentFocus+=1;else{if(e&&s)return this.listItems.eq(this.currentFocus+1).blur(),void this.moveUp();e&&!s?this.currentFocus+=1:(this.listItems.eq(this.currentFocus+1).blur().end().eq(this.currentFocus).focusin(),this._scrollToView("up"),this.triggerEvent("moveUp"))}return this._callbackSupport(t),this},o._setCurrentSearchOption=function(t){return(this.options.aggressiveChange||this.options.selectWhenHidden||this.listItems.eq(t).is(":visible"))&&!0!==this.listItems.eq(t).data("disabled")&&(this.listItems.eq(this.currentFocus).blur(),this.currentIndex=t,this.currentFocus=t,this.listItems.eq(this.currentFocus).focusin(),this._scrollToView("search"),this.triggerEvent("search")),this},o._searchAlgorithm=function(t,e){var s,i,o,n,r=!1,a=this.textArray,l=this.currentText;for(s=t,o=a.length;s<o;s+=1){for(n=a[s],i=0;i<o;i+=1)-1!==a[i].search(e)&&(r=!0,i=o);if(r||(this.currentText=this.currentText.charAt(this.currentText.length-1).replace(/[|()\[{.+*?$\\]/g,"\\$0"),l=this.currentText),e=new RegExp(l,"gi"),l.length<3){if(e=new RegExp(l.charAt(0),"gi"),-1!==n.charAt(0).search(e))return this._setCurrentSearchOption(s),(n.substring(0,l.length).toLowerCase()!==l.toLowerCase()||this.options.similarSearch)&&(this.currentIndex+=1),!1}else if(-1!==n.search(e))return this._setCurrentSearchOption(s),!1;if(n.toLowerCase()===this.currentText.toLowerCase())return this._setCurrentSearchOption(s),this.currentText="",!1}return!0},o.search=function(t,e,s){s?this.currentText+=t.replace(/[|()\[{.+*?$\\]/g,"\\$0"):this.currentText=t.replace(/[|()\[{.+*?$\\]/g,"\\$0");var i=this._searchAlgorithm(this.currentIndex,new RegExp(this.currentText,"gi"));return i&&this._searchAlgorithm(0,this.currentText),this._callbackSupport(e),this},o._updateMobileText=function(){var t,e,s;t=this.selectBox.find("option").filter(":selected"),e=t.attr("data-text"),s=e||t.text(),this._setText(this.dropdownText,s),this.list.find('li[data-val="'+t.val()+'"]').find("i").attr("class")&&this.dropdownImage.attr("class",this.list.find('li[data-val="'+t.val()+'"]').find("i").attr("class")).addClass("selectboxit-default-icon")},o._applyNativeSelect=function(){return this.dropdownContainer.append(this.selectBox),this.dropdown.attr("tabindex","-1"),this.selectBox.css({display:"block",visibility:"visible",width:this._realOuterWidth(this.dropdown),height:this.dropdown.outerHeight(),opacity:"0",position:"absolute",top:"0",left:"0",cursor:"pointer","z-index":"999999",margin:this.dropdown.css("margin"),padding:"0","-webkit-appearance":"menulist-button"}),this.originalElem.disabled&&this.triggerEvent("disable"),this},o._mobileEvents=function(){var t=this;t.selectBox.on({"changed.selectBoxIt":function(){t.hasChanged=!0,t._updateMobileText(),t.triggerEvent("option-click")},"mousedown.selectBoxIt":function(){t.hasChanged||!t.options.defaultText||t.originalElem.disabled||(t._updateMobileText(),t.triggerEvent("option-click"))},"enable.selectBoxIt":function(){t.selectBox.removeClass("selectboxit-rendering")},"disable.selectBoxIt":function(){t.selectBox.addClass("selectboxit-rendering")}})},o._mobile=function(t){return this.isMobile&&(this._applyNativeSelect(),this._mobileEvents()),this},o.remove=function(e,s){var i,o,n=this,r=t.type(e),a=0,l="";if("array"===r){for(o=e.length;a<=o-1;a+=1)i=e[a],"number"===t.type(i)&&(l.length?l+=", option:eq("+i+")":l+="option:eq("+i+")");n.selectBox.find(l).remove()}else"number"===r?n.selectBox.find("option").eq(e).remove():n.selectBox.find("option").remove();return n.dropdown?n.refresh(function(){n._callbackSupport(s)},!0):n._callbackSupport(s),n},o.selectOption=function(e,s){var i=t.type(e);return"number"===i?this.selectBox.val(this.selectItems.eq(e).val()).change():"string"===i&&this.selectBox.val(e).change(),this._callbackSupport(s),this},o.setOption=function(e,s,i){var o=this;return"string"===t.type(e)&&(o.options[e]=s),o.refresh(function(){o._callbackSupport(i)},!0),o},o.setOptions=function(e,s){var i=this;return t.isPlainObject(e)&&(i.options=t.extend({},i.options,e)),i.refresh(function(){i._callbackSupport(s)},!0),i},o.wait=function(t,e){return this.widgetProto._delay.call(this,e,t),this}}(window.jQuery,window,document)}();