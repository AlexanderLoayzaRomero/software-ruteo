// ---------------------------------------------------------------
// GET: Devuelve registros de ruteo o materiales del sheet como JSON
// Usado por el Aplicativo de Ruteo en WordPress
// ---------------------------------------------------------------
function doGet(e) {
  try {
    var ss = SpreadsheetApp.getActiveSpreadsheet();
    
    // Si solicitan materiales via doGet ?action=get_materiales
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

    // Por defecto: devuelve registros de ruteo de campo
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
    
    // Si la peticion es para crear/sincronizar documento Google Docs en Drive
    if (data.action_type === 'create_doc') {
      var docUrl = generarGoogleDoc(data.record || data, FOLDER_ID);
      return ContentService.createTextOutput(JSON.stringify({"status": "success", "doc_url": docUrl}))
        .setMimeType(ContentService.MimeType.JSON);
    }

    // Si la peticion es para guardar reporte de materiales
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

function generarGoogleDoc(data, folderId) {
  try {
    var validFolderId = extraerFolderId(folderId);
    var docName = "Ficha_Ruteo_" + (data.id_consol || data.codigo || ("Rec_" + new Date().getTime()));
    var doc = DocumentApp.create(docName);
    var body = doc.getBody();

    // Margenes compactos para garantizar 1 sola hoja
    body.setMarginTop(24);
    body.setMarginBottom(24);
    body.setMarginLeft(28);
    body.setMarginRight(28);

    // Titulo Encabezado
    var titlePara = body.appendParagraph("SOFTWARE O&M - FICHA TECNICA DE CAMPO");
    titlePara.setBold(true).setFontSize(13);
    try {
      if (DocumentApp.ParagraphHeading && DocumentApp.ParagraphHeading.HEADING1) {
        titlePara.setHeading(DocumentApp.ParagraphHeading.HEADING1);
      }
    } catch(hErr) {}
    
    var subPara = body.appendParagraph("Fecha: " + Utilities.formatDate(new Date(), Session.getScriptTimeZone(), "dd/MM/yyyy HH:mm") + "  |  Tramo: " + (data.tramo || "-") + "  |  ID Consol: " + (data.id_consol || "-"));
    subPara.setFontSize(8.5).setItalic(true);

    // Tabla Compacta de Especificaciones (4 columnas para ahorrar espacio vertical)
    var specsTableData = [
      ["ID Consol", data.id_consol || "-", "Codigo Estructura", data.codigo || "-"],
      ["Estructura", data.estructura || "-", "Tipo Estructura", data.tipo_estructura || "-"],
      ["Altura", (data.altura_estructura || data.altura || "-") + " m", "Ubicacion Coordenadas", data.ubicacion || "-"],
      ["Mufa / Herrajes", "Mufa: " + (data.mufa || "0") + " | Ret: " + (data.retencion || "0") + " | Susp: " + (data.suspension || "0"), "Cruceta / Accesorios", "Cruceta: " + (data.cruceta || "0") + " | Fleje: " + (data.fleje || "0") + "m"],
      ["Amortiguador / Kit", "Amort: " + (data.amortiguador || "0") + " | Kit Ret: " + (data.kit_retenida || "0"), "Hebillas / Extensor", "Hebillas: " + (data.hebillas || "0") + " | Ext: " + (data.brazo_extensor || "0")],
      ["Observaciones", data.observacion || "-", "Fecha Registro", data.fecha || "-"]
    ];

    var table = body.appendTable(specsTableData);
    table.setBorderWidth(0.5);
    table.setBorderColor("#CBD5E1");

    for (var r = 0; r < table.getNumRows(); r++) {
      var row = table.getRow(r);
      for (var c = 0; c < row.getNumCells(); c++) {
        var cell = row.getCell(c);
        cell.setPaddingTop(2);
        cell.setPaddingBottom(2);
        cell.setPaddingLeft(4);
        cell.setPaddingRight(4);
        var p = cell.getChild(0).asParagraph();
        p.setFontSize(8);
        if (c % 2 === 0) {
          p.setBold(true);
          cell.setBackgroundColor("#F1F5F9");
        }
      }
    }

    var headingFotos = body.appendParagraph("\nEVIDENCIAS FOTOGRAFICAS DE CAMPO");
    headingFotos.setBold(true).setFontSize(10);

    // Tabla de 2 Columnas para colocar Fotos en la Misma Fila lado a lado
    var photoTableData = [["", ""]];
    var photoTable = body.appendTable(photoTableData);
    photoTable.setBorderWidth(0.5);
    photoTable.setBorderColor("#CBD5E1");

    var cell1 = photoTable.getRow(0).getCell(0);
    var cell2 = photoTable.getRow(0).getCell(1);
    
    cell1.setPaddingTop(4).setPaddingBottom(4).setPaddingLeft(4).setPaddingRight(4);
    cell2.setPaddingTop(4).setPaddingBottom(4).setPaddingLeft(4).setPaddingRight(4);

    var img1Url = data.foto1_url || data.foto_1 || data.foto1;
    var pCell1 = cell1.getChild(0).asParagraph();
    pCell1.appendParagraph("Fotografia 1 - Estructura").setBold(true).setFontSize(8);
    if (img1Url) {
      var blob1 = obtenerBlobImagen(img1Url, "foto1.jpg");
      if (blob1) {
        try {
          var imgObj1 = pCell1.appendImage(blob1);
          imgObj1.setWidth(235);
          imgObj1.setHeight(165);
        } catch(e1) {
          pCell1.appendParagraph("Enlace: " + img1Url).setFontSize(7);
        }
      } else {
        pCell1.appendParagraph("Enlace: " + img1Url).setFontSize(7);
      }
    } else {
      pCell1.appendParagraph("Sin foto registrada").setFontSize(8).setItalic(true);
    }

    var img2Url = data.foto2_url || data.foto_2 || data.foto2;
    var pCell2 = cell2.getChild(0).asParagraph();
    pCell2.appendParagraph("Fotografia 2 - Mufa / Detalle").setBold(true).setFontSize(8);
    if (img2Url) {
      var blob2 = obtenerBlobImagen(img2Url, "foto2.jpg");
      if (blob2) {
        try {
          var imgObj2 = pCell2.appendImage(blob2);
          imgObj2.setWidth(235);
          imgObj2.setHeight(165);
        } catch(e2) {
          pCell2.appendParagraph("Enlace: " + img2Url).setFontSize(7);
        }
      } else {
        pCell2.appendParagraph("Enlace: " + img2Url).setFontSize(7);
      }
    } else {
      pCell2.appendParagraph("Sin foto registrada").setFontSize(8).setItalic(true);
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

// ---------------------------------------------------------------
// FUNCION MANUAL EN APPS SCRIPT: Genera/Regenera Google Docs para
// TODOS los registros existentes en la hoja de Google Sheets.
// Se ejecuta directamente desde el editor seleccionando la funcion.
// ---------------------------------------------------------------
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

function obtenerBlobImagen(inputStr, defaultName) {
  if (!inputStr || typeof inputStr !== 'string') return null;
  var str = inputStr.trim();
  if (!str) return null;

  // 1. Formato Base64
  if (str.indexOf('data:image/') === 0) {
    try {
      var parts = str.split(',');
      var header = parts[0];
      var base64Data = parts[1] || parts[0];
      var mime = 'image/jpeg';
      if (header.indexOf('image/png') !== -1) mime = 'image/png';
      else if (header.indexOf('image/webp') !== -1) mime = 'image/webp';
      var decoded = Utilities.base64Decode(base64Data);
      return Utilities.newBlob(decoded, mime, defaultName);
    } catch(e) {
      Logger.log("Error decodificando Base64: " + e.toString());
    }
  }

  // 2. Extraer ID de archivo de Google Drive (coincide con cualquier patron de URL o ID puro)
  var idMatch = str.match(/\/d\/([a-zA-Z0-9_-]{20,})/) || 
                str.match(/[?&]id=([a-zA-Z0-9_-]{20,})/) || 
                str.match(/^([a-zA-Z0-9_-]{25,50})$/);
  
  var fileId = idMatch ? idMatch[1] : null;
  if (!fileId && str.indexOf('google.com') !== -1) {
    var rawMatch = str.match(/([a-zA-Z0-9_-]{28,45})/);
    if (rawMatch) fileId = rawMatch[1];
  }

  if (fileId) {
    try {
      var file = DriveApp.getFileById(fileId);
      if (file) {
        var blob = file.getBlob();
        if (blob && blob.getContentType() && blob.getContentType().indexOf('image') !== -1) {
          return blob;
        }
      }
    } catch(eDrive) {
      Logger.log("Error obteniendo archivo Drive por ID " + fileId + ": " + eDrive.toString());
    }
  }

  // 3. Descarga de URL HTTP/HTTPS externa
  if (str.indexOf('http://') === 0 || str.indexOf('https://') === 0) {
    try {
      var resp = UrlFetchApp.fetch(str, { muteHttpExceptions: true });
      if (resp.getResponseCode() === 200) {
        var b = resp.getBlob();
        if (b && b.getContentType() && b.getContentType().indexOf('image') !== -1) {
          return b;
        }
      }
    } catch(eFetch) {
      Logger.log("Error UrlFetch imagen: " + eFetch.toString());
    }
  }

  return null;
}
