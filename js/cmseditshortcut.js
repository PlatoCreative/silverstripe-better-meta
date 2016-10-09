/**
enable the use of the x-cms-edit-link with
use: ctrl+e
use: alt+e
*/
function doc_keyUp(e) {
    if(e.altKey && e.keyCode == 69) {
        var m = $("meta[name=x-cms-edit-link]");
        if(m.length > 0){
            var link = m.attr('content');
            window.open(link);
        }
    }
}

if (document.addEventListener) {
    document.addEventListener('keyup', doc_keyUp, false);
} else if (document.attachEvent) {
    document.attachEvent('keyup', doc_keyUp);
}
