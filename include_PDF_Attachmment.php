<?php
$texthtmltest = generatehtml();
echo $texthtmltest;

function generatehtml() {
    global $wpdb;
    $uri=explode('/',$_SERVER['REQUEST_URI']);
    if($uri[1]=="pdf" && strlen($uri[3])>0){
        $entryid=$uri[3];
        $sql = " SELECT * ".
               " FROM ".$wpdb->prefix."gf_entry_meta ".
               " WHERE `form_id` = 1 AND `entry_id` =".$entryid;
        $result = $wpdb->get_results($sql);
        $sql_data = " SELECT * ".
               " FROM ".$wpdb->prefix."gf_entry ".
               " WHERE `form_id` = 1 AND `id` =".$entryid;
        $result_data = $wpdb->get_results($sql_data);
        date_default_timezone_set('Europe/Madrid');
        foreach($result_data as $fecha){
            $fecha_hora = date('Y-m-d H:i:s', strtotime($fecha->date_created . '+1 hour'));
        }
        $impt_plataforma=empty(get_option('cotizacion_plataforma_elevadora'))?'35':get_option('cotizacion_plataforma_elevadora');
        $nombre_user="";
        $usuario = get_userdata (get_current_user_id());
        $user_publico = $usuario->user_email;
        $valor_envio_aereo = 0;
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
            $precio_base = get_precio_por_zip($ciudad_origen, $cod_postal_origen, $peso_cotizar,$largo);
        }else{
            $precio_base = get_precio_por_zip($ciudad_origen, $cod_postal_origen, $peso_cotizar,$largo); 
        }
        if ($precio_base == false) {
           echo "<h1>Lo siento, No podemos obtener el precio para el código postal ingresado.</h1>";
           exit();
        }    
        $importe_por_mozo_hora=round($valor_importe_mozo*$peonaje,2);
        $valor_rec_comb=0;
        if($mercancia_peligrosa=="Si"){
            $valor_mercancia_peligrosa=round($precio_base*0.30,2);
            $valor_rec_comb=round((($precio_base+$valor_mercancia_peligrosa)*($rec_comb/100)),2 );
            $total = $precio_base + $valor_envio_aereo + $valor_plataforma_elevadora + $valor_mercancia_peligrosa+$importe_por_mozo_hora+$valor_rec_comb;
        }else{
            $total = $precio_base+$valor_envio_aereo+$valor_plataforma_elevadora+$valor_mercancia_peligrosa+$importe_por_mozo_hora;
        }
        
        ?>
        <style>
            .hidden_field,#field_20,#field_17 {
                display: none;
            }
            .gform_hidden{
                display:none!important;
            }
        </style>
        <div class="confirmacion">
            <h2>Cotización #<?php echo $entryid;?></h2>
            <p>Su cotización fue generada exitosamente. Esta cotización está sujeta a los siguientes <a href="<?php echo site_url();?>/condiciones-generales-transporte-terrestre/" target="_blank">términos y condiciones</a>.</p>
            <div class="detalle-cotizacion">
                <table>
                    <tr>
                        <td style="padding: 9px; background: #0A5687; color: white; border: solid 5px white;"><strong>Nº Cotización</strong></td>
                        <td style="padding: 9px; background: #0A5687; color: white; border: solid 5px white;"><strong>Fecha</strong></td>
                        <td style="padding: 9px; background: #0A5687; color: white; border: solid 5px white;" ><strong>Cliente</strong></td> 
                    </tr>
                    <tr>
                        <td style="padding: 9px; background: #F9F6F0; color: #010100; border: solid 5px white;"><?php echo $entryid;?></td>
                        <td style="padding: 9px; background: #F9F6F0; color: #010100; border: solid 5px white;"><?php echo $fecha_hora; ?></td>
                        <td style="padding: 9px; background: #F9F6F0; color: #010100; border: solid 5px white;"><?php echo $user_publico;?></td>
                    </tr>
                </table>
                <br>
                <table class="table1">
                    <tbody>
                        <tr>
                            <td style="padding: 9px; background: #0A5687; color: white; border: solid 5px white;" ><strong>C.P. Origen</strong></td>
                            <td colspan="2" style="padding: 9px; background: #F9F6F0; color: #010100;" ><?php echo $cod_postal_origen; ?></td>
                            <td style="padding: 9px; background: #0A5687; color: white; border: solid 5px white;" ><strong>C.P. Destino</strong></td>
                            <td colspan="2" style="padding: 9px; background: #F9F6F0; color: #010100;" ><?php echo $cod_postal_destino; ?></td>
                        </tr>
                    </tbody>
                </table>
                <table>
                    <tbody>
                        <tr>
                            <td style="padding: 9px; background: #0A5687; color: white; border: solid 5px white;" ><strong>Paquetes </strong></td>
                            <td style="padding: 9px; background: #F9F6F0; color: #010100; border: solid 5px white;" ><?php echo $paquetes;?></td>
                            <td style="padding: 9px; background: #0A5687; color: white; border: solid 5px white;" ><strong>Peso </strong></td>
                            <td style="padding: 9px; background: #F9F6F0; color: #010100; border: solid 5px white;" ><?php echo $peso;?> kg.</td>
                            <td style="padding: 9px; background: #0A5687; color: white; border: solid 5px white;" ><strong>Volumen </strong></td>
                            <td style="padding: 9px; background: #F9F6F0; color: #010100; border: solid 5px white;" ><?php echo $volumen;?> m3</td>   
                        </tr>
                    </tbody>
                </table>
                <br>
                <table>
                    <tbody >
                        <tr style="">
                            <td style="padding: 9px; background: #0A5687; color: white; border: solid 5px white;" ><strong>Precio Base</strong></td>
                            <td style="padding: 9px; text-align:right; background: #F9F6F0; color: #010100"><span id="valor_precio_base"><?php echo number_format($precio_base,2); ?> </span>€</td>
                        </tr>
                        <tr style="">
                            <td style="padding: 9px; background: #0A5687; color: white; border: solid 5px white;" ><strong>Suplemento Aeropuerto/Puerto</strong></td>
                            <td style="padding: 9px; text-align:right; background: #F9F6F0; color: #010100;"><span id="valor_envio_aereo"></span><?php echo $valor_envio_aereo; ?> €</td>
                        </tr>
                        <tr style="">
                            <td style="padding: 9px; background: #0A5687; color: white; border: solid 5px white;" ><strong>Plataforma Elevadora</strong></td>
                            <td style="padding: 9px; text-align:right; background: #F9F6F0; color: #010100;">
                                <span id="valor_plataforma_elevadora"></span><?php echo $valor_plataforma_elevadora;?> €
                            </td>
                        </tr>
                        <tr style="">
                            <td style="padding: 9px; background: #0A5687; color: white; border: solid 5px white;" ><strong>Mercancía Peligrosa</strong></td>
                            <td style="padding: 9px; text-align:right; background: #F9F6F0; color: #010100; border: solid 5px white;">
                                <span id="valor_mercancia_peligrosa">
                                <?php echo $valor_mercancia_peligrosa; ?> </span>€
                            </td>
                        </tr>
                        <?php if($mercancia_peligrosa=="Si"){ ?>
                        <tr><td style="padding: 9px; background: #0A5687; color: white; border: solid 5px white;" ><strong>Recargo del combustible <?php echo "(".$rec_comb."%)"?></strong></td>
                            <td style="padding: 9px; text-align:right; background: #F9F6F0; color: #010100; border: solid 5px white;">
                                <span id="valor_recargo_combustible">
                                <?php echo $valor_rec_comb; ?> </span>€
                            </td>
                        </tr>
                        <?php } ?>  
                        <tr style="">
                            <td style="padding: 9px; background: #0A5687; color: white; border: solid 5px white;" ><strong>Peonaje por Hora/Mozo</strong></td>
                            <td style="padding: 9px; text-align:right; background: #F9F6F0; color: #010100; border: solid 5px white;">
                                <span id="importe_por_mozo_hora">
                                <?php echo $importe_por_mozo_hora; ?> </span>€
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr><td style="padding: 9px; background: #0A5687; color: white; border: solid 5px white;" ><h2><strong>TOTAL:</strong></h2></td>
                            <td style="padding: 9px; text-align:right; background: #F9F6F0; font-weight:bold; border: solid 5px white;"><span id="valor_total"><?php echo number_format($total, 2);?></span> €</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <!--<p>Si usted tiene alguna pregunta sobre esta cotización, por favor, póngase en contacto con nosotros.</p>-->        
        </div>
    <?php }

    ?>
   
    <!--<link rel="stylesheet" href="<?php //echo get_template_directory_uri(); ?>/elements/styles/pdf_email.css" type="text/css" media="all" />
    <table> 
        <tbody>
            <tr>
                <td style="padding: 9px; background: #0A5687; color: #010100;" >
                    <table>
                        <tr>
                            <td valign="top" align="left" >
                                <img src="<?php //echo site_url(); ?>/wp-content/uploads/2015/04/350.png" style="border:non;display:inline;font-size:16px;font-weight:bold;min-height:auto;line-height:100%;outline:none;text-decoration:none;text-transform:capitalize" ><br><br>
                            </td>
                            <td valign="top" align="right" >
                                <img src="<?php //echo site_url(); ?>/wp-content/uploads/2015/04/350.png" style="border:non;display:inline;font-size:16px;font-weight:bold;min-height:auto;line-height:100%;outline:none;text-decoration:none;text-transform:capitalize" ><br><br>
                            </td>
                        </tr>
                        <tr>
                            <td valign="top" align="left">
                                <img src="<?php //echo site_url(); ?>/wp-content/uploads/spellbrite_logo_forinvoice-1.jpg" style="border:non;display:inline;font-size:16px;font-weight:bold;min-height:auto;line-height:100%;outline:none;text-decoration:none;text-transform:capitalize" ><br><br>
                            </td>
                            <td valign="top" align="right">
                                <p style="margin: 3px 0px;font-size: 16px;height: 25px; ">SpellBrite® Signs</p>
                                <p style="margin: 3px 0px;font-size: 16px;height: 25px;">242 N York St, Suite 503</p>
                                <p style="margin: 3px 0px;font-size: 16px;height: 25px;">Elmhurst, IL 60126</p>
                                <p style="margin: 3px 0px;font-size: 16px;height: 25px;">Phone: (312) 575-9620</p>
                                <p style="margin: 3px 0px;font-size: 16px;height: 25px;"><a href="mailto:info@spellbrite.com" target="_blank" style="font-size: 16px;line-height: 16px;"> info@SpellBrite.com</a></p>                             
                                <p style="margin: 3px 0px;font-size: 16px;height: 25px;"><a href="https://www.spellbrite.com" target="_blank" style="font-size: 16px;line-height: 16px;">www.SpellBrite.com</a></p>                             
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td valign="top" align="center" style=" color:#222;font-size: 30px; font-weight: 100; text-decoration: none;" >
                    <br>
                    Scale Image of Your SpellBrite Sign
                    <br>
                    <br> 
                    <br> 
                </td>
            </tr>
            <?php //echo $LineTexthtml ?>
            <tr>
                <td valign="top" align="center" style="font-size: 16px;">
                    <br>
                    <b>Total Power Draw:</b> <?php //echo $LineMilliampshml; ?>
                    <br>
                    <br>
                </td>
            </tr>
            <tr>
                <td valign="top" align="center" style="font-size: 16px;" >
                    <br>
                    <b >Shipping Weight: </b><?php //echo $Weight; ?> lbs.
                    <br>
                    <br>

                </td>
            </tr>
            <tr>
                <td valign="top" align="center" style="font-size:14px; font-weight:bold; color: #444;">
                    <br>Your sign includes all necessary accessories including power supplies and hanging hardware.
                    <br>
                    <?php //if(isset($_SESSION['url_sign_pdf'])&& isset($_SESSION['url_fecha_exp']) ){ ?> 
                         <br>Click <a style="color:#ee3124;text-decoration:none" href="<?php //echo $_SESSION['url_sign_pdf']; ?>">HERE</a> to ORDER (expires on <?php //echo $_SESSION['url_fecha_exp']; ?>)
                    <?php //} ?>   
                </td>
            </tr>
        </tbody>
    </table>-->
    <?php
}

function GenratePDFAttachmment() {
    global $urlpdfmail1;
    return $urlpdfmail1;
}


function nameChar($char) {
    if ($char === ' ') {
        return "space";
    } else if ($char === "'") {
        return "apostraphe";
    } else if ($char === '!') {
        return "exclamation";
    } else if ($char === '-') {
        return "hyphen";
    } else if ($char === '.') {
        return "period";
    } else if ($char === '%') {
        return "percent";
    } else if ($char === '&') {
        return "ampersand";
    } else if ($char === '$') {
        return "dollar";
    } else if ($char === '/') {
        return "";
    } elseif (($char . ".png") === "\.png") {
        return "";
    } else {
        return $char;
    }
}

