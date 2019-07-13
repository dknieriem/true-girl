/*
* TF-Numbers
* Author : Aleksej Vukomanovic
*/

//Statistics in numbers
jQuery.fn.statsCounter = function(){
   //declaring vars
    var stat = this.find('.statistics-inner').children();
    var startValue = 0;

    //iterate through every .stat class and collect values
   stat.each(function(){
      var count = parseInt( jQuery(this).data('count'), 10 );
      var number = jQuery(this).find('.number');
      var start = 0;
      var go = setInterval(function(){ startCounter(); },1); //increment value every 1ms

      function startCounter(){
          incrementBy = Math.round(count / 90); //Divide inputed number by 90 to gain optimal speed (not too fast, not too slow)
          if( count < 90 ) incrementBy = Math.round(count / 5);
          if( count < 5 ) incrementBy = Math.round(count / 2);
          start = start + incrementBy;
          if( count != 0 ) {
            jQuery(number).text(start);
          } else {
            jQuery(number).text(0);
             start = count;
          }
          //if desired number reched, stop counting
          if( start === count ) {
              clearInterval(go);
          } else if( start >= count ){ //or if greater than selected num - stop and return value
              clearInterval(go);
              jQuery(number).text(count);
          }
      }//startCounter;
  });//stat.each()
}//statsCounter();

  //if visible src = http://www.rupertlanuza.com/how-to-check-if-element-is-visible-in-the-screen-using-jquery/
  function isElementVisible(elementToBeChecked) {
    var TopView = jQuery(window).scrollTop();
    var BotView = TopView + jQuery(window).height();
    var TopElement = jQuery(elementToBeChecked).offset().top;
    var BotElement = TopElement + jQuery(elementToBeChecked).height();
    return ((BotElement <= BotView) && (TopElement >= TopView));
  }

jQuery(document).ready(function(jQuery){

   var statistics = jQuery('.statistics');
    var n = 0;

   if( statistics.length > 0 ) {

       statistics.each(function(){
           var thisStats = jQuery(this);
           var statId = thisStats.attr('id');
           thisStats.addClass( 'stats-custom-' + n );

           //setting counts to 0
           if( jQuery('.stat').length > 0 ){
               var stat = thisStats.find('.stat');
               stat.each(function(){
                   var icon = jQuery(this).find('span');
                   icon.each(function(){
                       var val = jQuery(this).attr('class');
                       if( val.indexOf('.') != -1 && jQuery(this).find('img').length <= 0 ) {
                           jQuery(this).append('<img src="'+val+'" />');
                       }
                   });
                   stat.find('.number').text(0);
               })
           }
           //animating when scrolled
           var countDone = 0;
           var cmo = thisStats.data('cmo');


           if( jQuery(window).width() > 780 ) {
               var visible = isElementVisible('#' + statId);
               if( visible && countDone == 0 ) { //check if it's not already done
                   setTimeout(function(){
                       thisStats.statsCounter();
                       countDone = 1;
                   },600);
               }//if visible && not shown
               jQuery(window).scroll(function(){
                   var visible = isElementVisible('#' + statId);

                   //if stats section visible, start the counting after 400ms
                   if( visible && countDone == 0 ) { //check if it's not already done
                       setTimeout(function(){
                           thisStats.statsCounter();
                           countDone = 1;
                       },400);
                   }//if visible && not shown
               });//scroll function
           } else {
               thisStats.statsCounter();
               countDone = 1;
           }// window.width()
       });
  }
});
