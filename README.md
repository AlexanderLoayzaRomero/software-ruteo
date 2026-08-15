# 🗺️ Aplicativo de Ruteo y Gestion de Campo O&M

Sistema integral de captura de datos de campo, trazabilidad de infraestructura de telecomunicaciones, monitoreo en tiempo real, auditoria multiempresa y gestion de seguridad laboral (SST/HSE) para proyectos de ingenieria y mantenimiento. Plugin WordPress desarrollado para la operacion de cuadrillas en campo, con backend conectado a Google Sheets, almacenamiento en Google Drive, modulo de Negativa al Trabajo (HSE-RE-NEG-01) y matriz de firmantes digitales por jerarquia.

---

## 🚀 Stack Tecnologico

| Capa | Tecnologia / Herramienta | Descripcion |
|------|--------------------------|-------------|
| **Frontend** | Plugin WordPress (PHP 8.x + ES6 Vanilla JS + CSS3) | Interfaz SPA sin recarga de pagina, diseño responsivo Glassmorphism con soporte de temas claro/oscuro. |
| **Backend Plugin** | PHP 8.x + WordPress Options & Transients API | Endpoints AJAX personalizados, endpoints de proxy, gestion de roles, autenticacion y logs de auditoria. |
| **Backend Cloud** | Google Apps Script (Google Sheets & Drive API) | Webhook `google_script.js` para almacenamiento en Google Sheets (pestaña por empresa) y gestion de archivos en Drive. |
| **Base de Datos** | MySQL 8.0 / MariaDB | Tablas `wp_users`, `wp_usermeta`, `wp_options` y tabla personalizada `wp_ruteo_empresas`. |
| **Librerias JS** | jsPDF 2.5.1, AutoTable 3.8.2, ExcelJS 4.4.0, docx 8.5.0, FileSaver 2.0.5 | Generacion automatica de documentos PDF, hojas de calculo `.xlsx` avanzadas y reportes Word `.docx` editables. |

---

## 📦 Arquitectura y Estructura de Archivos

```
software-ruteo/
├── README.md                           # Documentacion tecnica maestra del proyecto
├── google_script.js                    # Webhook de Google Apps Script (Sheets Multi-empresa & Drive)
├── .gitignore                          # Exclusiones de control de versiones Git
└── wp-content/
    └── plugins/
        └── wp-ruteo/                   # Plugin principal de WordPress (v2.0.0)
            ├── wp-ruteo.php             # Entry point, shortcodes, AJAX, roles, auditoria y empresas
            ├── assets/
            │   ├── css/
            │   │   └── style.css        # Sistema de diseño, variables CSS, Glassmorphism, Toasts, Responsive
            │   └── js/
            │       └── app.js           # Router SPA, renderizado, firmas canvas, exportacion PDF/Excel/Word
            └── includes/
                ├── portal-template.php  # 🏠 Portal: Dashboard, Registros, Materiales, Auditoria, Usuarios, Negativas
                ├── form-template.php    # 📝 Formulario de captura de campo de 17 campos e inline login
                └── login-template.php   # 🔐 Plantilla de inicio de sesion independiente
```

---

## ⚙️ Modulos Tecnicos del Sistema

### 1. 🏠 Portal de Registros (`[portal_ruteo]`)
- **Dashboard de Control**: KPIs dinamicos en tiempo real con contadores de estructuras (Postes, Torres, Mufas, Camaras, Clientes y total de registros).
- **Filtros Avanzados**: Filtrado dinamico por tramo, texto libre y tipo de estructura.
- **Exportacion PDF Masiva**: Generacion de reporte general horizontal A4 con 19 columnas tecnicas.
- **Exportacion Excel Masiva (`.xlsx`)**: Archivo con portada corporativa, dos pestañas (Informacion General + Detalle de Registros de Campo), 21 columnas e hipervinculos directos a fotografias de Drive y archivo KMZ.
- **Fichas Individuales (PDF / Word)**: Ficha tecnica detallada por estructura con fotografias incrustadas y firmas digitales de los responsables.

### 2. 📝 Formulario de Captura de Campo (`[formulario_ruteo]`)
- Registro completo de infraestructura en campo con 17 campos tecnicos (Tramo, Codigo, Tipo de Estructura, Coordenadas GPS, Materiales, etc.).
- Geolocalizacion automatica mediante API de Ubicacion del navegador.
- Captura de fotografias en formato Base64 con vista previa e integracion directa para subir a Google Drive.
- Tarjeta de restriccion con login inline integrado para usuarios no logueados.

### 3. 🛡️ Modulo de Negativa al Trabajo (HSE-RE-NEG-01)
Modulo diseñado bajo el marco legal de la **Ley 29783 (Art. 63)** y el **D.S. 005-2012-TR** para el derecho del trabajador a interrumpir actividades ante un riesgo grave e inminente.
- **Etapa 1 (Tecnico Reportante):** Registro del evento, causa de la negativa, condiciones de riesgo y subida de 2 evidencias fotograficas.
- **Etapa 2 (Supervisor Operativo):** Evaluacion operativa, definicion de medidas de control y acuerdo de trabajo seguro.
- **Etapa 3 (Supervisor de Seguridad SST):** Inspeccion de seguridad HSE y firma digital de conformidad.
- **Etapa 4 (Area HSE / Gerencia):** Visto bueno final y habilitacion de descarga del reporte PDF oficial impreso.

### 4. 🏢 Gestion Multiempresa y Pestañas Dinamicas en Google Sheets
- Tabla personalizada `wp_ruteo_empresas` para administracion de contratas y empresas colaboradoras.
- El script de Google Apps Script (`google_script.js`) detecta la empresa asignada al registro y crea/escribe automaticamente en una pestaña independiente dedicada a esa empresa dentro del libro de Google Sheets.

### 5. 📜 Modulo de Auditoria y Logs de Sistema
- Trazabilidad total de eventos criticos: Inicios de sesion, creacion/edicion/eliminacion de usuarios, asignacion de permisos de firmantes y registro/modificacion de negativas al trabajo.
- Pestaña protegida en el portal accesible unicamente para usuarios con rol de Administrador.

### 6. 🔔 Sistema de Notificaciones Toast
- Sistema de avisos flotantes interactivos (Toasts) que reemplaza las alertas nativas del navegador (`alert()`).
- Soporta notificaciones de Exito, Error, Advertencia e Informacion con temporizador de auto-cierre y animaciones fluidas.

---

## 🛡️ Jerarquia de Roles y Atribuciones de Firmantes Digitales

El Administrador General es el unico usuario habilitado para configurar y asignar roles, puestos y atribuciones de firma digital desde la seccion de *Gestion de Usuarios*:

| Rol del Sistema | Identificador WP | Atribucion de Firma Autorizada | Responsabilidades y Permisos |
| :--- | :--- | :--- | :--- |
| **Administrador General** | `ruteo_super_admin` / `ruteo_admin` | **Firma Global / Total** | Administracion total del sistema, gestion de usuarios, auditoria, asignacion de empresas y firmado total. |
| **Supervisor Operativo** | `ruteo_sup_operativo` | **Firma Operativa** | Valida liberaciones de obra, pruebas de calidad en mufas/postes, cierre de OT y firma Etapa 2 de Negativas. |
| **Supervisor HSE / Seguridad** | `ruteo_sup_hse` | **Firma HSE / SST** | Valida cumplimiento de normas de seguridad, aprueba AST, checklist EPP y firma Etapas 3 y 4 de Negativas. |
| **Operario de Campo** | `ruteo_worker` | **Firma Ejecutor** | Registra datos de campo, llena checklist inicial de cuadrilla y reporta Etapa 1 de Negativas al Trabajo. |

---

## 🔐 Tabla Maestra de Cuentas y Credenciales del Sistema

### 1. Cuentas de Usuarios del Aplicativo y WordPress (WP-Admin & Portal)

| Usuario | Clave de Acceso | Correo Electronico | Rol del Sistema | Cargo Asignado | Atribucion de Firma / Accesos |
|---------|-----------------|--------------------|-----------------|----------------|-------------------------------|
| `admin_ruteo` | `AdminGeneral123!` | `admin@ruteo.com` | Administrador General (`administrator`) | Administrador General O&M | Acceso Total WP-Admin y Portal |
| `admingeneral` | `AdminGeneral123!` | `admin@software-om.org.pe` | Administrador General (`ruteo_super_admin`) | Administrador General O&M | Acceso Total, Firma Ejecutor, Operativo, HSE |
| `supervisor1` | `Supervisor123!` | `supervisor1@ruteo.org.pe` | Supervisor Operativo (`ruteo_sup_operativo`) | Supervisor Operativo de Campo | Firma Supervisor Operativo (Etapa 2 Negativas) |
| `seguridad1` | `Seguridad123!` | `seguridad1@ruteo.org.pe` | Supervisor HSE (`ruteo_sup_hse`) | Supervisor de Seguridad SST | Firma Supervisor HSE (Etapa 3 Negativas) |
| `hse1` | `Hse123!` | `hse1@ruteo.org.pe` | Supervisor HSE (`ruteo_sup_hse`) | Lider de Area HSE | Firma Supervisor HSE / Visto Bueno (Etapa 4) |
| `tecnico1` | `Tecnico123!` | `tecnico1@ruteo.org.pe` | Operario de Campo (`ruteo_worker`) | Tecnico de Campo O&M | Firma Ejecutor (Etapa 1 Negativas) |
| `worker_ruteo` | `worker123` | `worker@ruteo.com` | Operario de Campo (`ruteo_worker`) | Operario de Campo | Firma Ejecutor |
| `adminnuevo` | `123456` | `nuevo@ruteo.com` | Administrador General (`administrator`) | Administrador Contingencia | Acceso Total WP-Admin |

---

### 2. Script SQL de Homologacion de Contraseñas (para Hostinger / phpMyAdmin)

Para sincronizar de un solo clic todas las contraseñas en tu servidor de Hostinger y hacer que coincidan exactamente con este README, copia y ejecuta este bloque en la pestaña **SQL** de tu phpMyAdmin:

```sql
-- Script de homologacion de contraseñas de usuarios
UPDATE wp_users SET user_pass = MD5('AdminGeneral123!') WHERE user_login IN ('admin_ruteo', 'admingeneral');
UPDATE wp_users SET user_pass = MD5('Supervisor123!') WHERE user_login = 'supervisor1';
UPDATE wp_users SET user_pass = MD5('Seguridad123!') WHERE user_login = 'seguridad1';
UPDATE wp_users SET user_pass = MD5('Hse123!') WHERE user_login = 'hse1';
UPDATE wp_users SET user_pass = MD5('Tecnico123!') WHERE user_login = 'tecnico1';
UPDATE wp_users SET user_pass = MD5('worker123') WHERE user_login = 'worker_ruteo';
UPDATE wp_users SET user_pass = MD5('123456') WHERE user_login = 'adminnuevo';
```

---

## 🌐 Guia de Instalacion y Migracion a Hostinger

1. **Subir Archivos del Plugin:**
   Copiar la carpeta `wp-content/plugins/wp-ruteo` al directorio `public_html/wp-content/plugins/` en Hostinger via FTP o Administrador de Archivos.
2. **Importar Base de Datos MySQL:**
   Importar el archivo `.sql` de tu respaldo en el phpMyAdmin de Hostinger.
3. **Ejecutar Script SQL de Homologacion:**
   Ejecutar el script SQL de la seccion anterior en phpMyAdmin para asegurar que todas las contraseñas funcionen.
4. **Configurar Pagina Estatica:**
   En el panel de WordPress (`wp-admin`), ir a **Ajustes > Lectura**, seleccionar **Una pagina estatica** y asignar la pagina **Portal de Ruteo**.

---

## 🔌 Shortcodes Disponibles en WordPress

| Shortcode | Descripcion | Ubicacion de Pagina Recomendada |
|-----------|-------------|---------------------------------|
| `[portal_ruteo]` | Portal de gestion con Dashboard, Registros, Materiales, Auditoria, Usuarios, Empresas, Negativas y Exportacion. | Pagina `/portal` (Portada) |
| `[formulario_ruteo]` | Formulario de captura de datos de campo con geolocalizacion, fotos y login inline. | Pagina `/formulario` |
| `[login_ruteo]` | Pantalla de inicio de sesion dedicada. | Pagina `/iniciar-sesion` |

---

## 📝 Licencia y Autor

Proyecto de Software de Ingenieria y Ruteo O&M. Todos los derechos reservados.

**Autor:** [Alexander Loayza Romero](https://github.com/AlexanderLoayzaRomero)
