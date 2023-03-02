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
	   $("#gform_submit_button_1").click(function(){
	   	  volumen=$("#input_1_6").val();
          peso=$("#input_1_5").val();
          cod_postal_destino=$("#input_1_10").val();
          cod_postal_origen=$("#input_1_11").val();
          largo=$("#input_1_29").val();
          tipo_servicio=$("#input_1_15").val();
          if(volumen==""){
              return true;
          }else{
          	 if(peso==""){
                return true;
          	 }else{
          	 	if(cod_postal_destino==""){
                    return true; 
          	 	}else{
          	 		if(cod_postal_origen==""){
                       return true;
          	 		}else{
          	 			console.log(tipo_servicio);
          	 			if(tipo_servicio=="Recogida"){
                           cp=cod_postal_origen.substring(0,2);
          	 			}else{
          	 			   cp=cod_postal_destino.substring(0,2);	
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
          	 			console.log(valido_cp);
          	 			//alert(cp);
          	 			if(valido_cp==false){
                           if(tipo_servicio=="Recogida"){
                           	   $("#msj_1_10").removeClass('validation_message');
                           	   $("#msj_1_10").html('');
                               $("#msj_1_11").addClass('validation_message');
                               $("#msj_1_11").html('Código Postal Inválido.');
                           }else{
                           	   $("#msj_1_11").removeClass('validation_message');
                           	   $("#msj_1_11").html('');
                               $("#msj_1_10").addClass('validation_message');
                               $("#msj_1_10").html('Código Postal Inválido');
                           }
                           return false;
          	 			}else{
          	 				if($("#input_1_6").val()==0){
                                Swal.fire({
                                      title: 'Estimado Cliente',
                                      text: 'Volumen Invalido,Ingrese el largo,ancho y alto distinto de cero.',
                                      icon: 'error'
                                }); 
          	 				}else{
                                $("#gform_1").submit();
                            }
          	 			}
          	 		}
          	 	}
          	 }
          }
	   	   
       });
	});	
})(jQuery);
