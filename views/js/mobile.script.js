var main = function(){   
    
    var scroll = $(window).scrollTop();
    $(window).scroll(function(e){
        scroll = $(window).scrollTop();
        if(scroll > 60){
            $('.logo-link').hide();
        } else {
            $('.logo-link').show();
        }
    });
    
    $(function () {
	$("a.youtube").YouTubePopup({title: '', autoplay: 1, draggable: true, showBorder: false});
    });
    
    $('.confirm').click(function(e){
        if(!confirm('Are you sure you want to delete this item?')){
            e.preventDefault();
        }
    });
};

$(document).ready(main);



//Polyfill
if (!String.prototype.endsWith) {
  String.prototype.endsWith = function(searchString, position) {
      var subjectString = this.toString();
      if (typeof position !== 'number' || !isFinite(position) || Math.floor(position) !== position || position > subjectString.length) {
        position = subjectString.length;
      }
      position -= searchString.length;
      var lastIndex = subjectString.indexOf(searchString, position);
      return lastIndex !== -1 && lastIndex === position;
  };
}



