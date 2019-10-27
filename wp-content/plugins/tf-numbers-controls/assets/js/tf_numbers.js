/*
 * TF-Numbers
 * Author : Aleksej Vukomanovic
 */

//Statistics in numbers
jQuery.fn.statsCounter = function(){
    //declaring vars
    var stat = this.find('.statistics-inner .stat');
    var speed = this.data('sp');
    if ( speed > 90 ) {
        speed = speed * 20;
    } else if ( speed > 80 ) {
        speed = speed * 30;
    }  else if ( speed > 70 ) {
        speed = speed * 45;
    } else if ( speed > 60 ) {
        speed = speed * 60;
    } else if ( speed > 50 ) {
        speed = speed * 70;
    } else if ( speed > 30 ) {
        speed = speed * 200;
    } else {
        speed = speed * 600;
    }
    var comma = this.data('cm');
    var go = {};
    var end = {};
    var number = {};
    var start = {};
    var count = {};

    //iterate through every .stat class and collect values
    stat.each(function(){
        var index = jQuery(this).index();
        var cnt = jQuery(this).data('count');
        count[index] = parseInt(cnt,10);
        number[index] = jQuery(this).find('.number');
        start[index] = 0;

        jQuery({someValue: 0}).animate({someValue: count[index]}, {
            duration: speed,
            easing:'linear', // can be anything
            step: function() { // called on every step
                // Update the element's text with rounded-up value:
                if( 'on' == comma && String(cnt).indexOf('.') === -1 ) {
                    number[index].text(commaSeparateNumber(Math.round(this.someValue)));
                } else if (  String(cnt).indexOf('.') !== -1 ) {
                    number[index].text(this.someValue.toFixed(2));

                } else {
                    number[index].text(Math.round(this.someValue));
                }
            },
            complete: function() {
                if( 'on' == comma && String(cnt).indexOf('.') === -1 ) {
                    number[index].text(commaSeparateNumber(count[index]));
                } else if (  String(cnt).indexOf('.') !== -1 ) {
                    number[index].text(cnt);
                } else {
                    number[index].text(count[index]);
                }
            }
        });

    });//stat.each()

}//statsCounter();

// source : http://goo.gl/EsdpV1
function commaSeparateNumber(val){
    while (/(\d+)(\d{3})/.test(val.toString())){
        val = val.toString().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
    }
    return val;
}

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
        statistics.each(function() {
            var thisStats = jQuery(this);
            var comma = thisStats.data('cm');
            thisStats.addClass('stats-custom-' + n);

            //setting counts to 0
            if (thisStats.find('.stat').length > 0) {
                var stat = thisStats.find('.stat');

                stat.each(function () {
                    jQuery(this).find('.number').text(0);
                });
            }
            //animating when scrolled
            var countDone = 0;
            var cmo = thisStats.data('cmo');
            
            if (jQuery(window).width() > 780 ) {
                var id = thisStats.attr('id');
                var visible = isElementVisible('#'+id);

                //if stats section visible, start the counting after 300ms
                if (visible && countDone == 0) { //check if it's not already done
                    setTimeout(function () {
                        countDone = 1;
                        thisStats.statsCounter();
                    }, 300);
                }//if visible && not shown

                jQuery(window).scroll(function () {
                    var id = thisStats.attr('id');
                    var visible = isElementVisible('#'+id);

                    //if stats section visible, start the counting after 300ms
                    if (visible && countDone == 0) { //check if it's not already done
                        setTimeout(function () {
                            countDone = 1;
                            thisStats.statsCounter();
                        }, 300);
                    }//if visible && not shown
                });//scroll function
            } else {
                countDone = 1;
                thisStats.statsCounter();
            }// window.width()
        });
    }
});
