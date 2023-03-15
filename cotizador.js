(function($){	
	$(document).ready(function () {
	   var MyAjax = { ajaxurl: "/wp-admin/admin-ajax.php" };
	   $(".nueva-cotizacion").click(function(){
	       window.location.href=location.origin+location.pathname;
	   });
	   $(".contratar").click(function(){
			Swal.fire({
			  //title: '',
			  //input: 'text',
			  /*inputAttributes: {
			    autocapitalize: 'off'
			  },*/
			  html:'<div><label style="font-size:13px;font-weight:bold">Direccion Recogida</label><input style="width:60%;margin-left:10px;margin-right:10px" id="swal_rec" class="swal2-input" required></div>' +
			       '<div><label style="font-size:13px;font-weight:bold">Refencia Recogida</label><input style="width:60%;margin-left:10px;margin-right:10px" id="swal_ref_rec" class="swal2-input" required></div>'+
                   '<hr style="margin-top:16px;margin-bottom:2px">'+  
				   '<div><label style="font-size:13px;font-weight:bold">Direccion Entrega</label><input style="width:60%;margin-left:10px;margin-right:10px" id="swal_ent" class="swal2-input" required></div>'+
				   '<div><label style="font-size:13px;font-weight:bold">Referencia Entrega</label><input style="width:60%;margin-left:10px;margin-right:10px" id="swal_ref_ent" class="swal2-input" required></div>',
			  showCancelButton: true,
			  cancelButtonText:'Cancelar',
			  confirmButtonColor: '#007DCC',
			  confirmButtonText: 'Enviar',
			  showLoaderOnConfirm: true,
			  preConfirm: (login) => {
			      return [
					      document.getElementById('swal_rec').value,
					      document.getElementById('swal_ent').value,
					      document.getElementById('swal_ref_rec').value,
					      document.getElementById('swal_ref_ent').value
					]
			  },
			  allowOutsideClick: () => !Swal.isLoading()
			}).then((result) => {
				console.log(result);
				dir_recogida=result.value[0];
				dir_entrega=result.value[1];
				ref_recogida=result.value[2];
				ref_entrega=result.value[3];
				//console.log(result.value);
				console.log(result.value[0]);
				console.log(result.value[1]);
				if (result.isConfirmed) {
                    if(dir_recogida!='' && dir_entrega!=''){
                        nro_cotizacion=$("#nro_cotizacion").val();
				        var data = { action: "send_add_contratar",
							         email: 'test888999@mailinator.com',
							         nro_cotizacion:nro_cotizacion,
							         dir_recogida:dir_recogida,
							         dir_entrega:dir_entrega,
							         ref_recogida:ref_recogida,
							         ref_entrega:ref_entrega};
				        $.ajax({ type: "POST",
						         url: MyAjax.ajaxurl,
						         data: data,
						         dataType: 'JSON',
						         beforeSend: function(){}
						    }).done(function(result){     
						    	$(".confirmacion").html("<h2>Su solicitud ha sido enviada a nuestro equipo, y será contactado por nuestro personal muy pronto.<h2>");	
						    	Swal.fire({
								      title: `Su solicitud ha sido enviada a nuestro equipo, y será contactado por nuestro personal muy pronto.`,
								      icon: 'success',
								});	      
						    });
                    }else{
                       Swal.fire({
						  icon: 'error',
						  title: 'Error',
						  text: 'Ingrese los datos campos vacios',
						  confirmButtonColor: '#007DCC',
						});
                    }
				}
			});
	   });
	   $("#save_cotizacion").click(function(){
		   	peonaje=$("#peonaje").val();
		   	email=$("#email_cotizacion").val();
		   	impt_plataforma_elev=$("#impt_plataforma_elev").val();
		   	recargo_combustible=$("#recargo_combustible").val();
		   	console.log(peonaje);
	   	    if(peonaje==""){
	          alert("Ingrese el peonaje");
	          return false;
	   	    }
	   	    if(email==""){
	          alert("Ingrese el correo electrónico");
	          return false;
	   	    }
	   	    if(impt_plataforma_elev==""){
	          alert("Ingrese la plataforma elevadora");
	          return false;
	   	    }
	   	    if(recargo_combustible==""){
              alert("Ingrese recargo combustible");
              return false; 
	   	    }
	   	    if(email.indexOf('@', 0) == -1 || email.indexOf('.', 0) == -1) {
              alert('El correo electrónico introducido no es correcto.');
              return false;
            }
	   	    var b = {action: "save_config_cotizacion",peonaje:peonaje,email:email,impt_plataforma_elev:impt_plataforma_elev,recargo_combustible:recargo_combustible};
		    $.post(MyAjax.ajaxurl, b, function (c) {
		    	console.log(recargo_combustible);
		        $(".msj").html("Se guardo correctamente!.");
		        setTimeout(function(){$(".msj").html("");},2000);
		    });
	   });

	   // desactivar el botón de envío al cargar la página
		$('#gform_submit_button_1').prop('disabled', true);

	   //Con el código de aquí verificaremos todo a golpe de teclado
	   $("input").blur(function(){
          peso=$("#input_1_5").val();
		  isUnvalid = false;
          cod_postal_origen=$("#input_1_11").val();
          cod_postal_destino=$("#input_1_10").val();
          largo=$("#input_1_29").val();
          tipo_servicio=$("#input_1_15").val();
		  zonas_prefijo = ['46', '12'];
		  codigos_zona = $('#input_1_36').val();


		// VERIFICAMOS QUE TODOS LOS CAMPOS ESTEN LLENOS SI NO LO ESTÁN EL BOTO DE ENVIO SE DESHABILITA
			if (cod_postal_destino == "") {
				$("#msj_1_10").html('Codigo Postal vacío.');
				$("#msj_1_10").addClass('validation_message');
				$('#gform_submit_button_1').prop('disabled', true);
			}else{
				$("#msj_1_10").html('');
				$("#msj_1_10").removeClass('validation_message');
			}

			if (cod_postal_origen == "") {
				$("#msj_1_11").html('Codigo Postal vacío.');
				$("#msj_1_11").addClass('validation_message');
					$('#gform_submit_button_1').prop('disabled', true);
			}else{
				$("#msj_1_11").html('');
				$("#msj_1_11").removeClass('validation_message');
			}

			if (peso == "" || peso == 0) {
				$("#msj_1_5").html('El peso debe ser mayor a 0 y no puede estar vacío');
				$("#msj_1_5").addClass('validation_message');
				$('#gform_submit_button_1').prop('disabled', true);
			}else{
				$("#msj_1_5").html('');
				$("#msj_1_5").removeClass('validation_message');
			}

				if(tipo_servicio=="Recogida"){
					$('#gform_submit_button_1').prop('disabled', true);
					cp=cod_postal_origen.substring(0,2);
					cp_zona= cod_postal_origen;
				}else{
					$('#gform_submit_button_1').prop('disabled', true);
					cp=cod_postal_destino.substring(0,2);
					cp_zona= cod_postal_destino;	
				}

				//SE VERIFICA SI EL INPUT ESTA VACÍO O NO SDI LO ESTA NO ENTRARÁ SI NO LO ESTA ENTRARÁ
				if(cp_zona != ""){
					zona_valida = true;
					if(jQuery.inArray(cp,zonas_prefijo) !== -1){
						codigos_zona = codigos_zona.split(' ');
						console.log(codigos_zona);
						if(codigos_zona.includes(cp_zona,0)){
							console.log("elcodigo postal",cp_zona,"existe");
							zona_valida = true;
						}else{
							console.log("el codigo postal",cp_zona,"no existe");
							zona_valida = false;
						}
					}
					
					console.log("cp = " + cp);
					prefijo_cp=$("#input_1_33").val();
					console.log("prefijo cp = " + prefijo_cp);
					prefijo_cp=prefijo_cp.split('|');
					valido_cp=false;
					for (var i = 0; i < prefijo_cp.length; i++) {
						if(prefijo_cp[i]==cp){
							valido_cp=true;
						}
						console.log(prefijo_cp[i]);
					}

					if(valido_cp == false || zona_valida == false){
						$('#gform_submit_button_1').prop('disabled', true);
						Swal.fire({
							title: 'Estimado Cliente',
							text: 'Este código postal o no existe o no es correcto',
							icon: 'warning'
						});
					}else{
						if(peso == "" || peso == 0){
							$("#msj_1_5").html('El peso debe ser mayor a 0 y no puede estar vacío');
							$("#msj_1_5").addClass('validation_message');
							$('#gform_submit_button_1').prop('disabled', true);
						}else{				
							$("#msj_1_5").html('');
							$("#msj_1_5").removeClass('validation_message');
							$('#gform_submit_button_1').prop('disabled', false);
						}

					}
				}
		
	   });

	   $('#input_1_15').change(function() {
		peso=$("#input_1_5").val();
		cod_postal_origen=$("#input_1_11").val();
		cod_postal_destino=$("#input_1_10").val();
		// Se ha seleccionado otro valor en el select
		var valorSeleccionado = $(this).val(); // obtiene el valor seleccionado

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

		$('#gform_submit_button_1').prop('disabled', true);
		console.log('Se ha seleccionado la opción ' + valorSeleccionado);
		
		// Aquí puedes agregar el código que deseas ejecutar cuando se selecciona otro valor en el select
	  });
	});	
})(jQuery);
