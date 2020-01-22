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
jQuery('.resource-category__section .js-wpv-view-layout').hcSticky({
  stickTo: '.col-sm-3',
  top: 150,
  responsive: {
  	575: {
  		disable: true
  	}
  }
});

jQuery("body").scrollspy({target: '#resource-nav', offset: 150});