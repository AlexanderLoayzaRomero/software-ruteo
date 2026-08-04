jQuery(document).ready(function($) {

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
        $('#ruteo-sidebar').toggleClass('collapsed');
    });

    // --- PREVISUALIZACION Y QUITAR FOTOS DE CAMPO ---
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
    $('#neg-foto1').change(function() { readURL(this, 'neg-preview1'); });
    $('#neg-foto2').change(function() { readURL(this, 'neg-preview2'); });

    $(document).on('click', '.btn-remove-photo', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var inputId = $(this).data('input');
        var previewId = $(this).data('preview');
        $('#' + inputId).val('');
        $('#' + previewId).removeClass('show').css('background-image', 'none');
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
            
            if (user.avatar) {
                $('#user-avatar-box').html('<img src="' + user.avatar + '" alt="Avatar">');
                $('#profile-avatar-img-box').html('<img src="' + user.avatar + '" alt="Avatar">');
            } else {
                var initial = (user.displayName || user.username || '?').charAt(0).toUpperCase();
                $('#user-avatar-box').html('<span id="user-avatar-text">' + initial + '</span>');
                $('#profile-avatar-large-text').text(initial);
            }

            var roleText = user.isAdmin ? 'Administrador (Admin)' : 'Operario (Worker)';
            $('#user-role-label').text(roleText);
            $('#btn-ruteo-logout').show();

            $('#ruteo-form-restricted-notice').hide();
            $('#ruteo-form').show();

            $('#tab-btn-login').hide();
            $('#tab-btn-perfil').show();

            if (user.isAdmin) {
                $('#tab-btn-usuarios').show();
                $('#admin-sheets-box').show();
            } else {
                $('#tab-btn-usuarios').hide();
                $('#admin-sheets-box').hide();
            }

            // Llenar datos de perfil
            $('#profile-name-heading').text(user.displayName || user.username);
            $('#profile-role-heading').text(roleText + ' - ' + (user.pmAssigned || 'Sin PM asignado'));
            $('#prof-display-name').val(user.displayName || user.username);
            $('#prof-email').val(user.email || '');
            $('#prof-phone').val(user.phone || '');
            $('#prof-pm').val(user.pmAssigned || '');

            if (typeof window.cargarDatosPortal === 'function' && (!window._ruteoRegistros || window._ruteoRegistros.length === 0)) {
                window.cargarDatosPortal();
            }

            cargarMateriales();

            if ($('#tab-login').hasClass('active') || !$('.sidebar-item.active').is(':visible')) {
                $('.sidebar-item[data-tab="inicio"]').click();
            }
        } else {
            window._ruteoRegistros = [];
            $('#ruteo-user-badge').hide();
            $('#btn-ruteo-logout').hide();

            $('#ruteo-form-restricted-notice').show();
            $('#ruteo-form').hide();

            $('#tab-btn-usuarios').hide();
            $('#tab-btn-perfil').hide();
            $('#tab-btn-login').show();

            $('.ruteo-tab-content').removeClass('active').hide();
            $('#tab-login').addClass('active').show();
        }
    }

    actualizarInterfazUsuario(currentUser);

    // --- NAVEGACION POR SIDEBAR Y ACCIONES RAPIDAS ---
    $('.sidebar-item, [data-goto]').on('click', function() {
        var targetTab = $(this).data('tab') || $(this).data('goto');
        if (!targetTab) return;

        $('.sidebar-item').removeClass('active');
        $('.sidebar-item[data-tab="' + targetTab + '"]').addClass('active');

        var titleMap = {
            'inicio': 'Panel de Administracion',
            'registros': 'Registros de Campo',
            'formulario': 'Nuevo Registro de Campo',
            'materiales': 'Consumo de Materiales',
            'sla-informes': 'SLA e Informes de Mantenimiento',
            'usuarios': 'Gestion de Cuentas de Usuario',
            'perfil': 'Perfil de Usuario',
            'login': 'Iniciar Sesion',
            'negativa': 'Negativa al Trabajo por Riesgo Inminente',
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
                }
            }
        });
    }

    function renderTablaMateriales(list) {
        var $tbody = $('#mat-reports-tbody');
        $tbody.empty();

        if (list.length === 0) {
            $tbody.append('<tr><td colspan="7" style="text-align:center; padding: 20px;">No hay reportes de materiales registrados aun.</td></tr>');
            return;
        }

        list.forEach(function(r) {
            var itemsSummary = r.items ? r.items.map(function(it) {
                return it.cantidad + ' ' + it.unidad + ' ' + it.descripcion + (it.codigo_sap ? ' (' + it.codigo_sap + ')' : '');
            }).join('<br>') : '-';

            var tr = '<tr>' +
                '<td>' + (r.fecha || '-') + '</td>' +
                '<td><strong>' + r.incidencia + '</strong>' + (r.crq ? '<br><small style="color:var(--text-muted);">' + r.crq + '</small>' : '') + '</td>' +
                '<td><span class="status-badge-info">' + r.almacen_pm + '</span></td>' +
                '<td>' + r.tramo + '</td>' +
                '<td>' + r.descripcion + '</td>' +
                '<td style="font-size: 12px; line-height: 1.4;">' + itemsSummary + '</td>' +
                '<td>' + r.user + '</td>' +
            '</tr>';
            $tbody.append(tr);
        });
    }

    // Filtros de busqueda de materiales
    $('#mat-search, #filter-mat-pm').on('input change', function() {
        var q = $('#mat-search').val().toLowerCase();
        var pm = $('#filter-mat-pm').val();

        var filtrados = allMaterialesList.filter(function(r) {
            var matchPm = !pm || r.almacen_pm === pm;
            var text = (r.incidencia + ' ' + r.crq + ' ' + r.tramo + ' ' + r.descripcion).toLowerCase();
            var matchQ = !q || text.indexOf(q) > -1;
            return matchPm && matchQ;
        });

        renderTablaMateriales(filtrados);
    });

    // --- ACCIONES SLA E INFORMES (MODAL E INTERACCION) ---
    var currentSlaType = 'Formato SLA';

    $('.btn-sla-action').on('click', function() {
        currentSlaType = $(this).data('type') || 'Formato SLA';
        $('#sla-modal-title').text(currentSlaType);
        $('#sla-modal-desc').text('Complete los detalles requeridos para generar ' + currentSlaType + ' estandarizado.');
        
        var user = (window.wpRuteoAjax && window.wpRuteoAjax.user) ? window.wpRuteoAjax.user : {};
        if (user.displayName || user.username) {
            $('#sla-input-tecnico').val(user.displayName || user.username);
        }
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
        var tramo = $('#sla-input-tramo').val();
        var incidencia = $('#sla-input-incidencia').val();
        var tecnico = $('#sla-input-tecnico').val();
        var detalle = $('#sla-input-detalle').val();

        var jsPDFConstructor = (window.jspdf && window.jspdf.jsPDF) ? window.jspdf.jsPDF : window.jsPDF;
        if (!jsPDFConstructor) {
            alert('Cargando libreria PDF, intente de nuevo en un instante.');
            return;
        }

        try {
            var doc = new jsPDFConstructor({ orientation: 'portrait', unit: 'mm', format: 'a4' });
            var pageW = doc.internal.pageSize.getWidth();
            var fecha = new Date().toLocaleDateString('es-PE', { day: '2-digit', month: 'long', year: 'numeric' });

            doc.setFillColor(0, 151, 216);
            doc.rect(0, 0, pageW, 32, 'F');
            doc.setTextColor(255, 255, 255);
            doc.setFontSize(16);
            doc.setFont('helvetica', 'bold');
            doc.text(currentSlaType.toUpperCase(), 14, 14);
            doc.setFontSize(9);
            doc.setFont('helvetica', 'normal');
            doc.text('Fecha: ' + fecha + '  |  Tecnico Responsable: ' + tecnico, 14, 23);

            doc.setTextColor(15, 23, 42);
            doc.setFontSize(11);
            doc.setFont('helvetica', 'bold');
            doc.text('INFORMACION GENERAL DE ATENCION', 14, 42);

            var dataInfo = [
                ['Tipo de Documento', currentSlaType],
                ['Tramo de Intervencion', tramo],
                ['No. Incidencia / Ticket', incidencia],
                ['Tecnico / Responsable', tecnico],
                ['Fecha de Registro', fecha]
            ];

            var autoTableFn = doc.autoTable || (window.jspdf && window.jspdf.autoTable);
            if (typeof doc.autoTable === 'function' || typeof autoTableFn === 'function') {
                var fn = doc.autoTable || autoTableFn;
                fn.call(doc, {
                    startY: 46,
                    body: dataInfo,
                    theme: 'grid',
                    headStyles: { fillColor: [0, 151, 216] },
                    bodyStyles: { fontSize: 9, cellPadding: 3 },
                    columnStyles: {
                        0: { fontStyle: 'bold', fillColor: [239, 246, 255], cellWidth: 55 },
                        1: { cellWidth: 'auto' }
                    },
                    margin: { left: 14, right: 14 }
                });

                var nextY = doc.lastAutoTable ? doc.lastAutoTable.finalY + 12 : 90;
                doc.setFontSize(11);
                doc.setFont('helvetica', 'bold');
                doc.setTextColor(0, 151, 216);
                doc.text('RESUMEN DE ACCIONES Y OBSERVACIONES', 14, nextY);

                fn.call(doc, {
                    startY: nextY + 4,
                    body: [
                        ['Detalle Tecnico', detalle || 'Sin observaciones adicionales registradas.']
                    ],
                    theme: 'grid',
                    bodyStyles: { fontSize: 9, cellPadding: 4 },
                    columnStyles: {
                        0: { fontStyle: 'bold', fillColor: [239, 246, 255], cellWidth: 55 },
                        1: { cellWidth: 'auto' }
                    },
                    margin: { left: 14, right: 14 }
                });
            }

            var blob = doc.output('blob');
            var safeName = currentSlaType.replace(/ /g, '_') + '_' + incidencia + '.pdf';
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
            alert('Error generando documento PDF.');
        }

        $('#sla-modal-overlay').fadeOut(200);
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

    // LOGOUT AJAX
    $('#btn-ruteo-logout').on('click', function() {
        if (!confirm('Deseas cerrar la sesion actual?')) return;
        $.ajax({
            url: wpRuteoAjax.ajaxurl,
            type: 'POST',
            data: { action: 'ruteo_logout', nonce: wpRuteoAjax.nonce },
            success: function() { window.location.reload(); }
        });
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
            var roleBadge = u.role === 'Admin' ? 
                '<span class="status-badge-active">Admin</span>' : 
                '<span class="status-badge-info">Worker</span>';

            var avatarHtml = u.avatar ? '<img src="' + u.avatar + '" alt="Avatar">' : (u.displayName || u.username || '?').charAt(0).toUpperCase();

            var tr = '<tr>' +
                '<td><div class="user-avatar-table">' + avatarHtml + '</div></td>' +
                '<td><strong>' + u.username + '</strong></td>' +
                '<td>' + (u.displayName || u.username) + '</td>' +
                '<td>' + u.email + '</td>' +
                '<td>' + (u.phone || '-') + '</td>' +
                '<td>' + (u.pmAssigned || 'Sin asignar') + '</td>' +
                '<td>' + roleBadge + '</td>' +
                '<td><button class="btn-del-row btn-del-user" data-id="' + u.id + '" data-name="' + u.username + '">Eliminar</button></td>' +
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

    // CREAR USUARIO AMPLIADO
    $('#form-create-user').on('submit', function(e) {
        e.preventDefault();
        var $msg = $('#create-user-msg');
        $msg.removeClass('success error').hide();

        var formData = new FormData();
        formData.append('action', 'ruteo_create_user');
        formData.append('nonce', wpRuteoAjax.nonce);
        formData.append('display_name', $('#user-display-name-input').val());
        formData.append('username', $('#user-username-input').val());
        formData.append('email', $('#user-email-input').val());
        formData.append('password', $('#user-password-input').val());
        formData.append('role', $('#user-role-select').val());
        formData.append('negativa_rol', $('#user-negativa-rol-select').val() || '');
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
            data: { action: 'ruteo_delete_user', nonce: wpRuteoAjax.nonce, user_id: userId },
            success: function(res) {
                if (res.success) cargarUsuarios();
                else alert(res.data.message || 'No se pudo eliminar el usuario.');
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
        formData.append('pm_assigned', $('#prof-pm').val());

        var fileInput = $('#prof-avatar-file')[0];
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
                    setTimeout(function() { window.location.reload(); }, 600);
                } else {
                    $msg.addClass('error').text(res.data.message || 'Error al actualizar perfil.').fadeIn(200);
                }
            }
        });
    });

    // --- MODULO: NEGATIVA AL TRABAJO POR RIESGO INMINENTE ---
var negativaActual = null;

function negativaEstadoLabel(estado) {
    var labels = {
        'pendiente_tecnico': 'Pendiente: Tecnico',
        'pendiente_supervisor': 'Pendiente: Supervisor Operativo',
        'pendiente_seguridad': 'Pendiente: Supervisor de Seguridad',
        'pendiente_hse': 'Pendiente: Visto Bueno HSE',
        'completado': 'Completado'
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
    $('#form-negativa-tecnico, #form-negativa-supervisor, #negativa-firma-simple, #btn-negativa-exportar-pdf').hide();

    if (!registro) {
        $('#negativa-estado-badge').text('');
        $('#neg-preview1, #neg-preview2').removeClass('show').css('background-image', 'none');
        $('#neg-foto1, #neg-foto2').val('').closest('.ruteo-photo-upload').removeClass('has-file');
        $('#form-negativa-tecnico').show();
        return;
    }

    $('#negativa-estado-badge').text(negativaEstadoLabel(registro.estado));

    var resumenHtml = '<strong>' + (registro.proceso || '') + '</strong> - ' + (registro.lugar_trabajo || '') + '<br>';
    resumenHtml += 'Tecnico: ' + (registro.firma_tecnico_user || '-') + ' | Supervisor Op.: ' + (registro.firma_sup_operativo_user || '-') +
                   ' | Seguridad: ' + (registro.firma_sup_seguridad_user || '-') + ' | HSE: ' + (registro.firma_hse_user || '-');

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

    if (registro.estado === 'pendiente_supervisor') {
        if (puedeActuar) { $('#form-negativa-supervisor').show(); }
        else { $('#negativa-firma-simple-texto').text('Esperando accion del Supervisor Operativo.'); $('#negativa-firma-simple').show().find('button').hide(); }
    } else if (registro.estado === 'pendiente_seguridad') {
        if (puedeActuar) {
            $('#negativa-firma-simple-texto').text('Firmar como Supervisor de Seguridad.');
            $('#negativa-firma-simple').show().find('button').show().data('etapa', 'seguridad');
        } else { $('#negativa-firma-simple-texto').text('Esperando firma del Supervisor de Seguridad.'); $('#negativa-firma-simple').show().find('button').hide(); }
    } else if (registro.estado === 'pendiente_hse') {
        if (puedeActuar) {
            $('#negativa-firma-simple-texto').text('Otorgar Visto Bueno del Area HSE.');
            $('#negativa-firma-simple').show().find('button').show().data('etapa', 'hse');
        } else { $('#negativa-firma-simple-texto').text('Esperando visto bueno de HSE.'); $('#negativa-firma-simple').show().find('button').hide(); }
    } else if (registro.estado === 'completado') {
        $('#btn-negativa-exportar-pdf').show();
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
    var fd = new FormData(this);
    fd.append('action', 'ruteo_negativa_guardar');
    fd.append('nonce', wpRuteoAjax.nonce);
    fd.append('etapa', 'tecnico');
    $.ajax({ url: wpRuteoAjax.ajaxurl, type: 'POST', data: fd, processData: false, contentType: false,
        success: function(res) {
            if (res.success) { alert('Etapa Tecnico guardada.'); cargarNegativas(); renderNegativa(res.data.registro); }
            else { alert('Error: ' + res.data.message); }
        }
    });
});

$('#form-negativa-supervisor').on('submit', function(e) {
    e.preventDefault();
    var fd = new FormData(this);
    fd.append('action', 'ruteo_negativa_guardar');
    fd.append('nonce', wpRuteoAjax.nonce);
    fd.append('etapa', 'supervisor');
    fd.append('id', negativaActual.id);
    $.ajax({ url: wpRuteoAjax.ajaxurl, type: 'POST', data: fd, processData: false, contentType: false,
        success: function(res) {
            if (res.success) { alert('Etapa Supervisor Operativo guardada.'); cargarNegativas(); renderNegativa(res.data.registro); }
            else { alert('Error: ' + res.data.message); }
        }
    });
});

$('#btn-negativa-firmar-simple').on('click', function() {
    var etapa = $(this).data('etapa');
    $.post(wpRuteoAjax.ajaxurl, { action: 'ruteo_negativa_guardar', nonce: wpRuteoAjax.nonce, etapa: etapa, id: negativaActual.id }, function(res) {
        if (res.success) { alert('Firmado correctamente.'); cargarNegativas(); renderNegativa(res.data.registro); }
        else { alert('Error: ' + res.data.message); }
    });
});

if ($('.sidebar-item[data-tab="negativa"]').length) {
    $('.sidebar-item[data-tab="negativa"]').on('click', function() { cargarNegativas(); });
}

});
