/*! Thrive Leads - The ultimate Lead Capture solution for wordpress - 2017-02-15
* https://thrivethemes.com 
* Copyright (c) 2017 * Thrive Themes */
var ThriveLeads=ThriveLeads||{};ThriveLeads.objects={},ThriveLeads["const"]=ThriveLeadsConst,ThriveLeads.ajaxModal=function(a){if(!a.view&&!a.onLoad)throw new Error("missing view constructor or onLoad property");TVE_Dash.showLoader();var b=_.extend({type:"get"},a);jQuery.ajax(b).done(function(b){if("function"==typeof a.onLoad)return a.onLoad.call(null);var c=TVE_Dash._instantiate(a.view,a);c.template=_.template(b),c.render().open(a)}).always(function(){TVE_Dash.hideLoader()})},ThriveLeads.resize_thickbox=function(){var a,b=90*jQuery(window).height()/100,c=jQuery("#TB_window"),d=c.find("#TB_ajaxContent"),e=40;d.children().each(function(){var a=jQuery(this);return a.is("script")||a.is("style")||!a.is(":visible")?!0:void(e+=a.outerHeight(!0))}),e=Math.min(e,b-100),a=e+100,d.css("max-height",b-100+"px").animate({height:e},200),c.animate({top:"50%",marginTop:-(a/2),height:e+100},200)},ThriveLeads.roundNumber=function(a,b){var c=Math.pow(10,b);return Math.round(a*c)/c},ThriveLeads.conversion_rate=function(a,b,c){if(a=parseInt(a),b=parseInt(b),!a||isNaN(a)||!b||isNaN(b))return"N/A";c="undefined"==typeof c?"%":"";var d=ThriveLeads.roundNumber(b/a*100,3).toFixed(2);return d+(c&&!isNaN(d)?" "+c:"")},ThriveLeads.addMessage=function(a,b){ThriveLeads.objects.messages.add(a),b&&b.call(null)},ThriveLeads.addErrorMessage=function(a,b){var c=new ThriveLeads.models.Message({status:"error",text:a});return ThriveLeads.addMessage(c,b)},ThriveLeads.addSuccessMessage=function(a,b){var c=new ThriveLeads.model.Message({status:"success",text:a});return ThriveLeads.addMessage(c,b)},ThriveLeads.addFailCallback=function(a,b){a.fail(function(c){ThriveLeads.addErrorMessage(c.responseText,ThriveLeads.displayMessages),TVE_Dash.hideLoader(),a.failed=!0,"undefined"!=typeof b&&b.close&&b.close()})},ThriveLeads.displayMessages=function(){ThriveLeads.objects.messages.each(function(a){TVE_Dash["success"===a.get("status")?"success":"err"](a.get("text"))}),ThriveLeads.objects.messages.reset()},ThriveLeads.errorHandler=function(a,b,c,d){ThriveLeads.addMessage({text:c.responseText,status:"error"}),ThriveLeads.router.navigate(a,{trigger:!0})},ThriveLeads.bindZClip=function(a){function b(){try{a.closest(".tve-leads-copy-row").find("input.tve-leads-copy").on("click",function(a){this.select(),a.preventDefault(),a.stopPropagation()}),a.zclip({path:ThriveLeads["const"].url.wp_content+"plugins/thrive-leads/js/jquery.zclip.1.1.1/ZeroClipboard.swf",copy:function(){return jQuery(this).parents(".tve-leads-copy-row").find("input").val()},afterCopy:function(){var a=jQuery(this);a.prev().select(),a.removeClass("tvd-btn-blue").addClass("tvd-btn-green").find(".tl-copy-text").html('<span class="tvd-icon-check"></span>'),setTimeout(function(){a.removeClass("tvd-btn-green").addClass("tvd-btn-blue").find(".tl-copy-text").html(ThriveLeads["const"].translations.Copy)},3e3),a.parent().prev().select()}})}catch(b){console.error&&console.error("Error embedding zclip - most likely another plugin is messing this up")&&console.error(b)}}setTimeout(b,200)},ThriveLeads.ajaxurl=function(a){return a&&a.length?(a=a.replace(/^(\?|&)/,""),ajaxurl+(-1!==ajaxurl.indexOf("?")?"&":"?")+a):ajaxurl},ThriveLeads.validateInputField=function(a){a.removeClass("tvd-invalid"),""==a.val()&&a.addClass("tvd-invalid")};