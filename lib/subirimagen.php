<?

extract($_GET);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Pragma"content="no-cache" />
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Subir Imagen</title>
<link rel="stylesheet" href="../Css/style-4.css" type="text/css" />

<script type="text/javascript" src="../js/jquery.js"></script>
<script type="text/javascript" src="../js/jquery-ajax_upload.js"></script>
<script type="text/javascript" >

$(document).ready(function(){

	var allowed = ['jpg', 'gif', 'jpeg', 'png', 'JPG', 'GIF', 'JPEG', 'PNG'];

	$.ajax_upload('#txtImagen1', {
		action: '../uploads/upload.php',
		data : {
			'key1' : "This string won't be send because we will overwrite it"
		},
		onSubmit : function(file , ext){
			if ($.inArray(ext, allowed ) === -1){
				// extension is not allowed
                $("#mensajes").toggleClass("ERROR");
                $('#mensajes').show("slow");
				$('#mensajes').html('Error: Solo se admiten imagenes');
				// cancel upload
				return false;
			}
			/* Setting data */
            var tiempo = new Date();
            var instante = tiempo.getTime();
			this.set_data({
				'time': instante
			});
            var imagen = instante.toString() + "_" + file;
            $("#hdnImagen").val(imagen);
            $('#mensajes').show("slow");
            $("#mensajes").toggleClass("WAIT");
			$('#mensajes').html('Subiendo ' + file);
		},
		onSuccess : function(file, respuesta){
            var imagen = "../uploads/" + $("#hdnImagen").val();
            //alert(imagen);
            var img = "<a href='" + imagen + "' target='_blank'><img src='" + imagen + "' /> </a>";
            $("#imagen").html(img);
            $("#imagen").show("fast");
            $("#mensajes").toggleClass("OK");
            $('#mensajes').show("fast");
			$('#mensajes').html('Terminado ' + file);
            var resp = respuesta.split(";");
            $("#hdnTipo").val(resp[2]);
            $("#hdnPeso").val(resp[1]);
		}
	});

});

function cerrar(){
    self.close();
}

function aceptar(){
    window.opener.document.proyecto.hdnImagen.value = document.proyecto.hdnImagen.value;
    window.opener.document.proyecto.hdnTipo.value = document.proyecto.hdnTipo.value;
    window.opener.document.proyecto.hdnPeso.value = document.proyecto.hdnPeso.value;
    self.close();
}

</script>

</head>
<body>	
    <form id="proyecto" name="proyecto" action="" method="post" enctype="multipart/form-data">
        <h1>Subir Imagen</h1>
        <fieldset class="fieldset" style="width:98%!important;">
            <legend class="legend">
                Datos Gu&iacute;a Avance
            </legend>
            <div id="form2" class="formleft" style="width:98%!important;">
                <label class="label" for="txtImagen">Seleccione Im&aacute;gen:</label>
                <div id="divImagen" class="div_texbox"><button id="txtImagen1" class="button btnUpload">Examinar...</button></div>
                <input type="hidden" id="hdnImagen" name="hdnImagen" value="" />
                <input type="hidden" id="hdnTipo" name="hdnTipo" value="" />
                <input type="hidden" id="hdnPeso" name="hdnPeso" value="" />
                <div class="clear"></div>
                <div id="mensajes" class="ERROR" style="display:none"></div>
            </div>
        </fieldset>
        <div class="button_div" style="width:98%!important;">
            <input type="button" id="btnCancel" name="btnCancel" value="Cancelar" class="button btnCancel" onclick="cerrar()" />
            <input type="button" id="btnAgregar" name="btnAgregar" value="Aceptar" class="button btnVerify" onclick="aceptar()" />
        </div>
        <div class="clear"></div>
        <div id="imagen" align="center" style="display:none; border:solid 1px Black;"></div>
    </form>
</body>
</html>