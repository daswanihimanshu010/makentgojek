var daterangepicker_format=$('meta[name="daterangepicker_format"]').attr("content"),datepicker_format=$('meta[name="datepicker_format"]').attr("content"),datedisplay_format=$('meta[name="datedisplay_format"]').attr("content");$("#input_dob").datepicker({dateFormat:"dd-mm-yy"});var night_value=$("#night").val(),cleaning_value=$("#cleaning").val(),additional=$("#additional_guest").val(),guests=$("#guests").val(),security_fee=$("#security").val(),weekend_price=$("#weekend").val(),week=$("#week").val(),month=$("#month").val(),currency_code=$("#currency_code").find("option:selected").prop("value");function step(e){$(".frm").hide(),$("#sf"+e).show(),$(".tab_btn").removeAttr("disabled"),$(".tab_btn#tab_btn_"+e).attr("disabled","disabled")}app.controller("rooms_admin",["$scope","$http","$compile","$filter",function($scope,$http,$compile,$filter){var v=$("#add_room_form").validate({ignore:":hidden:not(.do-not-ignore)",rules:{calendar:{required:!0},bedrooms:{required:!0},beds:{required:!0},bed_type:{required:!0},bathrooms:{required:!0},property_type:{required:!0},room_type:{required:!0},accommodates:{required:!0},"name[]":{required:!0},"summary[]":{required:!0},"language[]":{required:!0},country:{required:!0},address_line_1:{required:!0},city:{required:!0},state:{required:!0},latitude:{required:{depends:function(e){return address_line_1=$("#address_line_1").val(),!("4"!=$scope.step_id||!address_line_1)}}},night:{required:!0,digits:!0,min:1},cleaning:{digits:!0},additional_guest:{digits:!0},guests:{digits:!0},security:{digits:!0},weekend:{digits:!0},week:{digits:!0},month:{digits:!0},video:{youtube:!0},"photos[]":{required:{depends:function(e){return 0==$("#js-photo-grid li").length}},extension:"png|jpg|jpeg|gif"},cancel_policy:{required:!0},user_id:{required:!0}},messages:{night:{min:jQuery.validator.format("Please enter a value greater than 0")},latitude:{required:"Please choose the address from the google results."}},errorElement:"span",errorClass:"text-danger",errorPlacement:function(e,t){"container"===t.attr("data-error-placement")?(container=t.attr("data-error-container"),$(container).append(e)):e.insertAfter(t)},extension:"Only png file is allowed!"}),autocomplete;function next(e){v.form()&&(11!=e?($(".frm").hide(),$("#sf"+(e+1)).show()):document.getElementById("add_room_form").submit())}function back(e){$(".frm").hide(),$("#sf"+(e-1)).show()}function initAutocomplete(){(autocomplete=new google.maps.places.Autocomplete(document.getElementById("address_line_1"),{types:["geocode"]})).addListener("place_changed",fillInAddress)}function fillInAddress(){$scope.autocomplete_used=!0,fetchMapAddress(autocomplete.getPlace())}function fetchMapAddress(e){"street_address"==e.types&&($scope.location_found=!0);var t={street_number:"short_name",route:"long_name",sublocality_level_1:"long_name",sublocality:"long_name",locality:"long_name",administrative_area_level_1:"long_name",country:"short_name",postal_code:"short_name"};$("#city").val(""),$("#state").val(""),$("#country").val(""),$("#address_line_1").val(""),$("#address_line_2").val(""),$("#postal_code").val("");var a=e;$scope.street_number="";for(var i=0;i<a.address_components.length;i++){var o=a.address_components[i].types[0];if(t[o]){var s=a.address_components[i][t[o]];if("street_number"==o&&($scope.street_number=s),"route"==o)var n=$scope.street_number+" "+s;$("#address_line_1").val($.trim(n)),"postal_code"==o&&$("#postal_code").val(s),"locality"==o&&$("#city").val(s),"administrative_area_level_1"==o&&$("#state").val(s),"country"==o&&$("#country").val(s)}}$("#address_line_1").val();var r=a.geometry.location.lat(),d=a.geometry.location.lng();""==$("#address_line_1").val()&&$("#address_line_1").val($("#city").val()),""==$("#city").val()&&$("#city").val(""),""==$("#state").val()&&$("#state").val(""),""==$("#postal_code").val()&&$("#postal_code").val(""),$("#latitude").val(r),$("#longitude").val(d)}function disableAdditionalGuestCharge(){"0"==$("#additional_guest").val()?$("#guests").prop("disabled",!0):$("#guests").prop("disabled",!1)}$.validator.addMethod("extension",function(e,t,a){return a="string"==typeof a?a.replace(/,/g,"|"):"png|jpe?g|gif",this.optional(t)||e.match(new RegExp(".("+a+")$","i"))},$.validator.format("Please upload the images like JPG,JPEG,PNG,GIF File Only.")),$(".frm").hide(),$(".frm#sf1").show(),$scope.steps=["1","2","3","4","5","6","7","8","12","13","9","10","11"],$scope.add_steps=["2","3","4","5","6","7","8","12","13","9","10","11"],$scope.step_name="",$scope.step=0,$scope.go_to_step=function(e){step_id=$scope.steps[e],$scope.step_id=step_id,$(".frm").hide(),$("#sf"+step_id).show(),$scope.step_name=$("#sf"+step_id).attr("data-step-name"),$scope.step=e,$("#input_current_step_id").val(step_id),$("#input_current_step").val(e)},$scope.go_to_edit_step=function(e){$(".frm").hide(),$("#sf"+e).show(),$scope.step_id=e,$(".tab_btn").removeAttr("disabled"),$(".tab_btn#tab_btn_"+e).attr("disabled","disabled")},$scope.go_to_step($scope.step),$scope.add_room_steps=function(){$scope.steps=$scope.add_steps,$scope.go_to_step($scope.step)},$scope.next_step=function(e){current_step=$scope.steps[e],v.form()&&("11"!=current_step?($scope.step=next_step=e+1,$scope.go_to_step(next_step)):$("#add_room_form").submit())},$scope.back_step=function(e){$scope.step=next_step=e-1,$scope.go_to_step(next_step)},$scope.get_step_name=function(e){return step_id=$scope.steps[e],step_name=$("#sf"+step_id).attr("data-step-name"),step_name},initAutocomplete(),$scope.rows=[],$(document).ready(function(){var e=$("#room_id").val();$http.post(APP_URL+"/get_lang_details/"+e,{}).then(function(e){$scope.rows=e.data,$http.post(APP_URL+"/get_lang",{}).then(function(e){$scope.lang_list=e.data})})}),$scope.location_found=!1,$scope.autocomplete_used=!1,$scope.addNewRow=function(){var e=$scope.rows.length+1;$scope.rows.push({id:"rows"+e})},$scope.removeRow=function(name){for(var index=name,comArr=eval($scope.rows),i=0;i<comArr.length;i++)if(comArr[i].name===name){index=i;break}$scope.rows.splice(index,1)},$("#username").autocomplete({source:APP_URL+"/"+ADMIN_URL+"/rooms/users_list",select:function(e,t){$("#user_id").val(t.item.id)}}),$(document).on("click",".month-nav",function(){var e=$(this).attr("data-month"),t=$(this).attr("data-year"),a={};a.month=e,a.year=t;var i=JSON.stringify(a);return $http.post(APP_URL+"/"+ADMIN_URL+"/ajax_calendar/"+$("#room_id").val(),{data:i}).then(function(e){$("#ajax_container").html($compile(e.data)($scope))}),!1}),$(document).on("change","#calendar_dropdown",function(){var e=$(this).val(),t=e.split("-")[0],a=e.split("-")[1],i={};i.month=a,i.year=t;var o=JSON.stringify(i);return $http.post(APP_URL+"/"+ADMIN_URL+"/ajax_calendar/"+$("#room_id").val(),{data:o}).then(function(e){$("#ajax_container").html($compile(e.data)($scope))}),!1}),$(document).on("click",".delete-photo-btn",function(){var e=$(this).attr("data-photo-id"),t=$("#room_id").val();$('[id^="photo_li_"]').size()>1?$http.post(APP_URL+"/"+ADMIN_URL+"/delete_photo",{photo_id:e,room_id:t}).then(function(t){"true"==t.data.success&&$("#photo_li_"+e).remove()}):alert("You cannnot delete last photo. Please upload alternate photos and delete this photo.")}),$(document).on("click",".featured-photo-btn",function(){var e=$(this).attr("data-featured-id"),t=$("input[id=room_id]").val();$http.post(APP_URL+"/"+ADMIN_URL+"/featured_image",{id:t,photo_id:e}).then(function(e){"true"==e.data.success&&alert("success")})}),$(document).on("keyup",".highlights",function(){var e=$(this).val(),t=$(this).attr("data-photo-id");$("#saved_message").fadeIn(),$http.post(APP_URL+"/"+ADMIN_URL+"/photo_highlights",{photo_id:t,data:e}).then(function(e){$("#saved_message").fadeOut()})}),$(document).on("change","#additional_guest",function(){disableAdditionalGuestCharge()}),disableAdditionalGuestCharge(),$.validator.addMethod("youtube",function(e,t){if(null!=e&&e.length>0){var a=e.match(/^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=|\?v=)([^#\&\?]*).*/);return!(!a||11!=a[2].length)}return!0},"Please select a valid youtube url."),$.validator.addMethod("maximum_stay_value",function(e,t,a){return min_elem=$(t).attr("data-minimum_stay"),min_value=$(min_elem).val(),!(min_value-0>e-0&&""!=min_value&&""!=e)},$.validator.format("Maximum stay must be greater than Minimum stay")),$.validator.addClassRules({discount:{digits:!0,required:!0,min:1,max:99},early_bird_period:{digits:!0,required:!0,min:30,max:1080},last_min_period:{digits:!0,required:!0,min:1,max:28},minimum_stay:{digits:!0,min:1},maximum_stay:{digits:!0,min:1,maximum_stay_value:!0},availability_minimum_stay:{digits:!0,min:1},availability_maximum_stay:{required:{depends:function(e){return min_elem=$(e).attr("data-minimum_stay"),min_value=$(min_elem).val(),""==min_value}},digits:!0,min:1,maximum_stay_value:!0}}),$scope.add_price_rule=function(e){"length_of_stay"==e?(new_period=$scope.length_of_stay_period_select,$scope.length_of_stay_items.push({period:new_period-0}),$scope.length_of_stay_period_select=""):"early_bird"==e?$scope.early_bird_items.push({period:""}):"last_min"==e&&$scope.last_min_items.push({period:""})},$scope.remove_price_rule=function(e,t){"length_of_stay"==e?(item=$scope.length_of_stay_items[t],$scope.length_of_stay_items.splice(t,1)):"early_bird"==e?(item=$scope.early_bird_items[t],$scope.early_bird_items.splice(t,1)):"last_min"==e&&(item=$scope.last_min_items[t],$scope.last_min_items.splice(t,1)),""!=item.id&&item.id&&($("."+e+"_wrapper").addClass("loading"),$('button[type="submit"]').attr("disabled",!0),$http.post(APP_URL+"/"+ADMIN_URL+"/rooms/delete_price_rule/"+item.id,{}).then(function(t){$("."+e+"_wrapper").removeClass("loading"),$('button[type="submit"]').removeAttr("disabled")}))},$scope.length_of_stay_option_avaialble=function(e){var t=$filter("filter")($scope.length_of_stay_items,{period:e},!0),a=$filter("filter")($scope.length_of_stay_items,{period:""+e},!0);return!t.length&&!a.length},$scope.add_availability_rule=function(){$scope.availability_rules.push({type:""}),setTimeout(function(){$scope.availability_datepickers()},20)},$scope.remove_availability_rule=function(e){item=$scope.availability_rules[e],type="availability_rules",""!=item.id&&item.id&&($("."+type+"_wrapper").addClass("loading"),$('button[type="submit"]').attr("disabled",!0),$http.post(APP_URL+"/"+ADMIN_URL+"/rooms/delete_availability_rule/"+item.id,{}).then(function(e){$("."+type+"_wrapper").removeClass("loading"),$('button[type="submit"]').removeAttr("disabled")})),$scope.availability_rules.splice(e,1)},$scope.availability_rules_type_change=function(e){rule=$scope.availability_rules[e],"custom"!=rule.type&&(this_elem=$("#availability_rules_"+e+"_type option:selected"),start_date=this_elem.attr("data-start_date"),end_date=this_elem.attr("data-end_date"),$scope.availability_rules[e].start_date=start_date,$scope.availability_rules[e].end_date=end_date)},$scope.availability_datepickers=function(){$scope.availability_rules&&$.each($scope.availability_rules,function(e,t){var a=$("#availability_rules_"+e+"_start_date"),i=$("#availability_rules_"+e+"_end_date");a.datepicker({minDate:0,dateFormat:datepicker_format,onSelect:function(e,t){var o=a.datepicker("getDate");o.setDate(o.getDate()+1),i.datepicker("option","minDate",o)}}),i.datepicker({minDate:1,dateFormat:datepicker_format})})},$scope.copy_data=function(e){return angular.copy(e)},$(document).ready(function(){$scope.availability_datepickers()})}]);