jQuery(document).ready(function($) {

    // Previsualizacion de imagenes
    function readURL(input, previewId) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#' + previewId).css('background-image', 'url(' + e.target.result + ')');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    $('#foto1').change(function() {
        readURL(this, 'preview1');
    });

    $('#foto2').change(function() {
        readURL(this, 'preview2');
    });

    // Envio del formulario
    $('#ruteo-form').on('submit', function(e) {
        e.preventDefault();

        var $form = $(this);
        var $btn = $form.find('.ruteo-submit-btn');
        var $msg = $('#ruteo-message');

        // Limpiar mensajes anteriores
        $msg.removeClass('success error').text('').hide();

        // Estado de carga
        $btn.addClass('loading').prop('disabled', true);

        // Preparar datos usando FormData para archivos
        var formData = new FormData(this);
        formData.append('action', 'ruteo_submit');
        formData.append('nonce', wpRuteoAjax.nonce);

        // Peticion AJAX
        $.ajax({
            url: wpRuteoAjax.ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $btn.removeClass('loading').prop('disabled', false);

                if (response.success) {
                    $msg.addClass('success').text(response.data).fadeIn();
                    $form[0].reset();
                    $('.preview').css('background-image', 'none');
                } else {
                    $msg.addClass('error').text(response.data || 'Ocurrio un error desconocido.').fadeIn();
                }
            },
            error: function() {
                $btn.removeClass('loading').prop('disabled', false);
                $msg.addClass('error').text('Error de conexion con el servidor.').fadeIn();
            }
        });
    });

});
