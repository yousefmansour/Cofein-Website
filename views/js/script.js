
var main = function(){   
    
    $('#nav a').click(function(e){
        
        if($(this).hasClass('ajax')){
            e.preventDefault();
            var page = $(this).attr('href');
            if(page){
                  $.ajax({
                      url: '?page='+page,
                      dataType: 'html'
                  }).done(function(html){
                      $('#main-content').html(html);     
                      $('html, body').animate({
                        scrollTop: $("#social-media-links").offset().top
                     }, 0);
                  });     
            }
        }
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
