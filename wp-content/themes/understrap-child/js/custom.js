var images = document.querySelectorAll('.rellax');
new simpleParallax(images, {
	scale: 1.5
}); 
/*{
	scale: 2.0
}*/

/* Blog Sidebar Sticky */

jQuery('.blog__sidebar').hcSticky({
  stickTo: '.post-sidebar',
  bottom: 20,
  responsive: {
  	768: {
  		disable: true
  	}
  }
});

/* Promoters Page Sticky Nav */
jQuery('#wpv-view-layout-14473, #wpv-view-layout-14634').hcSticky({
  stickTo: '.col-sm-3',
  top: 150,
  responsive: {
  	575: {
  		disable: true
  	}
  }
});