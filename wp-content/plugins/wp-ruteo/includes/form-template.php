<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$is_logged_in = is_user_logged_in();
?>

<div class="ruteo-wrapper">
    <div class="ruteo-glass-container animate-fade-in">
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
                <h2 style="margin-top:0; font-size:24px;">Datos de Ruteo</h2>
                <p style="color:var(--text-muted); margin-bottom:24px;">Complete la informacion de la estructura en campo con precision</p>
            </div>

            <div class="ruteo-form-section">
                <h3 class="form-section-title">1. Fotografias de Campo</h3>
                <div class="ruteo-photos-grid animate-slide-up">
                    <div class="ruteo-photo-upload">
                        <label for="foto1" class="upload-label">
                            <svg class="upload-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span class="upload-text">Foto Principal</span>
                        </label>
                        <input type="file" id="foto1" name="foto1" accept="image/*" required>
                        <div class="preview" id="preview1"></div>
                    </div>
                    
                    <div class="ruteo-photo-upload">
                        <label for="foto2" class="upload-label">
                            <svg class="upload-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <span class="upload-text">Foto Secundaria</span>
                        </label>
                        <input type="file" id="foto2" name="foto2" accept="image/*" required>
                        <div class="preview" id="preview2"></div>
                    </div>
                </div>
            </div>

            <div class="ruteo-form-section">
                <h3 class="form-section-title">2. Identificacion y Ubicacion</h3>
                <div class="ruteo-fields">
                    <div class="form-group">
                        <label>Tramo</label>
                        <div class="input-wrapper">
                            <select name="tramo" required>
                                <option value="">Seleccione un tramo...</option>
                                <option value="Tramo A">Tramo A</option>
                                <option value="Tramo B">Tramo B</option>
                                <option value="Tramo C">Tramo C</option>
                            </select>
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
                        <label>Ubicacion</label>
                        <div class="input-wrapper">
                            <input type="text" name="ubicacion" placeholder="Coordenadas o direccion" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ruteo-form-section">
                <h3 class="form-section-title">3. Especificaciones Tecnicas</h3>
                <div class="ruteo-fields">
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
                            <select name="altura_estructura" required>
                                <option value="">Seleccione...</option>
                                <option value="7">7 m</option>
                                <option value="8">8 m</option>
                                <option value="9">9 m</option>
                                <option value="11">11 m</option>
                                <option value="12">12 m</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ruteo-form-section">
                <h3 class="form-section-title">4. Herrajes y Componentes</h3>
                <div class="ruteo-fields">
                    <div class="form-group">
                        <label>Mufa</label>
                        <div class="input-wrapper">
                            <select name="mufa" required>
                                <option value="">Seleccione...</option>
                                <option value="0">0</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Retencion</label>
                        <div class="input-wrapper">
                            <select name="retencion" required>
                                <option value="">Seleccione...</option>
                                <option value="0">0</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Suspension</label>
                        <div class="input-wrapper">
                            <select name="suspension" required>
                                <option value="">Seleccione...</option>
                                <option value="0">0</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Cruceta</label>
                        <div class="input-wrapper">
                            <select name="cruceta" required>
                                <option value="">Seleccione...</option>
                                <option value="0">0</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Hebillas</label>
                        <div class="input-wrapper">
                            <select name="hebillas" required>
                                <option value="">Seleccione...</option>
                                <option value="0">0</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Fleje Acero</label>
                        <div class="input-wrapper">
                            <select name="fleje" required>
                                <option value="">Seleccione...</option>
                                <option value="0">0</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Amortiguador</label>
                        <div class="input-wrapper">
                            <select name="amortiguador" required>
                                <option value="">Seleccione...</option>
                                <option value="0">0</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Brazo Extensor</label>
                        <div class="input-wrapper">
                            <select name="brazo_extensor" required>
                                <option value="">Seleccione...</option>
                                <option value="0">0</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Kit Retenida</label>
                        <div class="input-wrapper">
                            <select name="kit_retenida" required>
                                <option value="">Seleccione...</option>
                                <option value="0">0</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ruteo-form-section">
                <h3 class="form-section-title">5. Observacion Final</h3>
                <div class="ruteo-fields">
                    <div class="form-group full-width">
                        <label>Observacion Adicional</label>
                        <div class="input-wrapper">
                            <textarea name="observacion" rows="3" placeholder="Escriba cualquier detalle importante aqui..."></textarea>
                        </div>
                    </div>
                </div>
            </div>

                <div class="ruteo-footer animate-slide-up staggered-11">
                    <button type="submit" class="ruteo-submit-btn">
                        <span class="btn-text">Enviar Datos a Central</span>
                        <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        <div class="spinner"></div>
                    </button>
                    <div id="ruteo-message" class="ruteo-message"></div>
                </div>
            </form>
    </div>
</div>
