# 🗺️ Aplicativo de Ruteo

Sistema de captura de campo, monitoreo en tiempo real y gestion de seguridad laboral para proyectos de infraestructura. Plugin WordPress con backend en Google Sheets, almacenamiento en Google Drive y modulo de Negativa al Trabajo (HSE-RE-NEG-01).

---

## 🚀 Stack Tecnologico

| Capa | Tecnologia |
|------|-----------|
| **Frontend** | WordPress Plugin (PHP + JavaScript + Vanilla CSS) |
| **Backend** | Google Apps Script (Google Sheets + Drive) + MySQL 8.0 |
| **Librerias** | jsPDF 2.5.1, jsPDF AutoTable 3.8.2, ExcelJS 4.4.0, docx 8.5.0, FileSaver.js 2.0.5 |
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
        └── wp-ruteo/               # Plugin principal (v2.0.0)
            ├── wp-ruteo.php         # Entry point, shortcodes, AJAX handlers, DB Negativas
            ├── assets/
            │   ├── css/
            │   │   └── style.css    # Estilos (light/dark theme, responsive)
            │   └── js/
            │       └── app.js       # Login, formularios, tabs, interfaz
            └── includes/
                ├── portal-template.php   # 🏠 Portal: dashboard, tabla, exportacion, Negativas
                ├── form-template.php     # 📝 Formulario de captura de campo e inline login
                └── login-template.php    # 🔐 Login personalizado
```

---

## ⚙️ Funcionalidades

### 🏠 Portal de Registros (`[portal_ruteo]`)
- ✅ Tabla con todos los registros desde Google Sheets
- ✅ Filtros por tramo y busqueda por texto
- ✅ **PDF grupal**: reporte general en landscape A4 con tabla de 19 columnas formateadas
- ✅ **Excel grupal**: dos hojas (portada "Informacion" + "Registros de Campo"), 21 columnas, hipervinculos a fotos y KMZ
- ✅ **PDF individual** por registro: ficha tecnica con datos y fotografias incrustadas desde Google Drive
- ✅ **Word individual**: ficha tecnica editable en `.docx` generada 100% en el cliente
- ✅ **Modulo Negativa al Trabajo (HSE-RE-NEG-01)**: flujo por etapas para paralizacion de trabajos por riesgo inminente

### 🛡️ Modulo de Negativa al Trabajo (HSE-RE-NEG-01)
- **Etapa 1 (Tecnico):** Registro de razones, base legal (Ley 29783 Art. 63) y carga de 2 evidencias fotograficas (almacenamiento `LONGTEXT` Base64 con previsualizacion instantanea).
- **Etapa 2 (Supervisor Operativo):** Registro de acciones correctivas y acuerdo de condiciones inseguras.
- **Etapa 3 (Supervisor de Seguridad):** Firma de conformidad SST.
- **Etapa 4 (Area HSE):** Visto bueno final y habilitacion de exportacion a PDF oficial.
- **Cuentas de prueba por rol:** Generacion automatica de usuarios (`tecnico1`, `supervisor1`, `seguridad1`, `hse1`).

### 📝 Formulario de Captura (`[formulario_ruteo]`)
- Registro de estructuras de campo (torres, postes, etc.)
- 17 campos tecnicos + observaciones
- Formulario de login inline integrado en la tarjeta de restriccion para usuarios no autenticados
- Subida de fotos (2 por registro) a Google Drive y archivo KMZ con geolocalizacion inteligente

### 🔐 Login y Gestion de Usuarios (`[login_ruteo]`)
- Sistema de roles personalizado (`ruteo_admin`, `ruteo_worker`)
- Selector de Rol de Negativa al Trabajo (`tecnico`, `supervisor_operativo`, `supervisor_seguridad`, `hse`)
- Sesion persistente con `wp_set_auth_cookie`

### 📊 Google Sheets Sync & Resilience
- Proxy PHP (`admin-ajax.php`) optimizado con timeouts de 35s en PHP y 25s en JS para absorber arranques en frio de Google Apps Script
- Versionado dinamico de assets (`filemtime`) para invalidacion automatica de cache del navegador

---

## 🐳 Instalacion con Docker

```bash
git clone https://github.com/AlexanderLoayzaRomero/software-ruteo.git
cd software-ruteo
docker-compose up -d
# http://localhost:8080
```

---

## 🔌 Shortcodes

| Shortcode | Descripcion |
|-----------|-------------|
| `[portal_ruteo]` | Portal principal con dashboard, tabla, Negativa al Trabajo y exportacion |
| `[formulario_ruteo]` | Formulario de captura de datos en campo con login inline |
| `[login_ruteo]` | Pantalla de inicio de sesion |

---

## 🔐 Cuentas de Prueba (Negativa al Trabajo)

| Usuario | Clave de Acceso | Rol Asignado | Etapa que Firma |
|---------|-----------------|--------------|-----------------|
| `tecnico1` | `Tecnico123!` | Tecnico | Etapa 1: Registro y Fotos |
| `supervisor1` | `Supervisor123!` | Supervisor Operativo | Etapa 2: Acciones Correctivas |
| `seguridad1` | `Seguridad123!` | Supervisor de Seguridad | Etapa 3: Firma SST |
| `hse1` | `Hse123!` | Area HSE | Etapa 4: Visto Bueno y PDF |

---

## 📝 Licencia

Proyecto privado. Todos los derechos reservados.

---

## 👤 Autor

**Alexander Loayza Romero**

[GitHub](https://github.com/AlexanderLoayzaRomero)
