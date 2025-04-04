# Anti-Spam para Gravity Forms

Un plugin de WordPress avanzado para filtrar y bloquear el spam en formularios de Gravity Forms, con especial enfoque en bloquear spam conocido como el de "Eric Jones".

![Versión](https://img.shields.io/badge/versión-1.0.1-blue)
![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-0073aa)
![PHP](https://img.shields.io/badge/PHP-7.0%2B-777bb3)
![License](https://img.shields.io/badge/licencia-GPL--2.0%2B-green)
![Gravity Forms](https://img.shields.io/badge/Gravity%20Forms-2.4%2B-orange)

## ✨ Características

✅ **Filtrado inteligente**: Detecta y bloquea el spam basado en múltiples criterios configurables.

✅ **Bloqueo por nombre**: Filtra automáticamente envíos de nombres específicos como "Eric Jones", utilizando múltiples estrategias de detección.

✅ **Bloqueo por email**: Permite bloquear correos electrónicos específicos o dominios completos.

✅ **Análisis de contenido**: Filtra mensajes que contienen palabras clave comunes en spam.

✅ **Detección de bots**: Identifica y bloquea envíos automatizados analizando el User Agent y la velocidad de envío.

✅ **Sistema dual de tiempo**: Utiliza tanto cookies como campos ocultos para mayor compatibilidad entre navegadores.

✅ **Compatible con cualquier WordPress**: Funciona en cualquier configuración de WordPress, independientemente del tema o plugins instalados.

✅ **Registro detallado**: Mantiene un historial completo de los intentos de spam bloqueados para análisis.

✅ **Panel de administración**: Interfaz intuitiva para gestionar todas las configuraciones anti-spam.

✅ **Herramienta de depuración**: Diagnóstico avanzado para identificar y resolver problemas específicos.

## 📥 Instalación

### Método manual
1. Descarga el archivo PHP del plugin desde el repositorio
2. Ve a tu panel de administración de WordPress > Plugins > Añadir nuevo > Subir plugin
3. Selecciona el archivo PHP y haz clic en "Instalar ahora"
4. Activa el plugin desde la sección Plugins
5. Ve a Ajustes > Enlaces permanentes y haz clic en "Guardar cambios" (importante para que funcione el panel de administración)

### Vía FTP
1. Descarga y crea una carpeta llamada `anti-spam-gravity-forms`
2. Coloca el archivo PHP dentro de esta carpeta, renombrándolo a `anti-spam-gravity-forms.php`
3. Sube la carpeta `anti-spam-gravity-forms` al directorio `/wp-content/plugins/` de tu instalación de WordPress
4. Activa el plugin desde la sección Plugins de tu panel de administración
5. Ve a Ajustes > Enlaces permanentes y haz clic en "Guardar cambios"

## 🔧 Uso

Una vez activado el plugin, encontrarás un nuevo menú llamado "Anti-Spam GF" en tu panel de administración de WordPress.

## ⚙️ Configuración

1. Ve a **Anti-Spam GF** en el menú lateral
2. En la pestaña de configuración, personaliza los siguientes ajustes:
   - Nombres bloqueados (por defecto incluye "Eric Jones")
   - Emails bloqueados (direcciones específicas o dominios)
   - Palabras bloqueadas para detectar en el contenido
   - Opciones para detección de bots
3. Guarda los cambios para aplicarlos

### Gestión de nombres bloqueados

1. En el campo "Nombres bloqueados", introduce los nombres que deseas bloquear separados por comas
2. Por defecto, viene configurado con "Eric Jones"
3. Puedes añadir cualquier otro nombre común en spam que estés recibiendo
4. El sistema detectará coincidencias parciales y utilizará múltiples estrategias para detectar nombres en diferentes tipos de campos

### Filtrado de correos electrónicos

1. Para bloquear direcciones específicas, simplemente añádelas al campo "Emails bloqueados"
2. Para bloquear dominios completos, usa el formato `@dominio.com`
3. Cada entrada debe estar separada por comas
4. Ejemplo: `spam@example.com, @spamdominio.com, otro@spam.net`

### Palabras clave bloqueadas

1. En el campo "Palabras bloqueadas", introduce términos comunes en mensajes de spam
2. Por defecto incluye palabras como "SEO", "Boost" y "Rank"
3. Estas palabras se buscarán en todo el contenido del formulario
4. Añade palabras específicas según los patrones de spam que recibas

### Detección de bots

1. Activa la opción "Bloquear User Agent vacío" para filtrar bots básicos
2. Configura el "Tiempo mínimo para completar formulario" para detectar envíos automatizados
3. Los bots suelen completar formularios instantáneamente, mientras que los humanos tardan varios segundos

### Visualización del registro de spam

1. Accede a la pestaña "Registros" en el menú Anti-Spam GF
2. Aquí verás un historial de todos los intentos de spam bloqueados
3. Para cada intento, podrás ver:
   - Fecha y hora
   - Dirección IP
   - Razón del bloqueo
   - User Agent
   - Datos completos del formulario (expandibles)
4. Puedes limpiar el registro con el botón "Limpiar registros"

### Herramienta de depuración

1. Accede a la pestaña "Depuración" en el menú Anti-Spam GF
2. Aquí encontrarás información detallada sobre el último envío de formulario procesado
3. Puedes ver:
   - ID del formulario procesado
   - Campos analizados y sus valores
   - Si se encontraron coincidencias con nombres bloqueados
   - Datos completos del envío
4. Esta herramienta es útil para diagnosticar por qué ciertos envíos no se están bloqueando cuando deberían

## 📋 Ejemplos de uso

### Bloquear spam de "Eric Jones"
- El plugin viene preconfigurado para bloquear envíos con el nombre "Eric Jones"
- No requiere configuración adicional para este caso específico

### Bloquear spam de un dominio específico
- Añade el dominio en formato `@dominio-spam.com` en el campo de emails bloqueados
- Guarda los cambios
- Todos los envíos desde ese dominio serán automáticamente bloqueados

### Identificar y bloquear nuevos patrones de spam
1. Revisa periódicamente la pestaña "Registros"
2. Identifica patrones comunes en los intentos de spam (nombres, dominios, palabras)
3. Actualiza la configuración añadiendo estos nuevos patrones a las listas correspondientes
4. Guarda los cambios para implementar los nuevos filtros

## 💡 Casos de uso

Este plugin es ideal para:

- **Sitios de contacto**: Protege formularios de contacto de spam automatizado
- **Websites comerciales**: Filtra solicitudes falsas que consumirían tiempo del equipo
- **Formularios de inscripción**: Previene registros falsos en eventos o newsletters
- **Blogs con formularios de comentarios**: Reduce la carga de moderación de comentarios spam

## ❓ Solución de problemas

### El menú Anti-Spam GF no aparece
- Ve a Ajustes > Enlaces permanentes y haz clic en "Guardar cambios"
- Desactiva y reactiva el plugin
- Verifica que tienes permisos de administrador

### El spam sigue pasando
- Revisa la configuración y asegúrate de haber guardado los cambios
- Examina los registros para identificar patrones que no estás filtrando
- Utiliza la pestaña de depuración para ver por qué un envío específico no fue bloqueado
- Añade más palabras clave o patrones específicos al spam que recibes
- Considera aumentar el tiempo mínimo para completar el formulario

### Usuarios legítimos son bloqueados
- Revisa las listas de bloqueo para asegurarte de que no son demasiado restrictivas
- Ajusta el tiempo mínimo para completar el formulario si es demasiado alto
- Verifica si alguna palabra bloqueada puede aparecer en contenido legítimo

## 🛠️ Contribución

¡Las contribuciones son bienvenidas! Si deseas contribuir:

1. Haz un fork del repositorio
2. Crea una nueva rama (`git checkout -b feature/nueva-caracteristica`)
3. Realiza tus cambios
4. Haz commit de tus cambios (`git commit -m 'Añade nueva característica'`)
5. Sube tus cambios (`git push origin feature/nueva-caracteristica`)
6. Abre un Pull Request

### Directrices para contribuciones
- Sigue las convenciones de codificación de WordPress
- Asegúrate de que tu código sea compatible con la última versión de WordPress y Gravity Forms
- Incluye comentarios claros en tu código
- Actualiza la documentación si es necesario

## 📜 Licencia

Este plugin está licenciado bajo [GPL-2.0+](http://www.gnu.org/licenses/gpl-2.0.txt). Puedes usar, modificar y distribuir este software bajo los términos de esta licencia.

## 🔄 Registro de Cambios

### 1.0.1 (04-04-2025)
- Sistema dual de medición de tiempo (campo oculto + cookies) para mayor compatibilidad
- Detección mejorada de nombres bloqueados con múltiples estrategias
- Mejor compatibilidad con diferentes versiones de jQuery y JavaScript puro como fallback
- Nueva herramienta de depuración para diagnosticar problemas
- Validaciones adicionales para mayor robustez
- Compatibilidad mejorada con cualquier instalación de WordPress

### 1.0.0 (03-04-2025)
- Lanzamiento inicial del plugin
- Implementación de filtrado por nombres, emails y palabras clave
- Sistema de detección de bots por User Agent y velocidad
- Panel de administración completo
- Sistema de registro de intentos de spam

## 👥 Créditos

Desarrollado por n3uron4

## 📧 Contacto

Para soporte, sugerencias o reportar bugs, por favor:
- Abre un [Issue](https://github.com/n3uron4/anti-spam-gravity-forms/issues) en GitHub

---

Desarrollado con ❤️ para la comunidad WordPress