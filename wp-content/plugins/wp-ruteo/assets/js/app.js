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
            $('.ruteo-tab-protected-notice').show();
            $('.ruteo-tab-protected-content').hide();

            $('#tab-btn-usuarios').hide();
            $('#tab-btn-perfil').hide();
            $('#tab-btn-login').show();
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
                '<td><button type="button" class="btn-del-row btn-del-cliente" data-id="' + c.id + '" data-name="' + c.nombre + '">Eliminar</button></td>' +
            '</tr>';
            $tbody.append(tr);
        });

        var $selects = $('.neg-select-cliente');
        $selects.empty();
        clientes.forEach(function(c) {
            $selects.append('<option value="' + c.nombre + '">' + c.nombre + '</option>');
        });

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

    $('#form-cliente').on('submit', function(e) {
        e.preventDefault();
        var fd = new FormData();
        fd.append('action', 'ruteo_save_cliente');
        fd.append('nonce', wpRuteoAjax.nonce);
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
                    wpRuteoAjax.clientes = res.data.clientes;
                    renderTablaClientes(res.data.clientes);
                    setTimeout(function() { $msg.fadeOut(300); }, 4000);
                } else {
                    $msg.addClass('error').text(res.data.message || 'Error al guardar cliente.').fadeIn(200);
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
        $('#form-negativa-tecnico, #form-negativa-supervisor, #negativa-firma-simple, #btn-negativa-exportar-pdf').hide();

        if (!registro) {
            $('#negativa-estado-badge').text('');
            $('#neg-preview1, #neg-preview2').removeClass('show').css('background-image', 'none');
            $('#neg-foto1, #neg-foto2').val('').closest('.ruteo-photo-upload').removeClass('has-file');
            $('#form-negativa-tecnico').show();
            return;
        }

        $('#negativa-estado-badge').text(negativaEstadoLabel(registro.estado));

        var resumenHtml = '<div style="font-size:13px; line-height:1.6;">';
        resumenHtml += '<strong>Cliente:</strong> ' + (registro.cliente_nombre || 'CYMTEL') + ' | <strong>Proceso:</strong> ' + (registro.proceso || '') + ' | <strong>Lugar:</strong> ' + (registro.lugar_trabajo || '') + '<br>';
        resumenHtml += '<strong>Firmas:</strong> Tecnico: <span style="color:#0097D8;">' + (registro.firma_tecnico_user || 'Pendiente') + '</span>';
        resumenHtml += ' | Supervisor Op.: <span style="color:#0097D8;">' + (registro.firma_sup_operativo_user || 'Pendiente') + '</span>';
        resumenHtml += ' | Seguridad: <span style="color:#0097D8;">' + (registro.firma_sup_seguridad_user || 'Pendiente') + '</span>';
        resumenHtml += ' | HSE: <span style="color:#83CA16;">' + (registro.firma_hse_user || 'Pendiente') + '</span>';
        resumenHtml += '</div>';

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
            if (puedeActuar) {
                var $fs = $('#form-negativa-supervisor');
                $fs.find('select[name="cliente_nombre"]').val(registro.cliente_nombre || 'CYMTEL');
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
                $fs.find('textarea[name="acciones_correctivas"]').val(registro.acciones_correctivas || '');
                if (registro.acuerdo_inseguro) {
                    $fs.find('input[name="acuerdo_inseguro"][value="' + registro.acuerdo_inseguro + '"]').prop('checked', true);
                }
                $fs.show();
            } else {
                $('#negativa-firma-simple-texto').text('Esperando revision y firma del Supervisor Operativo.');
                $('#negativa-firma-simple').show().find('button').hide();
            }
        } else if (registro.estado === 'pendiente_seguridad') {
            if (puedeActuar) {
                $('#negativa-firma-simple-texto').text('Firmar como Supervisor de Seguridad.');
                $('#negativa-firma-simple').show().find('button').show().data('etapa', 'seguridad');
            } else {
                $('#negativa-firma-simple-texto').text('Esperando firma del Supervisor de Seguridad.');
                $('#negativa-firma-simple').show().find('button').hide();
            }
        } else if (registro.estado === 'pendiente_hse') {
            if (puedeActuar) {
                $('#negativa-firma-simple-texto').text('Otorgar Visto Bueno del Area HSE.');
                $('#negativa-firma-simple').show().find('button').show().data('etapa', 'hse');
            } else {
                $('#negativa-firma-simple-texto').text('Esperando visto bueno del Area HSE.');
                $('#negativa-firma-simple').show().find('button').hide();
            }
        } else if (registro.estado === 'completado') {
            // SOLO cuando se tengan todas las firmas completas (Firma HSE) se habilita exportar PDF
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
            alert('Por favor complete todos los campos obligatorios antes de firmar.');
            return;
        }

        var fd = new FormData(this);
        fd.append('action', 'ruteo_negativa_guardar');
        fd.append('nonce', wpRuteoAjax.nonce);
        fd.append('etapa', 'tecnico');
        $.ajax({
            url: wpRuteoAjax.ajaxurl, type: 'POST', data: fd, processData: false, contentType: false,
            success: function(res) {
                if (res.success) {
                    alert('Etapa Tecnico guardada y firmada exitosamente.');
                    cargarNegativas();
                    renderNegativa(res.data.registro);
                } else {
                    alert('Error: ' + res.data.message);
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
            alert('Por favor complete todos los datos antes de firmar como Supervisor Operativo.');
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
                    alert('Etapa Supervisor Operativo actualizada y firmada correctamente.');
                    cargarNegativas();
                    renderNegativa(res.data.registro);
                } else {
                    alert('Error: ' + res.data.message);
                }
            }
        });
    });

    $('#btn-negativa-firmar-simple').on('click', function() {
        var etapa = $(this).data('etapa');
        $.post(wpRuteoAjax.ajaxurl, { action: 'ruteo_negativa_guardar', nonce: wpRuteoAjax.nonce, etapa: etapa, id: negativaActual.id }, function(res) {
            if (res.success) {
                alert('Etapa firmada correctamente.');
                cargarNegativas();
                renderNegativa(res.data.registro);
            } else {
                alert('Error: ' + res.data.message);
            }
        });
    });

    // GENERACION DE PDF FORMATO HSE-RE-NEG-01 CON LOGO DE CYMTEL / CLIENTE Y 4 FIRMAS
    function generarPDFNegativa(r) {
        if (!r) { alert('No hay registro seleccionado.'); return; }

        var jsPDFConstructor = (window.jspdf && window.jspdf.jsPDF) ? window.jspdf.jsPDF : window.jsPDF;
        if (!jsPDFConstructor) {
            alert('Cargando libreria PDF, intente de nuevo en un instante.');
            return;
        }

        var clienteNombre = r.cliente_nombre || 'CYMTEL';
        var clienteObj = (window.wpRuteoAjax && window.wpRuteoAjax.clientes) ? window.wpRuteoAjax.clientes.find(function(c) { return c.nombre === clienteNombre; }) : null;
        var clienteLogo = r.cliente_logo || (clienteObj ? clienteObj.logo : '');

        var doc = new jsPDFConstructor({ orientation: 'portrait', unit: 'mm', format: 'a4' });
        var pageW = doc.internal.pageSize.getWidth();
        var pageH = doc.internal.pageSize.getHeight();

        // Cabecera principal azul
        doc.setFillColor(0, 151, 216);
        doc.rect(0, 0, pageW, 26, 'F');
        doc.setFillColor(0, 90, 140);
        doc.rect(0, 0, 6, 26, 'F');

        doc.setTextColor(255, 255, 255);
        doc.setFontSize(14);
        doc.setFont('helvetica', 'bold');
        doc.text('SOFTWARE O&M', 14, 11);

        doc.setFontSize(9);
        doc.setFont('helvetica', 'normal');
        doc.text('FORMATO DE NEGATIVA AL TRABAJO POR RIESGO INMINENTE (HSE-RE-NEG-01)', 14, 19);

        // Cliente y Logo en derecha
        doc.setFontSize(11);
        doc.setFont('helvetica', 'bold');
        doc.text('CLIENTE: ' + clienteNombre.toUpperCase(), pageW - 14, 14, { align: 'right' });

        if (clienteLogo) {
            try {
                var mime = clienteLogo.indexOf('image/png') !== -1 ? 'PNG' : 'JPEG';
                doc.addImage(clienteLogo, mime, pageW - 48, 2, 34, 11);
            } catch(e) {}
        }

        var y = 32;

        // Seccion 1: Datos Generales
        doc.setFillColor(239, 246, 255);
        doc.rect(14, y, pageW - 28, 6, 'F');
        doc.setFontSize(9); doc.setFont('helvetica', 'bold'); doc.setTextColor(0, 80, 130);
        doc.text('1. DATOS GENERALES', 16, y + 4.5);
        y += 8;

        var datosGen = [
            ['Proceso:', r.proceso || '-', 'CM / Localidad:', r.cm_localidad || '-'],
            ['Contratista:', r.contratista || '-', 'Sub Contratista:', r.sub_contratista || '-'],
            ['Relacionado a:', r.relacionado_a || '-', 'Lugar de Trabajo:', r.lugar_trabajo || '-'],
            ['Fecha:', r.fecha || '-', 'Horario:', (r.hora_inicio || '-') + ' a ' + (r.hora_fin || '-')],
            ['Total Horas:', r.total_horas || '-', 'Cliente Principal:', clienteNombre]
        ];

        doc.autoTable({
            startY: y,
            body: datosGen,
            theme: 'grid',
            styles: { fontSize: 8, cellPadding: 2 },
            columnStyles: {
                0: { fontStyle: 'bold', cellWidth: 32, fillColor: [248, 250, 252] },
                1: { cellWidth: 58 },
                2: { fontStyle: 'bold', cellWidth: 32, fillColor: [248, 250, 252] },
                3: { cellWidth: 'auto' }
            },
            margin: { left: 14, right: 14 }
        });
        y = doc.lastAutoTable.finalY + 6;

        // Seccion 2: Investigacion
        doc.setFillColor(239, 246, 255);
        doc.rect(14, y, pageW - 28, 6, 'F');
        doc.setFontSize(9); doc.setFont('helvetica', 'bold'); doc.setTextColor(0, 80, 130);
        doc.text('2. INVESTIGACION DEL SUPERVISOR OPERATIVO', 16, y + 4.5);
        y += 8;

        doc.autoTable({
            startY: y,
            body: [
                ['Supervisor Operativo:', r.supervisor_operativo_nombre || '-', 'Trabajador Reportante:', r.trabajador_reportante || '-']
            ],
            theme: 'grid',
            styles: { fontSize: 8, cellPadding: 2.5 },
            columnStyles: {
                0: { fontStyle: 'bold', cellWidth: 36, fillColor: [248, 250, 252] },
                1: { cellWidth: 54 },
                2: { fontStyle: 'bold', cellWidth: 36, fillColor: [248, 250, 252] },
                3: { cellWidth: 'auto' }
            },
            margin: { left: 14, right: 14 }
        });
        y = doc.lastAutoTable.finalY + 6;

        // Seccion 3: Razones para la Negativa
        doc.setFillColor(239, 246, 255);
        doc.rect(14, y, pageW - 28, 6, 'F');
        doc.setFontSize(9); doc.setFont('helvetica', 'bold'); doc.setTextColor(0, 80, 130);
        doc.text('3. RAZONES PARA LA NEGATIVA (CONDICIONES ADVERSAS / BASE LEGAL)', 16, y + 4.5);
        y += 8;

        doc.autoTable({
            startY: y,
            body: [ [ r.razones_negativa || '-' ] ],
            theme: 'grid',
            styles: { fontSize: 8, cellPadding: 3 },
            margin: { left: 14, right: 14 }
        });
        y = doc.lastAutoTable.finalY + 6;

        // Seccion 4: Acciones Correctivas y Acuerdo
        doc.setFillColor(239, 246, 255);
        doc.rect(14, y, pageW - 28, 6, 'F');
        doc.setFontSize(9); doc.setFont('helvetica', 'bold'); doc.setTextColor(0, 80, 130);
        doc.text('4. ACCIONES CORRECTIVAS Y ACUERDO DE CONDICIONES', 16, y + 4.5);
        y += 8;

        doc.autoTable({
            startY: y,
            body: [
                ['Acciones Correctivas:', r.acciones_correctivas || '-'],
                ['Acuerdo de Condiciones Inseguras:', (r.acuerdo_inseguro || 'NO') === 'SI' ? 'SI - Se acuerdo corregir la condicion antes de reiniciar.' : 'NO - No hay acuerdo de reinicio.']
            ],
            theme: 'grid',
            styles: { fontSize: 8, cellPadding: 2.5 },
            columnStyles: {
                0: { fontStyle: 'bold', cellWidth: 50, fillColor: [248, 250, 252] },
                1: { cellWidth: 'auto' }
            },
            margin: { left: 14, right: 14 }
        });
        y = doc.lastAutoTable.finalY + 8;

        // Seccion 5: Cuadro de Firmas (4 Firmas Digitales)
        doc.setFontSize(9); doc.setFont('helvetica', 'bold'); doc.setTextColor(0, 151, 216);
        doc.text('REGISTRO DE FIRMAS Y VALIDADORES', 14, y);
        y += 4;

        var colW = (pageW - 28) / 4;
        var boxH = 26;

        var firmas = [
            { titulo: 'TECNICO REPORTANTE', user: r.firma_tecnico_user, fecha: r.firma_tecnico_fecha },
            { titulo: 'SUPERVISOR OPERATIVO', user: r.firma_sup_operativo_user, fecha: r.firma_sup_operativo_fecha },
            { titulo: 'SUPERVISOR SEGURIDAD', user: r.firma_sup_seguridad_user, fecha: r.firma_sup_seguridad_fecha },
            { titulo: 'VISTO BUENO HSE', user: r.firma_hse_user, fecha: r.firma_hse_fecha }
        ];

        firmas.forEach(function(f, i) {
            var xBox = 14 + (i * colW);
            doc.setFillColor(255, 255, 255);
            doc.setDrawColor(200, 210, 225);
            doc.rect(xBox, y, colW - 2, boxH, 'FD');

            doc.setFillColor(0, 151, 216);
            doc.rect(xBox, y, colW - 2, 5, 'F');
            doc.setFontSize(6.5); doc.setFont('helvetica', 'bold'); doc.setTextColor(255, 255, 255);
            doc.text(f.titulo, xBox + (colW - 2)/2, y + 3.5, { align: 'center' });

            if (f.user) {
                doc.setFontSize(7.5); doc.setFont('helvetica', 'bold'); doc.setTextColor(15, 23, 42);
                doc.text(f.user, xBox + (colW - 2)/2, y + 13, { align: 'center' });
                doc.setFontSize(6.5); doc.setFont('helvetica', 'normal'); doc.setTextColor(100, 116, 139);
                doc.text('Fecha: ' + (f.fecha || '-'), xBox + (colW - 2)/2, y + 19, { align: 'center' });
                doc.setFontSize(6); doc.setTextColor(131, 202, 22);
                doc.text('[FIRMA DIGITAL CONFIRMADA]', xBox + (colW - 2)/2, y + 23, { align: 'center' });
            } else {
                doc.setFontSize(7); doc.setFont('helvetica', 'italic'); doc.setTextColor(165, 172, 184);
                doc.text('Pendiente de Firma', xBox + (colW - 2)/2, y + 16, { align: 'center' });
            }
        });

        // Pie de pagina
        doc.setFontSize(7); doc.setTextColor(148, 163, 184);
        doc.line(14, pageH - 8, pageW - 14, pageH - 8);
        doc.text('Software O&M  -  Formatos Oficiales  -  Cliente: ' + clienteNombre, 14, pageH - 4);
        doc.text('Pagina 1 de 1', pageW - 14, pageH - 4, { align: 'right' });

        doc.save('Negativa_' + (r.id || 'HSE') + '_' + (r.proceso || 'Trabajo').replace(/ /g, '_') + '.pdf');
    }

    $('#btn-negativa-exportar-pdf').on('click', function() {
        generarPDFNegativa(negativaActual);
    });

    if ($('.sidebar-item[data-tab="negativa"]').length) {
        $('.sidebar-item[data-tab="negativa"]').on('click', function() { cargarNegativas(); });
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
                '<a href="javascript:void(0)" onclick="window.generarDocumentoPDF(' + idx + ')" title="Descargar PDF" class="portal-link portal-link--red" style="margin-right:4px; padding:4px 8px;"><svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg> PDF</a>' +
                '<a href="javascript:void(0)" onclick="window.abrirODocumentoGoogleDocs(' + idx + ')" title="Abrir Google Doc en Drive" class="portal-link portal-link--blue" style="padding:4px 8px;"><svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg> Doc Drive</a>' +
                '</td>';
            tbody.appendChild(tr);
        });
    }

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
        var textoBusqueda = document.getElementById('portal-search') ? document.getElementById('portal-search').value.toLowerCase().trim() : '';

        var allRegistros = window._ruteoRegistros || [];
        var filtrados = allRegistros.filter(function(raw) {
            var r = normalizarRegistro(raw);
            if (tramoFiltro && r.tramo !== tramoFiltro) return false;
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
                if (error) error.style.display = 'flex';
            }
        }).fail(function() {
            isFetchingPortal = false;
            if (loader) loader.style.display = 'none';
            if (error) error.style.display = 'flex';
        });
    }

    window.cargarDatosPortal = cargarDatosPortal;

    $('#portal-search').on('input', filtrarRegistros);
    $('#filter-tramo').on('change', filtrarRegistros);
    $('#btn-refresh-portal').on('click', function() { cargarDatosPortal(false); });

    if (currentUser.isLoggedIn) {
        cargarDatosPortal(false);
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

    window.generarDocumentoPDF = function(idx) {
        var raw = window._ruteoRegistros ? window._ruteoRegistros[idx] : null; if (!raw) return;
        var r = normalizarRegistro(raw);
        var jsPDFConstructor = (window.jspdf && window.jspdf.jsPDF) ? window.jspdf.jsPDF : window.jsPDF;
        if (!jsPDFConstructor) {
            alert('Libreria PDF no disponible.');
            return;
        }
        var doc = new jsPDFConstructor({ orientation: 'portrait', unit: 'mm', format: 'a4' });
        var w = doc.internal.pageSize.getWidth();
        doc.setFillColor(0, 151, 216); doc.rect(0,0,w,28,'F'); doc.setTextColor(255,255,255);
        doc.setFontSize(16); doc.text('FICHA TECNICA DE REGISTRO', w/2, 13, { align: 'center' });
        doc.setFontSize(9); doc.text('Fecha: ' + r.fecha + ' | Tramo: ' + r.tramo, w/2, 21, { align: 'center' });
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
        if (typeof doc.autoTable === 'function') {
            doc.autoTable({ startY: 34, body: data, theme: 'grid', headStyles: { fillColor: [0, 151, 216] }, bodyStyles: { fontSize: 8.5, cellPadding: 2.5 }, margin: { left: 12, right: 12 } });
        }
        doc.save('Ficha_Ruteo_' + (r.codigo || r.id_consol || 'Registro') + '.pdf');
    };

});


