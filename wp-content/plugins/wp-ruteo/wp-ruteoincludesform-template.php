<div class="ruteo-container">
    <form id="ruteo-form" class="ruteo-form" enctype="multipart/form-data">
        
        <div class="ruteo-header">
            <h2>Datos de Ruteo</h2>
            <p>Complete la informacion de la estructura en campo</p>
        </div>

        <div class="ruteo-photos-grid">
            <div class="ruteo-photo-upload">
                <label for="foto1" class="upload-label">
                    <span class="upload-icon">📷</span>
                    <span class="upload-text">Subir Foto 1</span>
                </label>
                <input type="file" id="foto1" name="foto1" accept="image/*" required>
                <div class="preview" id="preview1"></div>
            </div>
            
            <div class="ruteo-photo-upload">
                <label for="foto2" class="upload-label">
                    <span class="upload-icon">📷</span>
                    <span class="upload-text">Subir Foto 2</span>
                </label>
                <input type="file" id="foto2" name="foto2" accept="image/*" required>
                <div class="preview" id="preview2"></div>
            </div>
        </div>

        <div class="ruteo-fields">
            <div class="form-group">
                <label>Tramo</label>
                <select name="tramo" required>
                    <option value="">Seleccione un tramo...</option>
                    <option value="Tramo A">Tramo A</option>
                    <option value="Tramo B">Tramo B</option>
                    <option value="Tramo C">Tramo C</option>
                </select>
            </div>

            <div class="form-group">
                <label>ID Consol</label>
                <input type="text" name="id_consol" placeholder="Digite el ID Consol" required>
            </div>

            <div class="form-group">
                <label>Estructura</label>
                <select name="estructura" required>
                    <option value="">Seleccione...</option>
                    <option value="Poste">Poste</option>
                    <option value="Torre">Torre</option>
                </select>
            </div>

            <div class="form-group">
                <label>Tipo de Estructura</label>
                <select name="tipo_estructura" required>
                    <option value="">Seleccione...</option>
                    <option value="Metal">Metal</option>
                    <option value="Concreto">Concreto</option>
                    <option value="Fibra de Vidrio">Fibra de Vidrio</option>
                </select>
            </div>

            <div class="form-group">
                <label>Altura de Estructura (m)</label>
                <select name="altura_estructura" required>
                    <option value="">Seleccione...</option>
                    <option value="7">7</option>
                    <option value="8">8</option>
                    <option value="9">9</option>
                    <option value="11">11</option>
                    <option value="12">12</option>
                </select>
            </div>

            <div class="form-group">
                <label>Ubicacion Estructura</label>
                <input type="text" name="ubicacion" placeholder="Digite la ubicacion" required>
            </div>

            <div class="form-group">
                <label>Codigo Estructura</label>
                <input type="text" name="codigo" placeholder="Digite el codigo" required>
            </div>

            <div class="form-group">
                <label>Mufa</label>
                <select name="mufa" required>
                    <option value="">Seleccione...</option>
                    <option value="0">0</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                </select>
            </div>

            <div class="form-group">
                <label>Herraje de Retencion</label>
                <select name="retencion" required>
                    <option value="">Seleccione...</option>
                    <option value="0">0</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                </select>
            </div>

            <div class="form-group">
                <label>Herraje de Suspension</label>
                <select name="suspension" required>
                    <option value="">Seleccione...</option>
                    <option value="0">0</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                </select>
            </div>

            <div class="form-group">
                <label>Cruceta</label>
                <select name="cruceta" required>
                    <option value="">Seleccione...</option>
                    <option value="0">0</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                </select>
            </div>

            <div class="form-group">
                <label>Hebillas</label>
                <select name="hebillas" required>
                    <option value="">Seleccione...</option>
                    <option value="0">0</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                </select>
            </div>

            <div class="form-group">
                <label>Fleje de Acero</label>
                <select name="fleje" required>
                    <option value="">Seleccione...</option>
                    <option value="0">0</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                </select>
            </div>

            <div class="form-group">
                <label>Amortiguador</label>
                <select name="amortiguador" required>
                    <option value="">Seleccione...</option>
                    <option value="0">0</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                </select>
            </div>

            <div class="form-group">
                <label>Brazo Extensor</label>
                <select name="brazo_extensor" required>
                    <option value="">Seleccione...</option>
                    <option value="0">0</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                </select>
            </div>

            <div class="form-group">
                <label>Kit de Retenida</label>
                <select name="kit_retenida" required>
                    <option value="">Seleccione...</option>
                    <option value="0">0</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                </select>
            </div>

            <div class="form-group full-width">
                <label>Observacion</label>
                <textarea name="observacion" rows="3" placeholder="Digite la observacion..."></textarea>
            </div>
        </div>

        <div class="ruteo-footer">
            <button type="submit" class="ruteo-submit-btn">
                <span>Enviar Datos</span>
                <div class="spinner"></div>
            </button>
            <div id="ruteo-message" class="ruteo-message"></div>
        </div>
    </form>
</div>
