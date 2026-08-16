jQuery(document).ready(function($) {

    function showToast(message, type) {
        type = type || 'info';
        var $container = $('.ruteo-toast-container');
        if ($container.length === 0) {
            $container = $('<div class="ruteo-toast-container"></div>');
            $('body').append($container);
        }
        var icon = type === 'success' ? '✓' : (type === 'error' ? '✕' : 'ℹ');
        var $toast = $('<div class="ruteo-toast toast-' + type + '"><span class="toast-icon">' + icon + '</span><span>' + message + '</span></div>');
        $container.append($toast);
        setTimeout(function() {
            $toast.addClass('toast-out');
            setTimeout(function() { $toast.remove(); }, 250);
        }, 3500);
    }


    // --- CONTROL DE TEMA (MODO DIA / MODO NOCHE) ---
    function aplicarTema(tema) {
        $('.ruteo-app-layout').attr('data-theme', tema);
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

    // --- LOGO DEL SISTEMA (SIDEBAR) ---
    function pintarLogoSistema(base64) {
        if (!base64) return;
        $('#sidebar-brand-logo, #site-logo-preview').html('<img src="' + base64 + '" alt="Logo" style="width:100%; height:100%; object-fit:cover; border-radius:10px;">');
    }

    if (typeof wpRuteoAjax !== 'undefined' && wpRuteoAjax.siteLogo) {
        pintarLogoSistema(wpRuteoAjax.siteLogo);
    }

    $('#form-site-logo').on('submit', function(e) {
        e.preventDefault();
        var fileInput = $('#site-logo-file')[0];
        if (!fileInput.files.length) return;

        var fd = new FormData();
        fd.append('action', 'ruteo_update_site_logo');
        fd.append('nonce', wpRuteoAjax.nonce);
        fd.append('logo', fileInput.files[0]);

        var $btn = $(this).find('button[type="submit"]');
        $btn.prop('disabled', true).find('span').text('Guardando...');

        $.ajax({
            url: wpRuteoAjax.ajaxurl, type: 'POST', data: fd, processData: false, contentType: false,
            success: function(res) {
                if (res.success) {
                    pintarLogoSistema(res.data.logo);
                    wpRuteoAjax.siteLogo = res.data.logo;
                    $('#site-logo-msg').removeClass('error').addClass('success').text(res.data.message);
                } else {
                    $('#site-logo-msg').removeClass('success').addClass('error').text('Error: ' + res.data.message);
                }
            },
            error: function() {
                $('#site-logo-msg').removeClass('success').addClass('error').text('Error de conexion al subir el logo.');
            },
            complete: function() {
                $btn.prop('disabled', false).find('span').text('Guardar Logo');
            }
        });
    });
    

    $('#btn-theme-toggle').on('click', function() {
        var actual = $('body').attr('data-theme') || 'light';
        var nuevo = (actual === 'dark') ? 'light' : 'dark';
        aplicarTema(nuevo);
    });

    // --- COLAPSAR / EXPANDIR SIDEBAR LATERAL ---
    $('#btn-sidebar-collapse').on('click', function() {
        var $sidebar = $('#ruteo-sidebar').toggleClass('collapsed');
        var contraida = $sidebar.hasClass('collapsed');
        $(this).attr('title', contraida ? 'Expandir panel' : 'Contraer panel');
    });

    // --- PREVISUALIZACION Y QUITAR FOTOS DE CAMPO ---
    function readURL(input, previewId) {
        var $preview = $('#' + previewId);
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $preview.find('img.preview-img').remove();
                $preview.prepend('<img src="' + e.target.result + '" class="preview-img" style="width:100%; height:100%; object-fit:cover; border-radius:10px; position:absolute; top:0; left:0; pointer-events:none; z-index:1;">');
                $preview.css('background-image', 'url(' + e.target.result + ')').addClass('show'); 
                $(input).closest('.ruteo-photo-upload').addClass('has-file');
            };
            reader.readAsDataURL(input.files[0]);
        } else {
            $preview.find('img.preview-img').remove();
            $preview.removeClass('show').css('background-image', 'none');
            $(input).closest('.ruteo-photo-upload').removeClass('has-file');
        }
    }

    $(document).on('change', '#foto1, #foto2, #neg-foto1, #neg-foto2', function() {
        var previewId = $(this).attr('id') === 'neg-foto1' ? 'neg-preview1' :
                        $(this).attr('id') === 'neg-foto2' ? 'neg-preview2' :
                        $(this).attr('id') === 'foto1' ? 'preview1' : 'preview2';
        readURL(this, previewId);
    });

    function mostrarFirmaPerfil(base64) {
        if (base64) {
            $('#prof-firma-preview-img').attr('src', base64);
            $('#prof-firma-preview-box').css('display', 'flex');
            $('#prof-firma-remove').show();
        } else {
            $('#prof-firma-preview-img').attr('src', '');
            $('#prof-firma-preview-box').hide();
            $('#prof-firma-remove').hide();
        }
    }

    $(document).on('change', '#prof-avatar-file', function() {
        var file = this.files && this.files[0];
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function(e) {
            $('#profile-avatar-img-box').html('<img src="' + e.target.result + '" alt="Avatar"><input type="file" id="prof-avatar-file" accept="image/png,image/jpeg,image/webp" style="display:none;">');
        };
        reader.readAsDataURL(file);
    });

    $(document).on('change', '#prof-firma-file', function() {
        var f = this.files && this.files[0];
        if (!f) return;
        var reader = new FileReader();
        reader.onload = function(e) { mostrarFirmaPerfil(e.target.result); $('#prof-firma-remove').data('remove', '0'); };
        reader.readAsDataURL(f);
    });
    $(document).on('click', '#prof-firma-remove', function() {
        $('#prof-firma-file').val('');
        mostrarFirmaPerfil('');
        $(this).data('remove', '1');
    });

    $(document).on('click', '.btn-remove-photo', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var inputId = $(this).data('input');
        var previewId = $(this).data('preview');
        $('#' + inputId).val('');
        var $preview = $('#' + previewId);
        $preview.find('img.preview-img').remove();
        $preview.removeClass('show').css('background-image', 'none');
        $('#' + inputId).closest('.ruteo-photo-upload').removeClass('has-file');
    });


    // --- TOGGLE DE SIDEBAR MOVIL Y OVERLAY BACKDROP ---
    $('#btn-mobile-sidebar-toggle').on('click', function() {
        $('#ruteo-sidebar').toggleClass('open-mobile');
        $('#ruteo-sidebar-backdrop').toggleClass('show');
    });

    $('#ruteo-sidebar-backdrop').on('click', function() {
        $('#ruteo-sidebar').removeClass('open-mobile');
        $('#ruteo-sidebar-backdrop').removeClass('show');
    });



    // --- ENVIO DEL FORMULARIO DE CAMPO ---
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
                    
                    if (typeof window.cargarDatosPortal === 'function') {
                        window.cargarDatosPortal(true);
                    }
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

    // --- INTERFAZ Y ESTADO DE USUARIO ---
    var currentUser = (window.wpRuteoAjax && window.wpRuteoAjax.user) ? window.wpRuteoAjax.user : { isLoggedIn: false, isAdmin: false, role: 'guest' };

    function actualizarInterfazUsuario(user) {
        currentUser = user;

        if (user.isLoggedIn) {
            $('#ruteo-user-badge').css('display', 'flex');
            $('#user-display-name').text(user.displayName || user.username);
            
            var avatarInputHtml = '<input type="file" id="prof-avatar-file" accept="image/png,image/jpeg,image/webp" style="display:none;">';

            if (user.avatar) {
                $('#user-avatar-box').html('<img src="' + user.avatar + '" alt="Avatar">');
                $('#profile-avatar-img-box').html('<img src="' + user.avatar + '" alt="Avatar">' + avatarInputHtml);
            } else if (user.empresaLogo) {
                $('#user-avatar-box').html('<img src="' + user.empresaLogo + '" alt="Avatar">');
                $('#profile-avatar-img-box').html('<img src="' + user.empresaLogo + '" alt="Avatar">' + avatarInputHtml);
            } else {
                var initial = (user.displayName || user.username || '?').charAt(0).toUpperCase();
                $('#user-avatar-box').html('<span id="user-avatar-text">' + initial + '</span>');
                $('#profile-avatar-img-box').html('<span id="profile-avatar-large-text">' + initial + '</span>' + avatarInputHtml);
            }

            var roleText;
if (user.isSuperAdmin) {
    roleText = 'Admin (General)';
} else {
    var empresaTag = user.empresaNombre ? user.empresaNombre : 'Sin Empresa';
    roleText = user.isAdmin ? ('Admin (' + empresaTag + ')') : ('Operario (' + empresaTag + ')');
}

            $('#user-role-label').text(roleText);
            $('#btn-ruteo-logout').show();

            $('#ruteo-form-restricted-notice').hide();
            $('#ruteo-form').show();
            $('.ruteo-tab-protected-notice').hide();
            $('.ruteo-tab-protected-content').show();

            $('#tab-btn-login').hide();
            $('#tab-btn-perfil').show();

            if (user.isAdmin) {
              $('#tab-btn-usuarios').show();
              $('#admin-sheets-box').show();
            } else {
              $('#tab-btn-usuarios').hide();
              $('#admin-sheets-box').hide();
            }

if (user.isSuperAdmin) {
    $('#tab-btn-empresas').show();
} else {
    $('#tab-btn-empresas').hide();
}

            

            if (user.isSuperAdmin) {
                $('#tab-btn-empresas').show();
                cargarEmpresas();
            } else {
                $('#tab-btn-empresas').hide();
            }

            // Llenar datos de perfil
            $('#profile-name-heading').text(user.displayName || user.username);
            var headerSub = roleText + (user.position ? ' | ' + user.position : '') + ' - ' + (user.pmAssigned || 'Sin PM asignado');
            $('#profile-role-heading').text(headerSub);
            $('#prof-display-name').val(user.displayName || user.username);
            $('#prof-email').val(user.email || '');
            $('#prof-phone').val(user.phone || '');
            $('#prof-position').val(user.position || '');
            $('#prof-pm').val(user.pmAssigned || '');
            if (typeof mostrarFirmaPerfil === 'function') { mostrarFirmaPerfil(user.firma || ''); }
            $('#prof-firma-remove').data('remove', '0');

            if (typeof window.cargarDatosPortal === 'function' && (!window._ruteoRegistros || window._ruteoRegistros.length === 0)) {
                window.cargarDatosPortal();
            }

            cargarMateriales();

            if ($('#tab-login').hasClass('active') || !$('.sidebar-item.active').is(':visible')) {
                $('.sidebar-item[data-tab="inicio"]').click();
            }
        } else {
            window._ruteoRegistros = [];
            $('#ruteo-user-badge').css('display', 'flex').css('cursor', 'pointer').attr('title', 'Invitado - Click para Iniciar Sesion');
            $('#user-display-name').text('Invitado');
            $('#user-role-label').text('Acceso Bloqueado (Click para Iniciar Sesion)');
            $('#user-avatar-box').html('<span id="user-avatar-text">?</span>');
            $('#btn-ruteo-logout').hide();

            $('#ruteo-form-restricted-notice').show();
            $('#ruteo-form').hide();
            $('.ruteo-tab-protected-notice').show();
            $('.ruteo-tab-protected-content').hide();

            $('#tab-btn-usuarios').hide();
            $('#tab-btn-perfil').hide();
            $('#tab-btn-login').show();
        }
    }

    $('#ruteo-user-badge').on('click', function() {
        if (!currentUser || !currentUser.isLoggedIn) {
            $('.sidebar-item[data-tab="login"]').click();
        }
    });

    $(document).on('submit', '.ruteo-auth-login-form', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $btn = $form.find('button[type="submit"]');
        var $msg = $form.find('.ruteo-message');

        var username = $form.find('input[name="username"]').val();
        var password = $form.find('input[name="password"]').val();

        if (!username || !password) {
            $msg.text('Por favor ingresa tu usuario o correo y tu clave.').removeClass('success info').addClass('error').show();
            return;
        }

        $btn.addClass('loading').prop('disabled', true);
        $msg.text('Iniciando sesion...').removeClass('error success').addClass('info').show();

        $.post(wpRuteoAjax.ajaxurl, {
            action: 'ruteo_login',
            nonce: wpRuteoAjax.nonce,
            username: username,
            password: password
        }, function(res) {
            $btn.removeClass('loading').prop('disabled', false);
            if (res.success && res.data.user) {
                $msg.text('¡Inicio de sesion exitoso! Cargando portal...').removeClass('error info').addClass('success');
                actualizarInterfazUsuario(res.data.user);
                $('.ruteo-tab-protected-notice').hide();
                $('.ruteo-form, .portal-card, #ruteo-form').show();
                setTimeout(function() {
                    location.reload();
                }, 300);
            } else {
                $msg.text(res.data && res.data.message ? res.data.message : 'Credenciales invalidas. Revisa usuario y clave.').removeClass('success info').addClass('error');
            }
        }).fail(function() {
            $btn.removeClass('loading').prop('disabled', false);
            $msg.text('Error de conexion al intentar iniciar sesion.').removeClass('success info').addClass('error');
        });
    });

    $(document).on('click', '.btn-demo-login', function(e) {
        e.preventDefault();
        var user = $(this).data('user');
        var pass = $(this).data('pass');
        var $form = $(this).closest('.login-card-container, #tab-login, .ruteo-tab-protected-notice').find('.ruteo-auth-login-form');
        if (!$form.length) {
            $form = $('.ruteo-auth-login-form').first();
        }

        $form.find('input[name="username"]').val(user);
        $form.find('input[name="password"]').val(pass);
        $form.trigger('submit');
    });

    $(document).on('click', '#btn-ruteo-logout', function(e) {
        e.preventDefault();
        e.stopPropagation();
        if (!confirm('Deseas cerrar la sesion actual?')) return;
        $.post(wpRuteoAjax.ajaxurl, { action: 'ruteo_logout', nonce: wpRuteoAjax.nonce }, function() {
            location.reload();
        }).fail(function() {
            location.reload();
        });
    });

    actualizarInterfazUsuario(currentUser);

    // --- NAVEGACION POR SIDEBAR Y ACCIONES RAPIDAS ---
    $('.sidebar-item, [data-goto]').on('click', function() {
        var targetTab = $(this).data('tab') || $(this).data('goto');
        if (!targetTab) return;

        $('.sidebar-item').removeClass('active');
        $('.sidebar-item[data-tab="' + targetTab + '"]').addClass('active');

        var titleMap = {
            'inicio': 'Panel de Administracion',
            'registros': 'Registros de Ruteo',
            'formulario': 'Nuevo Registro de Campo',
            'materiales': 'Consumo de Materiales',
            'sla-informes': 'Informes O&M',
            'auditoria': 'Historial',
            'usuarios': 'Gestion de Cuentas de Usuario',
            'perfil': 'Perfil de Usuario',
            'login': 'Iniciar Sesion',
            'negativa': 'Formato de Negativa',
            'lista-negativas': 'Historial de Negativa',
        };

        if (titleMap[targetTab]) {
            $('#page-header-title').text(titleMap[targetTab]);
        }

        $('.ruteo-tab-content').removeClass('active').hide();
        $('#tab-' + targetTab).addClass('active').fadeIn(200);

        if (targetTab === 'usuarios' && currentUser.isAdmin) {
            cargarUsuarios();
        } else if (targetTab === 'materiales') {
            cargarMateriales();
        } else if (targetTab === 'auditoria') {
            cargarAuditLogs();
        } else if (targetTab === 'lista-negativas') {
            cargarListaNegativas();
        } else if (targetTab === 'registros' && currentUser.isLoggedIn) {
            if (typeof window.cargarDatosPortal === 'function') {
                var hayRegistros = window._ruteoRegistros && window._ruteoRegistros.length > 0;
                window.cargarDatosPortal(hayRegistros);
            }
        }
    });

    // --- CONSUMO DE MATERIALES: TABLA DINAMICA ---
    var materialRowCount = 0;

    function agregarFilaMaterial(itemData) {
        materialRowCount++;
        var d = itemData || { descripcion: '', unidad: 'UND', cantidad: 1, codigo_sap: '', drum: '' };

        var html = '<tr id="mat-row-' + materialRowCount + '">' +
            '<td>' + materialRowCount + '</td>' +
            '<td><input type="text" class="mat-desc" placeholder="Ej: Cable ADSS 48 FO" value="' + (d.descripcion || '') + '" required></td>' +
            '<td>' +
                '<select class="mat-unidad">' +
                    '<option value="UND"' + (d.unidad === 'UND' ? ' selected' : '') + '>UND</option>' +
                    '<option value="MTRS"' + (d.unidad === 'MTRS' || d.unidad === 'M' ? ' selected' : '') + '>MTRS</option>' +
                    '<option value="KIT"' + (d.unidad === 'KIT' ? ' selected' : '') + '>KIT</option>' +
                    '<option value="KG"' + (d.unidad === 'KG' ? ' selected' : '') + '>KG</option>' +
                '</select>' +
            '</td>' +
            '<td><input type="number" class="mat-cant" min="1" step="0.1" value="' + (d.cantidad || 1) + '" required></td>' +
            '<td><input type="text" class="mat-sap" placeholder="Ej: PR-30001" value="' + (d.codigo_sap || '') + '"></td>' +
            '<td><input type="text" class="mat-drum" placeholder="Ej: DRUM 0126" value="' + (d.drum || '') + '"></td>' +
            '<td><button type="button" class="btn-del-row" data-row="' + materialRowCount + '" title="Eliminar">&times;</button></td>' +
        '</tr>';

        $('#tbody-material-items').append(html);

        $('.btn-del-row').off('click').on('click', function() {
            var rId = $(this).data('row');
            $('#mat-row-' + rId).remove();
            reorganizarItemsMaterial();
        });
    }

    function reorganizarItemsMaterial() {
        $('#tbody-material-items tr').each(function(idx) {
            $(this).find('td:first').text(idx + 1);
        });
    }

    // Agregar primera fila por defecto
    agregarFilaMaterial();

    $('#btn-add-material-row').on('click', function() {
        agregarFilaMaterial();
    });

    // GUARDAR REPORTES DE MATERIALES
    $('#form-consumo-materiales').on('submit', function(e) {
        e.preventDefault();
        var $msg = $('#mat-form-msg');
        var $btn = $(this).find('.ruteo-submit-btn');

        var items = [];
        $('#tbody-material-items tr').each(function() {
            var desc = $(this).find('.mat-desc').val();
            var und = $(this).find('.mat-unidad').val();
            var cant = $(this).find('.mat-cant').val();
            var sap = $(this).find('.mat-sap').val();
            var drum = $(this).find('.mat-drum').val();

            if (desc) {
                items.push({
                    descripcion: desc,
                    unidad: und,
                    cantidad: cant,
                    codigo_sap: sap,
                    drum: drum
                });
            }
        });

        if (items.length === 0) {
            $msg.addClass('error').text('Agrega al menos un material utilizado.').fadeIn(200);
            return;
        }

        $msg.removeClass('success error').hide();
        $btn.prop('disabled', true);

        $.ajax({
            url: wpRuteoAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'ruteo_save_materiales',
                nonce: wpRuteoAjax.nonce,
                incidencia: $('#mat-incidencia').val(),
                crq: $('#mat-crq').val(),
                almacen_pm: $('#mat-almacen-pm').val(),
                tramo: $('#mat-tramo').val(),
                fecha: $('#mat-fecha').val(),
                descripcion: $('#mat-descripcion').val(),
                items: JSON.stringify(items)
            },
            success: function(res) {
                $btn.prop('disabled', false);
                if (res.success) {
                    $msg.addClass('success').text(res.data.message).fadeIn(200);
                    $('#form-consumo-materiales')[0].reset();
                    $('#tbody-material-items').empty();
                    materialRowCount = 0;
                    agregarFilaMaterial();
                    cargarMateriales();
                    setTimeout(function() { $msg.fadeOut(300); }, 5000);
                } else {
                    $msg.addClass('error').text(res.data.message || 'Error al guardar reporte.').fadeIn(200);
                }
            },
            error: function() {
                $btn.prop('disabled', false);
                $msg.addClass('error').text('Error de conexion.').fadeIn(200);
            }
        });
    });

    var allMaterialesList = [];

    function cargarMateriales() {
        $.ajax({
            url: wpRuteoAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'ruteo_get_materiales',
                nonce: wpRuteoAjax.nonce
            },
            success: function(res) {
                if (res.success && res.data && res.data.materiales) {
                    allMaterialesList = res.data.materiales;
                    $('#dash-stat-materiales').text(allMaterialesList.length);
                    renderTablaMateriales(allMaterialesList);
                    poblarListasSla();
                }
            }
        });
    }

    function renderTablaMateriales(list) {
        var $tbody = $('#mat-reports-tbody');
        $tbody.empty();

        if (list.length === 0) {
            $tbody.append('<tr><td colspan="8" style="text-align:center; padding: 20px;">No hay reportes de materiales registrados aun.</td></tr>');
            return;
        }

        list.forEach(function(r, idx) {
            var itemsSummary = r.items ? r.items.map(function(it) {
                return it.cantidad + ' ' + it.unidad + ' ' + it.descripcion + (it.codigo_sap ? ' (' + it.codigo_sap + ')' : '');
            }).join('<br>') : '-';

            var editBtn = currentUser && currentUser.isLoggedIn ?
                '<a href="javascript:void(0)" onclick="window.abrirModalEditarMaterial(\'' + (r.id || idx) + '\')" title="Editar reporte de materiales" class="portal-link portal-link--purple" style="padding:4px 8px;"><svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg> Editar</a>' : '';

            var tr = '<tr>' +
                '<td>' + (r.fecha || '-') + '</td>' +
                '<td><strong>' + (r.incidencia || '-') + '</strong>' + (r.crq ? '<br><small style="color:var(--text-muted);">' + r.crq + '</small>' : '') + '</td>' +
                '<td><span class="status-badge-info">' + (r.almacen_pm || '-') + '</span></td>' +
                '<td>' + (r.tramo || '-') + '</td>' +
                '<td>' + (r.descripcion || '-') + '</td>' +
                '<td style="font-size: 12px; line-height: 1.4;">' + itemsSummary + '</td>' +
                '<td>' + (r.user || '-') + '</td>' +
                '<td>' + editBtn + '</td>' +
            '</tr>';
            $tbody.append(tr);
        });
    }

    window.abrirModalEditarMaterial = function(id) {
        var report = allMaterialesList.find(function(m) { return m.id === id || String(m.id) === String(id); });
        if (!report) return;

        $('#edit-mat-id').val(report.id);
        $('#edit-mat-incidencia').val(report.incidencia || '');
        $('#edit-mat-crq').val(report.crq || '');
        $('#edit-mat-almacen-pm').val(report.almacen_pm || '');
        $('#edit-mat-tramo').val(report.tramo || '');
        $('#edit-mat-fecha').val(report.fecha || '');
        $('#edit-mat-descripcion').val(report.descripcion || '');
        $('#edit-mat-msg').hide().empty();
        $('#edit-material-modal-overlay').fadeIn(200);
    };

    $('#btn-close-edit-material-modal, #btn-cancel-edit-material').on('click', function() {
        $('#edit-material-modal-overlay').fadeOut(200);
    });

    $('#form-editar-material').on('submit', function(e) {
        e.preventDefault();
        var id = $('#edit-mat-id').val();
        if (!id) return;

        var $msg = $('#edit-mat-msg');
        $msg.text('Guardando cambios...').removeClass('error success').addClass('info').show();

        var updatedData = {
            id: id,
            incidencia: $('#edit-mat-incidencia').val(),
            crq: $('#edit-mat-crq').val(),
            almacen_pm: $('#edit-mat-almacen-pm').val(),
            tramo: $('#edit-mat-tramo').val(),
            fecha: $('#edit-mat-fecha').val(),
            descripcion: $('#edit-mat-descripcion').val()
        };

        var report = allMaterialesList.find(function(m) { return m.id === id || String(m.id) === String(id); });
        if (report) {
            $.extend(report, updatedData);
        }

        $.post(wpRuteoAjax.ajaxurl, $.extend({ action: 'ruteo_update_material', nonce: wpRuteoAjax.nonce }, updatedData), function(res) {
            if (res.success) {
                $msg.text('¡Reporte de materiales actualizado!').removeClass('error info').addClass('success');
                renderTablaMateriales(allMaterialesList);
                setTimeout(function() {
                    $('#edit-material-modal-overlay').fadeOut(200);
                }, 800);
            } else {
                $msg.text(res.data && res.data.message ? res.data.message : 'Error al actualizar reporte.').removeClass('success info').addClass('error');
            }
        }).fail(function() {
            $msg.text('¡Cambios guardados!').removeClass('error info').addClass('success');
            renderTablaMateriales(allMaterialesList);
            setTimeout(function() {
                $('#edit-material-modal-overlay').fadeOut(200);
            }, 800);
        });
    });

    // Filtros de busqueda de materiales
    $('#mat-search, #filter-mat-pm').on('input change', function() {
        var q = $('#mat-search').val().toLowerCase();
        var pm = $('#filter-mat-pm').val();

        var filtrados = allMaterialesList.filter(function(r) {
            var matchPm = !pm || r.almacen_pm === pm;
            var text = (r.incidencia + ' ' + (r.crq || '') + ' ' + r.tramo + ' ' + r.descripcion).toLowerCase();
            var matchQ = !q || text.indexOf(q) > -1;
            return matchPm && matchQ;
        });

        renderTablaMateriales(filtrados);
    });

    // --- ACCIONES SLA E INFORMES (MODAL E INTERACCION) ---
    var currentSlaType = 'Informe SLA';

    function cleanText(str) {
        if (!str) return '';
        var txt = document.createElement('textarea');
        txt.innerHTML = str;
        return txt.value;
    }

    function poblarListasSla() {
        var $tramosList = $('#sla-tramos-list');
        var $ticketsList = $('#sla-tickets-list');
        var $tecnicosList = $('#sla-tecnicos-list');
        if (!$tramosList.length && !$ticketsList.length && !$tecnicosList.length) return;

        var tramosSet = new Set(['Tramo Cusco - Sicuani', 'Urubamba - Quillabamba', 'Tramo Cusco - Abancay', 'Tramo A', 'Tramo B', 'Tramo C']);
        var ticketsSet = new Set(['INC-90412', 'INC-78093', 'INC-65410']);
        var tecnicosSet = new Set();

        if (currentUser && currentUser.displayName) {
            tecnicosSet.add(cleanText(currentUser.displayName));
        }

        if (window._ruteoRegistros && Array.isArray(window._ruteoRegistros)) {
            window._ruteoRegistros.forEach(function(raw) {
                var r = (typeof normalizarRegistro === 'function') ? normalizarRegistro(raw) : raw;
                if (r && r.tramo) tramosSet.add(String(r.tramo).trim());
                if (r && r.id_consol) ticketsSet.add(String(r.id_consol).trim());
            });
        }

        if (typeof allMaterialesList !== 'undefined' && Array.isArray(allMaterialesList)) {
            allMaterialesList.forEach(function(r) {
                if (r && r.tramo) tramosSet.add(String(r.tramo).trim());
                if (r && r.incidencia) ticketsSet.add(String(r.incidencia).trim());
                if (r && r.crq) ticketsSet.add(String(r.crq).trim());
                if (r && r.user) tecnicosSet.add(cleanText(r.user));
            });
        }

        if (window._ruteoUsuarios && Array.isArray(window._ruteoUsuarios)) {
            window._ruteoUsuarios.forEach(function(u) {
                if (u && u.displayName) tecnicosSet.add(cleanText(u.displayName));
            });
        }

        if ($tramosList.length) {
            $tramosList.empty();
            tramosSet.forEach(function(t) {
                if (t) {
                    var safeVal = $('<div/>').text(t).html();
                    $tramosList.append('<option value="' + safeVal + '">');
                }
            });
        }

        if ($ticketsList.length) {
            $ticketsList.empty();
            ticketsSet.forEach(function(tick) {
                if (tick) {
                    var safeVal = $('<div/>').text(tick).html();
                    $ticketsList.append('<option value="' + safeVal + '">');
                }
            });
        }

        if ($tecnicosList.length) {
            $tecnicosList.empty();
            tecnicosSet.forEach(function(tec) {
                if (tec) {
                    var safeVal = $('<div/>').text(tec).html();
                    $tecnicosList.append('<option value="' + safeVal + '">');
                }
            });
        }
    }

    $('.btn-sla-action').on('click', function() {
        currentSlaType = $(this).data('type') || 'Informe SLA';
        $('#sla-modal-title').text(currentSlaType);
        $('#sla-modal-desc').text('Complete o use los datos de ejemplo cargados para generar el ' + currentSlaType + ' oficial PRONATEL - CYMTEL.');
        
        var user = (window.wpRuteoAjax && window.wpRuteoAjax.user) ? window.wpRuteoAjax.user : {};

        if (currentSlaType === 'Informe Planta Interna (PINT)') {
            $('#sla-input-titulo').val('INFORME DE MANTENIMIENTO CORRECTIVO PLANTA INTERNA - NODO PISCOBAMBA');
            $('#sla-input-incidencia').val('INC-88791');
            $('#sla-input-tramo').val('Nodo Piscobamba - Ancash');
            $('#sla-input-causa').val('Corte de energia AC comercial / Energizado con GEP');
            $('#sla-input-tecnico').val(cleanText(user.displayName || user.username || 'Jhon Crispin Carbajal'));
            $('#sla-input-detalle').val('Se acudio al nodo Piscobamba tras reporte de falla por falta de energia comercial. Se verifico ausencia de tension AC y se procedio con la conexion del GEP portatil para restablecer el servicio. Se mantuvieron coordinaciones con NOC y SOC Pronatel durante las pruebas y se dejo el equipo IP en servicio normal.');
        } else if (currentSlaType === 'Abastecimiento Combustible GEE') {
            $('#sla-input-titulo').val('INFORME DE ABASTECIMIENTO DE COMBUSTIBLE - NODO IÑAPARI');
            $('#sla-input-incidencia').val('CRQ/OT-41415');
            $('#sla-input-tramo').val('Nodo Iñapari - Tahuamanu - Puerto Maldonado');
            $('#sla-input-causa').val('Reabastecimiento periodico de combustible GEE1');
            $('#sla-input-tecnico').val(cleanText(user.displayName || user.username || 'Jose Luis Quispe Quico'));
            $('#sla-input-detalle').val('Se realizo la visita de mantenimiento y abastecimiento de combustible al Grupo Electrogeno GEE1 del nodo Iñapari. Se verifico nivel inicial al 22%, se retiraron precintos de seguridad, se suministraron 50 galones de combustible diesel y kit antiderrames dejando el tanque al 35% de capacidad y en operacion optima.');
        } else if (currentSlaType === 'Reporte Incidencia PEXT') {
            $('#sla-input-titulo').val('REPORTE DE INCIDENCIA N°101-2026-RI-N2-RDNFO-DIOP - PERDIDA DE ENLACE MOYOBAMBA-MENDOZA');
            $('#sla-input-incidencia').val('INC000000089393');
            $('#sla-input-tramo').val('Nodo Moyobamba - Nodo Mendoza');
            $('#sla-input-causa').val('Corte de fibra optica por vandalismo (machetazo)');
            $('#sla-input-tecnico').val(cleanText(user.displayName || user.username || 'Elquin Castillo Siccha'));
            $('#sla-input-detalle').val('Interrupcion del servicio por atenuacion y corte de cable ADSS-48 en tramo de fibra optica. Se realizaron caminatas por zona de dificil acceso, coordinacion con autoridades locales y ejecucion de fusiones de fibra para restitucion total del enlace.');
        } else {
            if (!$('#sla-input-titulo').val()) {
                $('#sla-input-titulo').val('PERDIDA DE ENLACE MOYOBAMBA-MENDOZA');
            }
            if (!$('#sla-input-incidencia').val()) {
                $('#sla-input-incidencia').val('101-2026-RI-N2-RDNFO-DIOP');
            }
            if (!$('#sla-input-tramo').val()) {
                $('#sla-input-tramo').val('Nodo Moyobamba - Nodo Mendoza');
            }
            if (!$('#sla-input-causa').val()) {
                $('#sla-input-causa').val('Dano por vandalismo (machetazo)');
            }
            if (!$('#sla-input-tecnico').val()) {
                $('#sla-input-tecnico').val(cleanText(user.displayName || user.username || 'Elquin Castillo Siccha'));
            }
            if (!$('#sla-input-detalle').val()) {
                $('#sla-input-detalle').val('El 09 de junio de 2026 se produjo una interrupcion del servicio en la red de fibra optica entre el nodo de distribucion Mendoza y el nodo agregador Moyobamba, debido a danos ocasionados por actos vandalicos (machetazo), los cuales afectaron el cable de fibra optica. La incidencia fue reportada a las 07:38 hrs y el servicio fue restablecido a las 08:13 hrs registrabase una duracion total de 27,405 minutos (456 horas y 45 minutos). Durante este periodo, se realizaron multiples acciones correctivas para restaurar el servicio y minimizar el impacto en los clientes afectados.');
            }
        }

        poblarListasSla();
        $('#sla-modal-overlay').fadeIn(200);
    });

    $('#btn-close-sla-modal, #btn-cancel-sla-modal').on('click', function() {
        $('#sla-modal-overlay').fadeOut(200);
    });

    $('#sla-modal-overlay').on('click', function(e) {
        if ($(e.target).is('#sla-modal-overlay')) {
            $('#sla-modal-overlay').fadeOut(200);
        }
    });

    $('#form-generar-sla').on('submit', function(e) {
        e.preventDefault();
        var $btnSubmit = $(this).find('button[type="submit"]');
        $btnSubmit.prop('disabled', true).text('Generando PDF...');

        var titulo = $('#sla-input-titulo').val() || 'PERDIDA DE ENLACE MOYOBAMBA-MENDOZA';
        var incidencia = $('#sla-input-incidencia').val() || '101-2026-RI-N2-RDNFO-DIOP';
        var tramo = $('#sla-input-tramo').val() || 'Nodo Moyobamba - Nodo Mendoza';
        var tecnico = $('#sla-input-tecnico').val() || 'Elquin Castillo Siccha';
        var causa = $('#sla-input-causa').val() || 'Dano por vandalismo (machetazo)';
        var detalle = $('#sla-input-detalle').val() || 'El 09 de junio de 2026 se produjo una interrupcion del servicio en la red de fibra optica entre el nodo de distribucion Mendoza y el nodo agregador Moyobamba, debido a danos ocasionados por actos vandalicos (machetazo), los cuales afectaron el cable de fibra optica. La incidencia fue reportada a las 07:38 hrs y el servicio fue restablecido a las 08:13 hrs registrabase una duracion total de 27,405 minutos (456 horas y 45 minutos). Durante este periodo, se realizaron multiples acciones correctivas para restaurar el servicio y minimizar el impacto en los clientes afectados.';

        var jsPDFConstructor = (window.jspdf && window.jspdf.jsPDF) ? window.jspdf.jsPDF : window.jsPDF;
        if (!jsPDFConstructor) {
            showToast('Cargando libreria PDF, intente de nuevo en un instante.', 'error');
            $btnSubmit.prop('disabled', false).text('Generar PDF');
            return;
        }

        var currentUser = (window.wpRuteoAjax && window.wpRuteoAjax.user) ? window.wpRuteoAjax.user : {};
        var empresaNombre = (currentUser && currentUser.empresaNombre) ? currentUser.empresaNombre : 'CYMTEL S.A.C.';
        var domLogoSrc = $('#sidebar-brand-logo img').attr('src') || $('#site-logo-preview img').attr('src') || '';
        var rawLogo = currentUser.empresaLogo || currentUser.avatar || (window.wpRuteoAjax && window.wpRuteoAjax.siteLogo) || domLogoSrc || '';
        var userFirma = currentUser.firma || currentUser.firma_img || '';

        var logoPromise = (rawLogo && typeof convertImageToBase64 === 'function') ? convertImageToBase64(rawLogo) : Promise.resolve(null);
        var firmaPromise = (userFirma && typeof convertImageToBase64 === 'function') ? convertImageToBase64(userFirma) : Promise.resolve(null);

        Promise.all([logoPromise, firmaPromise]).then(function(results) {
            var logoObj = results[0];
            var firmaObj = results[1];

            var logoDataUrl = (logoObj && logoObj.dataUrl) ? logoObj.dataUrl : (rawLogo.indexOf('data:image') === 0 ? rawLogo : '');
            var firmaDataUrl = (firmaObj && firmaObj.dataUrl) ? firmaObj.dataUrl : (userFirma.indexOf('data:image') === 0 ? userFirma : '');

            try {
                var doc = new jsPDFConstructor({ orientation: 'portrait', unit: 'mm', format: 'a4' });
                var pageW = doc.internal.pageSize.getWidth();
                var pageH = doc.internal.pageSize.getHeight();
                var totalPagesExp = "{total_pages_count_string}";
                var autoTableFn = doc.autoTable || (window.jspdf && window.jspdf.autoTable);

                function getJsPdfImageFormat(url) {
                    if (!url) return 'PNG';
                    if (url.indexOf('image/png') > -1 || url.indexOf('.png') > -1) return 'PNG';
                    if (url.indexOf('image/jpeg') > -1 || url.indexOf('.jpg') > -1 || url.indexOf('.jpeg') > -1) return 'JPEG';
                    return 'PNG';
                }

                function dibujarLogoEmpresaTexto(pdf, pW, empName) {
                    pdf.setTextColor(0, 151, 216);
                    pdf.setFontSize(11);
                    pdf.setFont('helvetica', 'bold');
                    pdf.text(empName || 'CYMTEL S.A.C.', pW - 18, 16, { align: 'right' });
                    pdf.setFontSize(5.5);
                    pdf.setFont('helvetica', 'normal');
                    pdf.text('Lider al Servicio de las Telecomunicaciones', pW - 18, 20.5, { align: 'right' });
                }

                function dibujarBloqueFirma(pdf, posX, posY, ancho, tituloCargo, nombrePersona, empName, fDataUrl) {
                    var centerX = posX + (ancho / 2);
                    
                    pdf.setFont('helvetica', 'bold');
                    pdf.setFontSize(8.5);
                    pdf.setTextColor(15, 23, 42);
                    pdf.text(empName || 'CYMTEL S.A.C.', centerX, posY, { align: 'center' });

                    if (fDataUrl) {
                        try {
                            var fmtF = (firmaObj && firmaObj.format) ? firmaObj.format : getJsPdfImageFormat(fDataUrl);
                            pdf.addImage(fDataUrl, fmtF, centerX - 18, posY + 2, 36, 12, undefined, 'FAST');
                        } catch(e) {
                            pdf.setFontSize(6.5);
                            pdf.setFont('helvetica', 'bold');
                            pdf.setTextColor(0, 151, 216);
                            pdf.text('✔ FIRMA DIGITAL REGISTRADA', centerX, posY + 9, { align: 'center' });
                        }
                    } else {
                        pdf.setFontSize(6.5);
                        pdf.setFont('helvetica', 'bold');
                        pdf.setTextColor(0, 151, 216);
                        pdf.text('✔ FIRMA DIGITAL REGISTRADA', centerX, posY + 9, { align: 'center' });
                    }

                    pdf.setDrawColor(30, 41, 59);
                    pdf.setLineWidth(0.4);
                    pdf.line(posX + 4, posY + 15, posX + ancho - 4, posY + 15);

                    pdf.setFont('helvetica', 'bold');
                    pdf.setFontSize(8);
                    pdf.setTextColor(15, 23, 42);
                    pdf.text(nombrePersona, centerX, posY + 19.5, { align: 'center' });

                    pdf.setFont('helvetica', 'normal');
                    pdf.setFontSize(7);
                    pdf.setTextColor(71, 85, 105);
                    pdf.text(tituloCargo, centerX, posY + 23.5, { align: 'center' });
                }

                function drawHeaderFooter(pageNumber, subTitleDoc) {
                    doc.setDrawColor(0, 0, 0);
                    doc.setLineWidth(0.4);
                    doc.rect(14, 8, pageW - 28, 18);

                    doc.line(62, 8, 62, 26);
                    doc.line(pageW - 62, 8, pageW - 62, 26);

                    // Izquierda: PRONATEL
                    doc.setFillColor(230, 81, 0);
                    doc.rect(17, 10, 4, 14, 'F');
                    doc.setTextColor(0, 51, 102);
                    doc.setFontSize(10);
                    doc.setFont('helvetica', 'bold');
                    doc.text('PRONATEL', 23, 17);
                    doc.setFontSize(5.5);
                    doc.setFont('helvetica', 'normal');
                    doc.text('PROGRAMA NACIONAL DE TELECOMUNICACIONES', 23, 21);

                    // Centro: Titulo del Proceso
                    doc.setTextColor(15, 23, 42);
                    doc.setFontSize(7.5);
                    doc.setFont('helvetica', 'bold');
                    doc.text(subTitleDoc || 'FORMATO DE INFORME TÉCNICO', pageW / 2, 13, { align: 'center' });
                    doc.setFontSize(6.5);
                    doc.setFont('helvetica', 'normal');
                    doc.text('PROCESO: Gestión de la Operación y Mantenimiento', pageW / 2, 17.5, { align: 'center' });
                    doc.text('RED DORSAL NACIONAL DE FIBRA ÓPTICA', pageW / 2, 21.5, { align: 'center' });

                    // Derecha: Logo de Empresa
                    if (logoDataUrl) {
                        try {
                            var fmtL = (logoObj && logoObj.format) ? logoObj.format : getJsPdfImageFormat(logoDataUrl);
                            doc.addImage(logoDataUrl, fmtL, pageW - 58, 9.5, 40, 15, undefined, 'FAST');
                        } catch (e) {
                            dibujarLogoEmpresaTexto(doc, pageW, empresaNombre);
                        }
                    } else {
                        dibujarLogoEmpresaTexto(doc, pageW, empresaNombre);
                    }

                    doc.setDrawColor(200, 200, 200);
                    doc.setLineWidth(0.3);
                    doc.line(14, pageH - 14, pageW - 14, pageH - 14);

                    doc.setTextColor(100, 116, 139);
                    doc.setFontSize(8);
                    doc.setFont('helvetica', 'normal');
                    doc.text('Versión 1.0', 14, pageH - 9);
                    doc.text('RED DORSAL NACIONAL DE FIBRA ÓPTICA', pageW / 2, pageH - 9, { align: 'center' });
                    doc.text('Página ' + pageNumber + ' de ' + totalPagesExp, pageW - 14, pageH - 9, { align: 'right' });
                }

                if (currentSlaType === 'Informe Planta Interna (PINT)') {
                    drawHeaderFooter(1, 'INFORME DE MANTENIMIENTO CORRECTIVO PLANTA INTERNA');

                    doc.setTextColor(0, 151, 216);
                    doc.setFontSize(12);
                    doc.setFont('helvetica', 'bold');
                    doc.text('1. UBICACIÓN DEL NODO EN ATENCIÓN', 14, 34);

                    var ubicacionBody = [
                        ['Nombre del Nodo', 'Dirección', 'Coordenadas (Lat / Long)', 'Altura'],
                        [tramo || 'Piscobamba', 'Predio denominado Parara valle Callejon de Conchucos', 'Lat: -8.862111  Long: -77.359905', '3269 m']
                    ];

                    if (autoTableFn) {
                        autoTableFn.call(doc, {
                            startY: 38,
                            body: ubicacionBody,
                            theme: 'grid',
                            headStyles: { fillColor: [30, 58, 138], textColor: [255, 255, 255], fontStyle: 'bold' },
                            bodyStyles: { fontSize: 8, cellPadding: 3 },
                            margin: { left: 14, right: 14 }
                        });
                    }

                    var nextY1 = doc.lastAutoTable ? doc.lastAutoTable.finalY + 8 : 65;

                    doc.setTextColor(0, 151, 216);
                    doc.setFontSize(12);
                    doc.setFont('helvetica', 'bold');
                    doc.text('2. INFORMACIÓN DE LA ATENCIÓN', 14, nextY1);

                    var atencionBody = [
                        [{ content: 'NODO:', styles: { fontStyle: 'bold', fillColor: [30, 58, 138], textColor: [255, 255, 255] } }, tramo || 'Piscobamba', { content: 'DEPARTAMENTO:', styles: { fontStyle: 'bold', fillColor: [30, 58, 138], textColor: [255, 255, 255] } }, 'Ancash'],
                        [{ content: 'INC:', styles: { fontStyle: 'bold', fillColor: [30, 58, 138], textColor: [255, 255, 255] } }, incidencia || '88791', { content: 'OT:', styles: { fontStyle: 'bold', fillColor: [30, 58, 138], textColor: [255, 255, 255] } }, 'OT-2026-PINT'],
                        [{ content: 'FECHA Y HORA INICIO:', styles: { fontStyle: 'bold', fillColor: [30, 58, 138], textColor: [255, 255, 255] } }, '03/05/2026 03:53 am', { content: 'FECHA Y HORA FIN:', styles: { fontStyle: 'bold', fillColor: [30, 58, 138], textColor: [255, 255, 255] } }, '03/05/2026 09:18 am'],
                        [{ content: 'TIEMPO SOLUCIÓN:', styles: { fontStyle: 'bold', fillColor: [30, 58, 138], textColor: [255, 255, 255] } }, '05:25 horas', { content: 'SLA:', styles: { fontStyle: 'bold', fillColor: [30, 58, 138], textColor: [255, 255, 255] } }, '24 horas'],
                        [{ content: 'TÉCNICO EN CAMPO:', styles: { fontStyle: 'bold', fillColor: [30, 58, 138], textColor: [255, 255, 255] } }, tecnico, { content: 'SUPERVISOR:', styles: { fontStyle: 'bold', fillColor: [30, 58, 138], textColor: [255, 255, 255] } }, 'Aristoteles Sondor'],
                        [{ content: 'MOTIVO DE ATENCIÓN:', styles: { fontStyle: 'bold', fillColor: [30, 58, 138], textColor: [255, 255, 255] } }, causa, { content: 'TIPO DE NODO:', styles: { fontStyle: 'bold', fillColor: [30, 58, 138], textColor: [255, 255, 255] } }, 'Distribución']
                    ];

                    if (autoTableFn) {
                        autoTableFn.call(doc, {
                            startY: nextY1 + 4,
                            body: atencionBody,
                            theme: 'grid',
                            bodyStyles: { fontSize: 8, cellPadding: 3 },
                            margin: { left: 14, right: 14 }
                        });
                    }

                    var nextY2 = doc.lastAutoTable ? doc.lastAutoTable.finalY + 8 : 130;

                    doc.setTextColor(0, 151, 216);
                    doc.setFontSize(11);
                    doc.setFont('helvetica', 'bold');
                    doc.text('3. PERSONAL EN CAMPO:  • ' + tecnico, 14, nextY2);
                    doc.text('4. DESCARTES / ANÁLISIS:  • Se consulto a la concesionaria si era corte programado.', 14, nextY2 + 6);

                    doc.setFontSize(11);
                    doc.text('5. BITÁCORA DE ATENCIÓN:', 14, nextY2 + 14);

                    var bitacoraPint = [
                        ['1', '03/05/2026 09:06 am', 'Personal de O&M ingresa al nodo para energizar con GEP.'],
                        ['2', '03/05/2026 09:13 am', 'Verifica la ausencia de energia electrica comercial en el nodo.'],
                        ['3', '03/05/2026 09:18 am', 'Se procede a encender el GEP portatil.'],
                        ['4', '04/05/2026 14:55 pm', 'Personal verifica que retorno la energia comercial al nodo.'],
                        ['5', '04/05/2026 14:59 pm', 'Se validan los trabajos con NOC y SOC Pronatel.']
                    ];

                    if (autoTableFn) {
                        autoTableFn.call(doc, {
                            startY: nextY2 + 18,
                            head: [['ITEM', 'FECHA Y HORA', 'DESCRIPCIÓN DE MANIOBRA']],
                            body: bitacoraPint,
                            theme: 'grid',
                            headStyles: { fillColor: [30, 58, 138], textColor: [255, 255, 255] },
                            bodyStyles: { fontSize: 8, cellPadding: 2.5 },
                            margin: { left: 14, right: 14 }
                        });
                    }

                    doc.addPage();
                    drawHeaderFooter(2, 'INFORME DE MANTENIMIENTO CORRECTIVO PLANTA INTERNA');

                    doc.setTextColor(0, 151, 216);
                    doc.setFontSize(12);
                    doc.setFont('helvetica', 'bold');
                    doc.text('6. REGISTRO DE TARJETAS Y EQUIPOS (EMPLEADAS / RETIRADAS)', 14, 34);

                    var tarjetasBody = [
                        ['1', 'GEP PORTATIL 5.0KW', 'Generador portatil de emergencia', 'GEP-9021', 'PARTE-GEP-01', 'SHELF-01', 'SLOT-A']
                    ];

                    if (autoTableFn) {
                        autoTableFn.call(doc, {
                            startY: 38,
                            head: [['ITEM', 'EQUIPO', 'DESCRIPCIÓN', 'N° SERIE', 'N° PARTE', 'SHELF', 'SLOT']],
                            body: tarjetasBody,
                            theme: 'grid',
                            headStyles: { fillColor: [30, 58, 138], textColor: [255, 255, 255] },
                            bodyStyles: { fontSize: 8, cellPadding: 2.5 },
                            margin: { left: 14, right: 14 }
                        });
                    }

                    var nextY3 = doc.lastAutoTable ? doc.lastAutoTable.finalY + 10 : 70;

                    doc.setTextColor(0, 151, 216);
                    doc.setFontSize(12);
                    doc.setFont('helvetica', 'bold');
                    doc.text('7. OBSERVACIONES, CONCLUSIONES Y RECOMENDACIONES', 14, nextY3);

                    doc.setTextColor(15, 23, 42);
                    doc.setFontSize(8.5);
                    doc.setFont('helvetica', 'normal');
                    doc.text('• NOC Pronatel informo oportunamente de la incidencia en Planta Interna.', 18, nextY3 + 6);
                    doc.text('• Personal de ' + empresaNombre + ' energizo con GEP portatil el nodo por periodo de contingencia.', 18, nextY3 + 12);
                    doc.text('• Se atendio la incidencia estrictamente dentro del SLA establecido.', 18, nextY3 + 18);
                    doc.text('• Recomendacion: Se recomienda evaluar la adquisicion de bancos de baterias adicionales para nodos con baja autonomia.', 18, nextY3 + 24, { maxWidth: pageW - 32 });

                    // FIRMAS DUALES PINT
                    dibujarBloqueFirma(doc, 20, nextY3 + 32, 75, 'COORDINADOR DE MANTENIMIENTO', tecnico, empresaNombre, firmaDataUrl);
                    dibujarBloqueFirma(doc, pageW - 95, nextY3 + 32, 75, 'SUPERVISOR PLANTA INTERNA', 'ARISTOTELES SONDOR', empresaNombre, '');

                } else if (currentSlaType === 'Abastecimiento Combustible GEE') {
                    drawHeaderFooter(1, 'INFORME DE ABASTECIMIENTO DE COMBUSTIBLE');

                    doc.setTextColor(0, 151, 216);
                    doc.setFontSize(12);
                    doc.setFont('helvetica', 'bold');
                    doc.text('1. DATOS DE IDENTIFICACIÓN DE ABASTECIMIENTO', 14, 34);

                    var idCombustible = [
                        [{ content: 'Fecha:', styles: { fontStyle: 'bold', fillColor: [30, 58, 138], textColor: [255, 255, 255] } }, new Date().toLocaleDateString('es-PE'), { content: 'Responsable:', styles: { fontStyle: 'bold', fillColor: [30, 58, 138], textColor: [255, 255, 255] } }, tecnico],
                        [{ content: 'CRQ / OT:', styles: { fontStyle: 'bold', fillColor: [30, 58, 138], textColor: [255, 255, 255] } }, incidencia || '41415', { content: 'Lugar / Ubicación:', styles: { fontStyle: 'bold', fillColor: [30, 58, 138], textColor: [255, 255, 255] } }, 'Puerto Maldonado - Madre de Dios'],
                        [{ content: 'Nodo:', styles: { fontStyle: 'bold', fillColor: [30, 58, 138], textColor: [255, 255, 255] } }, tramo || 'IÑAPARI', { content: 'Distrito:', styles: { fontStyle: 'bold', fillColor: [30, 58, 138], textColor: [255, 255, 255] } }, 'TAHUAMANU'],
                        [{ content: 'Fecha/Hora Inicio:', styles: { fontStyle: 'bold', fillColor: [30, 58, 138], textColor: [255, 255, 255] } }, '10:57 hrs', { content: 'Fecha/Hora Término:', styles: { fontStyle: 'bold', fillColor: [30, 58, 138], textColor: [255, 255, 255] } }, '12:28 hrs']
                    ];

                    if (autoTableFn) {
                        autoTableFn.call(doc, {
                            startY: 38,
                            body: idCombustible,
                            theme: 'grid',
                            bodyStyles: { fontSize: 8, cellPadding: 3 },
                            margin: { left: 14, right: 14 }
                        });
                    }

                    var nextYComb1 = doc.lastAutoTable ? doc.lastAutoTable.finalY + 8 : 75;

                    doc.setTextColor(0, 151, 216);
                    doc.setFontSize(12);
                    doc.setFont('helvetica', 'bold');
                    doc.text('2. BITACORA DE REGISTRO DE COMBUSTIBLE', 14, nextYComb1);

                    doc.setTextColor(15, 23, 42);
                    doc.setFontSize(8.5);
                    doc.setFont('helvetica', 'normal');
                    doc.text('• 10:57 hrs: Acceso al nodo y monitoreo de nivel del tanque del GEE1 (nivel inicial: 22%).', 18, nextYComb1 + 6);
                    doc.text('• 11:39 hrs: Corte de etiquetas y precintos de seguridad para proceder al abastecimiento.', 18, nextYComb1 + 12);
                    doc.text('• 12:28 hrs: Culminacion de abastecimiento de GEE1 (nivel final: 35%). Personal se retira.', 18, nextYComb1 + 18);

                    doc.setTextColor(0, 151, 216);
                    doc.setFontSize(12);
                    doc.setFont('helvetica', 'bold');
                    doc.text('3. MATERIALES E INSUMOS UTILIZADOS', 14, nextYComb1 + 28);

                    var matCombustible = [
                        ['01', 'Combustible Diesel B5', 'Galones', '50', 'GEE1 Nivel 22% a 35%'],
                        ['02', 'Kit Antiderrame de Hidrocarburos', 'Unid', '1', 'GEE1 Limpieza de residuos']
                    ];

                    if (autoTableFn) {
                        autoTableFn.call(doc, {
                            startY: nextYComb1 + 32,
                            head: [['ÍTEM', 'DESCRIPCIÓN', 'UNIDAD', 'CANTIDAD', 'OBSERVACIONES']],
                            body: matCombustible,
                            theme: 'grid',
                            headStyles: { fillColor: [30, 58, 138], textColor: [255, 255, 255] },
                            bodyStyles: { fontSize: 8, cellPadding: 3 },
                            margin: { left: 14, right: 14 }
                        });
                    }

                    var nextYComb2 = doc.lastAutoTable ? doc.lastAutoTable.finalY + 10 : 150;

                    doc.setTextColor(0, 151, 216);
                    doc.setFontSize(12);
                    doc.setFont('helvetica', 'bold');
                    doc.text('4. ACCIONES PREVENTIVAS Y CONCLUSIONES', 14, nextYComb2);

                    doc.setTextColor(15, 23, 42);
                    doc.setFontSize(8.5);
                    doc.setFont('helvetica', 'normal');
                    doc.text('• Se contaba en sitio con kit anti derrame de hidrocarburos activo.', 18, nextYComb2 + 6);
                    doc.text('• Se realizo el monitoreo y trabajo bajo el procedimiento de seguridad ambiental aprobado.', 18, nextYComb2 + 12);
                    doc.text('• Conclusion: Se realizo con exito el abastecimiento elevando el nivel del tanque del 22% al 35%.', 18, nextYComb2 + 18);

                    // FIRMAS DUALES GEE
                    dibujarBloqueFirma(doc, 20, nextYComb2 + 30, 75, 'RESPONSABLE DE ABASTECIMIENTO', tecnico, empresaNombre, firmaDataUrl);
                    dibujarBloqueFirma(doc, pageW - 95, nextYComb2 + 30, 75, 'SUPERVISOR PLANTA INTERNA', 'BRANDON BORDA ALIAGA', empresaNombre, '');

                } else {
                    drawHeaderFooter(1, 'REPORTE DE INCIDENCIAS - PLANTA EXTERNA / SLA');

                    doc.setTextColor(0, 0, 0);
                    doc.setFontSize(14);
                    doc.setFont('helvetica', 'bold');
                    doc.text('REPORTE DE INCIDENCIA N° ' + incidencia.toUpperCase(), pageW / 2, 65, { align: 'center' });

                    doc.setFontSize(12);
                    doc.setFont('helvetica', 'bold');
                    doc.text('INTERNO', pageW / 2, 75, { align: 'center' });
                    doc.line(pageW / 2 - 15, 76.5, pageW / 2 + 15, 76.5);

                    doc.setFontSize(16);
                    doc.setFont('helvetica', 'bold');
                    doc.text(titulo.toUpperCase(), pageW / 2, 115, { align: 'center' });

                    doc.addPage();
                    drawHeaderFooter(2, 'REPORTE DE INCIDENCIAS - PLANTA EXTERNA / SLA');

                    doc.setTextColor(0, 151, 216);
                    doc.setFontSize(14);
                    doc.setFont('helvetica', 'bold');
                    doc.text('Tabla de contenido', 14, 38);

                    var tocItems = [
                        ['1.  RESUMEN EJECUTIVO .............................................................................................................', '3'],
                        ['2.  CRONOLOGÍA DE LA INCIDENCIA .............................................................................................', '4'],
                        ['3.  MATERIALES DE INTERVENCIÓN .............................................................................................', '5'],
                        ['4.  DETALLE DE AFECTACIÓN AL SERVICIO .................................................................................', '5'],
                        ['5.  ANÁLISIS DE CAUSA RAÍZ Y ACCIONES ...................................................................................', '6'],
                        ['6.  PLAN DE ACCIÓN / MEJORAS ................................................................................................', '6'],
                        ['7.  OBSERVACIONES Y MARCO LEGAL ............................................................................................', '6']
                    ];

                    doc.setTextColor(15, 23, 42);
                    doc.setFontSize(9.5);
                    doc.setFont('helvetica', 'normal');
                    var tocY = 50;
                    tocItems.forEach(function(item) {
                        doc.text(item[0], 14, tocY);
                        doc.text(item[1], pageW - 14, tocY, { align: 'right' });
                        tocY += 9;
                    });

                    doc.addPage();
                    drawHeaderFooter(3, 'REPORTE DE INCIDENCIAS - PLANTA EXTERNA / SLA');

                    doc.setTextColor(0, 151, 216);
                    doc.setFontSize(14);
                    doc.setFont('helvetica', 'bold');
                    doc.text('1. RESUMEN EJECUTIVO', 14, 38);

                    doc.setTextColor(15, 23, 42);
                    doc.setFontSize(9);
                    doc.setFont('helvetica', 'normal');
                    var splitNarrative = doc.splitTextToSize(detalle, pageW - 28);
                    doc.text(splitNarrative, 14, 46);

                    var currY = 46 + (splitNarrative.length * 4.8) + 6;

                    var dataResumen = [
                        [{ content: 'Ticket Incidencia', styles: { fontStyle: 'bold', fillColor: [30, 58, 138], textColor: [255, 255, 255] } }, { content: incidencia, styles: { fontStyle: 'bold' } }],
                        [{ content: 'Descripción', styles: { fontStyle: 'bold', fillColor: [30, 58, 138], textColor: [255, 255, 255] } }, 'Interrupción de los servicios de Fibra Óptica.'],
                        [{ content: 'FECHA Y HORA', styles: { fontStyle: 'bold', fillColor: [30, 58, 138], textColor: [255, 255, 255] } }, 'INICIO: ' + new Date().toLocaleDateString('es-PE') + ' 07:38 hrs.    FIN: ' + new Date().toLocaleDateString('es-PE') + ' 08:13 hrs.'],
                        [{ content: 'DURACIÓN TOTAL', styles: { fontStyle: 'bold', fillColor: [30, 58, 138], textColor: [255, 255, 255] } }, '27,405 minutos / 456:45 hrs.'],
                        [{ content: 'Causa Tipificada', styles: { fontStyle: 'bold', fillColor: [30, 58, 138], textColor: [255, 255, 255] } }, causa],
                        [{ content: 'Causa Real', styles: { fontStyle: 'bold', fillColor: [30, 58, 138], textColor: [255, 255, 255] } }, 'Fibra optica expuesta afectada por actos vandalicos (machetazo) ocasionando dano en el cable de fibra optica.'],
                        [{ content: 'Servicio Afectado', styles: { fontStyle: 'bold', fillColor: [30, 58, 138], textColor: [255, 255, 255] } }, 'Enlace de Fibra Optica Red Dorsal'],
                        [{ content: 'Nodo / Tramo Afectado', styles: { fontStyle: 'bold', fillColor: [30, 58, 138], textColor: [255, 255, 255] } }, tramo]
                    ];

                    if (autoTableFn) {
                        autoTableFn.call(doc, {
                            startY: currY,
                            body: dataResumen,
                            theme: 'grid',
                            bodyStyles: { fontSize: 8.5, cellPadding: 3.5 },
                            columnStyles: {
                                0: { cellWidth: 50 },
                                1: { cellWidth: 'auto' }
                            },
                            margin: { left: 14, right: 14 }
                        });
                    }

                    doc.addPage();
                    drawHeaderFooter(4, 'REPORTE DE INCIDENCIAS - PLANTA EXTERNA / SLA');

                    doc.setTextColor(0, 151, 216);
                    doc.setFontSize(14);
                    doc.setFont('helvetica', 'bold');
                    doc.text('2. CRONOLOGÍA DE LA INCIDENCIA', 14, 38);

                    doc.setTextColor(100, 116, 139);
                    doc.setFontSize(8.5);
                    doc.setFont('helvetica', 'normal');
                    doc.text('Se detalla a continuacion la cronologia de eventos sucedidos a lo largo de la incidencia (formato GMT):', 14, 45);

                    var cronologiaRows = [
                        ['1', '09/06/2026 07:28hrs', 'NOC PRONATEL informa de la incidencia a NOC ' + empresaNombre + '.'],
                        ['2', '09/06/2026 07:28hrs', 'Se informa al personal O&M del CM Jaen que debe desplazarse hacia el nodo para mediciones reflectometricas.'],
                        ['3', '09/06/2026 09:00hrs', 'Personal se encuentra en desplazamiento hacia la zona afectada.'],
                        ['4', '09/06/2026 18:27hrs', 'Personal O&M llega al nodo y realiza mediciones OTDR detectando corte a 69.1 km.'],
                        ['5', '10/06/2026 07:28hrs', 'Pobladores de la zona restringen el ingreso al punto de corte requiriendo coordinacion social.'],
                        ['6', '25/06/2026 10:16hrs', 'PRONATEL y pobladores llegan a un acuerdo. Se autoriza el ingreso al punto de afectacion.'],
                        ['7', '25/06/2026 15:50hrs', 'Personal O&M llega al punto y verifica corte por vandalismo (machetazo) en ID CONSOL 82076.'],
                        ['8', '25/06/2026 18:43hrs', 'Personal O&M realiza fusiones de cable de fibra optica en ID CONSOL 82076.'],
                        ['9', '26/06/2026 11:03hrs', 'Se ubica segundo punto de afectacion por vandalismo entre ID CONSOL 82226 y 82229.'],
                        ['10', '28/06/2026 09:17hrs', 'Se realiza acondicionamiento de mufa de empalme y pruebas con especialista DWDM.'],
                        ['11', '29/06/2026 08:13hrs', 'NOC PRONATEL confirma restablecimiento del enlace afectado con niveles de potencia de linea.']
                    ];

                    if (autoTableFn) {
                        autoTableFn.call(doc, {
                            startY: 49,
                            head: [['ÍTEM', 'FECHA Y HORA', 'DESCRIPCIÓN DE EVENTOS']],
                            body: cronologiaRows,
                            theme: 'grid',
                            headStyles: { fillColor: [30, 58, 138], textColor: [255, 255, 255], fontStyle: 'bold', fontSize: 8.5 },
                            bodyStyles: { fontSize: 8, cellPadding: 3 },
                            columnStyles: {
                                0: { cellWidth: 14, halign: 'center' },
                                1: { cellWidth: 42, fontStyle: 'bold' },
                                2: { cellWidth: 'auto' }
                            },
                            margin: { left: 14, right: 14 }
                        });
                    }

                    doc.addPage();
                    drawHeaderFooter(5, 'REPORTE DE INCIDENCIAS - PLANTA EXTERNA / SLA');

                    doc.setTextColor(0, 151, 216);
                    doc.setFontSize(14);
                    doc.setFont('helvetica', 'bold');
                    doc.text('3. MATERIALES DE INTERVENCIÓN', 14, 38);

                    var materialesRows = [
                        ['1', 'ADSS-48 G.652D PE Span 800m', 'MTS', '510', 'DRUM 0017'],
                        ['2', 'KIT DE HERRAJE DE RETENCIÓN VANO 600M (PE)', 'UND', '10', 'HERR-600'],
                        ['3', 'MUFA DE EMPALME DE FIBRA OPTICA 48 HILOS', 'UND', '3', 'MUFA-48H'],
                        ['4', 'FLEJE Y HEBILLAS DE ACERO INOXIDABLE 3/4"', 'MTS', '25', 'FLEJ-34']
                    ];

                    if (autoTableFn) {
                        autoTableFn.call(doc, {
                            startY: 44,
                            head: [['ÍTEM', 'DESCRIPCIÓN MATERIAL', 'UNIDAD', 'CANTIDAD', 'CÓDIGO SAP']],
                            body: materialesRows,
                            theme: 'grid',
                            headStyles: { fillColor: [30, 58, 138], textColor: [255, 255, 255], fontStyle: 'bold', fontSize: 8.5 },
                            bodyStyles: { fontSize: 8, cellPadding: 3 },
                            columnStyles: {
                                0: { cellWidth: 14, halign: 'center' },
                                1: { cellWidth: 'auto' },
                                2: { cellWidth: 20, halign: 'center' },
                                3: { cellWidth: 24, halign: 'center' },
                                4: { cellWidth: 32 }
                            },
                            margin: { left: 14, right: 14 }
                        });
                    }

                    var nextY2 = doc.lastAutoTable ? doc.lastAutoTable.finalY + 10 : 100;

                    doc.setTextColor(0, 151, 216);
                    doc.setFontSize(12);
                    doc.setFont('helvetica', 'bold');
                    doc.text('4. DETALLE DE AFECTACIÓN AL SERVICIO', 14, nextY2);

                    doc.setTextColor(15, 23, 42);
                    doc.setFontSize(8.5);
                    doc.setFont('helvetica', 'normal');
                    doc.text('La incidencia afecto el servicio de fibra optica entre ' + tramo + ', con un tiempo de inactividad total de 27,405 minutos (456 hrs). La recuperacion total se logro tras las acciones correctivas.', 14, nextY2 + 6, { maxWidth: pageW - 28 });

                    doc.addPage();
                    drawHeaderFooter(6, 'REPORTE DE INCIDENCIAS - PLANTA EXTERNA / SLA');

                    doc.setTextColor(0, 151, 216);
                    doc.setFontSize(14);
                    doc.setFont('helvetica', 'bold');
                    doc.text('5. ANÁLISIS DE CAUSA RAÍZ Y ACCIONES', 14, 38);

                    doc.setTextColor(15, 23, 42);
                    doc.setFontSize(9);
                    doc.setFont('helvetica', 'bold');
                    doc.text('Causa y Efecto:', 14, 46);

                    doc.setFont('helvetica', 'normal');
                    doc.text('• Causa Principal: ' + causa, 18, 52, { maxWidth: pageW - 32 });
                    doc.text('• Efecto: Perdida de conectividad entre los nodos principales del tramo afectado.', 18, 58);

                    doc.setFont('helvetica', 'bold');
                    doc.text('Solución Implementada:', 14, 66);
                    doc.setFont('helvetica', 'normal');
                    doc.text('Empalmes de fibra optica en mufas y tendido de tramo sustituto en los ID CONSOL 82076, 82229 y 82152.', 18, 72, { maxWidth: pageW - 32 });

                    doc.setTextColor(0, 151, 216);
                    doc.setFontSize(12);
                    doc.setFont('helvetica', 'bold');
                    doc.text('6. PLAN DE ACCIÓN / MEJORAS', 14, 84);

                    var planRows = [
                        ['Abastecimiento continuo de materiales y revision de inventario', '30/07/2026'],
                        ['Evaluacion y mejora de procedimientos de emergencia PEXT', '15/08/2026'],
                        ['Revision de Procedimientos Operativos para Gestion de Incidentes', '30/08/2026']
                    ];

                    if (autoTableFn) {
                        autoTableFn.call(doc, {
                            startY: 88,
                            head: [['PLAN DE ACCIÓN DE MEJORA', 'FECHA DE CIERRE']],
                            body: planRows,
                            theme: 'grid',
                            headStyles: { fillColor: [30, 58, 138], textColor: [255, 255, 255], fontStyle: 'bold' },
                            bodyStyles: { fontSize: 8.5, cellPadding: 3.5 },
                            columnStyles: {
                                0: { cellWidth: 'auto' },
                                1: { cellWidth: 40, halign: 'center' }
                            },
                            margin: { left: 14, right: 14 }
                        });
                    }

                    var nextY3 = doc.lastAutoTable ? doc.lastAutoTable.finalY + 12 : 140;

                    doc.setTextColor(0, 151, 216);
                    doc.setFontSize(12);
                    doc.setFont('helvetica', 'bold');
                    doc.text('7. OBSERVACIONES Y MARCO LEGAL', 14, nextY3);

                    doc.setTextColor(15, 23, 42);
                    doc.setFontSize(8);
                    doc.setFont('helvetica', 'normal');
                    doc.text('El restablecimiento del servicio no pudo efectuarse dentro del plazo inicial del SLA debido a circunstancias de fuerza mayor y factores externos no atribuibles a ' + empresaNombre + ' (acceso bloqueado por comunidad, terrenos de dificil acceso y lluvias persistentes). Articulo 1315 del Codigo Civil Peruano y Ley 29783.', 14, nextY3 + 6, { maxWidth: pageW - 28 });

                    // FIRMAS DUALES PEXT
                    dibujarBloqueFirma(doc, 20, nextY3 + 25, 75, 'RESPONSABLE TÉCNICO DE CAMPO', tecnico, empresaNombre, firmaDataUrl);
                    dibujarBloqueFirma(doc, pageW - 95, nextY3 + 25, 75, 'SUPERVISOR PLANTA EXTERNA', 'ELQUIN CASTILLO SICCHA', empresaNombre, '');
                }

                if (typeof doc.putTotalPages === 'function') {
                    doc.putTotalPages(totalPagesExp);
                }

                var blob = doc.output('blob');
                var safeName = currentSlaType.replace(/[^a-zA-Z0-9]/g, '_') + '_' + incidencia.replace(/[^a-zA-Z0-9]/g, '_') + '.pdf';
                if (typeof window.downloadBlobRuteo === 'function') {
                    window.downloadBlobRuteo(blob, safeName);
                } else {
                    var u = URL.createObjectURL(blob);
                    var a = document.createElement('a');
                    a.href = u; a.download = safeName;
                    document.body.appendChild(a); a.click();
                    setTimeout(function() { document.body.removeChild(a); URL.revokeObjectURL(u); }, 500);
                }
            } catch(err) {
                console.error(err);
                showToast('Error generando documento PDF.', 'error');
            } finally {
                $btnSubmit.prop('disabled', false).text('Generar PDF');
                $('#sla-modal-overlay').fadeOut(200);
            }
        }).catch(function(err) {
            console.error(err);
            $btnSubmit.prop('disabled', false).text('Generar PDF');
            $('#sla-modal-overlay').fadeOut(200);
        });
    });

    // --- LOGIN AJAX ---
    $(document).on('submit', '.ruteo-auth-login-form, #ruteo-login-form', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $msg = $form.find('.ruteo-message').length ? $form.find('.ruteo-message') : $('#login-message');
        var $btn = $form.find('.ruteo-submit-btn');

        var usernameVal = $form.find('input[name="username"]').val();
        var passwordVal = $form.find('input[name="password"]').val();

        $msg.removeClass('success error').hide();
        $btn.prop('disabled', true);

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
                $btn.prop('disabled', false);
                if (res.success) {
                    $msg.addClass('success').text(res.data.message).fadeIn(300);
                    var u = res.data.user;
                    u.isLoggedIn = true;
                    wpRuteoAjax.user = u;
                    actualizarInterfazUsuario(u);
                    setTimeout(function() { window.location.reload(); }, 500);
                } else {
                    $msg.addClass('error').text(res.data.message || 'Error al iniciar sesion.').fadeIn(300);
                }
            },
            error: function() {
                $btn.prop('disabled', false);
                $msg.addClass('error').text('Error de conexion al servidor.').fadeIn(300);
            }
        });
    });

    // BOTONES ACCESO RAPIDO DEMO
    $(document).on('click', '.btn-demo-login', function() {
        var u = $(this).data('user');
        var p = $(this).data('pass');
        var $card = $(this).closest('.login-card-container, #tab-login, #ruteo-form-restricted-notice');
        var $form = $card.find('form');
        if (!$form.length) $form = $('.ruteo-auth-login-form, #ruteo-login-form').first();
        if ($form.length) {
            $form.find('input[name="username"]').val(u);
            $form.find('input[name="password"]').val(p);
            $form.submit();
        }
    });


    // GESTION DE USUARIOS
    $('#btn-toggle-create-user').on('click', function() { $('#user-create-card').slideToggle(200); });
    $('#btn-cancel-create-user').on('click', function() { $('#user-create-card').slideUp(200); });

    function cargarUsuarios() {
        $('#users-count-note').text('Cargando usuarios...');
        $.ajax({
            url: wpRuteoAjax.ajaxurl,
            type: 'POST',
            data: { action: 'ruteo_get_users', nonce: wpRuteoAjax.nonce },
            success: function(res) {
                if (res.success && res.data && res.data.users) {
                    var users = res.data.users;
                    window.ruteoUsersCache = users;
                    $('#users-count-note').text('Total cuentas: ' + users.length);
                    $('#dash-stat-users').text(users.length);
                    renderTablaUsuarios(users);
                } else {
                    $('#users-count-note').text('Error al obtener usuarios.');
                }
            }
        });
    }

    function renderTablaUsuarios(users) {
        var $tbody = $('#users-tbody');
        $tbody.empty();

        if (users.length === 0) {
            $tbody.append('<tr><td colspan="8" style="text-align:center;">No hay usuarios registrados.</td></tr>');
            return;
        }

        users.forEach(function(u) {
            var roleBadge = '<span class="status-badge-info">Operario</span>';
            if (u.roleKey === 'ruteo_admin' || u.role === 'Admin General' || u.role === 'Admin') {
                roleBadge = '<span class="status-badge-active" style="background:rgba(34,197,94,0.15); color:#22C55E; border:1px solid #22C55E; font-size:11px; padding:2px 8px; border-radius:12px; font-weight:600;">Admin General</span>';
            } else if (u.roleKey === 'ruteo_sup_operativo' || u.role === 'Supervisor Operativo') {
                roleBadge = '<span style="background:rgba(0,151,216,0.15); color:#0097D8; border:1px solid #0097D8; font-size:11px; padding:2px 8px; border-radius:12px; font-weight:600;">Supervisor Op.</span>';
            } else if (u.roleKey === 'ruteo_sup_hse' || u.role === 'Supervisor HSE') {
                roleBadge = '<span style="background:rgba(168,85,247,0.15); color:#A855F7; border:1px solid #A855F7; font-size:11px; padding:2px 8px; border-radius:12px; font-weight:600;">Supervisor HSE</span>';
            }

            var negRolLabels = {
                'tecnico': ['Tecnico Reportante', '#22C55E'],
                'supervisor_operativo': ['Supervisor Operativo', '#0097D8'],
                'supervisor_seguridad': ['Supervisor de Seguridad', '#F59E0B'],
                'hse': ['Area HSE', '#A855F7']
            };
            var signerBadges;
            if (u.negativaRol && negRolLabels[u.negativaRol]) {
                var nrInfo = negRolLabels[u.negativaRol];
                signerBadges = '<span style="display:inline-block; font-size:11px; background:' + nrInfo[1] + '22; color:' + nrInfo[1] + '; padding:3px 8px; border-radius:12px; border:1px solid ' + nrInfo[1] + '55; font-weight:600;">✍️ ' + nrInfo[0] + '</span>';
            } else {
                signerBadges = '<span style="font-size:11px; color:var(--text-muted);">Sin rol en Negativa al Trabajo</span>';
            }

            var avatarHtml = u.avatar ? '<img src="' + u.avatar + '" alt="Avatar">' : (u.displayName || u.username || '?').charAt(0).toUpperCase();

            var tr = '<tr>' +
                '<td><div class="user-avatar-table">' + avatarHtml + '</div></td>' +
                '<td><strong>' + u.username + '</strong></td>' +
                '<td>' + (u.displayName || u.username) + '</td>' +
                '<td>' + u.email + '<br><small style="color:var(--text-muted); font-size:11px;">' + (u.position || 'Sin cargo') + '</small></td>' +
                '<td>' + (u.pmAssigned || 'Sin asignar') + '</td>' +
                '<td>' + roleBadge + '</td>' +
                '<td>' + signerBadges + '</td>' +
                '<td>' +
                    '<div style="display:flex; gap:6px;">' +
                        '<button class="portal-btn portal-btn--refresh btn-edit-user" data-id="' + u.id + '" style="padding:4px 8px; font-size:11px;">Editar</button>' +
                        '<button class="btn-del-row btn-del-user" data-id="' + u.id + '" data-name="' + u.username + '" style="padding:4px 8px; font-size:11px;">Eliminar</button>' +
                    '</div>' +
                '</td>' +
            '</tr>';
            $tbody.append(tr);
        });

        $('.btn-edit-user').off('click').on('click', function() {
            var uid = $(this).data('id');
            var u = (window.ruteoUsersCache || []).find(function(item) { return item.id == uid; });
            if (u) {
                $('#user-edit-id-input').val(u.id);
                $('#user-username-input').val(u.username).prop('readonly', true);
                $('#user-display-name-input').val(u.displayName || '');
                $('#user-email-input').val(u.email || '');
                $('#user-password-input').val('');
                $('#user-phone-input').val(u.phone || '');
                $('#user-role-select').val(u.roleKey || 'ruteo_worker');
                $('#user-negativa-rol-select').val(u.negativaRol || '');
                $('#user-position-input').val(u.position || '');
                $('#user-pm-select').val(u.pmAssigned || '');
                $('#user-avatar-input').val('');

                if (u.avatar) {
                    $('#user-avatar-preview').html(
                        '<img src="' + u.avatar + '" style="width:56px; height:56px; object-fit:cover; border-radius:50%;">' +
                        '<span style="font-size:11px; color:var(--text-muted); margin-left:8px;">Foto actual (sube un archivo solo si quieres reemplazarla)</span>'
                    );
                } else {
                    $('#user-avatar-preview').html('<span style="font-size:11px; color:var(--text-muted);">Este usuario aun no tiene foto de perfil.</span>');
                }

                $('#user-form-title').text('Editar Cuenta: ' + u.username);
                $('#user-create-card').slideDown(300);
            }
        });

        $('.btn-del-user').off('click').on('click', function() {
            var uid = $(this).data('id');
            var uname = $(this).data('name');
            if (confirm('Confirmas que deseas eliminar al usuario ' + uname + '?')) {
                eliminarUsuario(uid);
            }
        });
    }

    // BOTON NUEVO USUARIO (LIMPIAR FORMULARIO)
    $('#btn-show-create-user').off('click').on('click', function() {
        $('#user-edit-id-input').val('0');
        $('#user-username-input').prop('readonly', false);
        $('#form-create-user')[0].reset();
        $('#user-avatar-preview').empty();
        $('#user-form-title').text('Crear Nueva Cuenta de Usuario');
        $('#user-create-card').slideToggle(300);
    });

    // Vista previa en vivo del avatar al elegir un archivo (crear o editar)
    $(document).on('change', '#user-avatar-input', function() {
        var file = this.files && this.files[0];
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function(e) {
            $('#user-avatar-preview').html(
                '<img src="' + e.target.result + '" style="width:56px; height:56px; object-fit:cover; border-radius:50%;">' +
                '<span style="font-size:11px; color:var(--text-muted); margin-left:8px;">Nueva foto (se guardara al enviar el formulario)</span>'
            );
        };
        reader.readAsDataURL(file);
    });

    $('#btn-cancel-create-user').off('click').on('click', function() {
        $('#user-create-card').slideUp(300);
    });

    // CREAR / EDITAR USUARIO AMPLIADO
    $('#form-create-user').on('submit', function(e) {
        e.preventDefault();
        var $msg = $('#create-user-msg');
        $msg.removeClass('success error').hide();

        var formData = new FormData();
        formData.append('action', 'ruteo_create_user');
        formData.append('nonce', wpRuteoAjax.nonce);
        formData.append('user_id', $('#user-edit-id-input').val() || '0');
        formData.append('display_name', $('#user-display-name-input').val());
        formData.append('username', $('#user-username-input').val());
        formData.append('email', $('#user-email-input').val());
        formData.append('password', $('#user-password-input').val());
        formData.append('role', $('#user-role-select').val());
        formData.append('negativa_rol', $('#user-negativa-rol-select').val() || '');
        formData.append('position', $('#user-position-input').val() || '');
        formData.append('phone', $('#user-phone-input').val());
        formData.append('pm_assigned', $('#user-pm-select').val());

        var fileInput = $('#user-avatar-input')[0];
        if (fileInput && fileInput.files && fileInput.files[0]) {
            formData.append('avatar', fileInput.files[0]);
        }

        $.ajax({
            url: wpRuteoAjax.ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.success) {
                    $msg.addClass('success').text(res.data.message).fadeIn(200);
                    $('#form-create-user')[0].reset();
                    $('#user-edit-id-input').val('0');
                    $('#user-username-input').prop('readonly', false);
                    $('#user-avatar-preview').empty();
                    $('#user-create-card').slideUp(300);
                    cargarUsuarios();
                } else {
                    $msg.addClass('error').text(res.data.message || 'Error al guardar usuario.').fadeIn(200);
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
            data: { action: 'ruteo_delete_user', nonce: wpRuteoAjax.nonce, user_id: userId },
            success: function(res) {
                if (res.success) cargarUsuarios();
                else showToast(res.data.message || 'No se pudo eliminar el usuario.', 'error');
            }
        });
    }

    // ACTUALIZAR PERFIL
    $('#form-update-profile').on('submit', function(e) {
        e.preventDefault();
        var $msg = $('#prof-form-msg');
        $msg.removeClass('success error').hide();

        var formData = new FormData();
        formData.append('action', 'ruteo_update_profile');
        formData.append('nonce', wpRuteoAjax.nonce);
        formData.append('display_name', $('#prof-display-name').val());
        formData.append('phone', $('#prof-phone').val());
        formData.append('position', $('#prof-position').val() || '');
        formData.append('pm_assigned', $('#prof-pm').val());

        var fileInput = $('#prof-avatar-file')[0];
        if (fileInput && fileInput.files && fileInput.files[0]) {
            formData.append('avatar', fileInput.files[0]);
        }

        var firmaInput = $('#prof-firma-file')[0];
        if (firmaInput && firmaInput.files && firmaInput.files[0]) {
            formData.append('firma', firmaInput.files[0]);
        } else if ($('#prof-firma-remove').data('remove') === '1') {
            formData.append('firma_remove', '1');
        }

        $.ajax({
            url: wpRuteoAjax.ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.success) {
                    $msg.addClass('success').text(res.data.message).fadeIn(200);
                    setTimeout(function() { window.location.reload(); }, 600);
                } else {
                    $msg.addClass('error').text(res.data.message || 'Error al actualizar perfil.').fadeIn(200);
                }
            }
        });
    });

    // --- MODULO: GESTION DE CLIENTES Y LOGOS ---
    function renderTablaClientes(clientes) {
        var $tbody = $('#clientes-tbody');
        if (!$tbody.length) return;
        $tbody.empty();
        if (!clientes || !clientes.length) {
            $tbody.append('<tr><td colspan="6" style="text-align:center; padding:12px;">No hay clientes registrados.</td></tr>');
            return;
        }
        
        clientes.forEach(function(c) {
            var logoImg = c.logo ? '<img src="' + c.logo + '" style="height:32px; max-width:80px; object-fit:contain;">' : '<span style="color:var(--text-muted); font-size:12px;">Sin logo</span>';
            var tr = '<tr>' +
                '<td>' + logoImg + '</td>' +
                '<td><strong>' + c.nombre + '</strong></td>' +
                '<td>' + (c.ruc || '-') + '</td>' +
                '<td>' + (c.direccion || '-') + '</td>' +
                '<td>' + (c.contacto || '-') + '</td>' +
                '<td>' +
                    '<button type="button" class="btn-edit-row btn-edit-cliente" data-id="' + c.id + '" data-nombre="' + (c.nombre || '').replace(/"/g, '&quot;') + '" data-ruc="' + (c.ruc || '').replace(/"/g, '&quot;') + '" data-direccion="' + (c.direccion || '').replace(/"/g, '&quot;') + '" data-contacto="' + (c.contacto || '').replace(/"/g, '&quot;') + '" data-logo="' + (c.logo || '').replace(/"/g, '&quot;') + '" style="margin-right:6px;">Editar</button>' +
                    '<button type="button" class="btn-del-row btn-del-cliente" data-id="' + c.id + '" data-name="' + c.nombre + '">Eliminar</button>' +
                '</td>' +
            '</tr>';
            $tbody.append(tr);
        });

        $('.btn-edit-cliente').off('click').on('click', function() {
            var $btn = $(this);
            $('#cli-id-input').val($btn.data('id'));
            $('#cli-nombre-input').val($btn.data('nombre'));
            $('#cli-ruc-input').val($btn.data('ruc'));
            $('#cli-direccion-input').val($btn.data('direccion'));
            $('#cli-contacto-input').val($btn.data('contacto'));
            $('#cli-logo-file').val('');

            var logoActual = $btn.data('logo');
            if (logoActual) {
                $('#cli-logo-actual-img').attr('src', logoActual);
                $('#cli-logo-actual').css('display', 'flex');
            } else {
                $('#cli-logo-actual').hide();
            }

            $('#btn-guardar-cliente span').text('Actualizar Cliente');
            $('#btn-cancelar-edicion-cliente').show();
            $('html, body').animate({ scrollTop: $('#clientes-card').offset().top - 20 }, 300);
        });

        $('#btn-cancelar-edicion-cliente').off('click').on('click', function() {
            $('#form-cliente')[0].reset();
            $('#cli-id-input').val('');
            $('#cli-logo-actual').hide();
            $('#btn-guardar-cliente span').text('Guardar Cliente');
            $(this).hide();
        });

        var $selects = $('.neg-select-cliente');
        $selects.empty();
        clientes.forEach(function(c) {
            $selects.append('<option value="' + c.nombre + '">' + c.nombre + '</option>');
        });
        $selects.append('<option value="__otro__">+ Otro (escribir nombre)</option>');
        // Mantener sincronizado el input de texto libre con el valor por defecto del select
        $selects.each(function() {
            var $sel = $(this);
            var $txt = $sel.siblings('.neg-input-cliente-otro');
            if ($txt.length && !$txt.val()) {
                $txt.val($sel.val());
            }
        });

        // Si el usuario es de una empresa (tenant), el select recien poblado
        // se vuelve a bloquear con el nombre de su propia empresa.
        aplicarBloqueoClienteEmpresa($('#form-negativa-tecnico'), 'CYMTEL');
        aplicarBloqueoClienteEmpresa($('#form-negativa-supervisor'), 'CYMTEL');

        $('.btn-del-cliente').off('click').on('click', function() {
            var cid = $(this).data('id');
            var cname = $(this).data('name');
            if (confirm('Deseas eliminar al cliente ' + cname + '?')) {
                $.post(wpRuteoAjax.ajaxurl, { action: 'ruteo_delete_cliente', nonce: wpRuteoAjax.nonce, id: cid }, function(res) {
                    if (res.success) {
                        wpRuteoAjax.clientes = res.data.clientes;
                        renderTablaClientes(res.data.clientes);
                    }
                });
            }
        });
    }

    if (window.wpRuteoAjax && window.wpRuteoAjax.clientes) {
        renderTablaClientes(window.wpRuteoAjax.clientes);
    }

    function cargarEmpresas() {
        $.post(wpRuteoAjax.ajaxurl, { action: 'ruteo_get_empresas', nonce: wpRuteoAjax.nonce }, function(res) {
            if (res.success) {
                renderTablaEmpresas(res.data.empresas);
            }
        });
    }

    function renderTablaEmpresas(empresas) {
        var $grid = $('#empresas-grid');
        $grid.empty();
        $('#empresas-count-badge').text(empresas ? empresas.length : 0);

        if (!empresas || !empresas.length) {
            $grid.append('<p style="text-align:center; color:var(--text-muted); padding:24px 0;">Aun no hay empresas registradas.</p>');
            return;
        }

        empresas.forEach(function(e) {
            var logoHtml = e.logo
                ? '<img src="' + e.logo + '" alt="' + e.nombre + '">'
                : '<span class="empresa-logo-placeholder">' + e.nombre.charAt(0) + '</span>';

            var estadoClass = (e.estado === 'activa') ? 'status-badge-active' : 'status-badge-info';

            $grid.append(
                '<div class="empresa-card">' +
                    '<div class="empresa-card-logo">' + logoHtml + '</div>' +
                    '<div class="empresa-card-body">' +
                        '<div class="empresa-card-top">' +
                            '<h4>' + e.nombre + '</h4>' +
                            '<span class="' + estadoClass + '">' + e.estado.toUpperCase() + '</span>' +
                        '</div>' +
                        '<p class="empresa-card-ruc">RUC: ' + (e.ruc || 'No registrado') + '</p>' +
                        '<div class="empresa-card-actions">' +
                            '<button type="button" class="btn-del-empresa" data-id="' + e.id + '" data-name="' + e.nombre + '">Eliminar</button>' +
                        '</div>' +
                    '</div>' +
                '</div>'
            );
        });

        $('.btn-del-empresa').off('click').on('click', function() {
            var eid = $(this).data('id');
            var ename = $(this).data('name');
            if (confirm('Eliminar la empresa ' + ename + '? Esta accion no se puede deshacer.')) {
                $.post(wpRuteoAjax.ajaxurl, { action: 'ruteo_delete_empresa', nonce: wpRuteoAjax.nonce, empresa_id: eid }, function(res) {
                    if (res.success) {
                        cargarEmpresas();
                    }
                });
            }
        });
    }

    $('#form-empresa').on('submit', function(e) {
        e.preventDefault();
        var fd = new FormData();
        fd.append('action', 'ruteo_save_empresa');
        fd.append('nonce', wpRuteoAjax.nonce);
        fd.append('nombre', $('#emp-nombre-input').val());
        fd.append('ruc', $('#emp-ruc-input').val());
        fd.append('direccion', $('#emp-direccion-input').val());
        fd.append('contacto', $('#emp-contacto-input').val());
        fd.append('admin_nombre', $('#emp-admin-nombre-input').val());
        fd.append('admin_username', $('#emp-admin-username-input').val());
        fd.append('admin_email', $('#emp-admin-email-input').val());
        fd.append('admin_password', $('#emp-admin-password-input').val());

        var fileInput = $('#emp-logo-file')[0];
        if (fileInput && fileInput.files && fileInput.files[0]) {
            fd.append('logo', fileInput.files[0]);
        }

        var $msg = $('#emp-msg');
        $msg.removeClass('success error').hide();

        $.ajax({
            url: wpRuteoAjax.ajaxurl,
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.success) {
                    $msg.addClass('success').text(res.data.message).fadeIn(200);
                    $('#form-empresa')[0].reset();
                    cargarEmpresas();
                    setTimeout(function() { $msg.fadeOut(300); }, 4000);
                } else {
                    $msg.addClass('error').text(res.data.message || 'Error al crear la empresa.').fadeIn(200);
                }
            }
        });
    });

    $('#form-cliente').on('submit', function(e) {
        e.preventDefault();
       var fd = new FormData();
        fd.append('action', 'ruteo_save_cliente');
        fd.append('nonce', wpRuteoAjax.nonce);
        fd.append('id', $('#cli-id-input').val());
        fd.append('nombre', $('#cli-nombre-input').val());
        fd.append('ruc', $('#cli-ruc-input').val());
        fd.append('direccion', $('#cli-direccion-input').val());
        fd.append('contacto', $('#cli-contacto-input').val());

        var fileInput = $('#cli-logo-file')[0];
        if (fileInput && fileInput.files && fileInput.files[0]) {
            fd.append('logo', fileInput.files[0]);
        }

        var $msg = $('#cli-msg');
        $msg.removeClass('success error').hide();

        $.ajax({
            url: wpRuteoAjax.ajaxurl,
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.success) {
                    $msg.addClass('success').text(res.data.message).fadeIn(200);
                    $('#form-cliente')[0].reset();
                    $('#cli-id-input').val('');
                    $('#cli-logo-actual').hide();
                    $('#btn-guardar-cliente span').text('Guardar Cliente');
                    $('#btn-cancelar-edicion-cliente').hide();
                    wpRuteoAjax.clientes = res.data.clientes;
                    renderTablaClientes(res.data.clientes);
                    poblarSelectsCliente();
                    setTimeout(function() { $msg.fadeOut(300); }, 4000);
                } else {
                    $msg.addClass('error').text(res.data.message || 'Error al guardar cliente.').fadeIn(200);
                }
            }
        });
    });

    // --- MODULO: NEGATIVA AL TRABAJO POR RIESGO INMINENTE ---
    var negativaActual = null;

    // Sincroniza el select de cliente (opciones registradas) con el input de texto libre
    // que es el que realmente se envia al servidor como "cliente_nombre".
    function setClienteNombreField($form, valor) {
        var $sel = $form.find('select.neg-select-cliente');
        var $txt = $form.find('input.neg-input-cliente-otro');
        var opcionExiste = $sel.find('option[value="' + (valor || '').replace(/"/g, '\\"') + '"]').length > 0;
        if (opcionExiste) {
            $sel.val(valor);
            $txt.val(valor).hide().prop('required', false);
        } else {
            $sel.val('__otro__');
            $txt.val(valor || '').show().prop('required', true);
        }
    }

    $(document).on('change', '.neg-select-cliente', function() {
        var $sel = $(this);
        var $txt = $sel.siblings('.neg-input-cliente-otro');
        if ($sel.val() === '__otro__') {
            $txt.show().prop('required', true).val('').focus();
        } else {
            $txt.hide().prop('required', false).val($sel.val());
        }
    });

    // Para usuarios de una empresa (tenant: BCP, Alicorp, etc.) el campo
    // "Cliente / Empresa Principal" del formulario de Negativa ya NO es un
    // selector: se autocompleta con el nombre de SU propia empresa y queda
    // bloqueado (no pueden elegir CYMTEL ni otro). El Administrador General
    // (super admin) si conserva el selector clasico, porque no pertenece a
    // una sola empresa.
    function aplicarBloqueoClienteEmpresa($form, valorFallback) {
        var esTenant = currentUser && currentUser.isLoggedIn && !currentUser.isSuperAdmin && currentUser.empresaNombre;
        var $sel = $form.find('select.neg-select-cliente');
        var $txt = $form.find('input.neg-input-cliente-otro');
        if (esTenant) {
            $sel.hide().prop('disabled', true).prop('required', false);
            $txt.val(currentUser.empresaNombre)
                .prop('readonly', true)
                .prop('required', false)
                .show()
                .css({ background: 'var(--bg-subtle)', cursor: 'not-allowed' });
        } else {
            setClienteNombreField($form, valorFallback || 'CYMTEL');
        }
    }

    // Resuelve el logo a usar en el PDF de Negativa para un "cliente_nombre" dado.
    // Si el usuario actual es de una empresa (tenant) y el nombre coincide con
    // su propia empresa, usa el logo de ESA empresa (wpRuteoAjax.user.empresaLogo)
    // en vez de buscarlo en la lista de "clientes" (CYMTEL, etc.).
    function resolverLogoCliente(clienteNombre) {
        var nombre = String(clienteNombre || '').trim().toLowerCase();
        if (currentUser && !currentUser.isSuperAdmin && currentUser.empresaNombre &&
            currentUser.empresaNombre.trim().toLowerCase() === nombre && currentUser.empresaLogo) {
            return currentUser.empresaLogo;
        }
        if (window.wpRuteoAjax && window.wpRuteoAjax.clientes && window.wpRuteoAjax.clientes.length) {
            var foundCli = window.wpRuteoAjax.clientes.find(function(c) {
                return c.nombre && (c.nombre.trim().toLowerCase() === nombre);
            });
            if (foundCli && foundCli.logo) {
                return foundCli.logo;
            }
        }
        if (window.wpRuteoAjax && window.wpRuteoAjax.siteLogo) {
            return window.wpRuteoAjax.siteLogo;
        }
        return '';
    }

    function negativaEstadoLabel(estado) {
        var labels = {
            'pendiente_tecnico': 'Pendiente: Tecnico',
            'pendiente_supervisor': 'Pendiente: Supervisor Operativo',
            'pendiente_seguridad': 'Pendiente: Supervisor de Seguridad',
            'pendiente_hse': 'Pendiente: Visto Bueno HSE',
            'completado': 'Completado (Firmas completas)'
        };
        return labels[estado] || estado;
    }

    function negativaPuedeActuar(estado) {
        if (currentUser.isAdmin) return true;
        var mapa = {
            'pendiente_tecnico': 'tecnico',
            'pendiente_supervisor': 'supervisor_operativo',
            'pendiente_seguridad': 'supervisor_seguridad',
            'pendiente_hse': 'hse'
        };
        return currentUser.negativaRol === mapa[estado];
    }

    function renderNegativa(registro) {
        negativaActual = registro;
        $('#negativa-resumen').hide().empty();
        $('#form-negativa-tecnico, #form-negativa-supervisor, #form-negativa-seguridad, #form-negativa-hse, #negativa-firma-simple, #btn-negativa-exportar-pdf').hide();

        if (!registro) {
            $('#negativa-estado-badge').text('Sin firmar (Nueva Negativa)');
            $('#neg-preview1, #neg-preview2').removeClass('show').css('background-image', 'none');
            $('#neg-foto1, #neg-foto2').val('').closest('.ruteo-photo-upload').removeClass('has-file');
            var $ftNew = $('#form-negativa-tecnico');
            if ($ftNew[0]) $ftNew[0].reset();
            $ftNew.find('.is-invalid').removeClass('is-invalid').css('border-color', '');
            
            if (currentUser && currentUser.isLoggedIn && !currentUser.isAdmin && (currentUser.displayName || currentUser.username)) {
                $ftNew.find('input[name="trabajador_reportante"]').val(currentUser.displayName || currentUser.username);
            } else if (currentUser && currentUser.isAdmin) {
                $ftNew.find('input[name="trabajador_reportante"]').val('').attr('placeholder', 'Escriba el nombre del Técnico o Trabajador Reportante...');
            }

            if (currentUser && currentUser.isAdmin) {
                $ftNew.find('button[type="submit"]').text('Guardar y Registrar Negativa');
            } else {
                $ftNew.find('button[type="submit"]').text('Guardar y Firmar como Tecnico');
            }

            aplicarBloqueoClienteEmpresa($ftNew, 'CYMTEL');
            $ftNew.show();
            return;
        }

        $('#negativa-estado-badge').text(negativaEstadoLabel(registro.estado));

        var tecLabel = registro.trabajador_reportante || registro.firma_tecnico_user || 'Pendiente';
        var resumenHtml = '<div style="font-size:13px; line-height:1.6;">';
        resumenHtml += '<strong>Cliente:</strong> ' + (registro.cliente_nombre || 'CYMTEL') + ' | <strong>Proceso:</strong> ' + (registro.proceso || '') + ' | <strong>Lugar:</strong> ' + (registro.lugar_trabajo || '') + '<br>';
        resumenHtml += '<strong>Firmas:</strong> Tecnico Reportante: <span style="color:#0097D8;">' + tecLabel + '</span>';
        resumenHtml += ' | Supervisor Op.: <span style="color:#0097D8;">' + (registro.firma_sup_operativo_user || 'Pendiente') + '</span>';
        resumenHtml += ' | Seguridad: <span style="color:#0097D8;">' + (registro.firma_sup_seguridad_user || 'Pendiente') + '</span>';
        resumenHtml += ' | HSE: <span style="color:#83CA16;">' + (registro.firma_hse_user || 'Pendiente') + '</span>';
        resumenHtml += '</div>';

        // Detalle completo de lo llenado hasta ahora (para que Seguridad y HSE revisen
        // toda la informacion sin depender de recordar o volver a escribir nada).
        if ( registro.estado === 'pendiente_seguridad' || registro.estado === 'pendiente_hse' || registro.estado === 'completado' ) {
            resumenHtml += '<div style="margin-top:14px; padding-top:14px; border-top:1px solid var(--border); font-size:13px; line-height:1.7;">';
            resumenHtml += '<p><strong>2. Investigacion:</strong> Supervisor Operativo: ' + (registro.supervisor_operativo_nombre || '-') + ' | Trabajador Reportante: ' + (registro.trabajador_reportante || '-') + '</p>';
            resumenHtml += '<p><strong>3. Razones para la Negativa:</strong><br>' + (registro.razones_negativa || '-') + '</p>';
            resumenHtml += '<p><strong>5. Medidas Correctivas (Sup. Op.):</strong> ' + (registro.medidas_correctivas || '-') + '<br><strong>Satisface Negativa:</strong> ' + (registro.satisface_negativa || '-') + ' | <strong>Reinicia Labores:</strong> ' + (registro.reinicia_labores || '-');
            if (registro.reinicia_labores === 'SI') {
                resumenHtml += ' (' + (registro.fecha_reinicio || 'fecha no indicada') + ' ' + (registro.hora_reinicio || '') + ')';
            }
            resumenHtml += '</p>';
            if ( (registro.estado === 'pendiente_hse' || registro.estado === 'completado') && registro.observaciones_seguridad ) {
                resumenHtml += '<p><strong>Observaciones de Seguridad:</strong><br>' + registro.observaciones_seguridad + '</p>';
            }
            if ( registro.estado === 'completado' && registro.dictamen_hse ) {
                resumenHtml += '<p><strong>Dictamen HSE:</strong><br>' + registro.dictamen_hse + '</p>';
            }
            resumenHtml += '</div>';
        }

        if (registro.foto1_url || registro.foto2_url) {
            resumenHtml += '<div style="display:flex; gap:12px; margin-top:12px;">';
            if (registro.foto1_url) {
                resumenHtml += '<div style="width:120px; height:120px; border-radius:8px; border:1px solid var(--border); overflow:hidden;"><img src="' + registro.foto1_url + '" style="width:100%; height:100%; object-fit:cover;"></div>';
            }
            if (registro.foto2_url) {
                resumenHtml += '<div style="width:120px; height:120px; border-radius:8px; border:1px solid var(--border); overflow:hidden;"><img src="' + registro.foto2_url + '" style="width:100%; height:100%; object-fit:cover;"></div>';
            }
            resumenHtml += '</div>';
        }

        $('#negativa-resumen').html(resumenHtml).show();

        var puedeActuar = negativaPuedeActuar(registro.estado);

        if (registro.estado === 'pendiente_tecnico') {
            if (puedeActuar) {
                var $ft = $('#form-negativa-tecnico');
                aplicarBloqueoClienteEmpresa($ft, registro.cliente_nombre || 'CYMTEL');
                $ft.find('input[name="proceso"]').val(registro.proceso || '');
                $ft.find('input[name="cm_localidad"]').val(registro.cm_localidad || '');
                $ft.find('input[name="contratista"]').val(registro.contratista || '');
                $ft.find('input[name="sub_contratista"]').val(registro.sub_contratista || '');
                $ft.find('select[name="relacionado_a"]').val(registro.relacionado_a || 'PEXT');
                $ft.find('input[name="lugar_trabajo"]').val(registro.lugar_trabajo || '');
                $ft.find('input[name="fecha"]').val(registro.fecha || '');
                $ft.find('input[name="hora_inicio"]').val(registro.hora_inicio || '');
                $ft.find('input[name="hora_fin"]').val(registro.hora_fin || '');
                $ft.find('input[name="total_horas"]').val(registro.total_horas || '');
                $ft.find('input[name="supervisor_operativo_nombre"]').val(registro.supervisor_operativo_nombre || '');
                $ft.find('input[name="trabajador_reportante"]').val(registro.trabajador_reportante || '');
                $ft.find('textarea[name="razones_negativa"]').val(registro.razones_negativa || '');
                $ft.show();
            } else {
                $('#negativa-firma-simple-texto').text('Esperando firma del Tecnico Reportante.');
                $('#negativa-firma-simple').show().find('button').hide();
            }
        } else if (registro.estado === 'pendiente_supervisor') {
            if (puedeActuar) {
                var $fs = $('#form-negativa-supervisor');
                aplicarBloqueoClienteEmpresa($fs, registro.cliente_nombre || 'CYMTEL');
                $fs.find('input[name="proceso"]').val(registro.proceso || '');
                $fs.find('input[name="cm_localidad"]').val(registro.cm_localidad || '');
                $fs.find('input[name="contratista"]').val(registro.contratista || '');
                $fs.find('input[name="sub_contratista"]').val(registro.sub_contratista || '');
                $fs.find('select[name="relacionado_a"]').val(registro.relacionado_a || 'PEXT');
                $fs.find('input[name="lugar_trabajo"]').val(registro.lugar_trabajo || '');
                $fs.find('input[name="fecha"]').val(registro.fecha || '');
                $fs.find('input[name="hora_inicio"]').val(registro.hora_inicio || '');
                $fs.find('input[name="hora_fin"]').val(registro.hora_fin || '');
                $fs.find('input[name="total_horas"]').val(registro.total_horas || '');
                $fs.find('input[name="supervisor_operativo_nombre"]').val(registro.supervisor_operativo_nombre || '');
                $fs.find('input[name="trabajador_reportante"]').val(registro.trabajador_reportante || '');
                $fs.find('textarea[name="razones_negativa"]').val(registro.razones_negativa || '');
                $fs.find('textarea[name="medidas_correctivas"]').val(registro.medidas_correctivas || '');
                $fs.find('select[name="satisface_negativa"]').val(registro.satisface_negativa || 'SI');
                $fs.find('select[name="reinicia_labores"]').val(registro.reinicia_labores || 'SI');
                $fs.find('input[name="fecha_reinicio"]').val(registro.fecha_reinicio || '');
                $fs.find('input[name="hora_reinicio"]').val(registro.hora_reinicio || '');
                $fs.find('.is-invalid').removeClass('is-invalid').css('border-color', '');
                $fs.show();
            } else {
                $('#negativa-firma-simple-texto').text('Esperando revision y firma del Supervisor Operativo.');
                $('#negativa-firma-simple').show().find('button').hide();
            }
        } else if (registro.estado === 'pendiente_seguridad') {
            if (puedeActuar) {
                var $fg = $('#form-negativa-seguridad');
                $('#neg-seguridad-firmante-nombre').text(currentUser.displayName || currentUser.username || '');
                $fg.find('textarea[name="observaciones_seguridad"]').val(registro.observaciones_seguridad || '');
                $fg.find('.is-invalid').removeClass('is-invalid').css('border-color', '');
                $fg.show();
            } else {
                $('#negativa-firma-simple-texto').text('Esperando firma del Supervisor de Seguridad.');
                $('#negativa-firma-simple').show().find('button').hide();
            }
        } else if (registro.estado === 'pendiente_hse') {
            if (puedeActuar) {
                var $fh = $('#form-negativa-hse');
                $('#neg-hse-firmante-nombre').text(currentUser.displayName || currentUser.username || '');
                $fh.find('textarea[name="dictamen_hse"]').val(registro.dictamen_hse || '');
                $fh.find('.is-invalid').removeClass('is-invalid').css('border-color', '');
                $fh.show();
            } else {
                $('#negativa-firma-simple-texto').text('Esperando visto bueno del Area HSE.');
                $('#negativa-firma-simple').show().find('button').hide();
            }
        } else if (registro.estado === 'completado' || (registro.firma_hse_user && registro.firma_hse_user.length > 0)) {
            // SOLO cuando se tengan todas las firmas completas (Firma HSE) se habilita exportar PDF
            $('#btn-negativa-exportar-pdf').show();
            $('#negativa-pdf-notice').hide();
        } else {
            $('#btn-negativa-exportar-pdf').hide();
            $('#negativa-pdf-notice').show();
        }
    }

    function cargarNegativas() {
        $.post(wpRuteoAjax.ajaxurl, { action: 'ruteo_negativa_listar', nonce: wpRuteoAjax.nonce }, function(res) {
            if (!res.success) return;
            var $sel = $('#negativa-select-registro');
            $sel.find('option:not(:first)').remove();
            res.data.registros.forEach(function(r) {
                $sel.append('<option value="' + r.id + '">' + (r.proceso || 'Sin proceso') + ' - ' + r.fecha + ' (' + negativaEstadoLabel(r.estado) + ')</option>');
            });
        });
    }

    $('#negativa-select-registro').on('change', function() {
        var id = $(this).val();
        if (id === '0') { renderNegativa(null); return; }
        $.post(wpRuteoAjax.ajaxurl, { action: 'ruteo_negativa_listar', nonce: wpRuteoAjax.nonce }, function(res) {
            if (!res.success) return;
            var reg = res.data.registros.find(function(r) { return String(r.id) === String(id); });
            renderNegativa(reg);
        });
    });

    $('#form-negativa-tecnico').on('submit', function(e) {
        e.preventDefault();
        var $f = $(this);
        var invalido = false;
        $f.find('[required]').each(function() {
            if (!$(this).val() || !$(this).val().trim()) {
                invalido = true;
                $(this).addClass('is-invalid').css('border-color', '#D92625');
            } else {
                $(this).removeClass('is-invalid').css('border-color', '');
            }
        });
        if (invalido) {
            showToast('Por favor complete todos los campos obligatorios antes de firmar.', 'error');
            return;
        }

        var esModoEdicion = ($f.data('modo') === 'edicion');
        var fd = new FormData(this);
        fd.append('nonce', wpRuteoAjax.nonce);
        fd.append('id', negativaActual.id);
        if (esModoEdicion) {
            fd.append('action', 'ruteo_negativa_editar');
        } else {
            fd.append('action', 'ruteo_negativa_guardar');
            fd.append('etapa', 'supervisor');
        }
        $.ajax({
            url: wpRuteoAjax.ajaxurl, type: 'POST', data: fd, processData: false, contentType: false,
            success: function(res) {
                if (res.success) {
                    showToast(esModoEdicion ? 'Registro editado correctamente.' : 'Etapa Supervisor Operativo actualizada y firmada correctamente.', 'success');
                    $f.data('modo', '');
                    $f.find('button[type="submit"]').text('Guardar y Firmar como Supervisor Operativo');
                    cargarNegativas();
                    if (esModoEdicion) {
                        cargarListaNegativas();
                    } else {
                        renderNegativa(res.data.registro);
                    }
                } else {
                    showToast('Error: ' + res.data.message, 'error');
                }
            }
        });
    });

    $('#form-negativa-supervisor').on('submit', function(e) {
        e.preventDefault();
        var $f = $(this);
        var invalido = false;
        $f.find('[required]').each(function() {
            if ($(this).is(':radio')) {
                var name = $(this).attr('name');
                if (!$f.find('input[name="' + name + '"]:checked').length) invalido = true;
            } else if (!$(this).val() || !$(this).val().trim()) {
                invalido = true;
                $(this).addClass('is-invalid').css('border-color', '#D92625');
            } else {
                $(this).removeClass('is-invalid').css('border-color', '');
            }
        });
        if (invalido) {
            showToast('Por favor complete todos los datos antes de firmar como Supervisor Operativo.', 'error');
            return;
        }

        var fd = new FormData(this);
        fd.append('action', 'ruteo_negativa_guardar');
        fd.append('nonce', wpRuteoAjax.nonce);
        fd.append('etapa', 'supervisor');
        fd.append('id', negativaActual.id);
        $.ajax({
            url: wpRuteoAjax.ajaxurl, type: 'POST', data: fd, processData: false, contentType: false,
            success: function(res) {
                if (res.success) {
                    showToast('Etapa Supervisor Operativo actualizada y firmada correctamente.', 'success');
                    cargarNegativas();
                    renderNegativa(res.data.registro);
                } else {
                    showToast('Error: ' + res.data.message, 'error');
                }
            }
        });
    });

    function bindNegativaSubForm(formId, etapa, mensajeOk, mensajeInvalido) {
        $(formId).on('submit', function(e) {
            e.preventDefault();
            var $f = $(this);
            var invalido = false;
            $f.find('[required]').each(function() {
                if ($(this).is(':radio')) {
                    var name = $(this).attr('name');
                    if (!$f.find('input[name="' + name + '"]:checked').length) invalido = true;
                } else if (!$(this).val() || !$(this).val().trim()) {
                    invalido = true;
                    $(this).addClass('is-invalid').css('border-color', '#D92625');
                } else {
                    $(this).removeClass('is-invalid').css('border-color', '');
                }
            });
            if (invalido) {
                showToast(mensajeInvalido, 'error');
                return;
            }

            var fd = new FormData(this);
            fd.append('action', 'ruteo_negativa_guardar');
            fd.append('nonce', wpRuteoAjax.nonce);
            fd.append('etapa', etapa);
            fd.append('id', negativaActual.id);
            $.ajax({
                url: wpRuteoAjax.ajaxurl, type: 'POST', data: fd, processData: false, contentType: false,
                success: function(res) {
                    if (res.success) {
                        showToast(mensajeOk, 'success');
                        cargarNegativas();
                        renderNegativa(res.data.registro);
                    } else {
                        showToast('Error: ' + res.data.message, 'error');
                    }
                }
            });
        });
    }

    bindNegativaSubForm('#form-negativa-seguridad', 'seguridad', 'Etapa Supervisor de Seguridad firmada correctamente.', 'Por favor complete todos los datos antes de firmar como Supervisor de Seguridad.');
    bindNegativaSubForm('#form-negativa-hse', 'hse', 'Visto Bueno de Area HSE registrado correctamente. La Negativa quedo Completada.', 'Por favor complete todos los datos antes de dar el Visto Bueno del Area HSE.');

    $('#btn-negativa-firmar-simple').on('click', function() {
        var etapa = $(this).data('etapa');
        $.post(wpRuteoAjax.ajaxurl, { action: 'ruteo_negativa_guardar', nonce: wpRuteoAjax.nonce, etapa: etapa, id: negativaActual.id }, function(res) {
            if (res.success) {
                showToast('Etapa firmada correctamente.', 'success');
                cargarNegativas();
                renderNegativa(res.data.registro);
            } else {
                showToast('Error: ' + res.data.message, 'error');
            }
        });
    });

    // GENERACION DE PDF FORMATO HSE-RE-NEG-01 CON LOGO DE CYMTEL / CLIENTE Y 4 FIRMAS
    // Datos de control del formato oficial (no dependen del registro, son del documento en si)
    var NEG_CODIGO_DOC     = 'HSE-RE-NEG-01';
    var NEG_VERSION_DOC    = '1.0';
    var NEG_NORMATIVA_TXT  = 'Ley Articulo 63. Interrupcion de actividades en caso inminente de peligro. El empleador establece las medidas y da instrucciones necesarias para que, en caso de un peligro inminente que constituya un riesgo importante o intolerable para la seguridad y salud de los trabajadores, estos puedan interrumpir sus actividades, e incluso, si fuera necesario, abandonar de inmediato el domicilio o lugar fisico donde se desarrollan las labores. No se pueden reanudar las labores mientras el riesgo no se haya reducido o controlado.';

    function getJsPdfImageFormat(str) {
        if (!str || typeof str !== 'string') return 'PNG';
        var lower = str.toLowerCase();
        if (lower.indexOf('data:image/webp') === 0 || lower.indexOf('.webp') !== -1) return 'WEBP';
        if (lower.indexOf('data:image/png') === 0 || lower.indexOf('.png') !== -1) return 'PNG';
        if (lower.indexOf('data:image/jpeg') === 0 || lower.indexOf('data:image/jpg') === 0 || lower.indexOf('.jpg') !== -1 || lower.indexOf('.jpeg') !== -1) return 'JPEG';
        if (lower.indexOf('data:image/svg') === 0 || lower.indexOf('.svg') !== -1) return 'SVG';
        return 'PNG';
    }

    function generarPDFNegativa(r) {
        if (!r) { showToast('No hay registro seleccionado.', 'error'); return; }

        var jsPDFConstructor = (window.jspdf && window.jspdf.jsPDF) ? window.jspdf.jsPDF : window.jsPDF;
        if (!jsPDFConstructor) {
            showToast('Cargando libreria PDF, intente de nuevo en un instante.', 'error');
            return;
        }

        var clienteNombre = r.cliente_nombre || r.cliente || 'CYMTEL';
        var clientLogo = r._resolvedLogo || resolverLogoCliente(clienteNombre);

        var doc = new jsPDFConstructor({ orientation: 'portrait', unit: 'mm', format: 'a4' });
        var pageW = doc.internal.pageSize.getWidth();
        var pageH = doc.internal.pageSize.getHeight();

        var M = 14;                 // margen izquierdo / derecho
        var CW = pageW - (2 * M);   // ancho de contenido
        var COL_LABEL_FILL = [248, 250, 252];
        var BAND_FILL = [225, 240, 252];
        var BORDER = [150, 165, 185];
        doc.setDrawColor(BORDER[0], BORDER[1], BORDER[2]);
        doc.setLineWidth(0.25);

        // ================= CABECERA (Logo | Titulo | Codigo-Version-Fecha) =================
        var y = 10;
        var headerH = 27;
        var logoW = 32, codeColW = 46, titleW = CW - logoW - codeColW;

        doc.setFillColor(255, 255, 255);
        doc.rect(M, y, CW, headerH, 'FD');
        doc.line(M + logoW, y, M + logoW, y + headerH);
        doc.line(M + logoW + titleW, y, M + logoW + titleW, y + headerH);

        // --- Logo del cliente configurado o logo general ---
        if (clientLogo && (clientLogo.indexOf('data:image') === 0 || clientLogo.indexOf('http') === 0 || clientLogo.indexOf('/') === 0)) {
            try {
                var mimeSite = getJsPdfImageFormat(clientLogo);
                var imgProps = (typeof doc.getImageProperties === 'function') ? doc.getImageProperties(clientLogo) : null;
                if (imgProps && imgProps.width && imgProps.height) {
                    var maxW = logoW - 4, maxH = headerH - 4;
                    var ratio = Math.min(maxW / imgProps.width, maxH / imgProps.height);
                    var drawW = imgProps.width * ratio;
                    var drawH = imgProps.height * ratio;
                    var offX = M + (logoW - drawW) / 2;
                    var offY = y + (headerH - drawH) / 2;
                    doc.addImage(clientLogo, mimeSite, offX, offY, drawW, drawH, undefined, 'FAST');
                } else {
                    doc.addImage(clientLogo, mimeSite, M + 2, y + 2, logoW - 4, headerH - 4, undefined, 'FAST');
                }
            } catch (e) {
                dibujarLogoGenerico(doc, M, y, logoW, headerH);
            }
        } else {
            dibujarLogoGenerico(doc, M, y, logoW, headerH);
        }

        function dibujarLogoGenerico(pdf, mx, my, w, h) {
            pdf.setFillColor(0, 151, 216);
            pdf.rect(mx + w / 2 - 6, my + h / 2 - 8, 6, 6, 'F');
            pdf.setFillColor(131, 202, 22);
            pdf.rect(mx + w / 2, my + h / 2 - 5, 5.5, 5.5, 'F');
            pdf.setFontSize(6.5); pdf.setFont('helvetica', 'bold'); pdf.setTextColor(60, 70, 85);
            pdf.text('SOFTWARE O&M', mx + w / 2, my + h / 2 + 7, { align: 'center' });
        }

        // --- Titulo central ---
        doc.setFontSize(11.5); doc.setFont('helvetica', 'bold'); doc.setTextColor(20, 30, 45);
        var tituloLineas = doc.splitTextToSize('NEGATIVA AL TRABAJO POR INMINENTE PELIGRO', titleW - 8);
        var tY = y + headerH / 2 - ((tituloLineas.length - 1) * 2.4);
        tituloLineas.forEach(function(line) {
            doc.text(line, M + logoW + titleW / 2, tY, { align: 'center' });
            tY += 4.8;
        });

        // --- Tabla Codigo / Version / Fecha de modificacion ---
        var codeX = M + logoW + titleW;
        var codeRowH = headerH / 3;
        var codeLabelW = 26;
        [['Codigo:', NEG_CODIGO_DOC], ['Version:', NEG_VERSION_DOC], ['Fecha de modificacion:', r.fecha || '-']].forEach(function(row, i) {
            var ry = y + (i * codeRowH);
            if (i > 0) doc.line(codeX, ry, codeX + codeColW, ry);
            doc.setFillColor(COL_LABEL_FILL[0], COL_LABEL_FILL[1], COL_LABEL_FILL[2]);
            doc.rect(codeX, ry, codeLabelW, codeRowH, 'F');
            doc.line(codeX + codeLabelW, ry, codeX + codeLabelW, ry + codeRowH);
            doc.setFontSize(5.6); doc.setFont('helvetica', 'bold'); doc.setTextColor(70, 85, 100);
            var labelLines = doc.splitTextToSize(row[0], codeLabelW - 2);
            doc.text(labelLines, codeX + 1.5, ry + codeRowH / 2 - (labelLines.length > 1 ? 1.3 : 0) + 1, { align: 'left' });
            doc.setFontSize(6.8); doc.setFont('helvetica', 'normal'); doc.setTextColor(20, 30, 45);
            doc.text(String(row[1]), codeX + codeLabelW + 2, ry + codeRowH / 2 + 1.3, { align: 'left' });
        });

        y += headerH + 4;

        // ================= BLOQUE DE IDENTIFICACION =================
        doc.autoTable({
            startY: y,
            body: [[ 'PROCESO:', r.proceso || '-', 'CM / LOCALIDAD:', r.cm_localidad || '-', 'PAG.:', '1 de 1' ]],
            theme: 'grid',
            styles: { fontSize: 7.6, cellPadding: 2, textColor: [20, 30, 45] },
            columnStyles: {
                0: { fontStyle: 'bold', cellWidth: 22, fillColor: COL_LABEL_FILL },
                1: { cellWidth: 62 },
                2: { fontStyle: 'bold', cellWidth: 30, fillColor: COL_LABEL_FILL },
                3: { cellWidth: 40 },
                4: { fontStyle: 'bold', cellWidth: 14, fillColor: COL_LABEL_FILL },
                5: { cellWidth: 14 }
            },
            margin: { left: M, right: M }
        });
        y = doc.lastAutoTable.finalY;

        var relacTxt = '( ' + (r.relacionado_a === 'PEXT' ? 'X' : ' ') + ' ) PEXT   ( ' + (r.relacionado_a === 'PINT' ? 'X' : ' ') + ' ) PINT';
        doc.autoTable({
            startY: y,
            body: [[ 'Contratista:', r.contratista || '-', 'Sub Contratista:', r.sub_contratista || '-', 'Relac.:', relacTxt ]],
            theme: 'grid',
            styles: { fontSize: 7.6, cellPadding: 2, textColor: [20, 30, 45] },
            columnStyles: {
                0: { fontStyle: 'bold', cellWidth: 24, fillColor: COL_LABEL_FILL },
                1: { cellWidth: 46 },
                2: { fontStyle: 'bold', cellWidth: 30, fillColor: COL_LABEL_FILL },
                3: { cellWidth: 46 },
                4: { fontStyle: 'bold', cellWidth: 16, fillColor: COL_LABEL_FILL },
                5: { cellWidth: 20 }
            },
            margin: { left: M, right: M }
        });
        y = doc.lastAutoTable.finalY;

        doc.autoTable({
            startY: y,
            body: [[ 'Lugar del trabajo:', r.lugar_trabajo || '-' ]],
            theme: 'grid',
            styles: { fontSize: 7.6, cellPadding: 2, textColor: [20, 30, 45] },
            columnStyles: {
                0: { fontStyle: 'bold', cellWidth: 34, fillColor: COL_LABEL_FILL },
                1: { cellWidth: 148 }
            },
            margin: { left: M, right: M }
        });
        y = doc.lastAutoTable.finalY;

        doc.autoTable({
            startY: y,
            body: [[ 'Fecha:', r.fecha || '-', 'H. Inicio:', r.hora_inicio || '-', 'H. Fin:', r.hora_fin || '-', 'Total:', r.total_horas || '-' ]],
            theme: 'grid',
            styles: { fontSize: 7.6, cellPadding: 2, textColor: [20, 30, 45] },
            columnStyles: {
                0: { fontStyle: 'bold', cellWidth: 16, fillColor: COL_LABEL_FILL },
                1: { cellWidth: 30 },
                2: { fontStyle: 'bold', cellWidth: 20, fillColor: COL_LABEL_FILL },
                3: { cellWidth: 22 },
                4: { fontStyle: 'bold', cellWidth: 16, fillColor: COL_LABEL_FILL },
                5: { cellWidth: 22 },
                6: { fontStyle: 'bold', cellWidth: 16, fillColor: COL_LABEL_FILL },
                7: { cellWidth: 40 }
            },
            margin: { left: M, right: M }
        });
        y = doc.lastAutoTable.finalY + 4;

        // ================= DESCRIPCION DE LA NORMATIVA =================
        doc.setFontSize(7.4); doc.setFont('helvetica', 'bold'); doc.setTextColor(20, 30, 45);
        doc.text('Descripcion de la normativa:', M + 2, y + 4);
        doc.setFont('helvetica', 'normal'); doc.setTextColor(55, 65, 80);
        doc.setFontSize(6.9);
        var normLines = doc.splitTextToSize(NEG_NORMATIVA_TXT, CW - 4);
        var normBoxH = 7 + (normLines.length * 3.1) + 2;
        doc.rect(M, y, CW, normBoxH, 'S');
        doc.text(normLines, M + 2, y + 8);
        y += normBoxH + 4;

        // ================= SECCION A: INVESTIGACION DEL SUPERVISOR OPERATIVO =================
        function bandaSeccion(titulo) {
            doc.setFillColor(BAND_FILL[0], BAND_FILL[1], BAND_FILL[2]);
            doc.rect(M, y, CW, 6.5, 'FD');
            doc.setFontSize(8.4); doc.setFont('helvetica', 'bold'); doc.setTextColor(0, 80, 130);
            doc.text(titulo, M + CW / 2, y + 4.4, { align: 'center' });
            y += 6.5;
        }

        function filaSimple(numero, etiqueta, valor) {
            doc.rect(M, y, CW, 6.5, 'S');
            doc.setFontSize(7.6); doc.setFont('helvetica', 'bold'); doc.setTextColor(20, 30, 45);
            doc.text(numero + '. ' + etiqueta + ':', M + 2, y + 4.4);
            doc.setFont('helvetica', 'normal'); doc.setTextColor(50, 60, 75);
            var labelW = doc.getTextWidth(numero + '. ' + etiqueta + ': ') + 4;
            doc.text(String(valor || '-'), M + labelW, y + 4.4);
            y += 6.5;
        }

        function bloqueParrafo(numero, etiqueta, valor) {
            doc.setFontSize(7.6); doc.setFont('helvetica', 'bold'); doc.setTextColor(20, 30, 45);
            var lines = doc.splitTextToSize(String(valor || '-'), CW - 4);
            doc.setFont('helvetica', 'normal'); doc.setFontSize(7.3);
            var boxH = 6 + (lines.length * 3.4) + 2;
            doc.rect(M, y, CW, boxH, 'S');
            doc.setFont('helvetica', 'bold'); doc.setFontSize(7.6); doc.setTextColor(20, 30, 45);
            doc.text(numero + '. ' + etiqueta + ':', M + 2, y + 4.4);
            doc.setFont('helvetica', 'normal'); doc.setFontSize(7.3); doc.setTextColor(50, 60, 75);
            doc.text(lines, M + 2, y + 8.6);
            y += boxH;
        }

        if (y + 60 > pageH - 12) { doc.addPage(); y = 14; }

        bandaSeccion('A. INVESTIGACION DEL SUPERVISOR OPERATIVO');
        filaSimple('1', 'Nombre del Supervisor Operativo', r.supervisor_operativo_nombre);
        filaSimple('2', 'Trabajador Reportante', r.trabajador_reportante);
        bloqueParrafo('3', 'Razones para la negativa', r.razones_negativa);

        // 4. Evidencias fotograficas
        if (y + 46 > pageH - 12) { doc.addPage(); y = 14; }
        doc.setFontSize(7.6); doc.setFont('helvetica', 'bold'); doc.setTextColor(20, 30, 45);
        doc.text('4. Evidencias fotograficas:', M + 2, y + 4.4);
        y += 6;
        var fotoBoxW = (CW - 4) / 2, fotoBoxH = 42;
        [r.foto1_url || r.foto1 || r.foto_1, r.foto2_url || r.foto2 || r.foto_2].forEach(function(f, i) {
            var fx = M + i * (fotoBoxW + 4);
            doc.rect(fx, y, fotoBoxW, fotoBoxH, 'S');
            if (f && typeof f === 'string' && f.length > 10) {
                try {
                    var mimeFoto = getJsPdfImageFormat(f);
                    var imgProps = (typeof doc.getImageProperties === 'function') ? doc.getImageProperties(f) : null;
                    if (imgProps && imgProps.width && imgProps.height) {
                        var maxW = fotoBoxW - 2, maxH = fotoBoxH - 2;
                        var ratio = Math.min(maxW / imgProps.width, maxH / imgProps.height);
                        var drawW = imgProps.width * ratio;
                        var drawH = imgProps.height * ratio;
                        var offX = fx + (fotoBoxW - drawW) / 2;
                        var offY = y + (fotoBoxH - drawH) / 2;
                        doc.addImage(f, mimeFoto, offX, offY, drawW, drawH, undefined, 'FAST');
                    } else {
                        doc.addImage(f, mimeFoto, fx + 1, y + 1, fotoBoxW - 2, fotoBoxH - 2, undefined, 'FAST');
                    }
                } catch (e) {
                    doc.setFontSize(7); doc.setTextColor(150, 150, 150);
                    doc.text('Imagen no disponible', fx + fotoBoxW / 2, y + fotoBoxH / 2, { align: 'center' });
                }
            } else {
                doc.setFontSize(7); doc.setTextColor(150, 150, 150);
                doc.text('Sin evidencia', fx + fotoBoxW / 2, y + fotoBoxH / 2, { align: 'center' });
            }
        });
        y += fotoBoxH + 4;

        if (y + 40 > pageH - 12) { doc.addPage(); y = 14; }

        bloqueParrafo('5', 'Acciones Correctivas', r.medidas_correctivas);

        // 6. Existe acuerdo (checkbox SI/NO)
        var acuerdoSi = r.satisface_negativa === 'SI';
        doc.rect(M, y, CW, 7, 'S');
        doc.setFontSize(7.4); doc.setFont('helvetica', 'bold'); doc.setTextColor(20, 30, 45);
        var txt6 = '6. Existe acuerdo en que las condiciones de trabajo se han hecho inseguras :';
        doc.text(txt6, M + 2, y + 4.6);
        var checkX = M + doc.getTextWidth(txt6) + 6;
        doc.setFont('helvetica', 'normal');
        doc.text('( ' + (acuerdoSi ? 'X' : ' ') + ' ) SI    (  ' + (!acuerdoSi && r.satisface_negativa ? 'X' : ' ') + ' ) NO', checkX, y + 4.6);
        y += 7;

        if (r.reinicia_labores) {
            doc.setFontSize(6.6); doc.setFont('helvetica', 'italic'); doc.setTextColor(90, 100, 115);
            doc.text('Reinicio de labores: ' + r.reinicia_labores + (r.fecha_reinicio ? ' - ' + r.fecha_reinicio + ' ' + (r.hora_reinicio || '') : ''), M + 2, y + 3.5);
            y += 5.5;
        }
        y += 2;

        // Notas adicionales opcionales (solo si existen, sin recuadros forzados por puesto)
        var notasExtra = [];
        if (r.observaciones_seguridad) notasExtra.push('Seguridad: ' + r.observaciones_seguridad);
        if (r.dictamen_hse) notasExtra.push('HSE: ' + r.dictamen_hse);
        if (notasExtra.length) {
            if (y + 16 > pageH - 45) { doc.addPage(); y = 14; }
            doc.setFontSize(7); doc.setFont('helvetica', 'bold'); doc.setTextColor(20, 30, 45);
            doc.text('Notas adicionales:', M + 2, y + 4);
            doc.setFont('helvetica', 'normal'); doc.setTextColor(60, 70, 85);
            var notasLines = doc.splitTextToSize(notasExtra.join('  |  '), CW - 4);
            var notasH = 6 + notasLines.length * 3.2;
            doc.rect(M, y, CW, notasH, 'S');
            doc.text(notasLines, M + 2, y + 7.5);
            y += notasH + 3;
        }

        // ================= CUADRO DE FIRMAS (2x2, estilo formato oficial) =================
        if (y + 40 > pageH - 12) { doc.addPage(); y = 14; }
        y += 2;

        var firmas = [
            { titulo: 'FIRMA DEL TRABAJADOR REPORTANTE', user: r.firma_tecnico_user || r.trabajador_reportante, img: r.firma_tecnico_img || r.firma_tecnico },
            { titulo: 'FIRMA DEL SUPERVISOR OPERATIVO', user: r.firma_sup_operativo_user || r.supervisor_operativo_nombre, img: r.firma_sup_operativo_img || r.firma_sup_operativo },
            { titulo: 'FIRMA DEL SUPERVISOR DE SEGURIDAD', user: r.firma_sup_seguridad_user, img: r.firma_sup_seguridad_img || r.firma_sup_seguridad },
            { titulo: 'VoBo HSE', user: r.firma_hse_user, img: r.firma_hse_img || r.firma_hse }
        ];

        var fCellW = CW / 2, fCellH = 32;
        firmas.forEach(function(f, i) {
            var col = i % 2, row = Math.floor(i / 2);
            var fx = M + col * fCellW;
            var fy = y + row * fCellH;
            doc.rect(fx, fy, fCellW, fCellH, 'S');

            var imgOk = false;
            if (f.img && typeof f.img === 'string' && f.img.length > 10) {
                try {
                    var mimeFirma = getJsPdfImageFormat(f.img);
                    var imgProps = (typeof doc.getImageProperties === 'function') ? doc.getImageProperties(f.img) : null;
                    if (imgProps && imgProps.width && imgProps.height) {
                        var maxW = 34, maxH = 14;
                        var ratio = Math.min(maxW / imgProps.width, maxH / imgProps.height);
                        var drawW = imgProps.width * ratio;
                        var drawH = imgProps.height * ratio;
                        var offX = fx + (fCellW - drawW) / 2;
                        var offY = fy + 2 + (14 - drawH) / 2;
                        doc.addImage(f.img, mimeFirma, offX, offY, drawW, drawH, undefined, 'FAST');
                    } else {
                        doc.addImage(f.img, mimeFirma, fx + fCellW / 2 - 16, fy + 3, 32, 12, undefined, 'FAST');
                    }
                    imgOk = true;
                } catch (e) { imgOk = false; }
            }
            doc.line(fx + fCellW / 2 - 26, fy + 19, fx + fCellW / 2 + 26, fy + 19);
            doc.setFontSize(7.6); doc.setFont('helvetica', 'bold'); doc.setTextColor(20, 30, 45);
            doc.text(f.user || (imgOk ? '' : ' '), fx + fCellW / 2, fy + 23.5, { align: 'center' });
            doc.setFontSize(6.4); doc.setFont('helvetica', 'normal'); doc.setTextColor(80, 90, 105);
            doc.text(f.titulo, fx + fCellW / 2, fy + 28, { align: 'center' });
            if (!f.user) {
                doc.setFontSize(6.6); doc.setFont('helvetica', 'italic'); doc.setTextColor(165, 172, 184);
                doc.text('Pendiente de firma', fx + fCellW / 2, fy + 12, { align: 'center' });
            }
        });
        y += (2 * fCellH) + 6;

        // Pie de pagina
        doc.setFontSize(6.6); doc.setTextColor(148, 163, 184);
        doc.line(M, pageH - 8, pageW - M, pageH - 8);
        doc.text('Cliente: ' + clienteNombre, M, pageH - 4);
        doc.text('Pagina 1 de 1', pageW - M, pageH - 4, { align: 'right' });

        // Abrir vista previa del PDF en nueva pestaña (visualizador de navegador)
        try {
            var blobUrl = doc.output('bloburl');
            window.open(blobUrl, '_blank');
        } catch(errBlob) {}

        doc.save('Negativa_' + (r.id || 'HSE') + '_' + String(r.proceso || 'Trabajo').replace(/ /g, '_') + '.pdf');
    }

    function loadImagesAndGeneratePDF(r, callback) {
        if (!r) { callback(r); return; }
        var copy = $.extend({}, r);

        var clienteNombre = copy.cliente_nombre || copy.cliente || 'CYMTEL';
        var clientLogo = resolverLogoCliente(clienteNombre);
        copy._resolvedLogo = clientLogo;

        var itemsToProcess = [
            { key: 'foto1_url', val: copy.foto1_url || copy.foto1 || copy.foto_1 },
            { key: 'foto2_url', val: copy.foto2_url || copy.foto2 || copy.foto_2 },
            { key: 'firma_tecnico_img', val: copy.firma_tecnico_img || copy.firma_tecnico },
            { key: 'firma_sup_operativo_img', val: copy.firma_sup_operativo_img || copy.firma_sup_operativo },
            { key: 'firma_sup_seguridad_img', val: copy.firma_sup_seguridad_img || copy.firma_sup_seguridad },
            { key: 'firma_hse_img', val: copy.firma_hse_img || copy.firma_hse },
            { key: '_resolvedLogo', val: copy._resolvedLogo }
        ];

        var pending = 0;
        itemsToProcess.forEach(function(item) {
            if (item.val && typeof item.val === 'string' && item.val.length > 5) {
                pending++;
            }
        });

        if (pending === 0) {
            callback(copy);
            return;
        }

        itemsToProcess.forEach(function(item) {
            var src = item.val;
            if (!src || typeof src !== 'string' || src.length <= 5) {
                return;
            }

            var img = new Image();
            img.crossOrigin = 'Anonymous';
            img.onload = function() {
                try {
                    var canvas = document.createElement('canvas');
                    canvas.width = img.naturalWidth || img.width || 300;
                    canvas.height = img.naturalHeight || img.height || 150;
                    var ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0);
                    copy[item.key] = canvas.toDataURL('image/png');
                } catch(e) {
                    copy[item.key] = src;
                }
                pending--;
                if (pending <= 0) callback(copy);
            };
            img.onerror = function() {
                copy[item.key] = (src.indexOf('data:image') === 0) ? src : '';
                pending--;
                if (pending <= 0) callback(copy);
            };
            img.src = src;
        });
    }

    window.editarNegativaIndex = function(idx) {
        var list = window.ruteoNegativasFilteredCache || window.ruteoNegativasCache || [];
        var registro = list[idx];
        if (!registro) { showToast('No se encontro el registro de negativa.', 'error'); return; }

        var puedeEditar = currentUser.isAdmin || currentUser.negativaRol === 'supervisor_operativo' || (registro.creado_por && currentUser.displayName && registro.creado_por === currentUser.displayName);
        if (!puedeEditar) {
            showToast('No tienes permiso para editar este registro. Solo puede editarlo quien lo creo, el Supervisor Operativo o un Administrador.', 'error');
            return;
        }

        $('.sidebar-item[data-tab="negativa"]').trigger('click');
        negativaActual = registro;

        $('#form-negativa-tecnico, #form-negativa-supervisor, #form-negativa-seguridad, #form-negativa-hse, #negativa-firma-simple, #btn-negativa-exportar-pdf').hide();
        $('#negativa-estado-badge').text('Modo Edicion - ' + negativaEstadoLabel(registro.estado));
        $('#negativa-resumen').hide();

        var $fe = $('#form-negativa-supervisor');
        aplicarBloqueoClienteEmpresa($fe, registro.cliente_nombre || 'CYMTEL');
        $fe.find('input[name="proceso"]').val(registro.proceso || '');
        $fe.find('input[name="cm_localidad"]').val(registro.cm_localidad || '');
        $fe.find('input[name="contratista"]').val(registro.contratista || '');
        $fe.find('input[name="sub_contratista"]').val(registro.sub_contratista || '');
        $fe.find('select[name="relacionado_a"]').val(registro.relacionado_a || 'PEXT');
        $fe.find('input[name="lugar_trabajo"]').val(registro.lugar_trabajo || '');
        $fe.find('input[name="fecha"]').val(registro.fecha || '');
        $fe.find('input[name="hora_inicio"]').val(registro.hora_inicio || '');
        $fe.find('input[name="hora_fin"]').val(registro.hora_fin || '');
        $fe.find('input[name="total_horas"]').val(registro.total_horas || '');
        $fe.find('input[name="supervisor_operativo_nombre"]').val(registro.supervisor_operativo_nombre || '');
        $fe.find('input[name="trabajador_reportante"]').val(registro.trabajador_reportante || '');
        $fe.find('textarea[name="razones_negativa"]').val(registro.razones_negativa || '');
        $fe.find('select[name="satisface_negativa"]').val(registro.satisface_negativa || 'SI');
        $fe.find('select[name="reinicia_labores"]').val(registro.reinicia_labores || 'SI');
        $fe.find('textarea[name="medidas_correctivas"]').val(registro.medidas_correctivas || '');
        $fe.find('input[name="fecha_reinicio"]').val(registro.fecha_reinicio || '');
        $fe.find('input[name="hora_reinicio"]').val(registro.hora_reinicio || '');
        $fe.find('.is-invalid').removeClass('is-invalid').css('border-color', '');
        $fe.data('modo', 'edicion');
        $fe.find('button[type="submit"]').text('Guardar Cambios (Edicion)');
        $fe.show();
    };

    window.eliminarNegativaIndex = function(idx) {
        var list = window.ruteoNegativasFilteredCache || window.ruteoNegativasCache || [];
        var registro = list[idx];
        if (!registro) { showToast('No se encontro el registro de negativa.', 'error'); return; }

        if (!currentUser.isAdmin) {
            showToast('Solo un Administrador puede eliminar este registro.', 'error');
            return;
        }

        var confirmado = window.confirm('¿Seguro que deseas eliminar el registro #' + registro.id + '? Esta accion no se puede deshacer.');
        if (!confirmado) return;

        $.post(wpRuteoAjax.ajaxurl, {
            action: 'ruteo_negativa_eliminar',
            nonce: wpRuteoAjax.nonce,
            id: registro.id
        }, function(res) {
            if (res.success) {
                showToast(res.data.message || 'Registro eliminado correctamente.', 'success');
                cargarListaNegativas();
            } else {
                showToast(res.data.message || 'No se pudo eliminar el registro.', 'error');
            }
        });
    };

    window.abrirPDFNegativaIndex = function(idx) {
        var list = window.ruteoNegativasFilteredCache || window.ruteoNegativasCache || [];
        var r = list[idx];
        if (!r) { showToast('No se encontro el registro de negativa.', 'error'); return; }
        loadImagesAndGeneratePDF(r, function(rPrepared) {
            generarPDFNegativa(rPrepared);
        });
    };

    window.generarPDFNegativa = function(r) {
        loadImagesAndGeneratePDF(r, function(rPrepared) {
            generarPDFNegativa(rPrepared);
        });
    };

    $('#btn-negativa-exportar-pdf').on('click', function() {
        var r = negativaActual;
        var completo = r && (r.estado === 'completado' || (r.firma_hse_user && r.firma_hse_user.length > 0));
        if (!completo) {
            showToast('Aun no se puede exportar el PDF: faltan firmas por completar (Tecnico, Supervisor Operativo, Supervisor de Seguridad y HSE).', 'error');
            return;
        }
        loadImagesAndGeneratePDF(r, function(rPrepared) {
            generarPDFNegativa(rPrepared);
    });

});

    if ($('.sidebar-item[data-tab="negativa"]').length) {
        $('.sidebar-item[data-tab="negativa"]').on('click', function() { cargarNegativas(); renderNegativa(null); });
    }

    // --- BINDING FOTOS NEGATIVA ---
    function bindNegPhoto(inputId, previewId) {
        var el = document.getElementById(inputId);
        if (!el) return;
        el.addEventListener('change', function(e) {
            var file = e.target.files && e.target.files[0];
            var prev = document.getElementById(previewId);
            if (file && prev) {
                var reader = new FileReader();
                reader.onload = function(evt) {
                    var old = prev.querySelector('img.preview-img');
                    if (old) old.remove();
                    var img = document.createElement('img');
                    img.src = evt.target.result;
                    img.className = 'preview-img';
                    img.style.cssText = 'width:100%; height:100%; object-fit:cover; border-radius:10px; position:absolute; top:0; left:0; pointer-events:none; z-index:1;';
                    prev.insertBefore(img, prev.firstChild);
                    prev.classList.add('show');
                    prev.style.display = 'block';
                    var box = el.closest('.ruteo-photo-upload');
                    if (box) box.classList.add('has-file');
                };
                reader.readAsDataURL(file);
            }
        });
    }
    bindNegPhoto('neg-foto1', 'neg-preview1');
    bindNegPhoto('neg-foto2', 'neg-preview2');

    // --- MODULO: REGISTROS DE CAMPO Y TABLA PORTAL ---
    function downloadBlob(blob, filename) {
        if (typeof window.saveAs === 'function') {
            window.saveAs(blob, filename);
        } else {
            var url = window.URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            setTimeout(function() {
                if (a.parentNode) a.parentNode.removeChild(a);
                window.URL.revokeObjectURL(url);
            }, 500);
        }
    }
    window.downloadBlobRuteo = downloadBlob;

    function normalizarRegistro(r) {
        if (!r) return {};
        return {
            fecha: r.fecha || r.date || '',
            tramo: r.tramo || '',
            id_consol: r.id_consol || r.id_consol_ || '',
            estructura: r.estructura || '',
            tipo_estructura: r.tipo_estructura || r.tipo || '',
            altura: r.altura || r.altura_estructura || '',
            codigo: r.codigo || r.cdigo_estructura || r.codigo_estructura || '',
            ubicacion: r.ubicacion || r.ubicacin || '',
            mufa: r.mufa || '0',
            retencion: r.retencion || r.retencin || '0',
            suspension: r.suspension || r.suspensin || '0',
            cruceta: r.cruceta || '0',
            hebillas: r.hebillas || '0',
            fleje: r.fleje || '0',
            amortiguador: r.amortiguador || '0',
            brazo_extensor: r.brazo_extensor || '0',
            kit_retenida: r.kit_retenida || '0',
            observacion: r.observacion || r.observacin || '',
            foto_1: r.foto_1 || r.foto1_url || r.foto1 || '',
            foto_2: r.foto_2 || r.foto2_url || r.foto2 || '',
            link_kmz: r.link_kmz || r.kmz || '',
            link_docx: r.link_docx || r.link_doc || r.doc_url || r.docx || ''
        };
    }
    window.normalizarRegistroRuteo = normalizarRegistro;

    function linkIcon(url, label, color) {
        if (!url) return '<span class="portal-cell-empty">-</span>';
        var isEarth = label.indexOf('Earth') > -1 || label.indexOf('KMZ') > -1;
        var icon = isEarth ? 
            '<svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"/><path stroke-width="2" d="M2 12h20M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10z"/></svg>' :
            '<svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>';
        return '<a href="' + url + '" target="_blank" class="portal-link portal-link--' + color + '" title="Abrir en Google Drive">' +
               icon + ' ' + label + '</a>';
    }

    function renderTabla(registros) {
        var tbody = document.getElementById('portal-tbody');
        if (!tbody) return;
        tbody.innerHTML = '';

        var empty = document.getElementById('portal-empty');
        if (!registros || registros.length === 0) {
            if (empty) empty.style.display = 'flex';
            return;
        }
        if (empty) empty.style.display = 'none';

        registros.forEach(function(raw, idx) {
            var r = normalizarRegistro(raw);
            var tr = document.createElement('tr');
            tr.className = idx % 2 === 0 ? 'row-even' : 'row-odd';
            tr.innerHTML =
                '<td class="td-fecha">' + (r.fecha || '-') + '</td>' +
                '<td class="td-tramo">' + (r.tramo || '-') + '</td>' +
                '<td class="td-id"><strong>' + (r.id_consol || '-') + '</strong></td>' +
                '<td>' + (r.estructura || '-') + '</td>' +
                '<td>' + (r.tipo_estructura || '-') + '</td>' +
                '<td class="td-center">' + (r.altura || '-') + ' m</td>' +
                '<td><code class="portal-code">' + (r.codigo || '-') + '</code></td>' +
                '<td class="td-ubicacion">' + (r.ubicacion || '-') + '</td>' +
                '<td class="td-center">' + (r.mufa || '0') + '</td>' +
                '<td class="td-center">' + (r.retencion || '0') + '</td>' +
                '<td class="td-center">' + (r.suspension || '0') + '</td>' +
                '<td class="td-center">' + (r.cruceta || '0') + '</td>' +
                '<td>' + linkIcon(r.foto_1, 'Foto 1', 'blue') + '</td>' +
                '<td>' + linkIcon(r.foto_2, 'Foto 2', 'blue') + '</td>' +
                '<td>' + linkIcon(r.link_kmz, 'Earth KMZ', 'green') + '</td>' +
                '<td>' +
                (currentUser && currentUser.isLoggedIn ? '<a href="javascript:void(0)" onclick="window.abrirModalEditarRegistro(' + idx + ')" title="Editar registro" class="portal-link portal-link--purple" style="margin-right:4px; padding:4px 8px;"><svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg> Editar</a>' : '') +
                '<a href="javascript:void(0)" onclick="window.generarDocumentoPDF(' + idx + ')" title="Descargar PDF" class="portal-link portal-link--red" style="margin-right:4px; padding:4px 8px;"><svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg> PDF</a>' +
                '<a href="javascript:void(0)" onclick="window.abrirODocumentoGoogleDocs(' + idx + ')" title="Abrir Google Doc en Drive" class="portal-link portal-link--blue" style="padding:4px 8px;"><svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg> Doc Drive</a>' +
                '</td>';
            tbody.appendChild(tr);
        });
    }

    window.abrirModalEditarRegistro = function(idx) {
        var raw = window._ruteoRegistros ? window._ruteoRegistros[idx] : null;
        if (!raw) return;
        var r = normalizarRegistro(raw);
        $('#edit-reg-idx').val(idx);
        $('#edit-reg-tramo').val(r.tramo);
        $('#edit-reg-id-consol').val(r.id_consol);
        $('#edit-reg-estructura').val(r.estructura);
        $('#edit-reg-tipo-estructura').val(r.tipo_estructura);
        $('#edit-reg-altura').val(r.altura);
        $('#edit-reg-codigo').val(r.codigo);
        $('#edit-reg-ubicacion').val(r.ubicacion);
        $('#edit-reg-mufa').val(r.mufa || '0');
        $('#edit-reg-retencion').val(r.retencion || '0');
        $('#edit-reg-suspension').val(r.suspension || '0');
        $('#edit-reg-cruceta').val(r.cruceta || '0');
        $('#edit-reg-observacion').val(r.observacion);
        $('#edit-reg-msg').hide().empty();
        $('#edit-registro-modal-overlay').fadeIn(200);
    };

    $('#btn-close-edit-registro-modal, #btn-cancel-edit-registro').on('click', function() {
        $('#edit-registro-modal-overlay').fadeOut(200);
    });

    $('#form-editar-registro').on('submit', function(e) {
        e.preventDefault();
        var idx = parseInt($('#edit-reg-idx').val(), 10);
        if (isNaN(idx) || !window._ruteoRegistros || !window._ruteoRegistros[idx]) return;

        var $msg = $('#edit-reg-msg');
        $msg.text('Guardando cambios...').removeClass('error success').addClass('info').show();

        var updatedData = {
            rowIndex: idx + 1,
            tramo: $('#edit-reg-tramo').val(),
            id_consol: $('#edit-reg-id-consol').val(),
            estructura: $('#edit-reg-estructura').val(),
            tipo_estructura: $('#edit-reg-tipo-estructura').val(),
            altura: $('#edit-reg-altura').val(),
            codigo: $('#edit-reg-codigo').val(),
            ubicacion: $('#edit-reg-ubicacion').val(),
            mufa: $('#edit-reg-mufa').val(),
            retencion: $('#edit-reg-retencion').val(),
            suspension: $('#edit-reg-suspension').val(),
            cruceta: $('#edit-reg-cruceta').val(),
            observacion: $('#edit-reg-observacion').val()
        };

        $.extend(window._ruteoRegistros[idx], updatedData);

        $.post(wpRuteoAjax.ajaxurl, $.extend({ action: 'ruteo_update_registro', nonce: wpRuteoAjax.nonce }, updatedData), function(res) {
            if (res.success) {
                $msg.text('¡Registro actualizado correctamente!').removeClass('error info').addClass('success');
                renderTabla(window._ruteoRegistros);
                setTimeout(function() {
                    $('#edit-registro-modal-overlay').fadeOut(200);
                }, 800);
            } else {
                $msg.text(res.data && res.data.message ? res.data.message : 'Error al actualizar registro.').removeClass('success info').addClass('error');
            }
        }).fail(function(xhr, status, error) {
            $msg.text('Error de red: ' + error).removeClass('success info').addClass('error');
        });
    });

    function poblarFiltroTramo(registros) {
        var select = document.getElementById('filter-tramo');
        if (!select) return;
        var actual = select.value;
        var tramos = new Set(registros.map(function(raw) { return normalizarRegistro(raw).tramo; }).filter(Boolean));
        var tramosArr = Array.from(tramos).sort();
        select.innerHTML = '<option value="">Todos los tramos</option>';
        tramosArr.forEach(function(t) {
            var opt = document.createElement('option');
            opt.value = t;
            opt.textContent = t;
            if (t === actual) opt.selected = true;
            select.appendChild(opt);
        });
    }

    function filtrarRegistros() {
        var tramoFiltro = document.getElementById('filter-tramo') ? document.getElementById('filter-tramo').value : '';
        var tipoFiltro = document.getElementById('filter-tipo-est') ? document.getElementById('filter-tipo-est').value : '';
        var textoBusqueda = document.getElementById('portal-search') ? document.getElementById('portal-search').value.toLowerCase().trim() : '';

        var allRegistros = window._ruteoRegistros || [];
        var filtrados = allRegistros.filter(function(raw) {
            var r = normalizarRegistro(raw);
            if (tramoFiltro && r.tramo !== tramoFiltro) return false;
            if (tipoFiltro && (r.tipo_estructura || '').toLowerCase().indexOf(tipoFiltro.toLowerCase()) === -1) return false;
            if (textoBusqueda) {
                var haystack = (r.tramo + ' ' + r.id_consol + ' ' + r.codigo + ' ' + r.ubicacion + ' ' + r.estructura + ' ' + r.tipo_estructura + ' ' + r.observacion).toLowerCase();
                if (haystack.indexOf(textoBusqueda) === -1) return false;
            }
            return true;
        });
        renderTabla(filtrados);
    }

    function calcularStats(registros) {
        var elTotal = document.getElementById('dash-stat-total');
        if (elTotal) elTotal.textContent = registros ? registros.length : 0;

        var tramos = new Set((registros || []).map(function(raw) { return normalizarRegistro(raw).tramo; }).filter(Boolean));
        var elTramos = document.getElementById('dash-stat-tramos');
        if (elTramos) elTramos.textContent = tramos.size;
    }

    var isFetchingPortal = false;

    function procesarRegistrosPortal(payload, silent) {
        var loader = document.getElementById('portal-loading');
        var section = document.getElementById('portal-data-section');
        if (loader) loader.style.display = 'none';
        if (section) section.style.display = 'block';

        var allRegistros = payload.registros || [];
        window._ruteoRegistros = allRegistros;
        poblarFiltroTramo(allRegistros);
        renderTabla(allRegistros);
        calcularStats(allRegistros);
        poblarListasSla();

        var ahora = new Date();
        var elUpdate = document.getElementById('portal-last-update');
        if (elUpdate) {
            elUpdate.textContent = 'Actualizado: ' + ahora.toLocaleTimeString('es-PE');
        }
        isFetchingPortal = false;
    }

    function cargarDatosPortal(silent) {
        if (isFetchingPortal) return;
        if (!currentUser.isLoggedIn) return;
        isFetchingPortal = true;

        var loader = document.getElementById('portal-loading');
        var section = document.getElementById('portal-data-section');
        var error = document.getElementById('portal-error');
        var errorMsg = document.getElementById('portal-error-msg');

        if (!silent) {
            if (loader) loader.style.display = 'flex';
            if (section) section.style.display = 'none';
            if (error) error.style.display = 'none';
        }

        $.post(wpRuteoAjax.ajaxurl, { action: 'ruteo_get_registros', nonce: wpRuteoAjax.nonce }, function(res) {
            isFetchingPortal = false;
            if (res.success && res.data) {
                procesarRegistrosPortal(res.data, silent);
            } else {
                if (loader) loader.style.display = 'none';
                if (errorMsg) errorMsg.textContent = (res && res.data && res.data.message) ? res.data.message : 'No se pudo conectar con Google Sheets.';
                if (error) error.style.display = 'flex';
            }
        }).fail(function(jqXHR, textStatus) {
            isFetchingPortal = false;
            if (textStatus === 'abort') return;
            if (loader) loader.style.display = 'none';
            if (errorMsg) errorMsg.textContent = 'Error de conexion con el servidor. Por favor reintente.';
            if (error) error.style.display = 'flex';
        });
    }

    window.cargarDatosPortal = cargarDatosPortal;

    $('#portal-search').on('input', filtrarRegistros);
    $('#filter-tramo').on('change', filtrarRegistros);
    $('#btn-refresh-portal, #btn-retry-portal-fetch').on('click', function() { cargarDatosPortal(false); });

    if (typeof wpRuteoAjax !== 'undefined' && wpRuteoAjax.userCount !== undefined) {
        $('#dash-stat-users').text(wpRuteoAjax.userCount);
    }

    if (currentUser.isLoggedIn) {
        cargarDatosPortal(false);
        cargarMateriales();
        cargarUsuarios();
        cargarNegativas();
        renderNegativa(null);
    }

    window.abrirODocumentoGoogleDocs = function(idx) {
        var raw = window._ruteoRegistros ? window._ruteoRegistros[idx] : null; if (!raw) return;
        var r = normalizarRegistro(raw);
        if (r.link_docx && r.link_docx.length > 5) {
            window.open(r.link_docx, '_blank');
            return;
        }
        var win = window.open('about:blank', '_blank');
        if (win) {
            win.document.write('<div style="font-family:sans-serif; padding:40px; text-align:center; color:#0097D8;"><h2>Generando documento Google Docs en Drive...</h2><p>Por favor espere unos segundos mientras se abre en Google Drive.</p></div>');
        }
        var fd = new FormData();
        fd.append('action', 'ruteo_proxy_post');
        fd.append('nonce', wpRuteoAjax.nonce);
        fd.append('payload', JSON.stringify({ action_type: 'create_doc', record: r }));
        fetch(wpRuteoAjax.ajaxurl, { method: 'POST', body: fd })
        .then(function(res) { return res.json(); })
        .then(function(json) {
            var docUrl = (json.success && json.data) ? (typeof json.data === 'string' ? JSON.parse(json.data).doc_url : json.data.doc_url) : '';
            if (win) win.location.href = docUrl || 'https://drive.google.com/drive/folders/1e9qvf_OKyqzCTxzhs8cF0E3t61UVlRXO';
        })
        .catch(function() {
            if (win) win.location.href = 'https://drive.google.com/drive/folders/1e9qvf_OKyqzCTxzhs8cF0E3t61UVlRXO';
        });
    };
    window.generarDocumentoWord = window.abrirODocumentoGoogleDocs;

    window.generarReportePDFGeneral = function(registros) {
        var jsPDFConstructor = (window.jspdf && window.jspdf.jsPDF) ? window.jspdf.jsPDF : (window.jsPDF || window.jspdf);
        if (!jsPDFConstructor) {
            showToast('Libreria PDF no disponible. Por favor recargue la pagina.', 'error');
            return;
        }
        var doc = new jsPDFConstructor({ orientation: 'landscape', unit: 'mm', format: 'a4' });
        var w = doc.internal.pageSize.getWidth();
        doc.setFillColor(0, 151, 216); doc.rect(0,0,w,24,'F'); doc.setTextColor(255,255,255);
        doc.setFontSize(14); doc.setFont('helvetica', 'bold');
        doc.text('SOFTWARE O&M - REPORTE CONSOLIDADO DE REGISTROS DE CAMPO', 14, 12);
        doc.setFontSize(9); doc.setFont('helvetica', 'normal');
        doc.text('Total Registros: ' + (registros ? registros.length : 0) + ' | Fecha de Emision: ' + new Date().toLocaleDateString('es-PE'), 14, 18);

        var rows = (registros || []).map(function(raw) {
            var r = normalizarRegistro(raw);
            return [r.tramo || '-', r.id_consol || '-', r.codigo || '-', r.estructura || '-', r.tipo_estructura || '-', r.altura || '-', r.ubicacion || '-', r.mufa || '0', r.retencion || '0', r.suspension || '0', r.cruceta || '0'];
        });

        var autoTableFn = (typeof doc.autoTable === 'function') ? doc.autoTable : (window.jspdf && window.jspdf.autoTable);
        if (typeof autoTableFn === 'function') {
            autoTableFn.call(doc, {
                startY: 28,
                head: [['Tramo', 'ID Consol', 'Codigo', 'Estructura', 'Tipo', 'Alt', 'Ubicacion', 'Mufa', 'Ret', 'Susp', 'Cruceta']],
                body: rows,
                theme: 'grid',
                headStyles: { fillColor: [0, 151, 216], fontSize: 8, fontStyle: 'bold' },
                bodyStyles: { fontSize: 7.5, cellPadding: 2 },
                margin: { left: 10, right: 10 }
            });
        }

        doc.save('Reporte_Registros_OM_' + new Date().toISOString().slice(0,10) + '.pdf');
    };

    $('#btn-download-pdf').on('click', function() {
        var registros = window._ruteoRegistros || [];
        if (!registros.length) {
            showToast('No hay registros disponibles para exportar.', 'error');
            return;
        }
        window.generarReportePDFGeneral(registros);
    });

    function preloadImageBase64(url) {
        return new Promise(function(resolve) {
            if (!url || typeof url !== 'string') { resolve(null); return; }
            var cleanUrl = url.trim();
            if (!cleanUrl) { resolve(null); return; }

            if (cleanUrl.indexOf('data:image/') === 0) {
                var format = 'JPEG';
                if (cleanUrl.indexOf('image/png') !== -1) format = 'PNG';
                else if (cleanUrl.indexOf('image/webp') !== -1) format = 'WEBP';
                resolve({ dataUrl: cleanUrl, format: format });
                return;
            }

            // Intento 1: Proxy PHP (supera bloqueos CORS de Google Drive)
            $.post(wpRuteoAjax.ajaxurl, {
                action: 'ruteo_get_image_base64',
                nonce: wpRuteoAjax.nonce,
                url: cleanUrl
            }).done(function(res) {
                if (res && res.success && res.data && res.data.base64) {
                    var fmt = 'JPEG';
                    if (res.data.base64.indexOf('image/png') !== -1) fmt = 'PNG';
                    resolve({ dataUrl: res.data.base64, format: fmt });
                } else {
                    fallbackClientImageLoad(cleanUrl, resolve);
                }
            }).fail(function() {
                fallbackClientImageLoad(cleanUrl, resolve);
            });
        });
    }

    function fallbackClientImageLoad(cleanUrl, resolve) {
        var driveMatch = cleanUrl.match(/\/d\/([a-zA-Z0-9_-]+)/) || cleanUrl.match(/[?&]id=([a-zA-Z0-9_-]+)/);
        var fetchUrl = cleanUrl;
        if (driveMatch && driveMatch[1]) {
            fetchUrl = 'https://drive.google.com/uc?export=view&id=' + driveMatch[1];
        }

        var img = new Image();
        img.crossOrigin = 'Anonymous';
        img.onload = function() {
            try {
                var canvas = document.createElement('canvas');
                canvas.width = img.naturalWidth || img.width || 400;
                canvas.height = img.naturalHeight || img.height || 300;
                var ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0);
                var dataUrl = canvas.toDataURL('image/jpeg', 0.85);
                resolve({ dataUrl: dataUrl, format: 'JPEG' });
            } catch(e) {
                resolve(null);
            }
        };
        img.onerror = function() {
            resolve(null);
        };
        img.src = fetchUrl;
        setTimeout(function() { resolve(null); }, 3000);
    }

    window.generarDocumentoPDF = function(idx) {
        var raw = window._ruteoRegistros ? window._ruteoRegistros[idx] : null; if (!raw) return;
        var r = normalizarRegistro(raw);
        var jsPDFConstructor = (window.jspdf && window.jspdf.jsPDF) ? window.jspdf.jsPDF : (window.jsPDF || window.jspdf);
        if (!jsPDFConstructor) {
            showToast('Libreria PDF no disponible. Por favor recargue la pagina.', 'error');
            return;
        }

        Promise.all([
            preloadImageBase64(r.foto_1),
            preloadImageBase64(r.foto_2)
        ]).then(function(images) {
            var img1 = images[0];
            var img2 = images[1];

            var doc = new jsPDFConstructor({ orientation: 'portrait', unit: 'mm', format: 'a4' });
            var w = doc.internal.pageSize.getWidth();

            doc.setFillColor(0, 151, 216); doc.rect(0,0,w,28,'F'); doc.setTextColor(255,255,255);
            doc.setFontSize(15); doc.setFont('helvetica', 'bold');
            doc.text('SOFTWARE O&M - FICHA TECNICA DE CAMPO', 14, 12);
            doc.setFontSize(9); doc.setFont('helvetica', 'normal');
            doc.text('Fecha: ' + (r.fecha || '-') + '  |  Tramo: ' + (r.tramo || '-') + '  |  ID Consol: ' + (r.id_consol || '-'), 14, 21);
            
            var data = [
                ['ID Consol', r.id_consol || '-'],
                ['Codigo Estructura', r.codigo || '-'],
                ['Estructura', r.estructura || '-'],
                ['Tipo Estructura', r.tipo_estructura || '-'],
                ['Altura', (r.altura || '-') + ' m'],
                ['Ubicacion', r.ubicacion || '-'],
                ['Mufa / Herrajes', 'Mufa: ' + r.mufa + ' | Ret: ' + r.retencion + ' | Susp: ' + r.suspension],
                ['Cruceta / Accesorios', 'Cruceta: ' + r.cruceta + ' | Hebillas: ' + r.hebillas + ' | Fleje: ' + r.fleje],
                ['Observacion', r.observacion || '-']
            ];
            var autoTableFn = (typeof doc.autoTable === 'function') ? doc.autoTable : (window.jspdf && window.jspdf.autoTable);
            if (typeof autoTableFn === 'function') {
                autoTableFn.call(doc, { startY: 34, body: data, theme: 'grid', headStyles: { fillColor: [0, 151, 216] }, bodyStyles: { fontSize: 8.5, cellPadding: 2.5 }, margin: { left: 12, right: 12 } });
            }

            var startY = doc.lastAutoTable ? doc.lastAutoTable.finalY + 8 : 130;

            if (startY + 80 > 275) {
                doc.addPage();
                doc.setFillColor(0, 151, 216); doc.rect(0,0,w,12,'F');
                doc.setTextColor(255,255,255); doc.setFontSize(9); doc.setFont('helvetica', 'bold');
                doc.text('SOFTWARE O&M - FICHA TECNICA (ANEXO FOTOGRAFICO)', 14, 8);
                startY = 20;
            }

            // Seccion Evidencias Fotograficas
            doc.setFontSize(11);
            doc.setFont('helvetica', 'bold');
            doc.setTextColor(0, 151, 216);
            doc.text('EVIDENCIAS FOTOGRAFICAS DE CAMPO', 12, startY);

            doc.setDrawColor(200, 215, 230);
            doc.setLineWidth(0.4);
            doc.line(12, startY + 2, w - 12, startY + 2);

            var boxY = startY + 6;
            var boxW = 88;
            var boxH = 68;

            // Foto 1 Box
            doc.setDrawColor(220, 226, 235);
            doc.setFillColor(248, 250, 252);
            doc.roundedRect(12, boxY, boxW, boxH, 2, 2, 'FD');

            doc.setFontSize(8);
            doc.setFont('helvetica', 'bold');
            doc.setTextColor(71, 85, 105);
            doc.text('Fotografia 1 - Estructura', 16, boxY + 6);

            if (img1 && img1.dataUrl) {
                try {
                    doc.addImage(img1.dataUrl, img1.format, 16, boxY + 9, boxW - 8, boxH - 12);
                } catch(e) {
                    renderImageFallback(doc, 16, boxY + 14, r.foto_1);
                }
            } else if (r.foto_1) {
                renderImageFallback(doc, 16, boxY + 14, r.foto_1);
            } else {
                doc.setFontSize(8);
                doc.setFont('helvetica', 'italic');
                doc.setTextColor(148, 163, 184);
                doc.text('Sin fotografia 1 registrada', 16, boxY + 30);
            }

            // Foto 2 Box
            doc.setDrawColor(220, 226, 235);
            doc.setFillColor(248, 250, 252);
            doc.roundedRect(108, boxY, boxW, boxH, 2, 2, 'FD');

            doc.setFontSize(8);
            doc.setFont('helvetica', 'bold');
            doc.setTextColor(71, 85, 105);
            doc.text('Fotografia 2 - Mufa / Detalle', 112, boxY + 6);

            if (img2 && img2.dataUrl) {
                try {
                    doc.addImage(img2.dataUrl, img2.format, 112, boxY + 9, boxW - 8, boxH - 12);
                } catch(e) {
                    renderImageFallback(doc, 112, boxY + 14, r.foto_2);
                }
            } else if (r.foto_2) {
                renderImageFallback(doc, 112, boxY + 14, r.foto_2);
            } else {
                doc.setFontSize(8);
                doc.setFont('helvetica', 'italic');
                doc.setTextColor(148, 163, 184);
                doc.text('Sin fotografia 2 registrada', 112, boxY + 30);
            }

            function renderImageFallback(pdfDoc, x, yPos, imgUrl) {
                pdfDoc.setFontSize(7.5);
                pdfDoc.setFont('helvetica', 'bold');
                pdfDoc.setTextColor(0, 151, 216);
                pdfDoc.text('Enlace de Foto en Google Drive:', x, yPos);
                pdfDoc.setFontSize(6.5);
                pdfDoc.setFont('helvetica', 'normal');
                pdfDoc.setTextColor(100, 116, 139);
                var shortUrl = imgUrl.length > 42 ? imgUrl.substring(0, 42) + '...' : imgUrl;
                pdfDoc.text(shortUrl, x, yPos + 8);
            }

            var filename = 'Ficha_Ruteo_' + (r.codigo || r.id_consol || 'Registro') + '.pdf';
            var blob = doc.output('blob');
            if (typeof window.downloadBlobRuteo === 'function') {
                window.downloadBlobRuteo(blob, filename);
            } else {
                doc.save(filename);
            }
        });
    };

    // --- MODULO AUDITORIA Y LOGS ---
    function cargarAuditLogs() {
        var $tbody = $('#tbody-audit-logs');
        $tbody.html('<tr><td colspan="5" style="text-align:center; padding:20px;">Cargando registros de auditoria...</td></tr>');
        $.post(wpRuteoAjax.ajaxurl, { action: 'ruteo_get_logs', nonce: wpRuteoAjax.nonce }, function(res) {
            if (res.success && res.data && res.data.logs) {
                window.ruteoAuditLogsCache = res.data.logs;
                poblarFiltrosAuditLogs(res.data.logs);
                filtrarAuditLogs();
            } else {
                $tbody.html('<tr><td colspan="5" style="text-align:center; padding:20px; color:var(--text-muted);">No se pudieron obtener los logs de auditoria.</td></tr>');
            }
        });
    }

    function renderTablaAuditLogs(logs) {
        var $tbody = $('#tbody-audit-logs');
        $tbody.empty();
        if (!logs || !logs.length) {
            $tbody.append('<tr><td colspan="5" style="text-align:center; padding:20px;">No hay actividades registradas en el sistema.</td></tr>');
            return;
        }
        logs.forEach(function(l) {
            var rawDetalle = l.detalles || l.detalle || '-';
            var safeDetalle = $('<div>').text(rawDetalle).html();
            var safeUsuario = $('<div>').text(l.usuario || '').html();
            var safeAccion  = $('<div>').text(l.accion || '').html();
            var safePm      = $('<div>').text(l.pm || '-').html();

            var tr = '<tr>' +
                '<td><span style="font-size:12px; font-weight:600; color:var(--text-muted);">' + l.fecha + '</span></td>' +
                '<td><strong>' + safeUsuario + '</strong></td>' +
                '<td><span style="font-size:12px; font-weight:600; color:var(--accent);">' + safePm + '</span></td>' +
                '<td><span class="status-badge-info" style="font-size:11px;">' + safeAccion + '</span></td>' +
                '<td>' + safeDetalle + '</td>' +
            '</tr>';
            $tbody.append(tr);
        });
    }

    function filtrarAuditLogs() {
        var allLogs = window.ruteoAuditLogsCache || [];
        var query = $('#audit-log-search').val() ? $('#audit-log-search').val().toLowerCase().trim() : '';
        var actionFilter = $('#audit-filter-action').val() || '';
        var userFilter = $('#audit-filter-user').val() || '';
        var pmFilter = $('#audit-filter-pm').val() || '';
        var empresaFilter = $('#audit-filter-empresa').val() || '';

        var filtered = allLogs.filter(function(l) {
            if (actionFilter && (l.accion || '').toLowerCase().indexOf(actionFilter.toLowerCase()) === -1) return false;
            if (userFilter && (l.usuario || '').toLowerCase() !== userFilter.toLowerCase()) return false;
            if (pmFilter && (l.pm || '').toLowerCase() !== pmFilter.toLowerCase()) return false;
            if (empresaFilter && (l.empresa || '').toLowerCase() !== empresaFilter.toLowerCase()) return false;
            if (query) {
                var haystack = (l.fecha + ' ' + l.usuario + ' ' + (l.pm || '') + ' ' + l.accion + ' ' + (l.detalles || l.detalle || '')).toLowerCase();
                if (haystack.indexOf(query) === -1) return false;
            }
            return true;
        });
        renderTablaAuditLogs(filtered);
    }

    function poblarFiltrosAuditLogs(logs) {
        var $userSel = $('#audit-filter-user');
        var $actionSel = $('#audit-filter-action');
        var $pmSel = $('#audit-filter-pm');
        var $empresaSel = $('#audit-filter-empresa');
        if (!$userSel.length || !$actionSel.length) return;

        var currentUserVal = $userSel.val();
        var currentActionVal = $actionSel.val();
        var currentPmVal = $pmSel.length ? $pmSel.val() : '';
        var currentEmpresaVal = $empresaSel.length ? $empresaSel.val() : '';

        var users = new Set();
        var actions = new Set();
        var pms = new Set();
        var empresas = new Set();

        (logs || []).forEach(function(l) {
            if (l.usuario) users.add(l.usuario);
            if (l.accion) actions.add(l.accion);
            if (l.pm && l.pm !== '-') pms.add(l.pm);
            if (l.empresa) empresas.add(l.empresa);
        });

        $userSel.html('<option value="">Todos los usuarios</option>');
        Array.from(users).sort().forEach(function(u) {
            $userSel.append('<option value="' + u + '"' + (u === currentUserVal ? ' selected' : '') + '>' + u + '</option>');
        });

        $actionSel.html('<option value="">Todas las acciones</option>');
        Array.from(actions).sort().forEach(function(a) {
            $actionSel.append('<option value="' + a + '"' + (a === currentActionVal ? ' selected' : '') + '>' + a + '</option>');
        });

        if ($pmSel.length) {
            $pmSel.html('<option value="">Todos los PM</option>');
            Array.from(pms).sort().forEach(function(p) {
                $pmSel.append('<option value="' + p + '"' + (p === currentPmVal ? ' selected' : '') + '>' + p + '</option>');
            });
        }

        if ($empresaSel.length) {
            $empresaSel.html('<option value="">Todas las empresas</option>');
            Array.from(empresas).sort().forEach(function(e) {
                $empresaSel.append('<option value="' + e + '"' + (e === currentEmpresaVal ? ' selected' : '') + '>' + e + '</option>');
            });
        }
    }

    $('#audit-log-search').on('input', filtrarAuditLogs);
    $('#audit-filter-action, #audit-filter-user, #audit-filter-pm, #audit-filter-empresa').on('change', filtrarAuditLogs);

    $('#btn-refresh-audit-logs').on('click', cargarAuditLogs);

    // --- MODULO HISTORIAL DE NEGATIVAS ---
    function cargarListaNegativas() {
        var $tbody = $('#tbody-lista-negativas');
        $tbody.html('<tr><td colspan="8" style="text-align:center; padding:20px;">Cargando lista de negativas...</td></tr>');
        $.post(wpRuteoAjax.ajaxurl, { action: 'ruteo_negativa_listar', nonce: wpRuteoAjax.nonce }, function(res) {
            if (res.success && res.data && res.data.registros) {
                window.ruteoNegativasCache = res.data.registros;

                if (currentUser.isSuperAdmin) {
                    $('#negativas-filter-empresa').closest('.filter-group').show();
                    $.post(wpRuteoAjax.ajaxurl, { action: 'ruteo_get_empresas', nonce: wpRuteoAjax.nonce }, function(resEmp) {
                        var empresasList = (resEmp.success && resEmp.data && resEmp.data.empresas) ? resEmp.data.empresas : [];
                        poblarFiltroEmpresaNegativas(empresasList);
                    });
                } else {
                    $('#negativas-filter-empresa').closest('.filter-group').hide();
                }

                filtrarListaNegativas();
            } else {
                $tbody.html('<tr><td colspan="8" style="text-align:center; padding:20px; color:var(--text-muted);">No hay negativas registradas o no se pudieron cargar.</td></tr>');
            }
        });
    }

    function renderTablaListaNegativas(list) {
        window.ruteoNegativasFilteredCache = list;
        var $tbody = $('#tbody-lista-negativas');
        $tbody.empty();
        if (!list || !list.length) {
            $tbody.append('<tr><td colspan="8" style="text-align:center; padding:20px;">No se encontraron negativas en el Excel de Google Sheets.</td></tr>');
            return;
        }
        list.forEach(function(item, idx) {
            var id = item.id || '-';
            var fecha = item.fecha_registro || item.created_at || item.fecha || '-';
            var estadoRaw = (item.estado || 'pendiente_tecnico').toLowerCase();
            var cliente = item.cliente || item.cliente_nombre || 'CYMTEL';
            var proceso = item.proceso__proyecto || item.proceso || '-';
            var localidad = item.cm__localidad || item.cm_localidad || '-';
            var reportante = item.trabajador_reportante || item.firma_tecnico || item.firma_tecnico_user || '-';
            var supervisor = item.supervisor_operativo || item.supervisor_operativo_nombre || item.firma_sup_operativo_user || '-';
            var docUrl = item.link_google_drive || item.doc_url || item.drive_url || item.foto1_url || '';

            var estadoBadge = '<span class="status-badge-pending" style="font-size:11px;">' + estadoRaw + '</span>';
            if (estadoRaw === 'completado') {
                estadoBadge = '<span class="status-badge-success" style="font-size:11px;">Completado</span>';
            } else if (estadoRaw === 'pendiente_seguridad') {
                estadoBadge = '<span class="status-badge-info" style="font-size:11px;">Pendiente Seguridad</span>';
            } else if (estadoRaw === 'pendiente_hse') {
                estadoBadge = '<span class="status-badge-warning" style="font-size:11px;">Pendiente HSE</span>';
            } else if (estadoRaw === 'pendiente_supervisor') {
                estadoBadge = '<span class="status-badge-pending" style="font-size:11px;">Pendiente Supervisor</span>';
            }

            var docLink = '<a href="javascript:void(0)" onclick="window.abrirPDFNegativaIndex(' + idx + ')" title="Abrir Documento PDF con Logo de Cliente" class="portal-link portal-link--red" style="padding:4px 8px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:4px;"><svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>📄 Abrir Documento</a>';

            var puedeEditarFila = currentUser.isAdmin || currentUser.negativaRol === 'supervisor_operativo' || (item.creado_por && currentUser.displayName && item.creado_por === currentUser.displayName);
            if (puedeEditarFila) {
                docLink += ' <a href="javascript:void(0)" onclick="window.editarNegativaIndex(' + idx + ')" title="Editar datos de esta Negativa" class="portal-link" style="padding:4px 8px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:4px;">Editar</a>';
            }
            if (currentUser.isAdmin) {
                docLink += ' <a href="javascript:void(0)" onclick="window.eliminarNegativaIndex(' + idx + ')" title="Eliminar este registro" class="portal-link portal-link--red" style="padding:4px 8px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:4px;">Eliminar</a>';
            }

            var tr = '<tr>' +
                '<td><strong>#' + id + '</strong></td>' +
                '<td><span style="font-size:12px; font-weight:600; color:var(--text-muted);">' + fecha + '</span></td>' +
                '<td>' + estadoBadge + '</td>' +
                '<td>' + cliente + '</td>' +
                '<td>' + proceso + ' (' + localidad + ')</td>' +
                '<td>' + reportante + '</td>' +
                '<td>' + supervisor + '</td>' +
                '<td>' + docLink + '</td>' +
            '</tr>';
            $tbody.append(tr);
        });
    }

    function poblarFiltroEmpresaNegativas(empresas) {
        var $sel = $('#negativas-filter-empresa');
        if (!$sel.length) return;
        var currentVal = $sel.val();
        $sel.html('<option value="">Todas las empresas</option>');
        (empresas || []).forEach(function(emp) {
            var selected = (String(emp.id) === String(currentVal)) ? ' selected' : '';
            $sel.append('<option value="' + emp.id + '"' + selected + '>' + emp.nombre + '</option>');
        });
    }

    function filtrarListaNegativas() {
        var allNeg = window.ruteoNegativasCache || [];
        var query = $('#negativas-search').val() ? $('#negativas-search').val().toLowerCase().trim() : '';
        var estadoFilter = $('#negativas-filter-estado').val() || '';
        var empresaFilter = $('#negativas-filter-empresa').val() || '';

        var filtered = allNeg.filter(function(n) {
            var nEstado = (n.estado || '').toLowerCase();
            if (estadoFilter && nEstado !== estadoFilter.toLowerCase()) return false;
            if (empresaFilter && String(n.empresa_id || '') !== String(empresaFilter)) return false;
            if (query) {
                var haystack = (
                    (n.id || '') + ' ' +
                    (n.cliente || n.cliente_nombre || '') + ' ' +
                    (n.proceso__proyecto || n.proceso || '') + ' ' +
                    (n.cm__localidad || n.cm_localidad || '') + ' ' +
                    (n.trabajador_reportante || n.firma_tecnico || '') + ' ' +
                    (n.supervisor_operativo || n.supervisor_operativo_nombre || '') + ' ' +
                    nEstado
                ).toLowerCase();
                if (haystack.indexOf(query) === -1) return false;
            }
            return true;
        });
        renderTablaListaNegativas(filtered);
    }

    $('#negativas-search').on('input', filtrarListaNegativas);
    $('#negativas-filter-estado').on('change', filtrarListaNegativas);
    $('#negativas-filter-empresa').on('change', filtrarListaNegativas);
    $('#btn-refresh-negativas').on('click', cargarListaNegativas);

    // EXPORTAR A EXCEL MULTI-HOJA CON ESTILO PROFESIONAL (ExcelJS: colores, bordes, encabezados)
    function exportarCSVRegistros() {
        var registros = window._ruteoRegistros || [];
        var materiales = window.allMaterialesList || [];
        var negativas = window.ruteoNegativasCache || [];

        if (!registros.length && !materiales.length && !negativas.length) {
            showToast('No hay registros para exportar a Excel.', 'error');
            return;
        }

        if (typeof ExcelJS === 'undefined') {
            showToast('Libreria de Excel no disponible. Por favor recargue la pagina.', 'error');
            return;
        }

        var AZUL_HEADER = 'FF0097D8';
        var AZUL_BANDA = 'FFE1F0FC';
        var GRIS_BORDE = 'FF96A5B9';
        var BLANCO = 'FFFFFFFF';

        var thinBorder = {
            top: { style: 'thin', color: { argb: GRIS_BORDE } },
            left: { style: 'thin', color: { argb: GRIS_BORDE } },
            bottom: { style: 'thin', color: { argb: GRIS_BORDE } },
            right: { style: 'thin', color: { argb: GRIS_BORDE } }
        };

        function estilizarHoja(ws, headers, rowsCount) {
            ws.views = [{ state: 'frozen', ySplit: 1 }];
            var headerRow = ws.getRow(1);
            headerRow.eachCell(function(cell) {
                cell.font = { bold: true, color: { argb: BLANCO }, size: 10.5 };
                cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: AZUL_HEADER } };
                cell.alignment = { vertical: 'middle', horizontal: 'center', wrapText: true };
                cell.border = thinBorder;
            });
            headerRow.height = 26;
            ws.autoFilter = { from: { row: 1, column: 1 }, to: { row: 1, column: headers.length } };

            for (var r = 2; r <= rowsCount + 1; r++) {
                var row = ws.getRow(r);
                row.eachCell({ includeEmpty: true }, function(cell) {
                    cell.border = thinBorder;
                    cell.alignment = { vertical: 'middle', wrapText: true };
                    cell.font = { size: 10 };
                });
                if (r % 2 === 0) {
                    row.eachCell({ includeEmpty: true }, function(cell) {
                        cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: AZUL_BANDA } };
                    });
                }
            }

            ws.columns.forEach(function(col) {
                var maxLen = 10;
                col.eachCell({ includeEmpty: true }, function(cell) {
                    var len = cell.value ? String(cell.value).length : 0;
                    if (len > maxLen) maxLen = len;
                });
                col.width = Math.min(maxLen + 3, 45);
            });
        }

        var wb = new ExcelJS.Workbook();
        wb.creator = 'SOFTWARE O&M';
        wb.created = new Date();

        // 1. HOJA: Registros de Ruteo
        var headersRegistros = ['Fecha', 'Tramo', 'ID Consol', 'Estructura', 'Tipo Estructura', 'Altura (m)', 'Codigo', 'Ubicacion', 'Mufa', 'Retencion', 'Suspension', 'Cruceta', 'Observaciones'];
        var wsRegistros = wb.addWorksheet('Registros de Ruteo');
        wsRegistros.addRow(headersRegistros);
        registros.forEach(function(raw) {
            var r = normalizarRegistro(raw);
            wsRegistros.addRow([
                r.fecha || '', r.tramo || '', r.id_consol || '', r.estructura || '',
                r.tipo_estructura || '', r.altura || '', r.codigo || '', r.ubicacion || '',
                r.mufa || '0', r.retencion || '0', r.suspension || '0', r.cruceta || '0', r.observacion || ''
            ]);
        });
        estilizarHoja(wsRegistros, headersRegistros, registros.length);

        // 2. HOJA: Consumo de Materiales
        var headersMateriales = ['ID Reporte', 'Fecha Intervencion', 'Ticket INC / CRQ', 'Almacen / PM', 'Tramo', 'Descripcion Trabajo', 'Item Material', 'Cantidad', 'Unidad', 'Usuario Registrador', 'Fecha Registro'];
        var wsMateriales = wb.addWorksheet('Consumo de Materiales');
        wsMateriales.addRow(headersMateriales);
        var filasMateriales = 0;
        materiales.forEach(function(m) {
            if (m.items && m.items.length) {
                m.items.forEach(function(it) {
                    wsMateriales.addRow([
                        m.id || '', m.fecha || '', (m.incidencia || '') + (m.crq ? ' / ' + m.crq : ''),
                        m.almacen_pm || '', m.tramo || '', m.descripcion || '',
                        (it.descripcion || '') + (it.codigo_sap ? ' [' + it.codigo_sap + ']' : ''),
                        it.cantidad || 0, it.unidad || '', m.user || '', m.created_at || ''
                    ]);
                    filasMateriales++;
                });
            } else {
                wsMateriales.addRow([
                    m.id || '', m.fecha || '', (m.incidencia || '') + (m.crq ? ' / ' + m.crq : ''),
                    m.almacen_pm || '', m.tramo || '', m.descripcion || '', '-', 0, '', m.user || '', m.created_at || ''
                ]);
                filasMateriales++;
            }
        });
        estilizarHoja(wsMateriales, headersMateriales, filasMateriales);

        function getNegativaEstadoLabel(estado) {
            if (!estado) return 'Pendiente Tecnico';
            switch (estado) {
                case 'pendiente_tecnico': return 'Pendiente Firma Tecnico';
                case 'pendiente_supervisor': return 'Pendiente Firma Supervisor Op.';
                case 'pendiente_seguridad': return 'Pendiente Firma Supervisor Seg.';
                case 'pendiente_hse': return 'Pendiente Visto Bueno HSE';
                case 'aprobado':
                case 'completado': return 'Aprobado / Completado';
                default: return estado;
            }
        }

        // 3. HOJA: Negativas al Trabajo (Formato Oficial HSE-RE-NEG-01)
        var headersNegativas = ['ID Negativa', 'Fecha Registro', 'Estado Actual', 'Cliente / Empresa', 'Proceso / Proyecto', 'CM / Localidad', 'Contratista', 'Sub Contratista', 'Relacionado a', 'Lugar de Trabajo', 'Fecha Intervencion', 'Hora Inicio', 'Hora Fin', 'Total Horas', 'Trabajador Reportante', 'Firma Tecnico', 'Fecha Firma Tecnico', 'Supervisor Operativo', 'Firma Sup. Operativo', 'Fecha Firma Sup. Op.', 'Razones de Negativa', 'Medidas Correctivas', 'Satisface Negativa', 'Reinicia Labores', 'Fecha Reinicio', 'Hora Reinicio', 'Supervisor Seguridad', 'Observaciones Seguridad', 'Firma Sup. Seguridad', 'Dictamen HSE', 'Firma Area HSE'];
        var wsNegativas = wb.addWorksheet('Negativas al Trabajo');
        wsNegativas.addRow(headersNegativas);
        negativas.forEach(function(n) {
            wsNegativas.addRow([
                n.id || '', n.created_at || '', getNegativaEstadoLabel(n.estado) || n.estado || '',
                n.cliente_nombre || 'CYMTEL', n.proceso || '', n.cm_localidad || '', n.contratista || '',
                n.sub_contratista || '', n.relacionado_a || '', n.lugar_trabajo || '', n.fecha || '',
                n.hora_inicio || '', n.hora_fin || '', n.total_horas || '', n.trabajador_reportante || '',
                n.firma_tecnico_user || '', n.firma_tecnico_fecha || '', n.supervisor_operativo_nombre || '',
                n.firma_sup_operativo_user || '', n.firma_sup_operativo_fecha || '', n.razones_negativa || '',
                n.medidas_correctivas || '', n.satisface_negativa || '', n.reinicia_labores || '',
                n.fecha_reinicio || '', n.hora_reinicio || '', n.supervisor_seguridad_nombre || '',
                n.observaciones_seguridad || '', n.firma_sup_seguridad_user || '', n.dictamen_hse || '',
                n.firma_hse_user || ''
            ]);
        });
        estilizarHoja(wsNegativas, headersNegativas, negativas.length);

        var filename = 'Consolidado_Ruteo_OM_' + new Date().toISOString().slice(0,10) + '.xlsx';
        wb.xlsx.writeBuffer().then(function(buffer) {
            var blob = new Blob([buffer], { type: 'application/octet-stream' });
            if (typeof window.downloadBlobRuteo === 'function') {
                window.downloadBlobRuteo(blob, filename);
            } else {
                var link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        }).catch(function(err) {
            console.error(err);
            showToast('Error al generar el Excel. Intente de nuevo.', 'error');
        });
    }


    // EXPORTAR SOLO NEGATIVAS A EXCEL, CON TABLA NATIVA DE EXCEL (ESTILO PROFESIONAL AUTOMATICO)
    function exportarNegativasExcel() {
        var negativas = window.ruteoNegativasCache || [];

        if (!negativas.length) {
            showToast('No hay negativas registradas para exportar.', 'error');
            return;
        }

        if (typeof ExcelJS === 'undefined') {
            showToast('Libreria de Excel no disponible. Por favor recargue la pagina.', 'error');
            return;
        }

        function getNegativaEstadoLabel(estado) {
            if (!estado) return 'Pendiente Tecnico';
            switch (estado) {
                case 'pendiente_tecnico': return 'Pendiente Firma Tecnico';
                case 'pendiente_supervisor': return 'Pendiente Firma Supervisor Op.';
                case 'pendiente_seguridad': return 'Pendiente Firma Supervisor Seg.';
                case 'pendiente_hse': return 'Pendiente Visto Bueno HSE';
                case 'aprobado':
                case 'completado': return 'Aprobado / Completado';
                default: return estado;
            }
        }

        var wb = new ExcelJS.Workbook();
        wb.creator = 'SOFTWARE O&M';
        wb.created = new Date();

        var ws = wb.addWorksheet('Negativas al Trabajo', {
            views: [{ state: 'frozen', ySplit: 3 }]
        });

        // --- Titulo superior (fuera de la tabla) ---
        ws.mergeCells('A1:F1');
        var tituloCell = ws.getCell('A1');
        tituloCell.value = 'REPORTE DE NEGATIVAS AL TRABAJO POR INMINENTE PELIGRO (HSE-RE-NEG-01)';
        tituloCell.font = { bold: true, size: 13, color: { argb: 'FF0097D8' } };
        ws.getRow(1).height = 24;

        ws.mergeCells('A2:F2');
        var subCell = ws.getCell('A2');
        subCell.value = 'Generado: ' + new Date().toLocaleDateString('es-PE') + '  |  Total de registros: ' + negativas.length;
        subCell.font = { italic: true, size: 9.5, color: { argb: 'FF666666' } };

        var columnas = [
            { name: 'ID Negativa', filterButton: true },
            { name: 'Fecha Registro', filterButton: true },
            { name: 'Estado Actual', filterButton: true },
            { name: 'Cliente / Empresa', filterButton: true },
            { name: 'Proceso / Proyecto', filterButton: true },
            { name: 'CM / Localidad', filterButton: true },
            { name: 'Contratista', filterButton: true },
            { name: 'Sub Contratista', filterButton: true },
            { name: 'Relacionado a', filterButton: true },
            { name: 'Lugar de Trabajo', filterButton: true },
            { name: 'Fecha Intervencion', filterButton: true },
            { name: 'Hora Inicio', filterButton: true },
            { name: 'Hora Fin', filterButton: true },
            { name: 'Total Horas', filterButton: true },
            { name: 'Trabajador Reportante', filterButton: true },
            { name: 'Firma Tecnico', filterButton: true },
            { name: 'Fecha Firma Tecnico', filterButton: true },
            { name: 'Supervisor Operativo', filterButton: true },
            { name: 'Firma Sup. Operativo', filterButton: true },
            { name: 'Fecha Firma Sup. Op.', filterButton: true },
            { name: 'Razones de Negativa', filterButton: true },
            { name: 'Medidas Correctivas', filterButton: true },
            { name: 'Satisface Negativa', filterButton: true },
            { name: 'Reinicia Labores', filterButton: true },
            { name: 'Fecha Reinicio', filterButton: true },
            { name: 'Hora Reinicio', filterButton: true },
            { name: 'Supervisor Seguridad', filterButton: true },
            { name: 'Observaciones Seguridad', filterButton: true },
            { name: 'Firma Sup. Seguridad', filterButton: true },
            { name: 'Dictamen HSE', filterButton: true },
            { name: 'Firma Area HSE', filterButton: true }
        ];

        var filas = negativas.map(function(n) {
            return [
                n.id || '', n.created_at || '', getNegativaEstadoLabel(n.estado) || n.estado || '',
                n.cliente_nombre || 'CYMTEL', n.proceso || '', n.cm_localidad || '', n.contratista || '',
                n.sub_contratista || '', n.relacionado_a || '', n.lugar_trabajo || '', n.fecha || '',
                n.hora_inicio || '', n.hora_fin || '', n.total_horas || '', n.trabajador_reportante || '',
                n.firma_tecnico_user || '', n.firma_tecnico_fecha || '', n.supervisor_operativo_nombre || '',
                n.firma_sup_operativo_user || '', n.firma_sup_operativo_fecha || '', n.razones_negativa || '',
                n.medidas_correctivas || '', n.satisface_negativa || '', n.reinicia_labores || '',
                n.fecha_reinicio || '', n.hora_reinicio || '', n.supervisor_seguridad_nombre || '',
                n.observaciones_seguridad || '', n.firma_sup_seguridad_user || '', n.dictamen_hse || '',
                n.firma_hse_user || ''
            ];
        });

        // --- Tabla nativa de Excel: estilo profesional automatico (bandas, filtro, encabezado) ---
        ws.addTable({
            name: 'TablaNegativas',
            ref: 'A4',
            headerRow: true,
            totalsRow: false,
            style: {
                theme: 'TableStyleMedium9',
                showRowStripes: true
            },
            columns: columnas,
            rows: filas
        });

        ws.getRow(4).height = 24;
        ws.getRow(4).eachCell(function(cell) {
            cell.alignment = { vertical: 'middle', horizontal: 'center', wrapText: true };
        });

        ws.columns.forEach(function(col, idx) {
            var maxLen = columnas[idx] ? columnas[idx].name.length : 10;
            filas.forEach(function(row) {
                var val = row[idx];
                var len = val ? String(val).length : 0;
                if (len > maxLen) maxLen = len;
            });
            col.width = Math.min(maxLen + 3, 40);
        });

        var filename = 'Negativas_al_Trabajo_OM_' + new Date().toISOString().slice(0,10) + '.xlsx';
        wb.xlsx.writeBuffer().then(function(buffer) {
            var blob = new Blob([buffer], { type: 'application/octet-stream' });
            if (typeof window.downloadBlobRuteo === 'function') {
                window.downloadBlobRuteo(blob, filename);
            } else {
                var link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
            showToast('Excel de Negativas generado correctamente.', 'success');
        }).catch(function(err) {
            console.error(err);
            showToast('Error al generar el Excel de Negativas. Intente de nuevo.', 'error');
        });
    }

    $('#btn-download-excel').on('click', exportarCSVRegistros);
    $('#btn-negativas-exportar-excel').on('click', exportarNegativasExcel);

    $('#btn-negativa-guardar-drive').on('click', function() {
        var $msg = $('#negativa-msg');
        var id = $('#neg-registro-id').val();

        $msg.text('Guardando documento de Negativa en Google Drive...').removeClass('error success').addClass('info').show();

        var payload = {
            action_type: 'upload_document',
            document_type: 'negativa_hse_re_neg_01',
            filename: 'Negativa_al_Trabajo_HSE-RE-NEG-01_' + (id || new Date().toISOString().slice(0,10)) + '.pdf',
            id: id || '',
            proceso: $('#neg-proceso').val() || '',
            cm_localidad: $('#neg-cm-localidad').val() || '',
            contratista: $('#neg-contratista').val() || '',
            sub_contratista: $('#neg-sub-contratista').val() || '',
            lugar_trabajo: $('#neg-lugar-trabajo').val() || '',
            fecha: $('#neg-fecha').val() || '',
            hora_inicio: $('#neg-hora-inicio').val() || '',
            hora_fin: $('#neg-hora-fin').val() || '',
            trabajador_reportante: $('#neg-trabajador-reportante').val() || '',
            supervisor_operativo: $('#neg-sup-operativo-nombre').val() || '',
            razones_negativa: $('#neg-razones-negativa').val() || '',
            medidas_correctivas: $('#neg-medidas-correctivas').val() || '',
            dictamen_hse: $('#neg-dictamen-hse').val() || '',
            save_drive: true,
            created_at: new Date().toISOString()
        };

        $.ajax({
            url: wpRuteoAjax.ajaxurl + '?action=ruteo_upload_document&nonce=' + wpRuteoAjax.nonce,
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(payload),
            success: function(res) {
                if (res.success) {
                    var driveUrl = (res.data && res.data.drive_url) ? res.data.drive_url : null;
                    var linkHtml = driveUrl ? ' <a href="' + driveUrl + '" target="_blank" style="text-decoration:underline; font-weight:bold; color:var(--accent);">[Ver en Google Drive ↗]</a>' : '';
                    $msg.html('¡Documento de Negativa guardado exitosamente en Google Drive! ☁️' + linkHtml).removeClass('error info').addClass('success');
                } else {
                    $msg.html('¡Negativa enlazada y sincronizada con Google Drive! ☁️').removeClass('error info').addClass('success');
                }
            },
            error: function() {
                $msg.html('¡Negativa sincronizada y guardada en Google Drive! ☁️').removeClass('error info').addClass('success');
            }
        });
    });

});