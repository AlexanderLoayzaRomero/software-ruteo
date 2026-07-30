# 🗺️ Aplicativo de Ruteo

Sistema de captura de campo y monitoreo en tiempo real para proyectos de infraestructura. Plugin WordPress con backend en Google Sheets y almacenamiento en Google Drive.

---

## 🚀 Stack Tecnológico

| Capa | Tecnología |
|------|-----------|
| **Frontend** | WordPress Plugin (PHP + JavaScript + CSS) |
| **Backend** | Google Apps Script (Google Sheets + Drive) |
| **Librerías** | jsPDF 2.5.1, jsPDF AutoTable 3.8.2, ExcelJS 4.4.0, docx 8.5.0, FileSaver.js 2.0.5 |
| **Infraestructura** | Docker (WordPress + MySQL 8.0) |

---

## 📦 Estructura del Proyecto

```
software-ruteo/
├── README.md
├── docker-compose.yml              # Docker: WordPress + MySQL
├── google_script.js                # Google Apps Script (backend API)
├── .gitignore
└── wp-content/
    └── plugins/
        └── wp-ruteo/               # Plugin principal (v1.2.0)
            ├── wp-ruteo.php         # Entry point, shortcodes, AJAX handlers
            ├── assets/
            │   ├── css/
            │   │   └── style.css    # Estilos (light/dark theme, responsive)
            │   └── js/
            │       └── app.js       # Login, formularios, tabs, interfaz
            └── includes/
                ├── portal-template.php   # 🏠 Portal: dashboard, tabla, exportación
                ├── form-template.php     # 📝 Formulario de captura de campo
                └── login-template.php    # 🔐 Login personalizado
```

---

## ⚙️ Funcionalidades

### 🏠 Portal de Registros (`[portal_ruteo]`)
- ✅ Tabla con todos los registros desde Google Sheets
- ✅ Filtros por tramo y búsqueda por texto
- ✅ **PDF grupal**: reporte general en landscape A4 con tabla de 17+ columnas formateadas, encabezado corporativo azul, fecha y responsable
- ✅ **Excel grupal**: dos hojas (portada "Información" + "Registros de Campo"), 21 columnas, hipervínculos a fotos y KMZ, encabezados congelados, auto-filtro, filas alternadas
- ✅ **PDF individual** por registro (ícono rojo): ficha técnica con datos de la estructura y **fotografías incrustadas** desde Google Drive (con fallback si no cargan)
- ✅ **Word individual** (ícono azul): ficha técnica editable en `.docx` generada 100% en el cliente, con tabla y fotografías incrustadas nativamente.
- ✅ Temas claro/oscuro
- ✅ Homepage configurada (`http://localhost:8080/` muestra el portal)

### 📝 Formulario de Captura (`[formulario_ruteo]`)
- Registro de estructuras de campo (torres, postes, etc.)
- 17 campos técnicos + observaciones
- **Entrada de Datos Flexible:** Uso de `datalists` HTML5 para sugerir opciones genéricas rápidas (ej. Tramo A, 0, 1, 2) sin restringir la escritura de texto/números personalizados.
- Subida de fotos (2 por registro) a Google Drive
- Archivo KMZ con **Geolocalización Inteligente**: detecta latitud/longitud por Regex o busca ubicaciones textuales ("Plaza de Armas, Arequipa") usando el Geocodificador interno de Google Maps.

### 🔐 Login y Gestión de Usuarios (`[login_ruteo]`)
- Sistema de roles personalizado
- Panel de administración de usuarios
- Sesión persistente

### 📊 Google Sheets Sync
- Captura en tiempo real desde campo al sheet
- Proxy PHP (`admin-ajax.php`) para sortear CORS
- JSONP como fallback
- Almacenamiento de imágenes en Google Drive
- Normalización automática de claves (headers del sheet → camelCase)

---

## 🐳 Instalación con Docker

```bash
git clone https://github.com/AlexanderLoayzaRomero/software-ruteo.git
cd software-ruteo
docker-compose up -d
# http://localhost:8080
```

### Configuración de Google Sheets

1. Crear Google Sheet con las columnas del [esquema de datos](#-esquema-de-datos)
2. **Extensiones > Apps Script** → pegar `google_script.js`
3. **Implementar > Nueva implementación** como aplicación web
4. Copiar URL del webhook → configurar en el plugin

---

## 🔌 Shortcodes

| Shortcode | Descripción |
|-----------|-------------|
| `[portal_ruteo]` | Portal principal con dashboard, tabla de registros, filtros y exportación |
| `[formulario_ruteo]` | Formulario de captura de datos en campo |
| `[login_ruteo]` | Pantalla de inicio de sesión |

---

## 🔐 Roles y Permisos

| Funcionalidad | `ruteo_admin` | `ruteo_worker` |
|--------------|:---:|:---:|
| Ver registros en portal | ✅ | ✅ |
| Filtrar y buscar registros | ✅ | ✅ |
| Descargar PDF grupal | ✅ | ✅ |
| Descargar Excel grupal | ✅ | ✅ |
| Descargar PDF individual (por registro) | ✅ | ✅ |
| Ver tema oscuro / claro | ✅ | ✅ |
| Crear nuevo registro | ✅ | ✅ |
| Subir fotos y KMZ | ✅ | ✅ |
| **Gestionar usuarios** (crear, editar, eliminar) | ✅ | ❌ |
| **Ver pestaña "Gestión Usuarios"** | ✅ | ❌ |
| Acceder a WordPress Admin (`/wp-admin`) | ✅ | ❌ |

---

## 📄 Exportación de Documentos

### PDF Grupal (botón "Descargar PDF")
- Orientación landscape A4
- Encabezado corporativo azul `#0097D8`
- 19 columnas: Fecha, Tramo, ID Consol, Estructura, Tipo, Altura, Código, Ubicación, Mufa, Retención, Suspensión, Cruceta, Hebillas, Fleje, Amortiguador, Brazo Extensor, Kit Retenida, Observación, Link KMZ
- Filas alternadas
- Librerías cargadas bajo demanda si no están disponibles

### PDF Individual (ícono rojo por fila)
- Orientación portrait A4
- Cabecera con fecha, tramo y código
- Tabla completa de datos técnicos
- **Fotografías incrustadas** desde Google Drive (carga asíncrona con timeout 8s)
- Fallback visual si la imagen no está disponible

### Excel Grupal (botón "Descargar Excel")
- **Hoja 1 - "Información"**: portada con fecha, responsable, rol, total de registros, tramos activos
- **Hoja 2 - "Registros de Campo"**: 21 columnas (17 datos + Observación + Foto 1 + Foto 2 + Link KMZ)
- Hipervínculos clicables: "Ver Foto 1", "Ver Foto 2" (azul), "Abrir KMZ" (verde)
- Encabezados estilo corporativo congelados
- Auto-filtro
- Filas alternadas con color

---

## 🛠️ Dependencias CDN

| Librería | Versión | CDN | Uso |
|----------|---------|-----|-----|
| jsPDF | 2.5.1 | cdnjs | PDF grupal e individual |
| jsPDF AutoTable | 3.8.2 | cdnjs | Tablas en PDF |
| ExcelJS | 4.4.0 | jsDelivr | Excel grupal |
| docx | 8.5.0 | unpkg | Word individual |
| FileSaver.js | 2.0.5 | cdnjs | Descarga de blobs |

> Las librerías se pre-cargan asíncronamente al iniciar el portal y se cargan bajo demanda si el usuario hace clic antes de que estén listas.

---

## 📋 Esquema de Datos (Google Sheets)

| # | Columna | Tipo | Descripción |
|---|---------|------|-------------|
| 1 | Fecha | DateTime | Fecha y hora del registro (`dd/MM/yyyy HH:mm`) |
| 2 | Tramo | Text | Identificador del tramo |
| 3 | ID Consol | Text | ID de consolidación |
| 4 | Estructura | Text | Torre, Poste, etc. |
| 5 | Tipo Estructura | Text | Metal, Concreto |
| 6 | Altura | Number | Altura en metros |
| 7 | Código | Text | Código de estructura |
| 8 | Ubicación | Text | Dirección o referencia |
| 9 | Mufa | Number | Cantidad |
| 10 | Retención | Number | Cantidad |
| 11 | Suspensión | Number | Cantidad |
| 12 | Cruceta | Number | Cantidad |
| 13 | Hebillas | Number | Cantidad |
| 14 | Fleje | Number | Cantidad |
| 15 | Amortiguador | Number | Cantidad |
| 16 | Brazo Extensor | Number | Cantidad |
| 17 | Kit Retenida | Number | Cantidad |
| 18 | Observación | Text | Notas de campo |
| 19 | Foto 1 | URL | Imagen (Google Drive) |
| 20 | Foto 2 | URL | Imagen (Google Drive) |
| 21 | Link KMZ | URL | Archivo KMZ (Google Drive) |

---

## 🏠 Página de Inicio

La homepage de WordPress (`http://localhost:8080/`) está configurada automáticamente para mostrar el portal de ruteo con el shortcode `[portal_ruteo]`.

Para restaurar el blog: **WordPress Admin > Settings > Reading > Your homepage displays > Your latest posts**

---

## 📝 Licencia

Proyecto privado. Todos los derechos reservados.

---

## 👤 Autor

**Alexander Loayza Romero**

[GitHub](https://github.com/AlexanderLoayzaRomero)
