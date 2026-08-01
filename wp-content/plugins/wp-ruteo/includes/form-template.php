<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$is_logged_in = is_user_logged_in();
?>

<div class="ruteo-form-card animate-fade-in">
    <div class="login-card-container ruteo-login-required-notice" id="ruteo-form-restricted-notice" style="<?php echo $is_logged_in ? 'display:none;' : ''; ?>">
        <div class="login-card-title">
            <div style="margin-bottom:10px;">
                <svg width="36" height="36" fill="none" stroke="var(--error)" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h3>Acceso Restringido</h3>
            <p>Debes iniciar sesion como Administrador o Worker para acceder al Formulario de Campo.</p>
        </div>
        <div style="text-align:center; margin-top:20px;">
            <button type="button" class="btn-secondary btn-goto-login" style="text-decoration:none; border:none; cursor:pointer;">
                Ir a Iniciar Sesion
            </button>
        </div>
    </div>

    <form id="ruteo-form" class="ruteo-form" enctype="multipart/form-data" style="<?php echo $is_logged_in ? '' : 'display:none;'; ?>">
        <div class="ruteo-header animate-slide-up">
            <p class="form-header-sub">Complete la informacion de la estructura en campo con precision para generar su ficha automatica.</p>
        </div>

        <!-- SECCION 1: FOTOGRAFIAS -->
        <div class="ruteo-form-section">
            <h3 class="form-section-title"><span class="step-badge">1</span> Fotografias de Campo</h3>
            <div class="ruteo-photos-grid animate-slide-up">
                <div class="ruteo-photo-upload" id="upload-box-1">
                    <label for="foto1" class="upload-label">
                        <svg class="upload-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span class="upload-text">Foto Principal</span>
                    </label>
                    <input type="file" id="foto1" name="foto1" accept="image/*" required>
                    <div class="preview" id="preview1">
                        <button type="button" class="btn-remove-photo" data-input="foto1" data-preview="preview1" title="Quitar foto">&times;</button>
                    </div>
                </div>
                
                <div class="ruteo-photo-upload" id="upload-box-2">
                    <label for="foto2" class="upload-label">
                        <svg class="upload-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span class="upload-text">Foto Secundaria</span>
                    </label>
                    <input type="file" id="foto2" name="foto2" accept="image/*" required>
                    <div class="preview" id="preview2">
                        <button type="button" class="btn-remove-photo" data-input="foto2" data-preview="preview2" title="Quitar foto">&times;</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECCION 2: IDENTIFICACION -->
        <div class="ruteo-form-section">
            <h3 class="form-section-title"><span class="step-badge">2</span> Identificacion y Ubicacion</h3>
            <div class="ruteo-fields-grid">
                <div class="form-group">
                    <label>Tramo</label>
                    <div class="input-wrapper">
                        <input type="text" name="tramo" placeholder="Ej: Tramo A, Tramo Sur..." list="tramos_list" required>
                        <datalist id="tramos_list">
                            <option value="Tramo A">
                            <option value="Tramo B">
                            <option value="Tramo C">
                        </datalist>
                    </div>
                </div>

                <div class="form-group">
                    <label>ID Consol</label>
                    <div class="input-wrapper">
                        <input type="text" name="id_consol" placeholder="Ej: CON-001" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Codigo Estructura</label>
                    <div class="input-wrapper">
                        <input type="text" name="codigo" placeholder="Ej: EST-405" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Ubicacion / Coordenadas</label>
                    <div class="input-wrapper">
                        <input type="text" name="ubicacion" placeholder="Ej: -16.388, -71.536" required>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECCION 3: ESPECIFICACIONES -->
        <div class="ruteo-form-section">
            <h3 class="form-section-title"><span class="step-badge">3</span> Especificaciones Tecnicas</h3>
            <div class="ruteo-fields-grid">
                <div class="form-group">
                    <label>Estructura</label>
                    <div class="input-wrapper">
                        <select name="estructura" required>
                            <option value="">Seleccione...</option>
                            <option value="Poste">Poste</option>
                            <option value="Torre">Torre</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Tipo de Estructura</label>
                    <div class="input-wrapper">
                        <select name="tipo_estructura" required>
                            <option value="">Seleccione...</option>
                            <option value="Metal">Metal</option>
                            <option value="Concreto">Concreto</option>
                            <option value="Fibra de Vidrio">Fibra de Vidrio</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Altura (m)</label>
                    <div class="input-wrapper">
                        <input type="number" name="altura_estructura" placeholder="Ej: 8" step="0.1" list="alturas_list" required>
                        <datalist id="alturas_list">
                            <option value="7">
                            <option value="8">
                            <option value="9">
                            <option value="11">
                            <option value="12">
                        </datalist>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECCION 4: HERRAJES -->
        <div class="ruteo-form-section">
            <h3 class="form-section-title"><span class="step-badge">4</span> Herrajes y Componentes</h3>
            <div class="ruteo-fields-grid">
                <div class="form-group">
                    <label>Mufa</label>
                    <div class="input-wrapper">
                        <input type="number" name="mufa" min="0" placeholder="0" list="hardware_list" required>
                        <datalist id="hardware_list">
                            <option value="0">
                            <option value="1">
                            <option value="2">
                            <option value="3">
                            <option value="4">
                        </datalist>
                    </div>
                </div>

                <div class="form-group">
                    <label>Retencion</label>
                    <div class="input-wrapper">
                        <input type="number" name="retencion" min="0" placeholder="0" list="hardware_list" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Suspension</label>
                    <div class="input-wrapper">
                        <input type="number" name="suspension" min="0" placeholder="0" list="hardware_list" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Cruceta</label>
                    <div class="input-wrapper">
                        <input type="number" name="cruceta" min="0" placeholder="0" list="hardware_list" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Hebillas</label>
                    <div class="input-wrapper">
                        <input type="number" name="hebillas" min="0" placeholder="0" list="hardware_list" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Fleje Acero</label>
                    <div class="input-wrapper">
                        <input type="number" name="fleje" min="0" placeholder="0" list="hardware_list" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Amortiguador</label>
                    <div class="input-wrapper">
                        <input type="number" name="amortiguador" min="0" placeholder="0" list="hardware_list" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Brazo Extensor</label>
                    <div class="input-wrapper">
                        <input type="number" name="brazo_extensor" min="0" placeholder="0" list="hardware_list" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Kit Retenida</label>
                    <div class="input-wrapper">
                        <input type="number" name="kit_retenida" min="0" placeholder="0" list="hardware_list" required>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECCION 5: OBSERVACIONES -->
        <div class="ruteo-form-section">
            <h3 class="form-section-title"><span class="step-badge">5</span> Observacion Final</h3>
            <div class="ruteo-fields-grid">
                <div class="form-group full-width">
                    <label>Observacion Adicional</label>
                    <div class="input-wrapper">
                        <textarea name="observacion" rows="3" placeholder="Escriba cualquier detalle o condicion relevante de la estructura..."></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="ruteo-footer animate-slide-up">
            <button type="submit" class="ruteo-submit-btn">
                <span class="btn-text">Enviar Datos a Central</span>
                <svg class="btn-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                <div class="spinner"></div>
            </button>
            <div id="ruteo-message" class="ruteo-message"></div>
        </div>
    </form>
</div>
