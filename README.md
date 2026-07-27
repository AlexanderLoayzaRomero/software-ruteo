# 🗺️ Aplicativo de Ruteo

Sistema de captura de campo y monitoreo en tiempo real para proyectos de infraestructura. Plugin WordPress con backend en Google Sheets.

---

## 🚀 Stack Tecnológico

| Capa | Tecnología |
|------|-----------|
| **Frontend** | WordPress Plugin (PHP + JavaScript + CSS) |
| **Backend** | Google Apps Script (Google Sheets + Drive) |
| **Librerías** | jsPDF, ExcelJS, docx, FileSaver.js |
| **Infraestructura** | Docker (WordPress + MySQL 8.0) |

---

## 📦 Estructura del Proyecto

```
software-ruteo/
├── docker-compose.yml              # Docker: WordPress + MySQL
├── google_script.js                # Google Apps Script (backend)
├── .gitignore
└── wp-content/
    └── plugins/
        └── wp-ruteo/               # Plugin principal
            ├── wp-ruteo.php         # Entry point (v1.2.0)
            ├── assets/
            │   ├── css/
            │   │   └── style.css    # Estilos (light/dark theme)
            │   └── js/
            │       └── app.js       # Frontend JS (login, forms, tabs)
            └── includes/
                ├── portal-template.php   # 🏠 Portal principal (dashboard)
                ├── form-template.php     # 📝 Formulario de captura
                └── login-template.php    # 🔐 Login personalizado
```

---

## ⚙️ Funcionalidades

### 🏠 Portal de Registros
- Visualización de todos los registros en tabla
- Filtros por tramo y búsqueda por texto
- Exportar a **PDF** (reporte general con tabla formateada)
- Exportar a **Excel** (con hipervínculos a fotos y KMZ)
- Documentos individuales por registro (PDF y Word)

### 📝 Formulario de Captura
- Registro de estructuras de campo (torres, postes)
- Campos: tramo, ID, estructura, tipo, altura, código, ubicación, componentes (mufa, retención, suspensión, cruceta, hebillas, fleje, amortiguador, brazo extensor, kit retenida)
- Subida de fotos (2 por registro)
- Subida de archivo KMZ
- Geolocalización integrada

### 🔐 Gestión de Usuarios
- Login personalizado (`ruteo_admin` y `ruteo_worker`)
- Panel de administración de usuarios
- Temas claro/oscuro

### 📊 Google Sheets Sync
- Captura en tiempo real desde campo
- Backend en Google Apps Script
- Proxy PHP para sortear CORS
- Almacenamiento de imágenes en Google Drive

---

## 🐳 Instalación con Docker

```bash
# Clonar el repositorio
git clone https://github.com/AlexanderLoayzaRomero/software-ruteo.git
cd software-ruteo

# Iniciar contenedores
docker-compose up -d

# Acceder a WordPress
# http://localhost:8080
```

### Configuración de Google Sheets

1. Crear un Google Sheet con las columnas correspondientes
2. Ir a **Extensiones > Apps Script** y pegar el contenido de `google_script.js`
3. Desplegar como aplicación web (`Implementar > Nueva implementación`)
4. Copiar la URL del webhook y configurarla en el plugin

---

## 🔌 Shortcodes

| Shortcode | Descripción |
|-----------|-------------|
| `[portal_ruteo]` | Portal principal con dashboard, registros y exportación |
| `[formulario_ruteo]` | Formulario de captura de datos en campo |
| `[login_ruteo]` | Pantalla de inicio de sesión |

---

## 🎨 Roles de Usuario

| Rol | Permisos |
|-----|----------|
| **ruteo_admin** | Ver registros, gestionar usuarios, exportar documentos |
| **ruteo_worker** | Ver registros, crear nuevos registros |

---

## 📄 Exportación de Documentos

### PDF
- Reporte grupal en landscape A4
- Encabezado corporativo azul
- Tabla con 17 columnas y filas alternadas
- Fecha, responsable y total de registros

### Excel
- Hoja "Información" con metadatos
- Hoja "Registros de Campo" con datos formateados
- Hipervínculos a fotos y KMZ
- Fila de encabezados congelada
- Auto-filtro en todas las columnas

### Word (individual por registro)
- Portada con datos del responsable
- Fotografías incrustadas
- Tabla de datos técnicos

---

## 🛠️ Dependencias CDN

| Librería | Versión | Uso |
|----------|---------|-----|
| jsPDF | 2.5.1 | Generación de PDF |
| jsPDF AutoTable | 3.8.2 | Tablas en PDF |
| ExcelJS | 4.4.0 | Generación de Excel |
| docx | 8.5.0 | Generación de Word |
| FileSaver.js | 2.0.5 | Descarga de archivos |

---

## 📋 Esquema de Datos

| Campo | Tipo | Descripción |
|-------|------|-------------|
| Fecha | DateTime | Fecha y hora del registro |
| Tramo | Text | Identificador del tramo |
| ID Consol | Text | ID de consolidación |
| Estructura | Text | Torre, Poste, etc. |
| Tipo Estructura | Text | Metal, Concreto |
| Altura | Number | Altura en metros |
| Código | Text | Código de estructura |
| Ubicación | Text | Dirección o referencia |
| Mufa | Number | Cantidad |
| Retención | Number | Cantidad |
| Suspensión | Number | Cantidad |
| Cruceta | Number | Cantidad |
| Hebillas | Number | Cantidad |
| Fleje | Number | Cantidad |
| Amortiguador | Number | Cantidad |
| Brazo Extensor | Number | Cantidad |
| Kit Retenida | Number | Cantidad |
| Observación | Text | Notas de campo |
| Foto 1 | URL | Imagen (Google Drive) |
| Foto 2 | URL | Imagen (Google Drive) |
| Link KMZ | URL | Archivo KMZ (Google Drive) |

---

## 📝 Licencia

Proyecto privado. Todos los derechos reservados.

---

## 👤 Autor

**Alexander Loayza Romero**

[GitHub](https://github.com/AlexanderLoayzaRomero)
