<?php

/**
 *
 * Plugin Name: Cotizador
 * Plugin URL: https://reduncle.es/
 * Description: Cotizador para la empresa SCTRANS
 * Version: 0.1
 * Author: Ronald Salazar
 * Author URI: https://reduncle.es/
 *
 **/

add_action('admin_menu', 'menu');

function menu() {
    add_menu_page('Config', 'Config Cotizador', 'administrator', 'menu_cotizador_id', 'funcion_config_cotizador_menu','',51);
    //add_submenu_page('menu_cotizador_id', 'Quote Response Interface OLD', 'Quote Response Interface OLD', 'administrator', 'quote_interface_test', 'quote_interface_test');
}
function funcion_config_cotizador_menu(){
    require ('config.php');
}

function rucotizador_shortcode($atts) {
    ob_start();
    include_once( WP_PLUGIN_DIR . '/cotizadorx/formulario.php');    
    $content = ob_get_clean();
    return $content;    
}
add_shortcode('cotizador_sctrans', 'rucotizador_shortcode');


function wpdocs_scripts_method() {
    //linea siguiente cambiada por xinxeta 
	//wp_enqueue_script( 'rucotizadora_jquery',  'https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js', false, '1.1',true);
	wp_enqueue_script( 'rucotizadora_jquery',  'https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js', false, '1.1',true);
    wp_enqueue_style( 'rucotizadora_style',  plugin_dir_url(__FILE__) . 'style.css', false, '1.1.2', 'all');
    wp_enqueue_script( 'script', plugin_dir_url(__FILE__). 'cotizador.js', false, '1.6.19', true);
    wp_enqueue_script('sweetalert_script','//cdn.jsdelivr.net/npm/sweetalert2@11', false, '1.6.19', true);
}
function admin_scripts_method() {
    wp_enqueue_style( 'rucotizadora_style',  plugin_dir_url(__FILE__) . 'style.css', false, '1.1.2', 'all');
    wp_enqueue_script( 'script', plugin_dir_url(__FILE__). 'cotizador.js', false, '1.2.14', true);
}
add_action( 'wp_enqueue_scripts', 'wpdocs_scripts_method' );
add_action('admin_enqueue_scripts', 'admin_scripts_method');


function get_precio_por_zip($zip, $peso,$largo){
    //verificar la correcta longitud del codigo postal
    $prefijo_cp = '';
    $tarifa_base = 0;

    //variables zonas
    $prefijo_x_zonas = [46,12];
    $tarifas_x_zonas = '';
    $localidades = '';
    $tarifa_zona_bool = false;
    $zona_ubi = '';

    if (strlen($zip) == 5 ){        
        $prefijo_cp = substr($zip, 0, 2);
    } else {        
        return false;
    }

    //si el código postal es de valencia se buscará a que zona pertenece
    if (in_array($prefijo_cp, $prefijo_x_zonas)) {

        //filtramos por el post_type de castellon valencia para sacar su info y de ahí cogeremos sus zonas y demás
        $zona_posts = get_posts(array(
            'post_type'=>'valencia_castellon',
            'order' => 'DESC', 
            'numberposts' => '200'
        ));
        $postmeta_id_zona = get_post_meta();
        /*aquí lo que voy a hacer va a ser extraer la id de la zona y luego extraeré la id asignada que tienen los post_metas,
        Si coinciden entonces sabremos en que zona nos ubicamos y luego de ahí podremos sacara la tarifa de la zona*/

            foreach ($zona_posts as $zona_post) {
                $postmeta_cp = get_post_meta(
                    $zona_post->ID,
                    'codigo_postal',
                    true
                );
                $posts_array = explode(' ',$postmeta_cp);
                if(in_array($zip, $posts_array)){
                    $zona_ubi = get_post_meta($zona_post->ID,'nombre',true);
                    $tarifas_x_zonas = get_post_meta($zona_post->ID, 'tarifa_por_peso','DESC' );
                    /*Deberemos implantar un código que detecte el código postal donde nos ubicamos para poder aceptarlo*/
                    $tarifa_zona_bool = true;
                    var_dump("Código postal detectado" .$zip. "<br>Extrayendo los códigos postales de la " .$zona_ubi. ": " .$postmeta_cp. "<br>Donde vamos a extraer las siguientes tarifas de la zona correspondiente: " .$tarifas_x_zonas);
                    break; // salimos del loop si encontramos una zona válida
                }else{
                    $tarifa_zona_bool = false;
                    var_dump("<br>Lo sentimos pero aunque haya pasado los filtros o este CP no existe o has introducido losdatos incorrectamente"); /*BORRAR*/
                }

            }     
    }
    // buscar la localidad
    if(!in_array($prefijo_cp, $prefijo_x_zonas)){
        $localidades = get_posts(array('post_type'=>'valencia_provincia', 'order' => 'DESC', 'numberposts' => '100'));
        $localidad_seleccionada = false;
        foreach ($localidades as $localidad) {
            if (strpos($localidad->post_title, '(' . $prefijo_cp . ')') !== false) {
                $localidad_seleccionada = $localidad;
                $tarifas_string = get_post_meta($localidad_seleccionada->ID, 'tarifa_por_peso', true);           
                break;
            }
        }
    }

    /*if($tarifa_zona_bool === true || $localidad_seleccionada !== false){
        return true;
    }*/ //Esto ya lo sovlventaremos!!!!
    
    /*if($localidad_seleccionada === false){
        return false;
    }*/

    // Obtener array de tarifas de la localidad seleccionada o de la zona seleccionada

    var_dump("<br>".$localidad->post_title."<-Esta es la comunidad asignada y sus datos->".$tarifas_string);
    var_dump("Valencia->".$tarifas_x_zonas);

    if(empty($tarifas_string)) {
        $tarifas = explode("\n", $tarifas_x_zonas );
    } else {
        $tarifas = explode("\n", $tarifas_string );
    }

    $tarifas_formateada = array();
    foreach ($tarifas as $tarifa) {
        $valores_array = explode("-", str_replace(' ', '', $tarifa));
        if (count($valores_array) != 3){            
            return false;
        }
        $tarifas_formateada[] = array(
            'peso_inicial' => $valores_array[0],
            'peso_final' => $valores_array[1],
            'precio' => $valores_array[2]
        );
    }    
    // Vasco 01,20,48  2.4 mts largo->consultar
    // Madrid 28       3 mts largo 25%
    // Andalucia 04    2.4 mts largo 25%
    // Valencia 03     3.5 mts largo 25%
    // Obtener el precio segun el peso    
    foreach ($tarifas_formateada as $tarifa) {        
        if ($peso >= $tarifa['peso_inicial'] && $peso <= $tarifa['peso_final'] ) {
            if($largo>3 && $prefijo_cp=='28'){
               $tarifa_base = (float)$tarifa['precio']+((float)$tarifa['precio']*0.25);
            }else{
                if($largo>2.4 && $prefijo_cp=='04'){
                    $tarifa_base = (float)$tarifa['precio']+((float)$tarifa['precio']*0.25);
                }else{
                   if($largo>3.5 && $prefijo_cp=='03'){
                      $tarifa_base = (float)$tarifa['precio']+((float)$tarifa['precio']*0.25);
                   }else{
                      $tarifa_base = $tarifa['precio'];
                   }
                }
            }            
        }

    }    
    return $tarifa_base;    
}


function send_add_contratar(){
    global $wpdb;
    $email = empty(get_option('cotizacion_email'))?'test888999@mailinator.com':get_option('cotizacion_email') ;
    $nro_cotizacion=$_POST['nro_cotizacion']; 
    $dir_recogida=$_POST['dir_recogida'];
    $dir_entrega=$_POST['dir_entrega'];
    $ref_recogida=$_POST['ref_recogida'];
    $ref_entrega=$_POST['ref_entrega'];
    $sql = " SELECT * ".
           " FROM ".$wpdb->prefix."gf_entry_meta ".
           " WHERE `form_id` = 1 AND `entry_id` =".$nro_cotizacion;
    $result = $wpdb->get_results($sql);    
    /////////////////////////////////
    $impt_plataforma=empty(get_option('cotizacion_plataforma_elevadora'))?'35':get_option('cotizacion_plataforma_elevadora');
    $nombre_user="";
    $valor_envio_aereo = 0;
    $valor_plataforma_elevadora = 0;
    $mercancia_peligrosa="No";
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
    $html='';
    if(count($result)==0){
        $html='<h1>No existe Cotizacion</h1>';
        wp_mail($email, "Cotizacion", $html);
        exit();  
    }
    $headers = array('content-type: text/html'); 
    foreach ($result as $value) {
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
                break;
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
        $precio_base = get_precio_por_zip($cod_postal_origen, $peso_cotizar,$largo);
    }else{
        $precio_base = get_precio_por_zip($cod_postal_destino, $peso_cotizar,$largo); 
    }
    
    if ($precio_base == false) {
       $html='<h1>Lo siento, No podemos obtener el precio para el código postal ingresado.</h1>';
       wp_mail($email, "Cotizacion", $html,$headers);
       exit();
    }    
    $importe_por_mozo_hora=round($valor_importe_mozo*$peonaje,2);
    $valor_rec_comb=0;
    if($mercancia_peligrosa=="Si"){
       $valor_mercancia_peligrosa=round($precio_base*0.30,2);
       $valor_rec_comb=round((($precio_base+$valor_mercancia_peligrosa)*($rec_comb/100)),2 );
       $total = $precio_base + $valor_envio_aereo + $valor_plataforma_elevadora + $valor_mercancia_peligrosa+$importe_por_mozo_hora+$valor_rec_comb;
    }else{
       $total = $precio_base + $valor_envio_aereo + $valor_plataforma_elevadora + $valor_mercancia_peligrosa+$importe_por_mozo_hora; 
    }
    
   
    $sql = " UPDATE ".$wpdb->prefix."gf_entry_meta ".
           " set meta_value='contratado' ".
           " WHERE `form_id` = 1 AND `entry_id` =".$nro_cotizacion." and meta_key='32' ";
    $wpdb->get_results($sql);    
    $html ='<div class="confirmacion" >'.
           '<p>Muchas gracias por contratar nuestro servicio. La cotización generada tiene como referencia el #'.$nro_cotizacion.'.<br> 
            Para encontrar nuestros diferentes canales de contacto en nuestra página <a href="https://sctrans.es/contacto-y-cotizacion/"traget="_blank">https://sctrans.es/contacto-y-cotizacion/</a> para obtener información sobre su servicio.También puede revisar  sobre las condiciones generales del transporte terrestre aquí  <a href="https://sctrans.es/condiciones-generales-transporte-terrestre/" target="_blank">https://sctrans.es/condiciones-generales-transporte-terrestre/ </a></p>'.
           '<p><b><label>Direccion de Recogida: </label></b>'.$dir_recogida.'</p>'.
           '<p><b><label>Direccion de Entrega: </label></b>'.$dir_entrega.'</p>'.
           '<p><b><label>Referencia de Recogida: </label></b>'.$ref_recogida.'</p>'.
           '<p><b><label>Referencia de Entrega: </label></b>'.$ref_entrega.'</p>'.
           '<h4>Cotización #'.$nro_cotizacion.'</h4>'.
           '<p>Su cotización fue generada exitosamente. Esta cotización está sujeta a los siguientes términos y condiciones que se enunciona <a href="'.site_url().'/condiciones-generales-transporte-terrestre/" target="_blank">aquí</a></p>
            <div class="detalle-cotizacion">
            <p><b><label>Cliente: </label></b><span class="data-cliente">'.$nombre_user.'</span></p>
            <p><b><label>Cod.Postal Origen: </label></b><span class="data-zip-origen">'.$cod_postal_origen.'</span></p>
            <p><b><label>Cod.Postal Destino:</label></b><span class="data-zip-destino">'.$cod_postal_destino.'</span></p>
            <p><b><label>Paquetes: </label></b><span class="data-paquetes">'.$paquetes.'</span></p>
            <p><b><label>Peso: </label></b><span class="data-peso">'.$peso.'</span>kg.</p>
            <p><b><label>Volumen: </label></b><span class="data-volumen">'.$volumen.'</span>m3</p>
            <p><b><label>Mozo/Hora: </label></b><span class="data-volumen">'.$valor_importe_mozo.'</span>hora</p>
            <table>
                <thead>
                    <tr><th>Descripción</th><th>Total</th></tr>
                </thead>
                <tbody>
                    <tr><td><b>Precio Base</b></td>
                        <td><span id="valor_precio_base">'.$precio_base.'€</span></td>
                    </tr>
                    <tr><td><b>Envío Aereo</b></td>
                        <td><span id="valor_envio_aereo">'.$valor_envio_aereo.'€</span></td>
                    </tr>
                    <tr><td><b>Plataforma Elevadora</b></td>
                        <td><span id="valor_plataforma_elevadora">'.$valor_plataforma_elevadora.'€</span></td>
                    </tr>
                    <tr><td><b>Mercancía Peligrosa</b></td>
                        <td><span id="valor_mercancia_peligrosa">'.$valor_mercancia_peligrosa.'€</span></td>
                    </tr>';
    if($mercancia_peligrosa=="Si"){
        $html .='   <tr><td><b>Recargo del combustible</b></td>
                        <td><span id="valor_recargo_combustible">'.$valor_rec_comb.'€</span></td>
                    </tr>';
    }                
    $html .='       <tr><td><b>Peonaje por Hora/Mozo</b></td>
                        <td><span id="importe_por_mozo_hora">'.$importe_por_mozo_hora.'€</span></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr><td><b>TOTAL</b></td>
                        <td><span id="valor_total">'.$total.'€</span></td>
                    </tr>
                </tfoot>
            </table>
            </div>
            <p>Si usted tiene alguna pregunta sobre esta cotización, por favor, póngase en contacto con nosotros.</p>        
        </div>';
    /*$headers = array(
        //'From: Me Myself <[email protected]>',
        'content-type: text/html',
        //'Cc: John Q Codex <[email protected]>',
        //'Cc: [email protected]',
    );*/    
    $usuario = get_userdata (get_current_user_id());
    $usuario_email=$usuario->user_email;
    wp_mail($email,"SC Trans -  Servicio Contratado",$html,$headers);
    //$email="rodrigolopez2012187@gmail.com";
    //wp_mail($email,"SC Trans -  Servicio Contratado",$html,$headers);
    wp_mail($usuario_email,"SC Trans -  Servicio Contratado",$html,$headers);

    

}

add_action('wp_ajax_send_add_contratar', 'send_add_contratar');
add_action('wp_ajax_nopriv_send_add_contratar', 'send_add_contratar');

function save_config_cotizacion(){
    $peonaje=$_POST['peonaje'];
    $email=$_POST['email'];
    $impt_plataforma_elev=isset($_POST['impt_plataforma_elev'])?$_POST['impt_plataforma_elev']:35;
    $recargo_combustible=isset($_POST['recargo_combustible'])?$_POST['recargo_combustible']:1;
    update_option('cotizacion_peonaje',$peonaje);
    update_option('cotizacion_email',$email);
    update_option('cotizacion_plataforma_elevadora',$impt_plataforma_elev);
    update_option('cotizacion_recargo_combustible',$recargo_combustible);
}
add_action('wp_ajax_save_config_cotizacion','save_config_cotizacion');
add_action('wp_ajax_nopriv_save_config_cotizacion','save_config_cotizacion');

function validar_codigo_postal(){
    $volumen=$_POST['volumen'];
    $peso=$_POST['peso'];
    $cod_postal_destino=$_POST['cod_postal_destino'];
    $cod_postal_origen=$_POST['cod_postal_destino'];
    $largo=$_POST['largo'];
    $volumen_cot=$volumen*270;
    if($volumen_cot>$peso){
       $peso_cotizar=$volumen*270;
    }else{
       $peso_cotizar=$peso;
    }
    $tipo_servicio=$_POST['tipo_servicio'];
    if($tipo_servicio=="Recogida"){
        //$precio_base = get_precio_por_zip($cod_postal_destino,$peso_cotizar,$largo);
        $prefijo_cp = '';
        $tarifa_base = 0;    
        if (strlen($cod_postal_destino) == 5 ){        
            $prefijo_cp = substr($cod_postal_destino, 0, 2);       
            // buscar la localidad
            $localidades = get_posts(array('post_type'=>'valencia_provincia', 'order' => 'DESC', 'numberposts' => '100'));
            $localidad_seleccionada = false;
            foreach ($localidades as $localidad) {        
                if (strpos($localidad->post_title, '(' . $prefijo_cp . ')') !== false) {
                    $localidad_seleccionada = $localidad;            
                    break;
                }
            }    
            if ($localidad_seleccionada === false) {        
                echo "false";
            }else{
                echo "true";
            } 
        } else {        
            echo "false";
        } 
    }else{
        //$precio_base = get_precio_por_zip($cod_postal_origen ,$peso_cotizar,$largo); 
        $prefijo_cp = '';
        $tarifa_base = 0;    
        if (strlen($cod_postal_origen) == 5 ){        
            $prefijo_cp = substr($cod_postal_origen, 0, 2);       
            // buscar la localidad
            $localidades = get_posts(array('post_type'=>'valencia_provincia', 'order' => 'DESC', 'numberposts' => '100'));
            $localidad_seleccionada = false;
            foreach ($localidades as $localidad) {        
                if (strpos($localidad->post_title, '(' . $prefijo_cp . ')') !== false) {
                    $localidad_seleccionada = $localidad;            
                    break;
                }
            }    
            if ($localidad_seleccionada === false) {        
                echo "false";
            }else{
                echo "true";
            } 
        } else {        
            echo "false";
        } 
    }
    exit();
}

add_action('wp_ajax_validar_codigo_postal','validar_codigo_postal');
add_action('wp_ajax_nopriv_validar_codigo_postal','validar_codigo_postal');

function sctrans_create_pdf() {
    ob_start();
    require_once 'include_PDF_Attachmment.php';
    return ob_get_clean();
}
add_shortcode("sctrans_es_create_pdf", "sctrans_create_pdf", 10);

?>