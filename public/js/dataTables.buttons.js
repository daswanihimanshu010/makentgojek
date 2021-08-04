!function(t,n,e){var i=function(e,i){"use strict";var o,s=0,r=0,a=i.ext.buttons,u=function(t,n){!0===n&&(n={}),e.isArray(n)&&(n={buttons:n}),this.c=e.extend(!0,{},u.defaults,n),n.buttons&&(this.c.buttons=n.buttons),this.s={dt:new i.Api(t),buttons:[],subButtons:[],listenKeys:"",namespace:"dtb"+s++},this.dom={container:e("<"+this.c.dom.container.tag+"/>").addClass(this.c.dom.container.className)},this._constructor()};e.extend(u.prototype,{action:function(t,n){var e=this._indexToButton(t).conf;return void 0===n?e.action:(e.action=n,this)},active:function(t,n){return this._indexToButton(t).node.toggleClass(this.c.dom.button.active,void 0===n||n),this},add:function(t,n){if("string"==typeof t&&-1!==t.indexOf("-")){var e=t.split("-");this.c.buttons[1*e[0]].buttons.splice(1*e[1],0,n)}else this.c.buttons.splice(1*t,0,n);return this.dom.container.empty(),this._buildButtons(this.c.buttons),this},container:function(){return this.dom.container},disable:function(t){return this._indexToButton(t).node.addClass(this.c.dom.button.disabled),this},destroy:function(){e("body").off("keyup."+this.s.namespace);var t,n,i,o,s=this.s.buttons,r=this.s.subButtons;for(t=0,n=s.length;t<n;t++)for(this.removePrep(t),i=0,o=r[t].length;i<o;i++)this.removePrep(t+"-"+i);this.removeCommit(),this.dom.container.remove();var a=this.s.dt.settings()[0];for(t=0,n=a.length;t<n;t++)if(a.inst===this){a.splice(t,1);break}return this},enable:function(t,n){return!1===n?this.disable(t):(this._indexToButton(t).node.removeClass(this.c.dom.button.disabled),this)},name:function(){return this.c.name},node:function(t){return this._indexToButton(t).node},removeCommit:function(){var t,n,e,i=this.s.buttons,o=this.s.subButtons;for(t=i.length-1;t>=0;t--)null===i[t]&&(i.splice(t,1),o.splice(t,1),this.c.buttons.splice(t,1));for(t=0,n=o.length;t<n;t++)for(e=o[t].length-1;e>=0;e--)null===o[t][e]&&(o[t].splice(e,1),this.c.buttons[t].buttons.splice(e,1));return this},removePrep:function(t){var n,e=this.s.dt;if("number"==typeof t||-1===t.indexOf("-"))(n=this.s.buttons[1*t]).conf.destroy&&n.conf.destroy.call(e.button(t),e,n,n.conf),n.node.remove(),this._removeKey(n.conf),this.s.buttons[1*t]=null;else{var i=t.split("-");(n=this.s.subButtons[1*i[0]][1*i[1]]).conf.destroy&&n.conf.destroy.call(e.button(t),e,n,n.conf),n.node.remove(),this._removeKey(n.conf),this.s.subButtons[1*i[0]][1*i[1]]=null}return this},text:function(t,n){var e=this._indexToButton(t),i=this.c.dom.buttonLiner.tag,o=this.s.dt,s=function(t){return"function"==typeof t?t(o,e.node,e.conf):t};return void 0===n?s(e.conf.text):(e.conf.text=n,i?e.node.children(i).html(s(n)):e.node.html(s(n)),this)},toIndex:function(t){var n,e,i,o,s=this.s.buttons,r=this.s.subButtons;for(n=0,e=s.length;n<e;n++)if(s[n].node[0]===t)return n+"";for(n=0,e=r.length;n<e;n++)for(i=0,o=r[n].length;i<o;i++)if(r[n][i].node[0]===t)return n+"-"+i},_constructor:function(){var t=this,i=this.s.dt,o=i.settings()[0];o._buttons||(o._buttons=[]),o._buttons.push({inst:this,name:this.c.name}),this._buildButtons(this.c.buttons),i.on("destroy",function(){t.destroy()}),e("body").on("keyup."+this.s.namespace,function(e){if(!n.activeElement||n.activeElement===n.body){var i=String.fromCharCode(e.keyCode).toLowerCase();-1!==t.s.listenKeys.toLowerCase().indexOf(i)&&t._keypress(i,e)}})},_addKey:function(t){t.key&&(this.s.listenKeys+=e.isPlainObject(t.key)?t.key.key:t.key)},_buildButtons:function(t,n,i){var o=this.s.dt;n||(n=this.dom.container,this.s.buttons=[],this.s.subButtons=[]);for(var s=0,r=t.length;s<r;s++){var a=this._resolveExtends(t[s]);if(a)if(e.isArray(a))this._buildButtons(a,n,i);else{var u=this._buildButton(a,void 0!==i);if(u){var c=u.node;if(n.append(u.inserter),void 0===i?(this.s.buttons.push({node:c,conf:a,inserter:u.inserter}),this.s.subButtons.push([])):this.s.subButtons[i].push({node:c,conf:a,inserter:u.inserter}),a.buttons){var l=this.c.dom.collection;a._collection=e("<"+l.tag+"/>").addClass(l.className),this._buildButtons(a.buttons,a._collection,s)}a.init&&a.init.call(o.button(c),o,c,a)}}}},_buildButton:function(t,n){var i=this.c.dom.button,o=this.c.dom.buttonLiner,s=this.c.dom.collection,a=this.s.dt,u=function(n){return"function"==typeof n?n(a,c,t):n};if(n&&s.button&&(i=s.button),n&&s.buttonLiner&&(o=s.buttonLiner),t.available&&!t.available(a,t))return!1;var c=e("<"+i.tag+"/>").addClass(i.className).attr("tabindex",this.s.dt.settings()[0].iTabIndex).attr("aria-controls",this.s.dt.table().node().id).on("click.dtb",function(n){n.preventDefault(),!c.hasClass(i.disabled)&&t.action&&t.action.call(a.button(c),n,a,c,t),c.blur()}).on("keyup.dtb",function(n){13===n.keyCode&&!c.hasClass(i.disabled)&&t.action&&t.action.call(a.button(c),n,a,c,t)});o.tag?c.append(e("<"+o.tag+"/>").html(u(t.text)).addClass(o.className)):c.html(u(t.text)),!1===t.enabled&&c.addClass(i.disabled),t.className&&c.addClass(t.className),t.namespace||(t.namespace=".dt-button-"+r++);var l,d=this.c.dom.buttonContainer;return l=d?e("<"+d.tag+"/>").addClass(d.className).append(c):c,this._addKey(t),{node:c,inserter:l}},_indexToButton:function(t){if("number"==typeof t||-1===t.indexOf("-"))return this.s.buttons[1*t];var n=t.split("-");return this.s.subButtons[1*n[0]][1*n[1]]},_keypress:function(t,n){var i,o,s,r,a=this.s.buttons,u=this.s.subButtons,c=function(i,o){if(i.key)if(i.key===t)o.click();else if(e.isPlainObject(i.key)){if(i.key.key!==t)return;if(i.key.shiftKey&&!n.shiftKey)return;if(i.key.altKey&&!n.altKey)return;if(i.key.ctrlKey&&!n.ctrlKey)return;if(i.key.metaKey&&!n.metaKey)return;o.click()}};for(i=0,o=a.length;i<o;i++)c(a[i].conf,a[i].node);for(i=0,o=u.length;i<o;i++)for(s=0,r=u[i].length;s<r;s++)c(u[i][s].conf,u[i][s].node)},_removeKey:function(t){if(t.key){var n=e.isPlainObject(t.key)?t.key.key:t.key,i=this.s.listenKeys.split(""),o=e.inArray(n,i);i.splice(o,1),this.s.listenKeys=i.join("")}},_resolveExtends:function(t){var n,i,o=this.s.dt,s=function(n){for(var i=0;!e.isPlainObject(n)&&!e.isArray(n);){if("function"==typeof n){if(!(n=n(o,t)))return!1}else if("string"==typeof n){if(!a[n])throw"Unknown button type: "+n;n=a[n]}if(++i>30)throw"Buttons: Too many iterations"}return e.isArray(n)?n:e.extend({},n)};for(t=s(t);t&&t.extend;){var r=s(a[t.extend]);if(e.isArray(r))return r;var u=r.className;t=e.extend({},r,t),u&&t.className!==u&&(t.className=u+" "+t.className);var c=t.postfixButtons;if(c){for(t.buttons||(t.buttons=[]),n=0,i=c.length;n<i;n++)t.buttons.push(c[n]);t.postfixButtons=null}var l=t.prefixButtons;if(l){for(t.buttons||(t.buttons=[]),n=0,i=l.length;n<i;n++)t.buttons.splice(n,0,l[n]);t.prefixButtons=null}t.extend=r.extend}return t}}),u.background=function(t,n,i){void 0===i&&(i=400),t?e("<div/>").addClass(n).css("display","none").appendTo("body").fadeIn(i):e("body > div."+n).fadeOut(i,function(){e(this).remove()})},u.instanceSelector=function(t,n){if(!t)return e.map(n,function(t){return t.inst});var i=[],o=e.map(n,function(t){return t.name}),s=function(t){if(e.isArray(t))for(var r=0,a=t.length;r<a;r++)s(t[r]);else if("string"==typeof t)if(-1!==t.indexOf(","))s(t.split(","));else{var u=e.inArray(e.trim(t),o);-1!==u&&i.push(n[u].inst)}else"number"==typeof t&&i.push(n[t].inst)};return s(t),i},u.buttonSelector=function(t,n){for(var i=[],o=function(t,n){var s,r,a=[];e.each(n.s.buttons,function(t,n){null!==n&&a.push({node:n.node[0],name:n.name})}),e.each(n.s.subButtons,function(t,n){e.each(n,function(t,n){null!==n&&a.push({node:n.node[0],name:n.name})})});var u=e.map(a,function(t){return t.node});if(e.isArray(t)||t instanceof e)for(s=0,r=t.length;s<r;s++)o(t[s],n);else if(null==t||"*"===t)for(s=0,r=a.length;s<r;s++)i.push({inst:n,idx:n.toIndex(a[s].node)});else if("number"==typeof t)i.push({inst:n,idx:t});else if("string"==typeof t)if(-1!==t.indexOf(",")){var c=t.split(",");for(s=0,r=c.length;s<r;s++)o(e.trim(c[s]),n)}else if(t.match(/^\d+(\-\d+)?$/))i.push({inst:n,idx:t});else if(-1!==t.indexOf(":name")){var l=t.replace(":name","");for(s=0,r=a.length;s<r;s++)a[s].name===l&&i.push({inst:n,idx:n.toIndex(a[s].node)})}else e(u).filter(t).each(function(){i.push({inst:n,idx:n.toIndex(this)})});else if("object"==typeof t&&t.nodeName){var d=e.inArray(t,u);-1!==d&&i.push({inst:n,idx:n.toIndex(u[d])})}},s=0,r=t.length;s<r;s++){var a=t[s];o(n,a)}return i},u.defaults={buttons:["copy","excel","csv","pdf","print"],name:"main",tabIndex:0,dom:{container:{tag:"div",className:"dt-buttons"},collection:{tag:"div",className:"dt-button-collection"},button:{tag:"a",className:"dt-button",active:"active",disabled:"disabled"},buttonLiner:{tag:"span",className:""}}},u.version="1.0.3",e.extend(a,{collection:{text:function(t,n,e){return t.i18n("buttons.collection","Collection")},className:"buttons-collection",action:function(i,o,s,r){var a=s,c=a.offset(),l=e(o.table().container());if(r._collection.addClass(r.collectionLayout).css("display","none").appendTo("body").fadeIn(r.fade),"absolute"===r._collection.css("position")){r._collection.css({top:c.top+a.outerHeight(),left:c.left});var d=c.left+r._collection.outerWidth(),f=l.offset().left+l.width();d>f&&r._collection.css("left",c.left-(d-f))}else{var h=r._collection.height()/2;h>e(t).height()/2&&(h=e(t).height()/2),r._collection.css("marginTop",-1*h)}r.background&&u.background(!0,r.backgroundClassName,r.fade),setTimeout(function(){e(n).on("click.dtb-collection",function(t){e(t.target).parents().andSelf().filter(r._collection).length||(r._collection.fadeOut(r.fade,function(){r._collection.detach()}),u.background(!1,r.backgroundClassName,r.fade),e(n).off("click.dtb-collection"))})},10)},background:!0,collectionLayout:"",backgroundClassName:"dt-button-background",fade:400},copy:function(t,n){return n.preferHtml&&a.copyHtml5?"copyHtml5":a.copyFlash&&a.copyFlash.available(t,n)?"copyFlash":a.copyHtml5?"copyHtml5":void 0},csv:function(t,n){return a.csvHtml5&&a.csvHtml5.available(t,n)?"csvHtml5":a.csvFlash&&a.csvFlash.available(t,n)?"csvFlash":void 0},excel:function(t,n){return a.excelHtml5&&a.excelHtml5.available(t,n)?"excelHtml5":a.excelFlash&&a.excelFlash.available(t,n)?"excelFlash":void 0},pdf:function(t,n){return a.pdfHtml5&&a.pdfHtml5.available(t,n)?"pdfHtml5":a.pdfFlash&&a.pdfFlash.available(t,n)?"pdfFlash":void 0}}),i.Api.register("buttons()",function(t,n){return void 0===n&&(n=t,t=void 0),this.iterator(!0,"table",function(e){if(e._buttons)return u.buttonSelector(u.instanceSelector(t,e._buttons),n)},!0)}),i.Api.register("button()",function(t,n){var e=this.buttons(t,n);return e.length>1&&e.splice(1,e.length),e}),i.Api.register(["buttons().active()","button().active()"],function(t){return this.each(function(n){n.inst.active(n.idx,t)})}),i.Api.registerPlural("buttons().action()","button().action()",function(t){return void 0===t?this.map(function(t){return t.inst.action(t.idx)}):this.each(function(n){n.inst.action(n.idx,t)})}),i.Api.register(["buttons().enable()","button().enable()"],function(t){return this.each(function(n){n.inst.enable(n.idx,t)})}),i.Api.register(["buttons().disable()","button().disable()"],function(){return this.each(function(t){t.inst.disable(t.idx)})}),i.Api.registerPlural("buttons().nodes()","button().node()",function(){var t=e();return e(this.each(function(n){t=t.add(n.inst.node(n.idx))})),t}),i.Api.registerPlural("buttons().text()","button().text()",function(t){return void 0===t?this.map(function(t){return t.inst.text(t.idx)}):this.each(function(n){n.inst.text(n.idx,t)})}),i.Api.registerPlural("buttons().trigger()","button().trigger()",function(){return this.each(function(t){t.inst.node(t.idx).trigger("click")})}),i.Api.registerPlural("buttons().containers()","buttons().container()",function(){var t=e();return e(this.each(function(n){t=t.add(n.inst.container())})),t}),i.Api.register("button().add()",function(t,n){return 1===this.length&&this[0].inst.add(t,n),this.button(t)}),i.Api.register("buttons().destroy()",function(t){return this.pluck("inst").unique().each(function(t){t.destroy()}),this}),i.Api.registerPlural("buttons().remove()","buttons().remove()",function(){return this.each(function(t){t.inst.removePrep(t.idx)}),this.pluck("inst").unique().each(function(t){t.removeCommit()}),this}),i.Api.register("buttons.info()",function(t,n,i){var s=this;return!1===t?(e("#datatables_buttons_info").fadeOut(function(){e(this).remove()}),clearTimeout(o),o=null,this):(o&&clearTimeout(o),e("#datatables_buttons_info").length&&e("#datatables_buttons_info").remove(),t=t?"<h2>"+t+"</h2>":"",e('<div id="datatables_buttons_info" class="dt-button-info"/>').html(t).append(e("<div/>")["string"==typeof n?"html":"append"](n)).css("display","none").appendTo("body").fadeIn(),void 0!==i&&0!==i&&(o=setTimeout(function(){s.buttons.info(!1)},i)),this)}),i.Api.register("buttons.exportData()",function(t){if(this.context.length)return c(new i.Api(this.context[0]),t)});var c=function(t,n){for(var i=e.extend(!0,{},{rows:null,columns:"",modifier:{search:"applied",order:"applied"},orthogonal:"display",stripHtml:!0,stripNewlines:!0,trim:!0},n),o=function(t){return"string"!=typeof t?t:(i.stripHtml&&(t=t.replace(/<.*?>/g,"")),i.trim&&(t=t.replace(/^\s+|\s+$/g,"")),i.stripNewlines&&(t=t.replace(/\n/g," ")),t)},s=t.columns(i.columns).indexes().map(function(n,e){return o(t.column(n).header().innerHTML)}).toArray(),r=t.table().footer()?t.columns(i.columns).indexes().map(function(n,e){var i=t.column(n).footer();return i?o(i.innerHTML):""}).toArray():null,a=t.cells(i.rows,i.columns,i.modifier).render(i.orthogonal).toArray(),u=s.length,c=a.length/u,l=new Array(c),d=0,f=0,h=c;f<h;f++){for(var b=new Array(u),p=0;p<u;p++)b[p]=o(a[d]),d++;l[f]=b}return{header:s,footer:r,body:l}};return e.fn.dataTable.Buttons=u,e.fn.DataTable.Buttons=u,e(n).on("init.dt.dtb",function(t,n,e){if("dt"===t.namespace){var o=n.oInit.buttons||i.defaults.buttons;o&&!n._buttons&&new u(n,o).container()}}),i.ext.feature.push({fnInit:function(t){var n=new i.Api(t),e=n.init().buttons;return new u(n,e).container()},cFeature:"B"}),u};"function"==typeof define&&define.amd?define(["jquery","datatables"],i):"object"==typeof exports?i(require("jquery"),require("datatables")):jQuery&&!jQuery.fn.dataTable.Buttons&&i(jQuery,jQuery.fn.dataTable)}(window,document);