jQuery(function() {
    
    var headerHeight = jQuery(".header").outerHeight();
    jQuery("body").css("padding-top",headerHeight);
    
    jQuery(".main-article table").wrap("<div class='table-wrapper'></div>");
    
    // Main Menu
    jQuery(".main-menu ul li ul").parent("li").addClass("has-child");
    jQuery(".main-menu ul li ul").parent("li").prepend("<span class='down-arrow'></span>");
    jQuery(".main-menu ul li ul li.active").parent("ul").parent("li").addClass("active");
    jQuery(".main-menu ul li ul li:nth-child(6)").parent().addClass("double-up");
    jQuery(".main-menu ul li .down-arrow").click(function(){
        jQuery(this).parent("li").siblings("li").removeClass("open"); 
        jQuery(this).parent("li").toggleClass("open"); 
    });
    
    jQuery(".main-menu .search > a").click(function(e){
       e.preventDefault();
       jQuery(this).parent("li").toggleClass("search-open"); 
    });
    
    jQuery(".main-menu .search > a").click(function(e){
       jQuery(".search-form").toggleClass("search-open"); 
    });
    
    var i = 0;
    jQuery("li.subscribe > a").click(function(e){
	   e.preventDefault();
	   if (i === 0) {
		   jQuery(this).text("Close");
		   i=1;	
	   }
	   else {
		   jQuery(this).text("Subscribe");
           i = 0;
	   }	    
       jQuery(".newsletter-signup").toggleClass("newsletter-signup-open"); 
    });
    
    
    jQuery(".menu-button").click(function(){
       jQuery("body").toggleClass("menu-open"); 
       jQuery(".region.region-primary-menu").toggleClass("region-open"); 
       jQuery(".search-form").toggleClass("search-open"); 
       
    });

    jQuery(".checkbox input").each(function(){
        if(jQuery(this).is(':checked')) {
            jQuery(this).parent().addClass("checked");
        }                      
    });
    
    jQuery(".checkbox input").change(function(){
        if(jQuery(this).is(':checked')) {
            jQuery(this).parent().addClass("checked");
        } else {
            jQuery(this).parent().removeClass("checked");
        }                           
    });
    
    jQuery(".radio input").click(function(){
        var thisName = jQuery(this).attr("name");
        jQuery("input:radio[name="+thisName+"]").parent().removeClass("checked");
        jQuery(this).parent().addClass("checked");
    });
    
    jQuery(".radio input:radio[name=eventFrequency]").change(function(){
        if(jQuery("#repeatingEvent").is(':checked')) {
            jQuery(".frequencyToggle").slideDown(200);
        } else {
            jQuery(".frequencyToggle").slideUp(200);
        }                           
    });
    
    jQuery("input#freeEvent").change(function(){
        if(jQuery(this).is(':checked')) {
            jQuery(".ticketsToggle").slideUp(200);
        } else {
            jQuery(".ticketsToggle").slideDown(200);
        }                           
    });
    
    jQuery("input#eventRules").change(function(){
        if(jQuery(this).is(':checked')) {
            jQuery(".eventRules").prop("disabled", false);
        } else {
            jQuery(".eventRules").prop("disabled", true);
        }                           
    });
    
    jQuery(".checkbox.disable-others input").change(function(){
        if(jQuery(this).is(':checked')) {
            jQuery(this).parent().parent().siblings().find("label.error").remove();
            jQuery(this).parent().parent().siblings().find("input").hide();
            jQuery(this).parent().parent().siblings().find("input").val("");
            jQuery(this).parent().parent().siblings().find("input").removeClass("error");
            jQuery(this).parent().parent().siblings().find("input").removeAttr("aria-invalid");
            jQuery(this).parent().parent().siblings().addClass("disabled");
        } else {
            jQuery(this).parent().parent().siblings().find("input").show();
            jQuery(this).parent().parent().siblings().removeClass("disabled");
        }                           
    });
    
    jQuery("a.disabled").click(function(e){
        e.preventDefault();
    });
    
    // MAKE FULL ARTICLE TILE CLICKABLE FROM SINGLE HREF
    
    jQuery(".articles").find("a.read-more").parent("p").parent("div").parent(".column").addClass("full-link");
    
    jQuery(".full-link").on("click", function() {
        window.location = jQuery(this).find("a.read-more").attr("href");
    });
    
    // DATE PICKER
    
    jQuery(".date-picker").dateDropper();
    //jQuery(".time-picker").timeDropper();
    
    // FORM VALIDATION
    jQuery("#eventForm").validate();
	
	/*show/hide no results block*/
	if(jQuery(".view-site-search").height() > 100) {
	    jQuery("#block-searchnoresultsblock").hide();
    }
    
    /*events datepicker setup*/
    if(jQuery("input[name='field_events_calculated_dates_value[min]']").length && jQuery("input[name='field_events_calculated_dates_value[max]']").length) {
		jQuery("input[name='field_events_calculated_dates_value[min]']").datepicker("setDate", new Date());
		jQuery("input[name='field_events_calculated_dates_value[max]']").datepicker("setDate", "2");    
    }
    
    /*show hide weekend view - start here*/
    jQuery(".view-events-overview-weekend").hide();
    jQuery(".view-events-overview .this_weekend").on("click", function(e) {
	    e.preventDefault();
        jQuery(".view-events-overview").hide();
        jQuery(".view-events-overview-weekend").show();
    });
    
    jQuery(".view-events-overview-weekend .this_weekend").on("click", function(e) {
        e.preventDefault();
        console.log("clicked");
		jQuery(".view-events-overview-weekend").hide();
        jQuery(".view-events-overview").show();
    });
    
    
});

jQuery(window).load(function() {
    
    var headerHeight = jQuery(".header").outerHeight();
    jQuery("body").css("padding-top",headerHeight);
    
    /*jQuery(".page-image").each(function(){
        var boxHeight = jQuery(this).find(".title-box").outerHeight();
        var boxWidth = jQuery(this).find(".title-box").outerWidth();
        jQuery(".title-box").css("margin-top",-boxHeight/2);
        jQuery(".title-box").css("margin-left",-boxWidth/2);
    });*/
    
    /*jQuery(".carousel").each(function(){
        var boxHeight = jQuery(this).find(".title-box").outerHeight();
        var boxWidth = jQuery(this).find(".title-box").outerWidth();
        jQuery(".title-box").css("margin-top",-boxHeight/2);
        jQuery(".title-box").css("margin-left",-boxWidth/2);
        var pagerTop = jQuery(".backgroundImage").height();
        jQuery(this).find(".cycle-pager").css("top",pagerTop-50);
    });*/
    
    jQuery(".title-box").css("opacity","1");
    // Set cycle pager position
    
    jQuery(".cycle-pager").fadeIn();
    
    
    jQuery(window).resize(function(){
        var pagerTop = jQuery(".backgroundImage").height();
        jQuery(".cycle-pager").css("top",pagerTop-50); 
        
        /*jQuery(".page-image").each(function(){
            var boxHeight = jQuery(this).find(".title-box").outerHeight();
            var boxWidth = jQuery(this).find(".title-box").outerWidth();
           jQuery(".title-box").css("margin-top",-boxHeight/2);
            jQuery(".title-box").css("margin-left",-boxWidth/2);
        });
        
        jQuery(".carousel").each(function(){
            var boxHeight = jQuery(this).find(".title-box").outerHeight();
            var boxWidth = jQuery(this).find(".title-box").outerWidth();
            jQuery(".title-box").css("margin-top",-boxHeight/2);
            jQuery(".title-box").css("margin-left",-boxWidth/2);
            var pagerTop = jQuery(".backgroundImage").height();
            jQuery(this).find(".cycle-pager").css("top",pagerTop-50);
        });*/
    });
    
    jQuery(window).resize();
    
    /*video colorbox load - starts here*/
    jQuery(".youtube").colorbox({iframe:true, innerWidth:"80%", innerHeight:"80%", maxWidth: "700px", maxHeight: "500px"}).resize();
    jQuery(".vimeo").colorbox({iframe:true, innerWidth:"80%", innerHeight:"80%", maxWidth: "700px", maxHeight: "500px"}).resize();
    
    /*vide colorbox load - ends here*/
    
    /*added for removing single pager on image gallery slider for major events*/
	jQuery('.major-events-images-header .carousel.cycle-slideshow').each(function (index, value) { 
		console.log(jQuery(this).find('.slide').length);
		if(jQuery(this).find('.slide').length <= 2) {
			jQuery(this).parent().find("nav.cycle-pager > span").css("display","none");
		}
	}); 
	
	/*added for removing single pager on image gallery slider for overview pages*/
	jQuery('.overview-page-header .carousel.cycle-slideshow').each(function (index, value) { 
		if(jQuery(this).find('.slide').length <= 2) {
			jQuery(this).parent().find("nav.cycle-pager > span").css("display","none");
		}
	}); 
	
	
	jQuery(".full-link").on("click", function() {
        window.location = jQuery(this).find("a.read-more").attr("href");
    });
	
	
	/*masonry load*/
	/*add for masonry code*/
	var isMobile = /Android|webOS|iPhone|BlackBerry/i.test(navigator.userAgent) ? true : false;
	var isIpad = /iPad/i.test(navigator.userAgent) ? true : false;
    if(!isMobile) {
		
		jQuery('.views-infinite-scroll-content-wrapper').masonry({
	      itemSelector: '.column'
		});
		jQuery('.visitor-information .views-infinite-scroll-content-wrapper').masonry('destroy');
		jQuery('.articles-search .views-infinite-scroll-content-wrapper').masonry('destroy');
    }
    
    jQuery('.related-content .articles').each(function (index, value) { 
	    console.log(jQuery(this).find('.column').length);
	    if(jQuery(this).find('.column').length == 4) {
		    jQuery(this).parents().find(".related-content").removeClass("related-content");
		}	
	});     
    
});