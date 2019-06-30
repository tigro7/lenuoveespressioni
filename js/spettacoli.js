function loadSpettacoli() {
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
        parseSpettacoli(this);
    }
  };
  xhttp.open("GET", "src/spettacoli.xml", true);
  xhttp.send();
}
function parseSpettacoli(xml) {
        // Parse the xml file and get data
        var xmlDoc = xml.responseXML;
        var listaSpettacoli = xmlDoc.getElementsByTagName("spettacolo");
        for (i = 0; i < listaSpettacoli.length; i++){
            //console.log(listaSpettacoli[i]);
            var cardSpettacolo = '<div class="card spettacoloLight" id="spettacolo' + i.toString() + '">';
            cardSpettacolo +=       '<div class="row">';
            
            if(i % 2 == 0){
                cardSpettacolo +=       '<div class="col-md-4">';
                cardSpettacolo +=           '<img class="imgSpettacolo" src="../img/spettacoli/' + $(listaSpettacoli[i]).find("immagine").text() + '">';
                cardSpettacolo +=       '</div>';
            }
            
                cardSpettacolo +=       '<div class="col-md-8 px-3">';
                cardSpettacolo +=           '<div class="card-block px-3">';
                cardSpettacolo +=               '<h2 class="card-title">';
                cardSpettacolo +=                   $(listaSpettacoli[i]).find("titolo").text();
                cardSpettacolo +=               '</h2>';
                cardSpettacolo +=               '<p class="card-text">';
                cardSpettacolo +=                   $(listaSpettacoli[i]).find("short").text();
                cardSpettacolo +=               '</p>';
                cardSpettacolo +=				'<br>';
                //cardSpettacolo +=               '<p class="card-text">';
                //cardSpettacolo +=                   $(listaSpettacoli[i]).find("desc").text();
                //cardSpettacolo +=               '</p>';
                cardSpettacolo +=               '<a class="btn btn-primary infoBtn" data-desc="' + $(listaSpettacoli[i]).find("desc").text() + '" data-foto="<foto>' + $(listaSpettacoli[i]).find("foto").html() + '</foto>">Dettagli</a>';
                cardSpettacolo +=           '</div>';
                cardSpettacolo +=       '</div>';
            
            if(i % 2 != 0){
                cardSpettacolo +=       '<div class="col-md-4">';
                cardSpettacolo +=           '<img class="imgSpettacolo" src="../img/spettacoli/' + $(listaSpettacoli[i]).find("immagine").text() + '">';
                cardSpettacolo +=       '</div>';
            }
            var nuovoHtml = $("#listaSpettacoli").html();
            nuovoHtml += cardSpettacolo;
            $("#listaSpettacoli").html(nuovoHtml);
        }
        $(".infoBtn").click(function(){
            $("#carouselModalSpettacoli").find("ol").children().remove();
            $("#carouselModalSpettacoli").find(".carousel-inner").children().remove();
            $("#spettacolo-modal").find(".modal-body").text("");
            var dir = "index.php?path=" + $(this).parents(".row").find("img").attr("src").replace(".jpg", "") + "/";
            console.log(dir);
            var fileextension = ".jpg";
            $.ajax({
                url: dir,
                success: function (data) {
                    //List all .jpg file names in the page
                    var contatore = 0;
                    $(data).find("a:contains(" + fileextension + ")").each(function () {
                        var filename = this.href.replace(window.location, "").replace("http://edespressionisimili.altervista.org", "");
                        //console.log(filename);
                        $("#carouselModalSpettacoli").find("ol").append('<li data-target="#carouselModalSpettacoli" data-slide-to="' + contatore + '" ' + (contatore == 0 ? "class='active'" : "") + '></li>');
                        contatore += 1;
                        $("#carouselModalSpettacoli").find(".carousel-inner").append('<div class="carousel-item ' + (contatore == 1 ? "active" : "") + '"><img class="d-block w-100" src="' + filename + '"></div>');
                    });
                }
            });
            $('#carouselModalSpettacoli').carousel();
            
            //$("#spettacolo-modal").find(".modal-img").attr("src", $(this).parents(".row").find("img").attr("src"));
            
            $("#spettacolo-modal").find(".modal-title").text($(this).parents(".row").find(".card-title").text());
            var newBody = $("#spettacolo-modal").find(".modal-body").text();
            newBody += $(this).parents(".row").find(".card-text").text();
            newBody += $(this).data("desc");
            let dataFoto = $(this).data("foto");
            let parser = new DOMParser();
            let xmlFoto = parser.parseFromString(dataFoto,"text/xml");
            let listaFoto = xmlFoto.getElementsByTagName("link");
            if (listaFoto.length > 0){
                newBody += '<br>';
            }
            for (let f = 0; f < listaFoto.length; f++){
                let bottoneFoto = '<a class="btn btn-primary fotoBtn" target="_blank" href="' + $(listaFoto[f]).find("url").text() + '">' + $(listaFoto[f]).find("testo").text() + '</a>';
                newBody += bottoneFoto;
            }
            $("#spettacolo-modal").find(".modal-body").html(newBody);
            $("#spettacolo-modal").modal('show');
        })
}

$(document).ready(function (){
    loadSpettacoli();
})