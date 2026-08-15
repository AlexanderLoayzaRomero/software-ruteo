// ---------------------------------------------------------------
<<<<<<< HEAD
// GET: Devuelve registros de ruteo o materiales del sheet como JSON
=======
// GET: Devuelve registros de ruteo, materiales o negativas del sheet como JSON
>>>>>>> origin/master
// Usado por el Aplicativo de Ruteo en WordPress
// ---------------------------------------------------------------
function doGet(e) {
  try {
    var ss = SpreadsheetApp.getActiveSpreadsheet();
    
<<<<<<< HEAD
    // Si solicitan materiales via doGet ?action=get_materiales
=======
    // 1. Si solicitan negativas via doGet ?action=get_negativas
    if (e && e.parameter && e.parameter.action === 'get_negativas') {
      var negSheet = ss.getSheetByName('Negativas');
      if (!negSheet) {
        return outputResponse({ status: 'success', negativas: [], total: 0 }, e);
      }
      var negData = negSheet.getDataRange().getValues();
      if (negData.length <= 1) {
        return outputResponse({ status: 'success', negativas: [], total: 0 }, e);
      }
      var negHeaders = negData[0];
      var negativas = [];
      for (var k = 1; k < negData.length; k++) {
        var nRow = {};
        for (var l = 0; l < negHeaders.length; l++) {
          var nKey = negHeaders[l].toString().toLowerCase().replace(/ /g, '_').replace(/[^a-z0-9_]/g, '');
          var nVal = negData[k][l];
          if (nVal instanceof Date) {
            nVal = Utilities.formatDate(nVal, Session.getScriptTimeZone(), 'dd/MM/yyyy HH:mm');
          }
          nRow[nKey] = nVal !== undefined && nVal !== null ? nVal.toString() : '';
        }
        negativas.push(nRow);
      }
      negativas.reverse();
      return outputResponse({ status: 'success', negativas: negativas, total: negativas.length }, e);
    }

    // 2. Si solicitan materiales via doGet ?action=get_materiales
>>>>>>> origin/master
    if (e && e.parameter && e.parameter.action === 'get_materiales') {
      var matSheet = ss.getSheetByName('Materiales');
      if (!matSheet) {
        return outputResponse({ status: 'success', materiales: [], total: 0 }, e);
      }
      var matData = matSheet.getDataRange().getValues();
      if (matData.length <= 1) {
        return outputResponse({ status: 'success', materiales: [], total: 0 }, e);
      }
      var matHeaders = matData[0];
      var materiales = [];
      for (var m = 1; m < matData.length; m++) {
        var mRow = {};
        for (var n = 0; n < matHeaders.length; n++) {
          var mKey = matHeaders[n].toString().toLowerCase().replace(/ /g, '_').replace(/[^a-z0-9_]/g, '');
          var mVal = matData[m][n];
          if (mVal instanceof Date) {
            mVal = Utilities.formatDate(mVal, Session.getScriptTimeZone(), 'dd/MM/yyyy HH:mm');
          }
          mRow[mKey] = mVal !== undefined && mVal !== null ? mVal.toString() : '';
        }
        materiales.push(mRow);
      }
      materiales.reverse();
      return outputResponse({ status: 'success', materiales: materiales, total: materiales.length }, e);
    }

<<<<<<< HEAD
    // Por defecto: devuelve registros de ruteo de campo
=======
    // 3. Por defecto: devuelve registros de ruteo de campo
>>>>>>> origin/master
    var sheet = ss.getActiveSheet();
    var data  = sheet.getDataRange().getValues();

    if (data.length <= 1) {
      var result = { status: 'success', registros: [], total: 0 };
      return outputResponse(result, e);
    }

    var headers  = data[0];
    var registros = [];

    for (var i = 1; i < data.length; i++) {
      var row = {};
      for (var j = 0; j < headers.length; j++) {
        var key = headers[j].toString().toLowerCase()
                    .replace(/ /g, '_')
                    .replace(/[^a-z0-9_]/g, '');
        var val = data[i][j];
        if (val instanceof Date) {
          val = Utilities.formatDate(val, Session.getScriptTimeZone(), 'dd/MM/yyyy HH:mm');
        }
        row[key] = val !== undefined && val !== null ? val.toString() : '';
      }
      registros.push(row);
    }

    registros.reverse();

    var result = { status: 'success', registros: registros, total: registros.length };
    return outputResponse(result, e);

  } catch (error) {
    return outputResponse({ status: 'error', message: error.toString() }, e);
  }
}

function outputResponse(obj, e) {
  var json = JSON.stringify(obj);
  if (e && e.parameter && e.parameter.callback) {
    return ContentService
      .createTextOutput(e.parameter.callback + '(' + json + ')')
      .setMimeType(ContentService.MimeType.JAVASCRIPT);
  }
  return ContentService
    .createTextOutput(json)
    .setMimeType(ContentService.MimeType.JSON);
}

function buildResponse(obj) {
  return ContentService
    .createTextOutput(JSON.stringify(obj))
    .setMimeType(ContentService.MimeType.JSON);
}

function doPost(e) {
  try {
    var FOLDER_ID = '1e9qvf_OKyqzCTxzhs8cF0E3t61UVlRXO';

    var data = JSON.parse(e.postData.contents);
<<<<<<< HEAD
    
    // Si la peticion es para crear/sincronizar documento Google Docs en Drive
=======

    // 1. Peticion para guardar / registrar / actualizar Negativa al Trabajo en Google Sheets y Drive
    if (data.action_type === 'save_negativa' || data.action_type === 'save_negativa_drive' || data.document_type === 'negativa_hse_re_neg_01') {
      var ss = SpreadsheetApp.getActiveSpreadsheet();
      var negSheet = null;
      var sheets = ss.getSheets();
      for (var i = 0; i < sheets.length; i++) {
        if (sheets[i].getName().toLowerCase() === 'negativas') {
          negSheet = sheets[i];
          break;
        }
      }
      
      if (!negSheet) {
        try {
          negSheet = ss.insertSheet('Negativas');
        } catch (e) {
          negSheet = sheets[0]; 
        }
      }

      if (negSheet.getLastRow() === 0) {
        negSheet.appendRow([
          "ID", "Fecha Registro", "Estado", "Cliente", "Proceso / Proyecto", "CM / Localidad",
          "Contratista", "Sub Contratista", "Relacionado A", "Lugar de Trabajo", "Fecha",
          "Hora Inicio", "Hora Fin", "Total Horas", "Supervisor Operativo", "Trabajador Reportante",
          "Razones Negativa", "Medidas Correctivas", "Satisface Negativa", "Reinicia Labores",
          "Fecha Reinicio", "Hora Reinicio", "Supervisor Seguridad", "Observaciones Seguridad",
          "Dictamen HSE", "Firma Tecnico", "Firma Sup. Operativo", "Firma Sup. Seguridad",
          "Firma HSE", "Link Google Drive"
        ]);
      }

      var neg = data.negativa || data || {};

      // Generar documento oficial Google Doc / PDF para Negativas en Google Drive
      var docUrl = generarGoogleDocNegativa(neg, FOLDER_ID);

      var rowValues = [
        neg.id || new Date().getTime(),
        neg.created_at || Utilities.formatDate(new Date(), Session.getScriptTimeZone(), 'dd/MM/yyyy HH:mm'),
        neg.estado || 'pendiente_tecnico',
        neg.cliente_nombre || 'CYMTEL',
        neg.proceso || '',
        neg.cm_localidad || '',
        neg.contratista || '',
        neg.sub_contratista || '',
        neg.relacionado_a || '',
        neg.lugar_trabajo || '',
        neg.fecha || '',
        neg.hora_inicio || '',
        neg.hora_fin || '',
        neg.total_horas || '',
        neg.supervisor_operativo_nombre || '',
        neg.trabajador_reportante || '',
        neg.razones_negativa || '',
        neg.medidas_correctivas || '',
        neg.satisface_negativa || '',
        neg.reinicia_labores || '',
        neg.fecha_reinicio || '',
        neg.hora_reinicio || '',
        neg.supervisor_seguridad_nombre || '',
        neg.observaciones_seguridad || '',
        neg.dictamen_hse || '',
        neg.firma_tecnico_user || '',
        neg.firma_sup_operativo_user || '',
        neg.firma_sup_seguridad_user || '',
        neg.firma_hse_user || '',
        docUrl
      ];

      // Buscar si ya existe la fila por ID (Columna A)
      var targetId = String(neg.id || '').trim();
      var dataRows = negSheet.getDataRange().getValues();
      var foundIndex = -1;

      if (targetId && dataRows.length > 1) {
        for (var r = 1; r < dataRows.length; r++) {
          if (String(dataRows[r][0] || '').trim() === targetId) {
            foundIndex = r + 1;
            break;
          }
        }
      }

      if (foundIndex >= 2) {
        for (var c = 0; c < rowValues.length; c++) {
          negSheet.getRange(foundIndex, c + 1).setValue(rowValues[c]);
        }
      } else {
        negSheet.appendRow(rowValues);
      }

      return ContentService.createTextOutput(JSON.stringify({
        "status": "success",
        "drive_url": docUrl,
        "doc_url": docUrl,
        "message": "Negativa id #" + (neg.id || '') + " procesada en pestaña 'Negativas' de Google Sheets."
      })).setMimeType(ContentService.MimeType.JSON);
    }

    // 1b. Peticion masiva para sincronizar/poblar la lista completa de Negativas en Google Sheets
    if (data.action_type === 'sync_all_negativas') {
      var ss = SpreadsheetApp.getActiveSpreadsheet();
      var negSheet = null;
      var sheets = ss.getSheets();
      for (var i = 0; i < sheets.length; i++) {
        if (sheets[i].getName().toLowerCase() === 'negativas') {
          negSheet = sheets[i];
          break;
        }
      }
      
      if (!negSheet) {
        try {
          negSheet = ss.insertSheet('Negativas');
        } catch (e) {
          negSheet = sheets[0];
        }
      }

      // Limpiar datos previos manteniendo encabezados
      negSheet.clearContents();
      negSheet.appendRow([
        "ID", "Fecha Registro", "Estado", "Cliente", "Proceso / Proyecto", "CM / Localidad",
        "Contratista", "Sub Contratista", "Relacionado A", "Lugar de Trabajo", "Fecha",
        "Hora Inicio", "Hora Fin", "Total Horas", "Supervisor Operativo", "Trabajador Reportante",
        "Razones Negativa", "Medidas Correctivas", "Satisface Negativa", "Reinicia Labores",
        "Fecha Reinicio", "Hora Reinicio", "Supervisor Seguridad", "Observaciones Seguridad",
        "Dictamen HSE", "Firma Tecnico", "Firma Sup. Operativo", "Firma Sup. Seguridad",
        "Firma HSE", "Link Google Drive"
      ]);

      var meList = data.negativas || [];
      var count = 0;

      for (var nIdx = 0; nIdx < meList.length; nIdx++) {
        var negItem = meList[nIdx];
        negSheet.appendRow([
          negItem.id || (nIdx + 1),
          negItem.created_at || Utilities.formatDate(new Date(), Session.getScriptTimeZone(), 'dd/MM/yyyy HH:mm'),
          negItem.estado || 'pendiente_tecnico',
          negItem.cliente_nombre || 'CYMTEL',
          negItem.proceso || '',
          negItem.cm_localidad || '',
          negItem.contratista || '',
          negItem.sub_contratista || '',
          negItem.relacionado_a || '',
          negItem.lugar_trabajo || '',
          negItem.fecha || '',
          negItem.hora_inicio || '',
          negItem.hora_fin || '',
          negItem.total_horas || '',
          negItem.supervisor_operativo_nombre || '',
          negItem.trabajador_reportante || '',
          negItem.razones_negativa || '',
          negItem.medidas_correctivas || '',
          negItem.satisface_negativa || '',
          negItem.reinicia_labores || '',
          negItem.fecha_reinicio || '',
          negItem.hora_reinicio || '',
          negItem.supervisor_seguridad_nombre || '',
          negItem.observaciones_seguridad || '',
          negItem.dictamen_hse || '',
          negItem.firma_tecnico_user || '',
          negItem.firma_sup_operativo_user || '',
          negItem.firma_sup_seguridad_user || '',
          negItem.firma_hse_user || '',
          negItem.doc_url || negItem.drive_url || ''
        ]);
        count++;
      }

      return ContentService.createTextOutput(JSON.stringify({
        "status": "success",
        "message": "Se sincronizaron " + count + " negativas en la pestaña 'Negativas'."
      })).setMimeType(ContentService.MimeType.JSON);
    }
    
    // 2. Si la peticion es para crear/sincronizar documento Google Docs en Drive para Ruteo
>>>>>>> origin/master
    if (data.action_type === 'create_doc') {
      var docUrl = generarGoogleDoc(data.record || data, FOLDER_ID);
      return ContentService.createTextOutput(JSON.stringify({"status": "success", "doc_url": docUrl}))
        .setMimeType(ContentService.MimeType.JSON);
    }

<<<<<<< HEAD
    // Si la peticion es para guardar reporte de materiales
=======
    // 3. Si la peticion es para guardar reporte de materiales
>>>>>>> origin/master
    if (data.action_type === 'save_materiales') {
      var ss = SpreadsheetApp.getActiveSpreadsheet();
      var matSheet = ss.getSheetByName('Materiales');
      if (!matSheet) {
        matSheet = ss.insertSheet('Materiales');
        matSheet.appendRow(["Fecha", "Incidencia", "CRQ", "Almacen PM", "Tramo", "Descripcion", "Materiales", "Usuario"]);
      }
      
      var rep = data.report || {};
      var itemsStr = (rep.items || []).map(function(it) {
        return it.cantidad + ' ' + it.unidad + ' ' + it.descripcion + (it.codigo_sap ? ' [' + it.codigo_sap + ']' : '');
      }).join('; ');
      
      matSheet.appendRow([
        rep.fecha || new Date(),
        rep.incidencia || '',
        rep.crq || '',
        rep.almacen_pm || '',
        rep.tramo || '',
        rep.descripcion || '',
        itemsStr,
        rep.user || ''
      ]);
      
      return ContentService.createTextOutput(JSON.stringify({"status": "success", "message": "Reporte de materiales guardado en Google Sheets"}))
        .setMimeType(ContentService.MimeType.JSON);
    }
    
<<<<<<< HEAD
    // Si la peticion es para subir un documento generado (Word)
    if (data.action_type === 'save_document') {
      var folder = DriveApp.getFolderById(FOLDER_ID);
      var decoded = Utilities.base64Decode(data.file_base64);
      var blob = Utilities.newBlob(decoded, data.mimeType, data.filename);
      var file = folder.createFile(blob);
      file.setSharing(DriveApp.Access.ANYONE_WITH_LINK, DriveApp.Permission.VIEW);
      return ContentService.createTextOutput(JSON.stringify({"status": "success", "url": file.getUrl()}))
        .setMimeType(ContentService.MimeType.JSON);
    }
    
    // Sino, es la subida normal del formulario de campo
    var sheet = SpreadsheetApp.getActiveSpreadsheet().getActiveSheet();
    
=======
    // 4. Si la peticion es para actualizar/editar un Registro de Ruteo existente
    if (data.action_type === 'update_registro') {
      var ss = SpreadsheetApp.getActiveSpreadsheet();
      var sheet = ss.getSheetByName('Ruteo');
      if (!sheet) {
        var allSheets = ss.getSheets();
        for (var i = 0; i < allSheets.length; i++) {
          var s = allSheets[i];
          var sName = s.getName().toLowerCase();
          if (sName !== 'negativas' && sName !== 'materiales') {
            var lastCol = s.getLastColumn();
            if (lastCol > 0) {
              var firstRow = s.getRange(1, 1, 1, Math.min(10, lastCol)).getValues()[0];
              if ((firstRow.length > 2 && firstRow[2] && String(firstRow[2]).toLowerCase().indexOf('id consol') !== -1) || 
                  (firstRow.length > 7 && firstRow[7] && String(firstRow[7]).toLowerCase().indexOf('codigo') !== -1)) {
                sheet = s;
                break;
              }
            }
          }
        }
      }
      if (!sheet) sheet = ss.getSheets()[0]; // Fallback final

      var dataRows = sheet.getDataRange().getValues();
      var rec = data.record || data.registro || data || {};
      var targetRowIndex = -1;

      var targetIdConsol = String(rec.id_consol || rec.id || '').trim().toLowerCase();
      var targetCodigo   = String(rec.codigo || '').trim().toLowerCase();

      // Buscar por ID Consol o Codigo en la hoja de Google Sheets
      for (var r = 1; r < dataRows.length; r++) {
        var rowIdConsol = String(dataRows[r][2] || '').trim().toLowerCase(); // Col C: ID Consol
        var rowCodigo   = String(dataRows[r][7] || '').trim().toLowerCase(); // Col H: Codigo

        if (targetIdConsol && rowIdConsol === targetIdConsol) {
          targetRowIndex = r + 1;
          break;
        }
        if (targetCodigo && rowCodigo === targetCodigo) {
          targetRowIndex = r + 1;
          break;
        }
      }

      // Si no se encontro por ID ni Codigo, calcular el indice considerando el orden invertido
      if (targetRowIndex === -1 && rec.rowIndex) {
        var totalRows = dataRows.length - 1; // sin encabezado
        var realIdx = (totalRows - Number(rec.rowIndex) + 1) + 1;
        if (realIdx >= 2 && realIdx <= dataRows.length) {
          targetRowIndex = realIdx;
        }
      }

      if (targetRowIndex >= 2) {
        if (rec.tramo)            sheet.getRange(targetRowIndex, 2).setValue(rec.tramo);
        if (rec.id_consol)        sheet.getRange(targetRowIndex, 3).setValue(rec.id_consol);
        if (rec.estructura)       sheet.getRange(targetRowIndex, 4).setValue(rec.estructura);
        if (rec.tipo_estructura)  sheet.getRange(targetRowIndex, 5).setValue(rec.tipo_estructura);
        if (rec.altura !== undefined && rec.altura !== '') sheet.getRange(targetRowIndex, 6).setValue(rec.altura);
        if (rec.ubicacion)       sheet.getRange(targetRowIndex, 7).setValue(rec.ubicacion);
        if (rec.codigo)          sheet.getRange(targetRowIndex, 8).setValue(rec.codigo);
        if (rec.mufa !== undefined && rec.mufa !== '') sheet.getRange(targetRowIndex, 9).setValue(rec.mufa);
        if (rec.retencion !== undefined && rec.retencion !== '') sheet.getRange(targetRowIndex, 10).setValue(rec.retencion);
        if (rec.suspension !== undefined && rec.suspension !== '') sheet.getRange(targetRowIndex, 11).setValue(rec.suspension);
        if (rec.cruceta !== undefined && rec.cruceta !== '') sheet.getRange(targetRowIndex, 12).setValue(rec.cruceta);
        if (rec.observacion !== undefined && rec.observacion !== '') sheet.getRange(targetRowIndex, 18).setValue(rec.observacion);

        return ContentService.createTextOutput(JSON.stringify({"status": "success", "message": "Registro #" + targetRowIndex + " actualizado exitosamente en Google Sheets."}))
          .setMimeType(ContentService.MimeType.JSON);
      } else {
        return ContentService.createTextOutput(JSON.stringify({"status": "error", "message": "No se encontro la fila para actualizar: " + (targetIdConsol || targetCodigo)}))
          .setMimeType(ContentService.MimeType.JSON);
      }
    }

    // 5. Si la peticion es para actualizar/editar un reporte de materiales
    if (data.action_type === 'update_material') {
      var ss = SpreadsheetApp.getActiveSpreadsheet();
      var matSheet = ss.getSheetByName('Materiales');
      if (matSheet) {
        var mRows = matSheet.getDataRange().getValues();
        var mRep = data.report || data || {};
        var mTargetId = String(mRep.id || mRep.incidencia || '').trim();
        for (var mIdx = 1; mIdx < mRows.length; mIdx++) {
          var rowInc = String(mRows[mIdx][1] || '').trim();
          if (mTargetId && (rowInc === mTargetId || String(mIdx) === mTargetId)) {
            if (mRep.incidencia) matSheet.getRange(mIdx + 1, 2).setValue(mRep.incidencia);
            if (mRep.crq !== undefined) matSheet.getRange(mIdx + 1, 3).setValue(mRep.crq);
            if (mRep.almacen_pm) matSheet.getRange(mIdx + 1, 4).setValue(mRep.almacen_pm);
            if (mRep.tramo) matSheet.getRange(mIdx + 1, 5).setValue(mRep.tramo);
            if (mRep.descripcion) matSheet.getRange(mIdx + 1, 6).setValue(mRep.descripcion);
            return ContentService.createTextOutput(JSON.stringify({"status": "success", "message": "Material actualizado en Google Sheets"})).setMimeType(ContentService.MimeType.JSON);
          }
        }
      }
      return ContentService.createTextOutput(JSON.stringify({"status": "error", "message": "No se encontro reporte de material a actualizar"})).setMimeType(ContentService.MimeType.JSON);
    }

    // 6. Si la peticion es para subir un documento generado (Word / PDF)
    if (data.action_type === 'save_document' || data.action_type === 'upload_document') {
      var folder = DriveApp.getFolderById(FOLDER_ID);
      var decoded = Utilities.base64Decode(data.file_base64 || '');
      var blob = Utilities.newBlob(decoded, data.mimeType || 'application/pdf', data.filename || ('Documento_' + new Date().getTime() + '.pdf'));
      var file = folder.createFile(blob);
      file.setSharing(DriveApp.Access.ANYONE_WITH_LINK, DriveApp.Permission.VIEW);
      return ContentService.createTextOutput(JSON.stringify({"status": "success", "drive_url": file.getUrl(), "url": file.getUrl()}))
        .setMimeType(ContentService.MimeType.JSON);
    }

    // 7. Sino, es la subida normal del formulario de campo
    var sheet = SpreadsheetApp.getActiveSpreadsheet().getActiveSheet();
>>>>>>> origin/master
    var nextRow = sheet.getLastRow() + 1;
    
    if (nextRow === 1) {
      sheet.appendRow([
        "Fecha", "Tramo", "ID Consol", "Estructura", "Tipo Estructura", "Altura",
        "Ubicacion", "Codigo", "Mufa", "Retencion", "Suspension", "Cruceta",
        "Hebillas", "Fleje", "Amortiguador", "Brazo Extensor", "Kit Retenida",
        "Observacion", "Foto 1", "Foto 2", "Link KMZ", "Link Docx"
      ]);
      nextRow = 2;
    }
    
    function saveImageToDrive(base64Data, filename) {
      if (!base64Data) return "";
      try {
        var folder = DriveApp.getFolderById(FOLDER_ID);
        var splitBase = base64Data.split(',');
        var type = splitBase[0].split(';')[0].replace('data:', '');
        var decoded = Utilities.base64Decode(splitBase[1]);
        var blob = Utilities.newBlob(decoded, type, filename);
        var file = folder.createFile(blob);
        file.setSharing(DriveApp.Access.ANYONE_WITH_LINK, DriveApp.Permission.VIEW);
        return file.getUrl(); 
      } catch (err) {
        return "";
      }
    }

    var dateString = new Date().getTime();
    data.foto1_url = saveImageToDrive(data.foto1_base64, "foto1_" + dateString + ".jpg");
    data.foto2_url = saveImageToDrive(data.foto2_base64, "foto2_" + dateString + ".jpg");

    var kmzUrl = generarKMZ(data, FOLDER_ID);
    var docUrl = generarGoogleDoc(data, FOLDER_ID);
    
    var row = [
      new Date(),
      data.tramo || '',
      data.id_consol || '',
      data.estructura || '',
      data.tipo_estructura || '',
      data.altura_estructura || '',
      data.ubicacion || '',
      data.codigo || '',
      data.mufa || '',
      data.retencion || '',
      data.suspension || '',
      data.cruceta || '',
      data.hebillas || '',
      data.fleje || '',
      data.amortiguador || '',
      data.brazo_extensor || '',
      data.kit_retenida || '',
      data.observacion || '',
      data.foto1_url || '',
      data.foto2_url || '',
      kmzUrl,
      docUrl
    ];
    
    sheet.appendRow(row);
    
    return ContentService.createTextOutput(JSON.stringify({"status": "success", "message": "Datos, fotos, KMZ y Documento Google Docs guardados en Drive", "doc_url": docUrl}))
      .setMimeType(ContentService.MimeType.JSON);

  } catch (error) {
    return ContentService.createTextOutput(JSON.stringify({"status": "error", "message": error.toString()}))
      .setMimeType(ContentService.MimeType.JSON);
  }
}

function extraerFolderId(input) {
  var DEFAULT_ID = '1e9qvf_OKyqzCTxzhs8cF0E3t61UVlRXO';
  if (!input || typeof input !== 'string') return DEFAULT_ID;
  var str = input.trim();
  if (str.indexOf('/folders/') > -1) {
    var parts = str.split('/folders/');
    var idPart = parts[1].split('?')[0].split('/')[0];
    return idPart || DEFAULT_ID;
  }
  return str || DEFAULT_ID;
}

<<<<<<< HEAD
=======
function generarGoogleDocNegativa(data, folderId) {
  try {
    var validFolderId = extraerFolderId(folderId);
    var docName = "Negativa_Trabajo_HSE-RE-NEG-01_ID_" + (data.id || new Date().getTime());
    var doc = DocumentApp.create(docName);
    var body = doc.getBody();

    var titlePara = body.appendParagraph("FORMATO DE NEGATIVA AL TRABAJO POR RIESGO INMINENTE");
    titlePara.setBold(true);
    titlePara.setFontSize(14);
    body.appendParagraph("CODIGO: HSE-RE-NEG-01  |  FECHA REGISTRO: " + Utilities.formatDate(new Date(), Session.getScriptTimeZone(), "dd/MM/yyyy HH:mm"));
    body.appendParagraph("----------------------------------------------------------------------------------");

    body.appendParagraph("1. DATOS DE LA EMPRESA Y TRABAJO");
    body.appendTable([
      ["Cliente / Empresa", data.cliente_nombre || "CYMTEL"],
      ["Proceso / Proyecto", data.proceso || "-"],
      ["CM / Localidad", data.cm_localidad || "-"],
      ["Contratista", data.contratista || "-"],
      ["Sub Contratista", data.sub_contratista || "-"],
      ["Lugar del Trabajo", data.lugar_trabajo || "-"],
      ["Fecha y Hora", (data.fecha || "-") + " | " + (data.hora_inicio || "-") + " a " + (data.hora_fin || "-")]
    ]);

    body.appendParagraph("");
    body.appendParagraph("2. PERSONAL Y DESCRIPCION DEL RIESGO");
    body.appendTable([
      ["Trabajador Reportante", data.trabajador_reportante || data.firma_tecnico_user || "-"],
      ["Supervisor Operativo", data.supervisor_operativo_nombre || data.firma_sup_operativo_user || "-"],
      ["Razones de la Negativa (Punto 3)", data.razones_negativa || "-"],
      ["Medidas Correctivas (Punto 5)", data.medidas_correctivas || "-"]
    ]);

    body.appendParagraph("");
    body.appendParagraph("3. VERIFICACION DE SEGURIDAD Y DICTAMEN HSE");
    body.appendTable([
      ["Satisface Negativa (Acuerdo Inseguro)", data.satisface_negativa || "-"],
      ["Reinicia Labores (SI/NO)", data.reinicia_labores || "-"],
      ["Supervisor Seguridad", data.supervisor_seguridad_nombre || data.firma_sup_seguridad_user || "-"],
      ["Observaciones de Seguridad", data.observaciones_seguridad || "-"],
      ["Dictamen Final HSE", data.dictamen_hse || "-"],
      ["Firma Autorizada HSE", data.firma_hse_user || "-"]
    ]);

    doc.saveAndClose();

    var docFile = DriveApp.getFileById(doc.getId());
    if (validFolderId) {
      try {
        var targetFolder = DriveApp.getFolderById(validFolderId);
        docFile.moveTo(targetFolder);
      } catch(fErr) {}
    }

    try {
      docFile.setSharing(DriveApp.Access.ANYONE_WITH_LINK, DriveApp.Permission.VIEW);
    } catch(sErr) {}

    return docFile.getUrl();
  } catch (err) {
    Logger.log("Error creando Google Doc Negativa: " + err.toString());
    return "";
  }
}

>>>>>>> origin/master
function generarGoogleDoc(data, folderId) {
  try {
    var validFolderId = extraerFolderId(folderId);
    var docName = "Ficha_Ruteo_" + (data.id_consol || data.codigo || ("Rec_" + new Date().getTime()));
    var doc = DocumentApp.create(docName);
    var body = doc.getBody();

    var titlePara = body.appendParagraph("FICHA TECNICA DE REGISTRO DE CAMPO");
    titlePara.setBold(true);
    titlePara.setFontSize(16);
    try {
      if (DocumentApp.ParagraphHeading && DocumentApp.ParagraphHeading.HEADING1) {
        titlePara.setHeading(DocumentApp.ParagraphHeading.HEADING1);
      }
    } catch(hErr) {}
    body.appendParagraph("Fecha: " + Utilities.formatDate(new Date(), Session.getScriptTimeZone(), "dd/MM/yyyy HH:mm"));
    body.appendParagraph("Tramo: " + (data.tramo || "-") + " | Codigo: " + (data.codigo || "-") + " | ID Consol: " + (data.id_consol || "-"));
    body.appendParagraph("");

    body.appendTable([
      ["Parametro", "Valor Registrado"],
      ["Estructura", data.estructura || "-"],
      ["Tipo Estructura", data.tipo_estructura || "-"],
      ["Altura", (data.altura_estructura || data.altura || "-") + " m"],
      ["Ubicacion Coordenadas", data.ubicacion || "-"],
      ["Codigo", data.codigo || "-"],
      ["Mufa", data.mufa || "0"],
      ["Retencion", data.retencion || "0"],
      ["Suspension", data.suspension || "0"],
      ["Cruceta", data.cruceta || "0"],
      ["Hebillas", data.hebillas || "0"],
      ["Fleje", data.fleje || "0"],
      ["Amortiguador", data.amortiguador || "0"],
      ["Brazo Extensor", data.brazo_extensor || "0"],
      ["Kit Retenida", data.kit_retenida || "0"],
      ["Observaciones", data.observacion || "-"]
    ]);

    body.appendParagraph("");
    if (data.foto1_url || data.foto_1) {
      body.appendParagraph("Fotografia 1: " + (data.foto1_url || data.foto_1));
    }
    if (data.foto2_url || data.foto_2) {
      body.appendParagraph("Fotografia 2: " + (data.foto2_url || data.foto_2));
    }

    doc.saveAndClose();

    var docFile = DriveApp.getFileById(doc.getId());
    if (validFolderId) {
      try {
        var targetFolder = DriveApp.getFolderById(validFolderId);
        docFile.moveTo(targetFolder);
      } catch(fErr) {
        Logger.log("Error moviendo archivo a carpeta: " + fErr.toString());
      }
    }

    try {
      docFile.setSharing(DriveApp.Access.ANYONE_WITH_LINK, DriveApp.Permission.VIEW);
    } catch(sErr) {}

    return docFile.getUrl();
  } catch (err) {
    Logger.log("Error creando Google Doc: " + err.toString());
    return "";
  }
}

function generarKMZ(data, folderId) {
  var coords = "0,0,0";
  
  if (data.ubicacion) {
    var latLonRegex = /(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)/;
    var match = data.ubicacion.match(latLonRegex);
    
    if (match) {
      coords = match[2].trim() + "," + match[1].trim() + ",0";
    } else {
      try {
        var geocoder = Maps.newGeocoder().geocode(data.ubicacion);
        if (geocoder.status === 'OK' && geocoder.results.length > 0) {
          var loc = geocoder.results[0].geometry.location;
          coords = loc.lng + "," + loc.lat + ",0";
        }
      } catch (e) {
      }
    }
  }

  var nombrePunto = data.id_consol ? data.id_consol : "Punto_Ruteo_" + new Date().getTime();
  var descripcion = "Estructura: " + data.estructura + "<br>" +
                    "Codigo: " + data.codigo + "<br>" +
                    "Observacion: " + data.observacion + "<br>";
  if (data.foto1_url) descripcion += "<img src='" + data.foto1_url + "' width='300'>";

  var kmlContent = "<?xml version='1.0' UTF-8'?>\n" +
  "<kml xmlns='http://www.opengis.net/kml/2.2'>\n" +
  "  <Document>\n" +
  "    <name>Ruteo_" + nombrePunto + ".kml</name>\n" +
  "    <Placemark>\n" +
  "      <name>" + nombrePunto + "</name>\n" +
  "      <description><![CDATA[" + descripcion + "]]></description>\n" +
  "      <Point>\n" +
  "        <coordinates>" + coords + "</coordinates>\n" +
  "      </Point>\n" +
  "    </Placemark>\n" +
  "  </Document>\n" +
  "</kml>";

  var kmlBlob = Utilities.newBlob(kmlContent, "application/vnd.google-earth.kml+xml", "doc.kml");
  var zipBlob = Utilities.zip([kmlBlob], "Ruteo_" + nombrePunto + ".zip");
  zipBlob.setName("Ruteo_" + nombrePunto + ".kmz");
  zipBlob.setContentType("application/vnd.google-earth.kmz");
  
  try {
    var folder = DriveApp.getFolderById(folderId);
    var file = folder.createFile(zipBlob);
    file.setSharing(DriveApp.Access.ANYONE_WITH_LINK, DriveApp.Permission.VIEW);
    return file.getUrl();
  } catch(e) {
    var file = DriveApp.createFile(zipBlob);
    file.setSharing(DriveApp.Access.ANYONE_WITH_LINK, DriveApp.Permission.VIEW);
    return file.getUrl();
  }
}

<<<<<<< HEAD
// ---------------------------------------------------------------
// FUNCION MANUAL EN APPS SCRIPT: Genera/Regenera Google Docs para
// TODOS los registros existentes en la hoja de Google Sheets.
// Se ejecuta directamente desde el editor seleccionando la funcion.
// ---------------------------------------------------------------
=======
>>>>>>> origin/master
function generarDocumentosTodosLosRegistros() {
  var FOLDER_ID = '1e9qvf_OKyqzCTxzhs8cF0E3t61UVlRXO';
  var sheet = SpreadsheetApp.getActiveSpreadsheet().getActiveSheet();
  var data = sheet.getDataRange().getValues();
  if (data.length <= 1) return;

  var headers = data[0];
  var docxColIndex = -1;
  for (var h = 0; h < headers.length; h++) {
    if (headers[h].toString().toLowerCase().indexOf('doc') > -1) {
      docxColIndex = h;
      break;
    }
  }

  if (docxColIndex === -1) {
    sheet.getRange(1, headers.length + 1).setValue("Link Docx");
    docxColIndex = headers.length;
  }

  for (var i = 1; i < data.length; i++) {
    var record = {
      fecha: data[i][0],
      tramo: data[i][1],
      id_consol: data[i][2],
      estructura: data[i][3],
      tipo_estructura: data[i][4],
      altura: data[i][5],
      ubicacion: data[i][6],
      codigo: data[i][7],
      mufa: data[i][8],
      retencion: data[i][9],
      suspension: data[i][10],
      cruceta: data[i][11],
      hebillas: data[i][12],
      fleje: data[i][13],
      amortiguador: data[i][14],
      brazo_extensor: data[i][15],
      kit_retenida: data[i][16],
      observacion: data[i][17],
      foto1_url: data[i][18],
      foto2_url: data[i][19]
    };

    var docUrl = generarGoogleDoc(record, FOLDER_ID);
    if (docUrl) {
      sheet.getRange(i + 1, docxColIndex + 1).setValue(docUrl);
    }
  }
}
