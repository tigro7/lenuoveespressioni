function loadAttori()
{
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
    parseAttori(this);
    }
  };
  xhttp.open("GET", "src/attori.xml", true);
  xhttp.send();
}
function parseAttori(xml)
{
         // Parse the xml file and get data
        var xmlDoc = xml.responseXML;
        //var xmlDoc = $.parseXML(xml),
        //    $xml = $(xmlDoc);
        var listaAttori = xmlDoc.getElementsByTagName("attore");
        // var contatore = 0;
        //$(xmlDoc).find('spettacolo').each(function () {
    
        var tabTotale = '';
        var tabAttori = '';
        var tabDescrizione = '';
        for(i = 0; i<listaAttori.length;)
        {
            tabAttori += '<div class="row text-center placeholder" data-toggle="collapse" data-target="#riga'+i.toString()+'">';
            
            tabDescrizione += '<div class="collapse" id="riga'+i.toString()+'">';
            
            //console.log("attore" + i.toString());
            for (j = 0; (j<6 && i<listaAttori.length); j++,i++)
            {
                strNome=$(listaAttori[i]).find("nome").text();
                strImg=strNome.replace(' ','').toLowerCase();
                strDesc=$(listaAttori[i]).find("descrizione").text().replace("è", "&eacute;").replace("é", "&egrave;");

                tabAttori += '<div class="col-6 col-sm-2 placeholder">';
                if (ImageExist("img/attori/" + strImg + ".jpg")){
                    tabAttori += '<img src="img/attori/'+ strImg +'.jpg" width="200" height="200" class="img-fluid rounded-circle fotoattore" id="attore'+i.toString()+'">';
                }else{
                    tabAttori += '<img src="img/attori/'+ strImg +'.png" width="200" height="200" class="img-fluid rounded-circle fotoattore" id="attore'+i.toString()+'">';
                }
                tabAttori += '<h4>'+strNome+'</h4>';
                tabAttori += '<div class="testoQuote">' + $(listaAttori[i]).find("quote").text().replace("è", "&eacute;").replace("é", "&egrave;") + '</div>';
                tabAttori += '</div>';
                
                tabDescrizione += '<div class="descrizioneattore" id="descrizione'+i.toString()+'">';
                tabDescrizione += '<h2>'+strNome+'</h2>';
                tabDescrizione += '<p>'+strDesc+'</p>';
                tabDescrizione += '</div>';
            }
            
            tabAttori += '</div>';
            tabDescrizione += '</div>';
            
            //console.log(tabAttori);
            //console.log(tabDescrizione);
            
            tabTotale += tabAttori;
            tabTotale += tabDescrizione;
            
            tabAttori = '';
            tabDescrizione = '';
        }

        $("#listaAttori").html(tabTotale);
            //contatore += 1;
        $(".fotoattore").hover(function(){
            $(".descrizioneattore").hide();
            $("#descrizione"+this.id.replace("attore", "")).show();
        });
        $(".fotoattore").mouseover(function(){
            //this.src = this.src.toString().split(".jpg")[0]+"hover.jpg"; 
        })
        $(".fotoattore").mouseout(function(){
            //this.src = this.src.toString().split("hover.jpg")[0]+".jpg"; 
        })
}

$(document).ready(function (){
    loadAttori();
})

function ImageExist(url) 
{
   var img = new Image();
   img.src = url;
   console.log (url);
   console.log(img);
   return img.height != 0;
}