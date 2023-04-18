<?php
/*require_once('/home/sctrans/www/wp-content/plugins/gravityforms/gravityforms.php' );

if ( is_plugin_active( 'gravityforms/gravityforms.php' ) ) {
    var_dump("it's ok");
} else {
   var_dump("error");
}

add_filter( 'gform_merge_tag_filter', 'filter_merge_tag', 10, 6 );
$text = '{precio_base}';
$url_encode = true;
function filter_merge_tag( $text, $form, $entry, $url_encode, $esc_html, $nl2br ) {
    if ( $text == 'precio_base' ) {
        var_dump("hello");
    }else{
        var_dump("bye");
    }
}*/
/*var_dump($content);*/
    /*function ru_mostrar_error($mensaje){
        echo '<div class="ru_error">' . $mensaje . '</div>';
    }


    $class_container_formulario = '';
    $current_user = wp_get_current_user();    
    $user_id = get_current_user_id();
    if ($user_id == 0) {
        ru_mostrar_error('Para realizar cotizaciones es necesario logueado.');
        $class_container_formulario = ' hidden ';
    } else {        
        $user_meta = get_userdata($user_id);
        $user_roles = $user_meta->roles;
        if (in_array("administrator", $user_roles) == false){
            $class_container_formulario = ' hidden ';
            ru_mostrar_error('Su usuario no tiene permisos para solicitar cotizaciones');
        }
    }   */ 
    $recargo_combustible=empty(get_option('cotizacion_recargo_combustible'))?'1':get_option('cotizacion_recargo_combustible');
    //echo $recargo_combustible;
?>
<div id="primary" class="container_cotizador">

    <?php if(isset($_GET['coti_sent'])){
        global $wpdb; 
        $sql = " SELECT * ".
               " FROM ".$wpdb->prefix."gf_entry_meta ".
               " WHERE `form_id` = 1 AND `entry_id` =".$_GET['coti_sent'];
        $result = $wpdb->get_results($sql);
        //echo "<pre>";
        //print_r($result);
        //echo "</pre>";
        $impt_plataforma=empty(get_option('cotizacion_plataforma_elevadora'))?'35':get_option('cotizacion_plataforma_elevadora');
        $nombre_user="";
        $valor_envio_aereo = 0;
        $usuario = get_userdata (get_current_user_id());
        $user_publico = $usuario->user_email;
        $valor_plataforma_elevadora = 0;
        $mercancia_peligrosa="No";
        $mercancia_no_remontable="No";
        $valor_mercancia_peligrosa = 0;  
        $cod_postal_origen=0;
        $cod_postal_destino=0;  
        $peso=0;
        $paquetes="";
        $volumen=0;
        $tipo_servicio="";
        $peonaje=empty(get_option('cotizacion_peonaje'))?'35':get_option('cotizacion_peonaje');
        $valor_importe_mozo=0;
        $largo=0;
        $ancho=0;
        $alto=0;
        if(count($result)==0){
            echo "<h1>No existe Cotizacion</h1>";
            exit();  
        }
        foreach ($result as $value) {
            if($value->meta_key === '16'){
                $ciudad_origen = $value->meta_value;
            }
            
            switch($value->meta_key) {
                case 3:
                     $paquetes=$value->meta_value;
                break;
                case 5:
                     $peso=$value->meta_value;
                break;
                case 6:
                     $volumen=$value->meta_value;
                break;
                case 7:
                     $envio_aereo= $value->meta_value;
                     if($envio_aereo=="Si"){
                       $valor_envio_aereo=25;
                     }
                break;
                case 8:
                    $plataforma_elevadora =$value->meta_value;
                    if($plataforma_elevadora=="Si"){
                        $valor_plataforma_elevadora = (float)$impt_plataforma;
                    }
                break;
                case 9:
                    $mercancia_peligrosa=$value->meta_value;
                break;
                case 11:
                    $cod_postal_origen=$value->meta_value;
                break;
                case 10:
                    $cod_postal_destino=$value->meta_value;
                break;
                case 28:
                    $nombre_user=$value->meta_value; 
                break;
                case 15:
                    $tipo_servicio=$value->meta_value; 
                break;
                case 18:
                    $mercancia_no_remontable=$value->meta_value; 
                break;
                case 19:
                    $valor_importe_mozo=$value->meta_value; 
                break;
                case 29:
                   $largo=$value->meta_value;
                break;
                case 30:
                   $ancho=$value->meta_value;
                break;
                case 31:
                   $alto=$value->meta_value;
                case 34:
                   $rec_comb=$value->meta_value;   
                break;
            }
        }

        $volumen_cot=$volumen*270;
        if($volumen_cot>$peso){
            $peso_cotizar=$volumen*270;
         }else{
            $peso_cotizar=$peso;
         }
        if($tipo_servicio=="Recogida"){
            /*echo $cod_postal_destino."<br>";
            echo $peso_cotizar."<br>";
            echo $largo."<br>";*/
            $precio_base = get_precio_por_zip($ciudad_origen, $cod_postal_origen ,$peso_cotizar,$largo);
        }else{
            $precio_base = get_precio_por_zip($ciudad_origen, $cod_postal_destino ,$peso_cotizar,$largo); 
        }
        
        if ($precio_base == false) {
           echo "<h1>Lo siento, No podemos obtener el precio para el código postal ingreeesado.</h1>";
           exit();
        }    
        $importe_por_mozo_hora=round($valor_importe_mozo*$peonaje,2);
        $valor_rec_comb=0;
        if($mercancia_peligrosa=="Si"){
            $valor_mercancia_peligrosa=round($precio_base*0.30,2);
            $valor_rec_comb=round((($precio_base+$valor_mercancia_peligrosa)*($rec_comb/100)),2);
            $total=$precio_base+$valor_envio_aereo+$valor_plataforma_elevadora+$valor_mercancia_peligrosa+$importe_por_mozo_hora+$valor_rec_comb;
        }else{
            $total = $precio_base+$valor_envio_aereo+$valor_plataforma_elevadora+$valor_mercancia_peligrosa+$importe_por_mozo_hora;
        }  ?>
        <style>
            td{
                border-bottom: solid 5px #f7f8f9;
            }

            table, td, th {
                border-right: 0px;
                border-left: 0px;
                border-top: 0px;
            }
        </style>
        <div class="confirmacion" >
            <input type="hidden" name="nro_cotizacion" id="nro_cotizacion" value="<?php echo $_GET['coti_sent'];?>">
            <h4 style="text-align: justify;">Resumen de cotización #<?php echo $_GET['coti_sent'];?></h4>
            <p>Su cotización fue generada exitosamente. Esta cotización está sujeta a los siguientes <a href="<?php echo site_url();?>/condiciones-generales-transporte-terrestre/" target="_blank">términos y condiciones</a>.</p>
            <div class="detalle-cotizacion">
                <div style="overflow-x:auto;">
                    <table class="table1" style="overflow-x:auto">
                        <tbody>
                            <tr>
                                <td style="padding: 9px; background: #0A5687; color: white; " ><strong>Cliente</strong></td>
                                <td colspan="2" style="padding: 9px; background: #F9F6F0; color: #010100; "><?php echo $user_publico;?></td>
                                <td style="padding: 9px; background: #0A5687; color: white; " ><strong>C.P. Origen </strong></td>
                                <td colspan="2" style="padding: 9px; background: #F9F6F0; color: #010100;" ><?php echo $cod_postal_origen; ?></td>
                                <td style="padding: 9px; background: #0A5687; color: white; " ><strong>C.P. Destino  </strong></td>
                                <td colspan="2" style="padding: 9px; background: #F9F6F0; color: #010100;" ><?php echo $cod_postal_destino; ?></td>
                            </tr>
                            <tr>
                                <td style="padding: 9px; background: #0A5687; color: white; " ><strong>Paquetes </strong></td>
                                <td colspan="2" style="padding: 9px; background: #F9F6F0; color: #010100; " ><?php echo $paquetes;?></td>
                                <td style="padding: 9px; background: #0A5687; color: white; " ><strong>Peso </strong></td>
                                <td colspan="2" style="padding: 9px; background: #F9F6F0; color: #010100; " ><?php echo $peso;?> kg.</td>
                                <td style="padding: 9px; background: #0A5687; color: white; " ><strong>Volumen </strong></td>
                                <td colspan="2" style="padding: 9px; background: #F9F6F0; color: #010100; " ><?php echo $volumen;?> m3</td>   
                            </tr>
                        </tbody>
                    </table>
                </div>
                <table>
                    <tbody >
                        <tr style="">
                            <td style="padding: 9px; background: #0A5687; color: white; " ><strong>Precio Base</strong></td>
                            <td style="text-align:center; background: #F9F6F0; color: #010100; width: 400px;"><span id="valor_precio_base"><?php echo number_format($precio_base,2); ?> </span>€</td>
                        </tr>
                        <tr style="">
                            <td style="padding: 9px; background: #0A5687; color: white; " ><strong>Suplemento Aeropuerto/Puerto</strong></td>
                            <td style="text-align:center; background: #F9F6F0; color: #010100; width: 400px;"><span id="valor_envio_aereo"></span><?php echo $valor_envio_aereo; ?> €</td>
                        </tr>
                        <tr style="">
                            <td style="padding: 9px; background: #0A5687; color: white; " ><strong>Plataforma Elevadora</strong></td>
                            <td style="text-align:center; background: #F9F6F0; color: #010100; width: 400px;">
                                <span id="valor_plataforma_elevadora"></span><?php echo $valor_plataforma_elevadora;?> €
                            </td>
                        </tr>
                        <tr style="">
                            <td style="padding: 9px; background: #0A5687; color: white; " ><strong>Mercancía Peligrosa</strong></td>
                            <td style="text-align:center; background: #F9F6F0; color: #010100; width: 400px;">
                                <span id="valor_mercancia_peligrosa">
                                <?php echo $valor_mercancia_peligrosa; ?> </span>€
                            </td>
                        </tr>
                        <?php if($mercancia_peligrosa=="Si"){ ?>
                        <tr><td style="padding: 9px; background: #0A5687; color: white; " ><strong>Recargo del combustible <?php echo "(".$rec_comb."%)"?></strong></td>
                            <td style="text-align:center; background: #F9F6F0; color: #010100;width: 400px;">
                                <span id="valor_recargo_combustible">
                                <?php echo $valor_rec_comb; ?> </span>€
                            </td>
                        </tr>
                        <?php } ?>  
                        <tr style="">
                            <td style="padding: 9px; background: #0A5687; color: white; " ><strong>Peonaje por Hora/Mozo</strong></td>
                            <td style="text-align:center; background: #F9F6F0; color: #010100;width: 400px;">
                                <span id="importe_por_mozo_hora">
                                <?php echo $importe_por_mozo_hora; ?> </span>€
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr><td style="padding: 9px; background: #0A5687; color: white;font-size: 25px;" ><strong>TOTAL:</strong></td>
                            <td style="text-align:center; background: #F9F6F0; font-weight:bold;width: 400px;"><span id="valor_total"><?php echo number_format($total, 2);?></span> €</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <p>Si usted tiene alguna pregunta sobre esta cotización, por favor, póngase en contacto con nosotros.</p>        
            <div class="botones">
                <button class="nueva-cotizacion">Nueva Cotizacion</button>
                <?php if($mercancia_no_remontable=="Si"){ ?>
                    <button class="consultar">Consultar</button>
                <?php }else{ ?>    
                    <button class="contratar">Contratar</button>
                <?php } ?>    
            </div>
        </div>
    <?php }else{ ?>
        <h1 class="tblue" style="font-size:36px;text-align:center">COTIZADOR EN LÍNEA</h1>
        <p>Rellena los campos del formulario con toda la información para tu servicio de transporte y recibirás un email con una cotización por el servicio.</p>
        <div class="adc-form">
            <?php echo do_shortcode('[gravityform id="1" title="false" description="false"]'); ?>
        </div>
        <script type="text/javascript">
            function actualizar_boton_cotizar(){
                if($("#input_1_18").val()=="Si"){
                    $(".msj_peligroso").html("Estimado Cliente:<br>Esta cotización no se puede realizar por la web, pongase en contacto con nuestro departamento comercial a la dirección: info@sctrans.es <br><br>");
                    Swal.fire({
                        title: 'Estimado Cliente',
                        text: 'Esta cotización no se puede realizar por la web, pongase en contacto con nuestro departamento comercial a la dirección: info@sctrans.es ',
                        icon: 'warning'
                    });
                    $("#gform_submit_button_1").prop("disabled",true); 
                }else{
                    ///validar que no haiga una medida mayor a 3///
                    existe_med_my_3=false;
                    $(".largo").each(function( index ) {
                        largo = parseFloat($(this).val());
                        if(largo>3){
                           existe_med_my_3=true;
                        }
                    });
                    if(existe_med_my_3){
                        $(".msj_peligroso").html("Estimado Cliente:<br>Esta cotización no se puede realizar por la web, pongase en contacto con nuestro departamento comercial a la dirección: info@sctrans.es <br><br>");
                        Swal.fire({
                            title: 'Estimado Cliente',
                            text: 'Esta cotización no se puede realizar por la web, pongase en contacto con nuestro departamento comercial a la dirección: info@sctrans.es ',
                            icon: 'warning'
                        });
                    }else{    
                        $(".msj_peligroso").html("");
                        $("#gform_submit_button_1").prop("disabled",false);
                    }
                }                   
            }
            function validar_largo_merc(){
                es_valido=true;
                $(".largo").each(function( index ) {
                    largo = parseFloat($(this).val());
                    if(largo>3){
                       es_valido=false;
                    }
                });
                return es_valido;
            }
            (function($){
                $(document).ready(function(){
                    
                    $(".gform_footer").prepend("<div class='msj_peligroso'></div>");
                    $("#input_1_34").val("<?php echo $recargo_combustible; ?>");
                    $("#input_1_3").attr("readonly",true);
                    $("#input_1_6").attr("readonly",true);
                    $("#input_1_11").attr("readonly",true);
                    $("#input_1_27").val("<?php echo get_current_user_id();?>");
                    $("#input_1_15").change(function(){
                        tipo_servicio = $(this).val();
                        ciudad_origen = $("#input_1_16").val();
                        console.log(ciudad_origen);
                        if(tipo_servicio=="Recogida"){
                            if(ciudad_origen=="Valencia"){
                               $("#input_1_10").val("46394");
                            }
                            if(ciudad_origen=="Barcelona"){
                               $("#input_1_10").val("08820");
                            }
                            if(ciudad_origen=="Madrid"){
                               $("#input_1_10").val("28830");
                            }
                            if(ciudad_origen=="Sevilla"){
                               $("#input_1_10").val("41011"); 
                            }
                            if(ciudad_origen=="Algeciras"){
                               $("#input_1_10").val("11207");
                            }
                            if(ciudad_origen=='Vigo'){
                               $("#input_1_10").val("36416");  
                            }
                            $("#input_1_11").val("");
                            $("#input_1_11").attr("readonly",false);
                            $("#input_1_10").attr("readonly",true);
                        }else{
                            if(ciudad_origen=="Valencia"){
                               $("#input_1_11").val("46394");
                            }
                            if(ciudad_origen=="Barcelona"){
                               $("#input_1_11").val("08820");
                            }
                            if(ciudad_origen=="Madrid"){
                               $("#input_1_11").val("28830");
                            }
                            if(ciudad_origen=="Sevilla"){
                               $("#input_1_11").val("41011"); 
                            }
                            if(ciudad_origen=="Algeciras"){
                               $("#input_1_11").val("11207");
                            }
                            if(ciudad_origen=='Vigo'){
                               $("#input_1_11").val("36416");  
                            }
                            $("#input_1_10").val("");
                            $("#input_1_10").attr("readonly",false);
                            $("#input_1_11").attr("readonly",true);
                        }
                    });
                    $("#field_1_10").append("<div id='msj_1_10'></div>");
                    $("#field_1_11").append("<div id='msj_1_11'></div>");
                    $("#field_1_5").append("<div id='msj_1_5'></div>");
                    function calcular_volumen(){
                        total_paquete=0;
                        $(".paquete").each(function( index ) {
                            total_paquete += parseFloat($(this).val());
                        });
                        total_largo=0;
                        $(".largo").each(function( index ) {
                            total_largo += parseFloat($(this).val());
                        });
                        total_ancho=0;
                        $(".ancho").each(function( index ) {
                            total_ancho += parseFloat($(this).val());
                        });
                        total_alto=0;
                        $(".alto").each(function( index ) {
                            total_alto += parseFloat($(this).val());
                        });
                        return total_paquete*total_largo*total_ancho*total_alto;
                    }
                    $("#input_1_16").change(function(){
                        ciudad_origen=$(this).val();
                        tipo_servicio = $("#input_1_15").val();
                        if(tipo_servicio=="Recogida"){
                            if(ciudad_origen=="Valencia"){
                               $("#input_1_10").val("46394");
                            }
                            if(ciudad_origen=="Barcelona"){
                               $("#input_1_10").val("08820");
                            }
                            if(ciudad_origen=="Madrid"){
                               $("#input_1_10").val("28830");
                            }
                            if(ciudad_origen=="Algeciras"){
                               $("#input_1_10").val("11207");
                            }
                            if(ciudad_origen=='Vigo'){
                               $("#input_1_10").val("36416");  
                            }
                            $("#input_1_11").val("");
                            $("#input_1_11").attr("readonly",false);
                            $("#input_1_10").attr("readonly",true);
                        }else{
                            if(ciudad_origen=="Valencia"){
                               $("#input_1_11").val("46394");
                            }
                            if(ciudad_origen=="Barcelona"){
                               $("#input_1_11").val("08820");
                            }
                            if(ciudad_origen=="Madrid"){
                               $("#input_1_11").val("28830");
                            }
                            if(ciudad_origen=="Algeciras"){
                               $("#input_1_11").val("11207");
                            }
                            if(ciudad_origen=='Vigo'){
                               $("#input_1_11").val("36416");  
                            }
                            $("#input_1_10").val("");
                            $("#input_1_10").attr("readonly",false);
                            $("#input_1_11").attr("readonly",true);
                        }
                    });
                    html="<b> Dimensiones de Carga</b><input type='button' class='adicionar' value='adicionar' style='margin:15px;'>"+
                         "<table id='measure'><thead>"+
                         "<tr><th></th><th>Paquetes</th><th>Largo (m.)</th><th>Ancho (m.)</th><th>Alto (m.)</th><th></th><tr></thead>"+
                         "<tbody></tbody>"+
                         "<tfoot><tr><th>Totales</th><th><label id='tot_paquete'>0</label></th><th><label id='tot_largo'>0</label></th><th><label id='tot_ancho'>0</label></th><th><label id='tot_alto'>0</label></th><th></th><tr></tfoot>"+
                         "</table>";
                    $("#field_1_4").append(html);
                    $(document).on('click', '.borrar', function (event) {
                        event.preventDefault();
                        $(this).closest('tr').remove();
                        total_largo=0;
                        $(".largo").each(function( index ) {
                            total_largo += parseFloat($(this).val());
                            /////////////////Mercancia Peligrosa//////
                            merc_remontable=$("#input_1_18").val();
                            if(merc_remontable=="No"){
                                if(parseFloat($(this).val())>3){
                                    $(".msj_peligroso").html("Estimado Cliente:<br>Esta cotización no se puede realizar por la web, pongase en contacto con nuestro departamento comercial a la dirección: info@sctrans.es <br><br>");
                                    Swal.fire({
                                      title: 'Estimado Cliente',
                                      text: 'Esta cotización no se puede realizar por la web, pongase en contacto con nuestro departamento comercial a la dirección: info@sctrans.es ',
                                      icon: 'warning'
                                    });
                                   $("#gform_submit_button_1").prop("disabled",true);
                                }else{
                                    $(".msj_peligroso").html("");
                                    $("#gform_submit_button_1").prop("disabled",false);
                                }
                            }
                        });
                        $("#tot_largo").html(total_largo);
                        $("#input_1_29").val(total_largo);
                        total_ancho=0;
                        $(".ancho").each(function( index ) {
                            total_ancho += parseFloat($(this).val());
                        });
                        $("#tot_ancho").html(total_ancho);
                        $("#input_1_30").val(total_ancho);
                        total_alto=0;
                        $(".alto").each(function( index ) {
                            total_alto += parseFloat($(this).val());
                        });
                        $("#tot_alto").html(total_alto);
                        $("#input_1_31").val(total_alto);
                        total_paquete=0;
                        $(".paquete").each(function( index ) {
                            total_paquete += parseFloat($(this).val());
                        });
                        $("#tot_paquete").html(total_paquete);
                        $("#input_1_3").val(total_paquete);
                        ///////////volumen/////
                        volumen=total_largo*total_alto*total_ancho;
                        $("#input_1_6").val(volumen);
                    });
                    $(document).on('click', '.adicionar', function (event) {
                        add="<tr>"+
                            "<td></td>"+
                            "<td style='text-align:center'><input type='number' class='paquete' value='1' min='1' style='max-width:65px'></td>"+
                            "<td style='text-align:center'><input type='number' class='largo' value='0' min='0' style='max-width:65px'></td>"+
                            "<td style='text-align:center'><input type='number' class='ancho' value='0' min='0' style='max-width:65px'></td>"+
                            "<td style='text-align:center'><input type='number' class='alto'  value='0' min='0' style='max-width:65px'></td>"+
                            "<td style='text-align:center'><input type='button' class='borrar' value='Eliminar' /></td>"+
                            "</tr>";
                        $("#measure tbody").append(add);    
                        total_paquete=0;
                        $(".paquete").each(function( index ) {
                            total_paquete += parseFloat($(this).val());
                        });
                        $("#tot_paquete").html(total_paquete);
                        $("#input_1_3").val(total_paquete);
                        volumen=calcular_volumen();
                        $("#input_1_6").val(volumen.toFixed(2));
                    });
                    $(document).on('change','.largo',function(event){
                        if($(this).val()==""){
                            $(this).val('0'); 
                        }
                        total_largo=0;
                        $(".largo").each(function( index ) {
                            total_largo += parseFloat($(this).val());
                        });
                        $("#tot_largo").html(total_largo);
                        $("#input_1_29").val(total_largo);
                        volumen=calcular_volumen();
                        $("#input_1_6").val(volumen.toFixed(2));
                        /////////////////Mercancia Peligrosa//////
                        //actualizar_boton_cotizar();
                        merc_remontable=$("#input_1_18").val();
                        if(merc_remontable=="No"){
                            if(parseFloat($(this).val())>3){
                                $(".msj_peligroso").html("Estimado Cliente:<br>Esta cotización no se puede realizar por la web, pongase en contacto con nuestro departamento comercial a la dirección: info@sctrans.es <br><br>");
                                Swal.fire({
                                      title: 'Estimado Cliente',
                                      text: 'Esta cotización no se puede realizar por la web, pongase en contacto con nuestro departamento comercial a la dirección: info@sctrans.es ',
                                      icon: 'warning'
                                });
                               $("#gform_submit_button_1").prop("disabled",true);
                            }else{
                                if(validar_largo_merc()){
                                    $(".msj_peligroso").html("");
                                    $("#gform_submit_button_1").prop("disabled",false);
                                }
                            }
                        }
                    });
                    $(document).on('change','.ancho',function(event){
                        if($(this).val()==""){
                            $(this).val('0'); 
                        }
                        total_ancho=0;
                        $(".ancho").each(function( index ) {
                            total_ancho += parseFloat($(this).val());
                        });
                        $("#tot_ancho").html(total_ancho);
                        $("#input_1_30").val(total_ancho);
                        volumen=calcular_volumen();
                        $("#input_1_6").val(volumen.toFixed(2));
                    });
                    $(document).on('change','.alto',function(event){
                        if($(this).val()==""){
                            $(this).val('0'); 
                        }
                        total_alto=0;
                        $(".alto").each(function( index ) {
                            total_alto += parseFloat($(this).val());
                        });
                        $("#tot_alto").html(total_alto);
                        $("#input_1_31").val(total_alto);
                        volumen=calcular_volumen();
                        $("#input_1_6").val(volumen.toFixed(2));
                    });
                    $(document).on('change','.paquete',function(event){
                        if($(this).val()=="" || $(this).val()=="0"){
                            $(this).val('1'); 
                        }
                        total_paquete=0;
                        $(".paquete").each(function( index ) {
                            total_paquete += parseFloat($(this).val());
                        });
                        $("#tot_paquete").html(total_paquete);
                        $("#input_1_3").val(total_paquete);
                        volumen=calcular_volumen();
                        $("#input_1_6").val(volumen.toFixed(2));
                    });
                    
                    $(document).on('change','.largo, .alto, .ancho',function(event){
                        if($('.largo').val() == 0 || $('.alto').val() == 0 || $('.ancho').val() == 0){
                            $("#gform_submit_button_1").prop("disabled",true);
                        }else{
                            $("#gform_submit_button_1").prop("disabled",false);
                        }

                        if(peso == "" || peso == 0){
                            $('#gform_submit_button_1').prop('disabled', true);
                            $("#msj_1_5").html('Peso vacío.');
                            $("#msj_1_5").addClass('validation_message');
                        }else{
                            $("#msj_1_5").html('');
                            $("#msj_1_5").removeClass('validation_message');
                        }
                        
                        if(cod_postal_destino == ""){
                            $("#msj_1_10").html('Codigo Postal vacío.');
                            $("#msj_1_10").addClass('validation_message');
                        }else{
                            $("#msj_1_10").html('');
                            $("#msj_1_10").removeClass('validation_message');
                        }

                        if(cod_postal_origen == ""){
                            $("#msj_1_11").html('Codigo Postal vacío.');
                            $("#msj_1_11").addClass('validation_message');
                        }else{
                            $("#msj_1_11").html('');
                            $("#msj_1_11").removeClass('validation_message');
                        }
                    });

                    if($("#input_1_18").val()=="Si"){
                        $(".msj_peligroso").html("Estimado Cliente:<br>Esta cotización no se puede realizar por la web, pongase en contacto con nuestro departamento comercial a la dirección: info@sctrans.es <br><br>");
                        Swal.fire({
                            title: 'Estimado Cliente',
                            text: 'Esta cotización no se puede realizar por la web, pongase en contacto con nuestro departamento comercial a la dirección: info@sctrans.es ',
                            icon: 'warning'
                        });
                        $("#gform_submit_button_1").prop("disabled",true); 
                    }else{
                        $(".msj_peligroso").html("");
                        $("#gform_submit_button_1").prop("disabled",false);
                    }
                    $("#input_1_18").change(function(){
                        actualizar_boton_cotizar();
                    });
                });
            })(jQuery);    
        </script>
    <?php
    ?>
    <?php } ?>
</div>