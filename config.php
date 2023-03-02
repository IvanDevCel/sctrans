<?php 
   $peonaje=empty(get_option('cotizacion_peonaje'))?'35':get_option('cotizacion_peonaje');
   $email = empty(get_option('cotizacion_email'))?'test888999@mailinator.com':get_option('cotizacion_email') ;
   $impt_plat_elev=empty(get_option('cotizacion_plataforma_elevadora'))?'35':get_option('cotizacion_plataforma_elevadora');
   $recargo_combustible=empty(get_option('cotizacion_recargo_combustible'))?'1':get_option('cotizacion_recargo_combustible');
   echo "<pre style='dispaly:none'>";
   print_r($recargo_combustible);
   echo "</pre>";
?>
<div class="wrap">
	<form method="post" >
		<h1>Configuracion</h1>
		<table class="form-table">
			<tbody>
				<tr>
					<th><label>Peonaje(€/hora)</label></th>
					<td><input type="number" name="peonaje" id="peonaje" value="<?php echo $peonaje; ?>"></td>
				</tr>
				<tr>
					<th><label>Email (notificación para contratar)</label></th>
					<td>
						<input required type="email" name="email" id="email_cotizacion" value="<?php echo $email; ?>" style="min-width:300px">
					</td>
				</tr>
				<tr>
					<th><label>Importe Fijo de Plataforma Elevadora (€)</label></th>
					<td>
						<input type="number" name="impt_plataforma_elev" id="impt_plataforma_elev" value="<?php echo $impt_plat_elev;?>">
					</td>
				</tr>
				<tr>
					<th><label>Recargo del combustible</label></th>
					<td>
						<input type="number" name="recargo_combustible" id="recargo_combustible" value="<?php echo $recargo_combustible;?>">
					</td>
				</tr>
			</tbody>
		</table>
		<p>
			Click <a href="https://sctrans.es/wp-admin/admin.php?page=gf_edit_forms&view=settings&subview=notification&id=1&nid=61c9e920732b5">aquí</a> para configurar los emails de notificación al realizar una cotización.
		</p>
		<p class="msj">
			
		</p>
		<p class="submit">
			<input type="button" name="submit" id="save_cotizacion" class="button button-primary" value="Guardar cambios">
		</p>
	</form>
</div>
