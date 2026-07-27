jQuery(document).ready(function($) {

    // CONTROL DE TEMA (MODO DIA / MODO NOCHE EN TODA LA PAGINA)
    function aplicarTema(tema) {
        $('.ruteo-wrapper').attr('data-theme', tema);
        $('body').attr('data-theme', tema);
        $('html').attr('data-theme', tema);
        localStorage.setItem('ruteo_theme', tema);
        if (tema === 'dark') {
            $('#theme-toggle-icon').text('\u2600\uFE0F');
            $('#theme-toggle-text').text('Modo Dia');
        } else {
            $('#theme-toggle-icon').text('\uD83C\uDF19');
            $('#theme-toggle-text').text('Modo Noche');
        }
    }

    var temaGuardado = localStorage.getItem('ruteo_theme') || 'light';
    aplicarTema(temaGuardado);

    $('#btn-theme-toggle').on('click', function() {
        var actual = $('body').attr('data-theme') || 'light';
        var nuevo = (actual === 'dark') ? 'light' : 'dark';
        aplicarTema(nuevo);
    });

    // Observer para animaciones suaves
    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.05
    };

    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target); 
            }
        });
    }, observerOptions);

    document.querySelectorAll('.animate-slide-up').forEach(el => {
        observer.observe(el);
    });

    // Previsualizacion de fotos
    function readURL(input, previewId) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                const previewElement = $('#' + previewId);
                previewElement.css('background-image', 'url(' + e.target.result + ')');
                previewElement.addClass('show'); 
                $(input).closest('.ruteo-photo-upload').addClass('has-file');
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            $('#' + previewId).removeClass('show').css('background-image', 'none');
            $(input).closest('.ruteo-photo-upload').removeClass('has-file');
        }
    }

    $('#foto1').change(function() { readURL(this, 'preview1'); });
    $('#foto2').change(function() { readURL(this, 'preview2'); });

    // Envio del Formulario de Campo
    $('#ruteo-form').on('submit', function(e) {
        e.preventDefault();

        var $form = $(this);
        var $btn = $form.find('.ruteo-submit-btn');
        var $msg = $('#ruteo-message');

        $msg.removeClass('success error').text('').hide();
        $btn.addClass('loading').prop('disabled', true);

        var formData = new FormData(this);
        formData.append('action', 'ruteo_submit');
        formData.append('nonce', wpRuteoAjax.nonce);

        $.ajax({
            url: wpRuteoAjax.ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $btn.removeClass('loading').prop('disabled', false);

                if (response.success) {
                    var msg = (response.data && response.data.message) ? response.data.message : 'Datos enviados correctamente.';
                    $msg.addClass('success').html(msg).fadeIn(300);
                    $form[0].reset();
                    $('.preview').removeClass('show').css('background-image', 'none');
                    $('.ruteo-photo-upload').removeClass('has-file');
                    
                    setTimeout(function() { $msg.fadeOut(300); }, 5000);
                } else {
                    $msg.addClass('error').html('<strong>Error:</strong> ' + (response.data || 'El proceso fallo.')).fadeIn(300);
                }
            },
            error: function() {
                $btn.removeClass('loading').prop('disabled', false);
                $msg.addClass('error').html('<strong>Error:</strong> No se pudo conectar con el servidor.').fadeIn(300);
            }
        });
    });

    // SISTEMA DE PESTANAS (TABS) Y ESTADO DE USUARIO
    var currentUser = (window.wpRuteoAjax && window.wpRuteoAjax.user) ? window.wpRuteoAjax.user : { isLoggedIn: false, isAdmin: false, role: 'guest' };

    function actualizarInterfazUsuario(user) {
        currentUser = user;

        if (user.isLoggedIn) {
            $('#ruteo-user-badge').css('display', 'flex');
            $('#user-display-name').text(user.displayName || user.username);
            var initial = (user.displayName || user.username || '?').charAt(0).toUpperCase();
            $('#user-avatar-text').text(initial);

            var roleText = user.isAdmin ? 'Administrador (Admin)' : 'Operario (Worker)';
            $('#user-role-label').text(roleText);
            $('#btn-ruteo-logout').show();

            // Mostrar form y ocultar aviso de restriccion
            $('#ruteo-form-restricted-notice').hide();
            $('#ruteo-form').show();

            // Mostrar barra de pestanas y opciones autenticadas
            $('.ruteo-tabs-bar').show();
            $('.ruteo-tab-btn[data-tab="registros"]').show();
            $('.ruteo-tab-btn[data-tab="formulario"]').show();
            $('#tab-btn-login').hide();

            if (user.isAdmin) {
                $('#tab-btn-usuarios').show();
            } else {
                $('#tab-btn-usuarios').hide();
            }

            // Cargar datos del portal si el usuario esta autenticado
            if (typeof window.cargarDatosPortal === 'function' && (!window._ruteoRegistros || window._ruteoRegistros.length === 0)) {
                window.cargarDatosPortal();
            }

            // Si estaba en login, ir a registros
            if ($('#tab-login').hasClass('active') || !$('.ruteo-tab-btn.active').is(':visible')) {
                $('.ruteo-tab-btn[data-tab="registros"]').click();
            }
        } else {
            // Limpiar datos de sesion anterior
            window._ruteoRegistros = [];
            var tbody = document.querySelector('#portal-table-body');
            if (tbody) tbody.innerHTML = '';
            // Ocultar badge de usuario en cabecera
            $('#ruteo-user-badge').hide();
            $('#btn-ruteo-logout').hide();

            // Ocultar form y mostrar aviso
            $('#ruteo-form-restricted-notice').show();
            $('#ruteo-form').hide();

            // Ocultar barra de pestanas para vista limpia de inicio de sesion
            $('.ruteo-tabs-bar').hide();

            // Mostrar pantalla de login directamente
            $('.ruteo-tab-content').removeClass('active').hide();
            $('#tab-login').addClass('active').show();
        }
    }

    actualizarInterfazUsuario(currentUser);

    // Boton ir a iniciar sesion dentro del aviso de formulario
    $(document).on('click', '.btn-goto-login', function() {
        $('.ruteo-tab-content').removeClass('active').hide();
        $('#tab-login').addClass('active').show();
        $('.ruteo-tabs-bar').hide();
    });

    // Cambio de Pestanas
    $('.ruteo-tab-btn').on('click', function() {
        var targetTab = $(this).data('tab');
        
        $('.ruteo-tab-btn').removeClass('active');
        $(this).addClass('active');

        $('.ruteo-tab-content').removeClass('active').hide();
        $('#tab-' + targetTab).addClass('active').fadeIn(200);

        if (targetTab === 'usuarios' && currentUser.isAdmin) {
            cargarUsuarios();
        }
    });

    // LOGIN AJAX UNIFICADO
    $(document).on('submit', '.ruteo-auth-login-form, #ruteo-login-form', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $msg = $form.find('.ruteo-message').length ? $form.find('.ruteo-message') : $('#login-message');
        var $btn = $form.find('.ruteo-submit-btn');
        var isRedirect = $form.data('redirect') === true;

        var usernameVal = $form.find('input[name="username"]').val() || $('#login-username').val();
        var passwordVal = $form.find('input[name="password"]').val() || $('#login-password').val();

        $msg.removeClass('success error').hide();
        $btn.addClass('loading').prop('disabled', true);

        $.ajax({
            url: wpRuteoAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'ruteo_login',
                nonce: wpRuteoAjax.nonce,
                username: usernameVal,
                password: passwordVal
            },
            success: function(res) {
                $btn.removeClass('loading').prop('disabled', false);
                if (res.success) {
                    $msg.addClass('success').text(res.data.message).fadeIn(300);
                    var u = res.data.user;
                    u.isLoggedIn = true;
                    actualizarInterfazUsuario(u);
                    
                    // Recargar registros automaticamente tras login exitoso
                    if (typeof window.cargarDatosPortal === 'function') {
                        window.cargarDatosPortal();
                    }
                    setTimeout(function() {
                        $msg.fadeOut(300);
                        if (isRedirect) {
                            window.location.href = window.location.origin + '/portal-ruteo/';
                        } else {
                            $('.ruteo-tab-btn[data-tab="registros"]').click();
                        }
                    }, 1000);
                } else {
                    $msg.addClass('error').text(res.data.message || 'Error al iniciar sesion.').fadeIn(300);
                }
            },
            error: function() {
                $btn.removeClass('loading').prop('disabled', false);
                $msg.addClass('error').text('Error de conexion al servidor.').fadeIn(300);
            }
        });
    });

    // LOGOUT AJAX
    $('#btn-ruteo-logout').on('click', function() {
        if (!confirm('Deseas cerrar la sesion actual?')) return;

        $.ajax({
            url: wpRuteoAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'ruteo_logout',
                nonce: wpRuteoAjax.nonce
            },
            success: function() {
                // Sincronizar estado cliente
                wpRuteoAjax.user.isLoggedIn = false;
                wpRuteoAjax.user.isAdmin = false;
                wpRuteoAjax.user.role = 'guest';
                // Limpiar datos y tabla
                window._ruteoRegistros = [];
                var tbody = document.querySelector('#portal-table-body');
                if (tbody) tbody.innerHTML = '';
                var stats = document.getElementById('portal-stats');
                if (stats) stats.innerHTML = '';
                // Redirigir a login
                actualizarInterfazUsuario({ isLoggedIn: false, isAdmin: false, role: 'guest' });
            }
        });
    });

    // GESTION DE USUARIOS (SOLO ADMIN)
    $('#btn-toggle-create-user').on('click', function() {
        $('#user-create-card').slideToggle(200);
    });

    $('#btn-cancel-create-user').on('click', function() {
        $('#user-create-card').slideUp(200);
    });

    function cargarUsuarios() {
        $('#users-count-note').text('Cargando usuarios...');
        $.ajax({
            url: wpRuteoAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'ruteo_get_users',
                nonce: wpRuteoAjax.nonce
            },
            success: function(res) {
                if (res.success && res.data && res.data.users) {
                    var users = res.data.users;
                    $('#users-count-note').text('Total cuentas: ' + users.length);
                    renderTablaUsuarios(users);
                } else {
                    $('#users-count-note').text('Error al obtener usuarios.');
                }
            },
            error: function() {
                $('#users-count-note').text('Error de conexion.');
            }
        });
    }

    function renderTablaUsuarios(users) {
        var $tbody = $('#users-tbody');
        $tbody.empty();

        if (users.length === 0) {
            $tbody.append('<tr><td colspan="6" style="text-align:center;">No hay usuarios registrados.</td></tr>');
            return;
        }

        users.forEach(function(u) {
            var roleBadge = u.role === 'Admin' ? 
                '<span class="badge-role-admin">Admin</span>' : 
                '<span class="badge-role-worker">Worker</span>';
            
            var tr = '<tr>' +
                '<td>' + u.id + '</td>' +
                '<td><strong>' + u.username + '</strong></td>' +
                '<td>' + (u.displayName || u.username) + '</td>' +
                '<td>' + u.email + '</td>' +
                '<td>' + roleBadge + '</td>' +
                '<td>' +
                    '<button class="btn-danger btn-del-user" data-id="' + u.id + '" data-name="' + u.username + '">Eliminar</button>' +
                '</td>' +
            '</tr>';
            $tbody.append(tr);
        });

        $('.btn-del-user').off('click').on('click', function() {
            var uid = $(this).data('id');
            var uname = $(this).data('name');
            if (confirm('Confirmas que deseas eliminar al usuario ' + uname + '?')) {
                eliminarUsuario(uid);
            }
        });
    }

    // CREAR USUARIO
    $('#form-create-user').on('submit', function(e) {
        e.preventDefault();
        var $msg = $('#create-user-msg');
        $msg.removeClass('success error').hide();

        $.ajax({
            url: wpRuteoAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'ruteo_create_user',
                nonce: wpRuteoAjax.nonce,
                display_name: $('#user-display-name-input').val(),
                username: $('#user-username-input').val(),
                email: $('#user-email-input').val(),
                password: $('#user-password-input').val(),
                role: $('#user-role-select').val()
            },
            success: function(res) {
                if (res.success) {
                    $msg.addClass('success').text(res.data.message).fadeIn(200);
                    $('#form-create-user')[0].reset();
                    $('#user-create-card').slideUp(300);
                    cargarUsuarios();
                } else {
                    $msg.addClass('error').text(res.data.message || 'Error al crear usuario.').fadeIn(200);
                }
            },
            error: function() {
                $msg.addClass('error').text('Error de conexion con el servidor.').fadeIn(200);
            }
        });
    });

    function eliminarUsuario(userId) {
        $.ajax({
            url: wpRuteoAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'ruteo_delete_user',
                nonce: wpRuteoAjax.nonce,
                user_id: userId
            },
            success: function(res) {
                if (res.success) {
                    cargarUsuarios();
                } else {
                    alert(res.data.message || 'No se pudo eliminar el usuario.');
                }
            },
            error: function() {
                alert('Error de conexion al intentar eliminar el usuario.');
            }
        });
    }

    $('.input-wrapper input, .input-wrapper select, .input-wrapper textarea').on('focus', function() {
        $(this).parent().addClass('focused');
    }).on('blur', function() {
        $(this).parent().removeClass('focused');
    });
});
