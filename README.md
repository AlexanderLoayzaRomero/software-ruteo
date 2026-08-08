# 🗺️ Aplicativo de Ruteo y Gestion de Campo O&M

Sistema de captura de datos de campo, trazabilidad de infraestructura, monitoreo en tiempo real, auditoria y gestion de seguridad laboral (SST/HSE) para proyectos de ingenieria y mantenimiento. Plugin WordPress integral con backend en Google Sheets, almacenamiento en Google Drive, modulo de Negativa al Trabajo (HSE-RE-NEG-01) y matriz de firmantes por jerarquia.

---

## 🚀 Stack Tecnologico

| Capa | Tecnologia |
|------|-----------|
| **Frontend** | Plugin WordPress (PHP 8.x + JavaScript ES6 + Vanilla CSS) |
| **Backend** | Google Apps Script (Google Sheets + Drive API) + MySQL 8.0 / WordPress Options |
| **Librerias** | jsPDF 2.5.1, jsPDF AutoTable 3.8.2, ExcelJS 4.4.0, docx 8.5.0, FileSaver.js 2.0.5 |
| **Infraestructura** | Docker (WordPress + MySQL 8.0) |

---

## 📦 Estructura del Proyecto

```
software-ruteo/
├── README.md
├── docker-compose.yml              # Docker: WordPress + MySQL
├── google_script.js                # Google Apps Script (backend API & Drive Proxy)
├── .gitignore
└── wp-content/
    └── plugins/
        └── wp-ruteo/               # Plugin principal (v2.0.0)
            ├── wp-ruteo.php         # Entry point, shortcodes, AJAX handlers, DB & Audit Logs
            ├── assets/
            │   ├── css/
            │   │   └── style.css    # Estilos (temas dinamicos, glassmorphism, responsive)
            │   └── js/
            │       └── app.js       # SPA Router, login, formularios, exportacion, auditoria
            └── includes/
                ├── portal-template.php   # 🏠 Portal: dashboard, auditoria, usuarios, Negativas
                ├── form-template.php     # 📝 Formulario de captura de campo e inline login
                └── login-template.php    # 🔐 Login de acceso general
```

---

## ⚙️ Funcionalidades Principales

### 🏠 Portal de Registros (`[portal_ruteo]`)
- ✅ **Dashboard de Control**: Estadisticas en tiempo real de registros, mufas, postes, torres, clientes y logs de auditoria.
- ✅ **Filtros Avanzados**: Filtrado por tramo, texto libre y **tipo de estructura** (Poste, Torre, Mufa, Camara).
- ✅ **PDF Grupal**: Reporte general formateado en hoja horizontal A4 landscape con 19 columnas tecnicas.
- ✅ **Excel Grupal**: Archivo `.xlsx` con dos pestañas (Portada de Informacion General + Registros de Campo), 21 columnas e hipervinculos directos a fotografias de Drive y archivo KMZ.
- ✅ **PDF Individual**: Ficha tecnica oficial por estructura con datos consolidados e imagenes incrustadas.
- ✅ **Word Individual**: Exportacion a `.docx` 100% editable generada dinamicamente en el navegador del cliente.

### 🛡️ Jerarquia de Roles y Permisos de Firmantes Digitales
El Administrador General es el unico responsable habilitado para configurar y asignar roles, puestos y permisos de firmante al personal en la seccion de *Gestion de Usuarios*:

| Rol del Sistema | Nivel de Jerarquia | Atribucion de Firma Digital Autorizada |
| :--- | :--- | :--- |
| **Administrador General** (`ruteo_admin`) | Nivel 1 (Total) | Permiso total de administracion, gestion de usuarios, parametros, auditoria y firma global. |
| **Supervisor Operativo** (`ruteo_sup_operativo`) | Nivel 2 (Operaciones) | **Firma Operativa:** Valida liberaciones de obra, pruebas de calidad de mufas/postes y cierre de OT. |
| **Supervisor HSE / Seguridad** (`ruteo_sup_hse`) | Nivel 2 (Seguridad) | **Firma HSE:** Valida cumplimiento de normas de seguridad, aprueba AST, checklist EPP y Negativa al Trabajo. |
| **Operario de Campo** (`ruteo_worker`) | Nivel 3 (Ejecutor) | **Firma Ejecutor:** Registra datos tecnicos en campo, llena checklist inicial y firma reportes de cuadrilla. |

### 📜 Modulo de Auditoria y Registro de Logs
- **Trazabilidad en Tiempo Real**: Captura eventos criticos (inicios de sesion, creacion y edicion de usuarios, cambios de roles, permisos de firmantes y firma de actas).
- **Control de Acceso**: Pestaña protegida visible unicamente para usuarios autenticados.
- **Boton de Actualizacion**: Refresco instantaneo via AJAX (`ruteo_get_logs`) de la tabla de auditoria.

### 🛡️ Modulo de Negativa al Trabajo (HSE-RE-NEG-01)
- **Etapa 1 (Tecnico Reportante):** Registro de motivos, base legal (Ley 29783 Art. 63) y carga de 2 evidencias fotograficas en formato Base64 con vista previa.
- **Etapa 2 (Supervisor Operativo):** Registro de medidas correctivas y acuerdos de trabajo seguro.
- **Etapa 3 (Supervisor de Seguridad):** Firma de conformidad SST por el Supervisor HSE.
- **Etapa 4 (Area HSE):** Visto bueno final y habilitacion de descarga del PDF oficial.

### 📝 Formulario de Captura de Campo (`[formulario_ruteo]`)
- Registro completo de estructuras (torres, postes, mufas, camaras) con 17 campos tecnicos.
- Login inline integrado en la tarjeta de restriccion para usuarios no autenticados.
- Carga automatica de fotografias a Google Drive y archivo KMZ con geolocalizacion inteligente.

---

## 🔐 Cuentas de Prueba Preconfiguradas

| Usuario | Clave de Acceso | Rol del Sistema | Cargo Asignado | Atribucion de Firma Activa |
|---------|-----------------|-----------------|----------------|----------------------------|
| `admingeneral` | `AdminGeneral123!` | Administrador General | Administrador General O&M | Ejecutor, Operativo, HSE |
| `supervisor1` | `Supervisor123!` | Supervisor Operativo | Supervisor Operativo de Campo | Firma Supervisor Operativo |
| `seguridad1` | `Seguridad123!` | Supervisor HSE | Supervisor de Seguridad SST | Firma Supervisor HSE |
| `hse1` | `Hse123!` | Supervisor HSE | Lider de Area HSE | Firma Supervisor HSE |
| `tecnico1` | `Tecnico123!` | Operario de Campo | Tecnico de Campo O&M | Firma Ejecutor |

---

## 🐳 Instalacion con Docker

```bash
git clone https://github.com/AlexanderLoayzaRomero/software-ruteo.git
cd software-ruteo
docker-compose up -d
# Acceso al sistema: http://localhost:8080
```

---

## 🔌 Shortcodes de WordPress

| Shortcode | Descripcion |
|-----------|-------------|
| `[portal_ruteo]` | Portal de gestion con dashboard, auditoria, usuarios, Negativa al Trabajo y exportacion |
| `[formulario_ruteo]` | Formulario de captura de datos de campo con login inline |
| `[login_ruteo]` | Pantalla de inicio de sesion |

---

## 📝 Licencia y Autor

Proyecto privado. Todos los derechos reservados.

**Autor:** [Alexander Loayza Romero](https://github.com/AlexanderLoayzaRomero)
