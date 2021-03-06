jQuery(function($){
  $('.annotation-like').each(function(){
    var self = $(this);
    var count = self.find('.counter');
    self.find('a').each(function(){
      var handler = $(this);
      handler.click(function(){
        if (handler.hasClass('working')){
          return false;
        }
        handler.addClass('working')
        $.ajax({
          type: 'POST',
          url: handler.attr('href'),
          dataType: 'json'
        }).then(function(res){
          if (parseInt(res) || res.status === 0){
            // Swap href, text for data-href, data-text
            var text = handler.text();
            var href = handler.attr('href');
            handler.attr('href', handler.data('href'));
            handler.data('href', href);
            handler.text(handler.data('text'));
            handler.data('text', text);
            
            var liked = self.find('.liked').length > 0;
            // Count up/down
            count.text(parseInt(count.text()) + (liked ? -1 : 1));
            
            // Update like status
            handler.toggleClass('liked');
            handler.toggleClass('like');
            self.trigger('annotation-like-success');
          }else{
            self.trigger('annotation-like-error');
//            alert('error');
          }
          handler.removeClass('working');
        });
        return false;
      });
    });
  });
});
