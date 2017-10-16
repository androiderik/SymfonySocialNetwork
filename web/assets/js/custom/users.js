$(document).ready(function(){
    var ias = new  $.ias({
       container: '.box-users',
       item: '.user-item',
       pagination: '.pagination',
       next: '.pagination .next_link',
       triggerPageThreshold: 2
       
    }); //InfiniteAjaxScroll Instance
    
    ias.extension(new IASTriggerExtension({
        text: 'Ver más', //Button if want to see more content
        offset: 2 //The number of pages which should load automatically, then button appear
    })); 
    
    ias.extension(new IASSpinnerExtension({
        src: URL+'/../assets/images/ajax-loader.gif', //URL exist in layout.twig
    })); //Loader
    
    ias.extension(new IASNoneLeftExtension({
        text: ' No hay más contenido'
    })); //If there is no more registers
    
    
});