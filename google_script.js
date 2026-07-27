// ---------------------------------------------------------------
// GET: Devuelve todos los registros del sheet como JSON
// Usado por el Portal de Consulta de Registros en WordPress
// ---------------------------------------------------------------
function doGet(e) {
  try {
    var sheet = SpreadsheetApp.getActiveSpreadsheet().getActiveSheet();
    var data  = sheet.getDataRange().getValues();

    if (data.length <= 1) {
      // Solo cabecera o hoja vacia
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
        // Formatear fechas
        if (val instanceof Date) {
          val = Utilities.formatDate(val, Session.getScriptTimeZone(), 'dd/MM/yyyy HH:mm');
        }
        row[key] = val !== undefined && val !== null ? val.toString() : '';
      }
      registros.push(row);
    }

    // Mas recientes primero
    registros.reverse();

    var result = { status: 'success', registros: registros, total: registros.length };
    return outputResponse(result, e);

  } catch (error) {
    return outputResponse({ status: 'error', message: error.toString() }, e);
  }
}

function outputResponse(obj, e) {
  var json = JSON.stringify(obj);
  // Soporte JSONP: si recibe parametro ?callback=nombreFuncion, devuelve JSONP
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
    var FOLDER_ID = '1m19aeKOuPJYw01yvFPP9_SGmpdJgUg_q';

    var data = JSON.parse(e.postData.contents);
    
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
    
    // Obtener la ultima fila
    var nextRow = sheet.getLastRow() + 1;
    
    // Si la hoja esta vacia, agregar cabeceras
    if (nextRow === 1) {
      sheet.appendRow([
        "Fecha", "Tramo", "ID Consol", "Estructura", "Tipo Estructura", "Altura",
        "Ubicacion", "Codigo", "Mufa", "Retencion", "Suspension", "Cruceta",
        "Hebillas", "Fleje", "Amortiguador", "Brazo Extensor", "Kit Retenida",
        "Observacion", "Foto 1", "Foto 2", "Link KMZ"
      ]);
      nextRow = 2;
    }
    
    // Función para guardar imagen en Drive
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

    // Generar el archivo KMZ (KML Zippeado) en la misma carpeta
    var kmzUrl = generarKMZ(data, FOLDER_ID);
    
    // Preparar fila de datos
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
      kmzUrl
    ];
    
    // Insertar en la hoja
    sheet.appendRow(row);
    
    return ContentService.createTextOutput(JSON.stringify({"status": "success", "message": "Datos y fotos guardados en Drive, KMZ generado"}))
      .setMimeType(ContentService.MimeType.JSON);
      
  } catch (error) {
    return ContentService.createTextOutput(JSON.stringify({"status": "error", "message": error.toString()}))
      .setMimeType(ContentService.MimeType.JSON);
  }
}

function generarKMZ(data, folderId) {
  var coords = "0,0,0"; // Coordenada por defecto
  
  if (data.ubicacion && data.ubicacion.indexOf(',') > -1) {
    var parts = data.ubicacion.split(',');
    if (parts.length >= 2) {
      coords = parts[1].trim() + "," + parts[0].trim() + ",0";
    }
  }

  var nombrePunto = data.id_consol ? data.id_consol : "Punto_Ruteo_" + new Date().getTime();
  var descripcion = "Estructura: " + data.estructura + "<br>" +
                    "Codigo: " + data.codigo + "<br>" +
                    "Observacion: " + data.observacion + "<br>";
  if (data.foto1_url) descripcion += "<img src='" + data.foto1_url + "' width='300'>";

  // Contenido KML basico
  var kmlContent = "<?xml version='1.0' encoding='UTF-8'?>\n" +
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
    // Si falla (ej. ID de carpeta invalido), guardar en raiz temporalmente
    var file = DriveApp.createFile(zipBlob);
    file.setSharing(DriveApp.Access.ANYONE_WITH_LINK, DriveApp.Permission.VIEW);
    return file.getUrl();
  }
}

// ==============================================================================
// SCRIPT PARA ENLAZAR IMAGENES YA SUBIDAS A DRIVE
// ==============================================================================
function enlazarImagenesYaSubidas() {
  // 1. REEMPLAZA AQUI TU ID DE CARPETA DE DRIVE
  var FOLDER_ID = '1m19aeKOuPJYw01yvFPP9_SGmpdJgUg_q'; 
  
  var folder = DriveApp.getFolderById(FOLDER_ID);
  var files = folder.getFiles();
  
  // Crear un diccionario (mapa) de NombreDeArchivo -> URL de Drive
  var fileMap = {};
  while (files.hasNext()) {
    var file = files.next();
    fileMap[file.getName()] = file.getUrl();
  }
  
  Logger.log("Archivos encontrados en Drive: " + Object.keys(fileMap).length);
  
  var sheet = SpreadsheetApp.getActiveSpreadsheet().getActiveSheet();
  var dataRange = sheet.getDataRange();
  var values = dataRange.getValues();
  var headers = values[0];
  
  var colFoto1 = -1;
  var colFoto2 = -1;
  
  for (var i = 0; i < headers.length; i++) {
    if (headers[i].toString().toLowerCase().trim() === 'foto 1') colFoto1 = i;
    if (headers[i].toString().toLowerCase().trim() === 'foto 2') colFoto2 = i;
  }
  
  if (colFoto1 === -1 && colFoto2 === -1) {
    Logger.log("No se encontraron columnas de Foto 1 ni Foto 2. Verifica los nombres.");
    return;
  }
  
  // Recorrer filas y actualizar si el nombre de archivo coincide
  for (var r = 1; r < values.length; r++) {
    var rowData = values[r];
    var updated = false;
    
    // Función auxiliar para extraer el nombre del archivo de una URL local
    function getFilenameFromUrl(url) {
      if (!url) return null;
      var parts = url.split('/');
      return parts[parts.length - 1].split('?')[0]; // quitar parametros si hay
    }
    
    if (colFoto1 !== -1) {
      var url1 = rowData[colFoto1];
      if (url1 && url1.indexOf('drive.google.com') === -1) {
        var fname1 = getFilenameFromUrl(url1);
        if (fname1 && fileMap[fname1]) {
          sheet.getRange(r + 1, colFoto1 + 1).setValue(fileMap[fname1]);
          updated = true;
          Logger.log("Fila " + (r+1) + " - Foto 1 actualizada.");
        }
      }
    }
    
    if (colFoto2 !== -1) {
      var url2 = rowData[colFoto2];
      if (url2 && url2.indexOf('drive.google.com') === -1) {
        var fname2 = getFilenameFromUrl(url2);
        if (fname2 && fileMap[fname2]) {
          sheet.getRange(r + 1, colFoto2 + 1).setValue(fileMap[fname2]);
          updated = true;
          Logger.log("Fila " + (r+1) + " - Foto 2 actualizada.");
        }
      }
    }
  }
  
  Logger.log("PROCESO DE ACTUALIZACION DE ENLACES TERMINADO.");
}
