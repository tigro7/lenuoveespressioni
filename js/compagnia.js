$(document).ready(function(){
    $(".fotoattore").hover(function(){
        $(".descrizioneattore").hide();
        $("#descrizione"+this.id.replace("attore", "")).show();
    });
    $(".fotoattore").mouseover(function(){
        this.src = this.src.toString().split(".jpg")[0]+"hover.jpg"; 
    })
    $(".fotoattore").mouseout(function(){
        this.src = this.src.toString().split("hover.jpg")[0]+".jpg"; 
    })
});