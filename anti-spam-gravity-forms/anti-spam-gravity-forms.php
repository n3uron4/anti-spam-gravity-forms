<?php
/**
 * Plugin Name: Anti-Spam para Gravity Forms
 * Plugin URI: 
 * Description: Plugin personalizado para filtrar spam en Gravity Forms, especialmente de remitentes como "Eric Jones"
 * Version: 1.0.0
 * Author: n3uron4
 * Author URI: 
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: anti-spam-gravity-forms
 */

// Evitar acceso directo al archivo
if (!defined('ABSPATH')) {
    exit;
}

class GF_Anti_Spam_Filter {

    /**
     * Constructor
     */
    public function __construct() {
        // Hook principal para filtrar formularios antes de procesar
        add_filter('gform_pre_submission', array($this, 'check_spam_submission'), 10, 1);
        
        // Añadir página de opciones en el menú de administración
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Registrar configuraciones
        add_action('admin_init', array($this, 'register_settings'));
        
        // Añadir mensaje de activación para forzar actualización de permalinks
        if (get_option('gf_anti_spam_activated') !== 'yes') {
            add_action('admin_notices', array($this, 'activation_notice'));
            update_option('gf_anti_spam_activated', 'yes');
        }
    }
    
    /**
     * Mostrar aviso de activación
     */
    public function activation_notice() {
        ?>
        <div class="notice notice-info is-dismissible">
            <p><strong><?php _e('Anti-Spam para Gravity Forms activado:', 'gf-anti-spam'); ?></strong> <?php _e('Para completar la instalación, por favor ve a Ajustes > Enlaces permanentes y haz clic en "Guardar cambios" sin cambiar nada.', 'gf-anti-spam'); ?></p>
        </div>
        <?php
    }

    /**
     * Verificar si el envío es spam
     * 
     * @param array $form Los datos del formulario
     * @return array Los datos del formulario, posiblemente modificados
     */
    public function check_spam_submission($form) {
        // Obtener opciones guardadas
        $options = get_option('gf_anti_spam_settings', array(
            'blocked_names' => 'Eric Jones',
            'blocked_emails' => '',
            'blocked_words' => 'SEO,Boost,Rank',
            'block_empty_user_agent' => 'yes',
            'submission_speed' => '2'
        ));
        
        // Convertir listas separadas por comas en arrays
        $blocked_names = array_map('trim', explode(',', $options['blocked_names']));
        $blocked_emails = array_map('trim', explode(',', $options['blocked_emails']));
        $blocked_words = array_map('trim', explode(',', $options['blocked_words']));
        
        // Verificar nombre
        foreach ($form['fields'] as $field) {
            if ($field->type == 'name') {
                $input_name = 'input_' . $field->id;
                // Para campos de nombre compuesto
                if (isset($field->inputs) && is_array($field->inputs)) {
                    foreach ($field->inputs as $input) {
                        $input_name = 'input_' . str_replace('.', '_', $input['id']);
                        if (isset($_POST[$input_name]) && $this->is_blocked_name($_POST[$input_name], $blocked_names)) {
                            $this->block_submission('Nombre bloqueado detectado');
                            break;
                        }
                    }
                } 
                // Para campos de nombre simple
                else if (isset($_POST[$input_name]) && $this->is_blocked_name($_POST[$input_name], $blocked_names)) {
                    $this->block_submission('Nombre bloqueado detectado');
                }
            }
        }
        
        // Verificar email
        foreach ($form['fields'] as $field) {
            if ($field->type == 'email') {
                $input_name = 'input_' . $field->id;
                if (isset($_POST[$input_name])) {
                    $email = sanitize_email($_POST[$input_name]);
                    
                    // Verificar dominios/correos bloqueados
                    if ($this->is_blocked_email($email, $blocked_emails)) {
                        $this->block_submission('Correo electrónico bloqueado');
                    }
                }
            }
        }
        
        // Verificar palabras clave en todos los campos
        foreach ($_POST as $key => $value) {
            if (is_string($value) && $this->contains_blocked_words($value, $blocked_words)) {
                $this->block_submission('Palabras bloqueadas detectadas');
            }
        }
        
        // Verificar User Agent vacío
        if ($options['block_empty_user_agent'] == 'yes' && empty($_SERVER['HTTP_USER_AGENT'])) {
            $this->block_submission('User Agent vacío detectado');
        }
        
        // Verificar velocidad de envío (usando una cookie para medir el tiempo)
        if (isset($options['submission_speed']) && $options['submission_speed'] > 0) {
            $form_id = $form['id'];
            $cookie_name = 'gf_form_' . $form_id . '_start_time';
            
            if (isset($_COOKIE[$cookie_name])) {
                $start_time = (int) $_COOKIE[$cookie_name];
                $time_spent = time() - $start_time;
                
                if ($time_spent < (int) $options['submission_speed']) {
                    $this->block_submission('Envío demasiado rápido');
                }
            }
        }
        
        return $form;
    }
    
    /**
     * Verificar si un nombre está en la lista de bloqueados
     */
    private function is_blocked_name($name, $blocked_names) {
        $name = strtolower(trim($name));
        foreach ($blocked_names as $blocked) {
            if (!empty($blocked) && stripos($name, strtolower($blocked)) !== false) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Verificar si un email está en la lista de bloqueados
     */
    private function is_blocked_email($email, $blocked_emails) {
        $email = strtolower(trim($email));
        $email_domain = substr(strrchr($email, "@"), 1);
        
        foreach ($blocked_emails as $blocked) {
            // Si es un dominio (comienza con @)
            if (substr($blocked, 0, 1) == '@') {
                $blocked_domain = strtolower(trim(substr($blocked, 1)));
                if ($email_domain == $blocked_domain) {
                    return true;
                }
            }
            // Si es una dirección de correo completa
            else if (!empty($blocked) && $email == strtolower(trim($blocked))) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Verificar si el texto contiene palabras bloqueadas
     */
    private function contains_blocked_words($text, $blocked_words) {
        $text = strtolower(trim($text));
        foreach ($blocked_words as $word) {
            if (!empty($word) && stripos($text, strtolower($word)) !== false) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Bloquear el envío y mostrar mensaje de error
     */
    private function block_submission($reason) {
        // Registrar el intento de spam en el log
        if (WP_DEBUG) {
            error_log('GF Anti-Spam: Envío bloqueado - ' . $reason);
        }
        
        // Registrar la información del envío para análisis
        $this->log_spam_attempt($reason);
        
        // Detener el procesamiento con un mensaje de error genérico
        wp_die(
            '<h1>' . __('Error de validación', 'gf-anti-spam') . '</h1>' .
            '<p>' . __('Lo sentimos, su envío ha sido identificado como posible spam. Si cree que esto es un error, contacte con el administrador del sitio.', 'gf-anti-spam') . '</p>',
            __('Error de validación', 'gf-anti-spam'),
            array('response' => 403)
        );
    }
    
    /**
     * Registrar intento de spam para análisis
     */
    private function log_spam_attempt($reason) {
        $spam_log = get_option('gf_anti_spam_log', array());
        
        // Limitar el tamaño del registro a las últimas 100 entradas
        if (count($spam_log) > 100) {
            $spam_log = array_slice($spam_log, -99);
        }
        
        // Añadir nuevo registro
        $spam_log[] = array(
            'time' => current_time('mysql'),
            'ip' => $this->get_client_ip(),
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
            'form_data' => $this->sanitize_log_data($_POST),
            'reason' => $reason
        );
        
        update_option('gf_anti_spam_log', $spam_log);
    }
    
    /**
     * Sanitizar datos para el registro
     */
    private function sanitize_log_data($data) {
        $sanitized = array();
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitize_log_data($value);
            } else {
                // No guardar valores de campos de contraseña
                if (strpos($key, 'password') !== false || strpos($key, 'pass') !== false) {
                    $sanitized[$key] = '[REDACTADO]';
                } else {
                    $sanitized[$key] = sanitize_text_field($value);
                }
            }
        }
        return $sanitized;
    }
    
    /**
     * Obtener IP del cliente
     */
    private function get_client_ip() {
        $ip = '';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        return sanitize_text_field($ip);
    }
    
    /**
     * Añadir menú de administración
     */
    public function add_admin_menu() {
        // Añadir una página principal en lugar de un submenú para evitar problemas de ruta
        add_menu_page(
            __('Anti-Spam GF', 'gf-anti-spam'),
            __('Anti-Spam GF', 'gf-anti-spam'),
            'manage_options',
            'gf-anti-spam',
            array($this, 'display_admin_page'),
            'dashicons-shield'
        );
    }
    
    /**
     * Registrar configuraciones
     */
    public function register_settings() {
        register_setting('gf_anti_spam_settings', 'gf_anti_spam_settings');
        
        add_settings_section(
            'gf_anti_spam_main_section',
            __('Configuración de filtros anti-spam', 'gf-anti-spam'),
            array($this, 'main_section_callback'),
            'gf-anti-spam'
        );
        
        add_settings_field(
            'blocked_names',
            __('Nombres bloqueados', 'gf-anti-spam'),
            array($this, 'blocked_names_callback'),
            'gf-anti-spam',
            'gf_anti_spam_main_section'
        );
        
        add_settings_field(
            'blocked_emails',
            __('Emails bloqueados', 'gf-anti-spam'),
            array($this, 'blocked_emails_callback'),
            'gf-anti-spam',
            'gf_anti_spam_main_section'
        );
        
        add_settings_field(
            'blocked_words',
            __('Palabras bloqueadas', 'gf-anti-spam'),
            array($this, 'blocked_words_callback'),
            'gf-anti-spam',
            'gf_anti_spam_main_section'
        );
        
        add_settings_field(
            'block_empty_user_agent',
            __('Bloquear User Agent vacío', 'gf-anti-spam'),
            array($this, 'block_empty_user_agent_callback'),
            'gf-anti-spam',
            'gf_anti_spam_main_section'
        );
        
        add_settings_field(
            'submission_speed',
            __('Tiempo mínimo para completar formulario (segundos)', 'gf-anti-spam'),
            array($this, 'submission_speed_callback'),
            'gf-anti-spam',
            'gf_anti_spam_main_section'
        );
    }
    
    /**
     * Callback para la sección principal
     */
    public function main_section_callback() {
        echo '<p>' . __('Configure los filtros para detectar y bloquear spam en sus formularios de Gravity Forms.', 'gf-anti-spam') . '</p>';
    }
    
    /**
     * Callback para nombres bloqueados
     */
    public function blocked_names_callback() {
        $options = get_option('gf_anti_spam_settings', array('blocked_names' => 'Eric Jones'));
        $value = isset($options['blocked_names']) ? $options['blocked_names'] : '';
        
        echo '<textarea name="gf_anti_spam_settings[blocked_names]" rows="3" cols="50">' . esc_textarea($value) . '</textarea>';
        echo '<p class="description">' . __('Lista de nombres bloqueados, separados por comas. Por ejemplo: Eric Jones, John Doe', 'gf-anti-spam') . '</p>';
    }
    
    /**
     * Callback para emails bloqueados
     */
    public function blocked_emails_callback() {
        $options = get_option('gf_anti_spam_settings', array('blocked_emails' => ''));
        $value = isset($options['blocked_emails']) ? $options['blocked_emails'] : '';
        
        echo '<textarea name="gf_anti_spam_settings[blocked_emails]" rows="3" cols="50">' . esc_textarea($value) . '</textarea>';
        echo '<p class="description">' . __('Lista de correos o dominios bloqueados, separados por comas. Para bloquear dominios enteros use @dominio.com', 'gf-anti-spam') . '</p>';
    }
    
    /**
     * Callback para palabras bloqueadas
     */
    public function blocked_words_callback() {
        $options = get_option('gf_anti_spam_settings', array('blocked_words' => 'SEO,Boost,Rank'));
        $value = isset($options['blocked_words']) ? $options['blocked_words'] : '';
        
        echo '<textarea name="gf_anti_spam_settings[blocked_words]" rows="3" cols="50">' . esc_textarea($value) . '</textarea>';
        echo '<p class="description">' . __('Lista de palabras clave bloqueadas, separadas por comas.', 'gf-anti-spam') . '</p>';
    }
    
    /**
     * Callback para bloquear User Agent vacío
     */
    public function block_empty_user_agent_callback() {
        $options = get_option('gf_anti_spam_settings', array('block_empty_user_agent' => 'yes'));
        $value = isset($options['block_empty_user_agent']) ? $options['block_empty_user_agent'] : 'yes';
        
        echo '<select name="gf_anti_spam_settings[block_empty_user_agent]">';
        echo '<option value="yes" ' . selected($value, 'yes', false) . '>' . __('Sí', 'gf-anti-spam') . '</option>';
        echo '<option value="no" ' . selected($value, 'no', false) . '>' . __('No', 'gf-anti-spam') . '</option>';
        echo '</select>';
        echo '<p class="description">' . __('Bloquear envíos que no tengan User Agent (generalmente bots)', 'gf-anti-spam') . '</p>';
    }
    
    /**
     * Callback para velocidad de envío
     */
    public function submission_speed_callback() {
        $options = get_option('gf_anti_spam_settings', array('submission_speed' => '2'));
        $value = isset($options['submission_speed']) ? $options['submission_speed'] : '2';
        
        echo '<input type="number" name="gf_anti_spam_settings[submission_speed]" value="' . esc_attr($value) . '" min="0" max="60" />';
        echo '<p class="description">' . __('Tiempo mínimo en segundos que debe tardar un usuario en completar el formulario. Use 0 para desactivar.', 'gf-anti-spam') . '</p>';
    }
    
    /**
     * Mostrar página de administración
     */
    public function display_admin_page() {
        // Verificar permisos
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Mostrar pestañas
        $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'settings';
        
        echo '<div class="wrap">';
        echo '<h1>' . __('Anti-Spam para Gravity Forms', 'gf-anti-spam') . '</h1>';
        
        echo '<h2 class="nav-tab-wrapper">';
        echo '<a href="?page=gf-anti-spam&tab=settings" class="nav-tab ' . ($active_tab == 'settings' ? 'nav-tab-active' : '') . '">' . __('Configuración', 'gf-anti-spam') . '</a>';
        echo '<a href="?page=gf-anti-spam&tab=logs" class="nav-tab ' . ($active_tab == 'logs' ? 'nav-tab-active' : '') . '">' . __('Registros', 'gf-anti-spam') . '</a>';
        echo '</h2>';
        
        if ($active_tab == 'settings') {
            echo '<form method="post" action="options.php">';
            settings_fields('gf_anti_spam_settings');
            do_settings_sections('gf-anti-spam');
            submit_button();
            echo '</form>';
            
            // Agregar JavaScript para agregar cookie que mida el tiempo en el formulario
            echo '<h3>' . __('Código JavaScript para medir el tiempo en el formulario', 'gf-anti-spam') . '</h3>';
            echo '<p>' . __('Agrega este código a tu tema o usando un plugin de inserción de código:', 'gf-anti-spam') . '</p>';
            echo '<pre style="background: #f1f1f1; padding: 10px; overflow: auto;">
&lt;script type="text/javascript"&gt;
jQuery(document).ready(function($) {
    // Para cada formulario de Gravity Forms en la página
    $(".gform_wrapper form").each(function() {
        var form_id = $(this).attr("id").split("_")[2];
        // Establecer cookie con el tiempo de inicio
        document.cookie = "gf_form_" + form_id + "_start_time=" + Math.floor(Date.now() / 1000) + "; path=/";
    });
});
&lt;/script&gt;</pre>';
        } 
        else if ($active_tab == 'logs') {
            $this->display_logs_tab();
        }
        
        echo '</div>';
    }
    
    /**
     * Mostrar pestaña de registros
     */
    private function display_logs_tab() {
        $logs = get_option('gf_anti_spam_log', array());
        
        echo '<div class="wrap">';
        
        // Botón para limpiar logs
        if (!empty($logs)) {
            echo '<div style="margin: 15px 0;">';
            echo '<form method="post" action="">';
            wp_nonce_field('clear_spam_logs', 'gf_anti_spam_nonce');
            echo '<input type="hidden" name="action" value="clear_logs">';
            echo '<button type="submit" class="button button-secondary">' . __('Limpiar registros', 'gf-anti-spam') . '</button>';
            echo '</form>';
            echo '</div>';
            
            // Procesar la acción de limpiar
            if (isset($_POST['action']) && $_POST['action'] == 'clear_logs') {
                if (check_admin_referer('clear_spam_logs', 'gf_anti_spam_nonce')) {
                    update_option('gf_anti_spam_log', array());
                    echo '<div class="notice notice-success is-dismissible"><p>' . __('Registros limpiados correctamente.', 'gf-anti-spam') . '</p></div>';
                    $logs = array();
                }
            }
        }
        
        if (empty($logs)) {
            echo '<p>' . __('No hay registros de intentos de spam.', 'gf-anti-spam') . '</p>';
        } else {
            // Mostrar tabla de logs
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>' . __('Fecha/Hora', 'gf-anti-spam') . '</th>';
            echo '<th>' . __('IP', 'gf-anti-spam') . '</th>';
            echo '<th>' . __('Razón', 'gf-anti-spam') . '</th>';
            echo '<th>' . __('User Agent', 'gf-anti-spam') . '</th>';
            echo '<th>' . __('Datos', 'gf-anti-spam') . '</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            
            // Mostrar logs en orden inverso (más recientes primero)
            $logs = array_reverse($logs);
            
            foreach ($logs as $log) {
                echo '<tr>';
                echo '<td>' . esc_html($log['time']) . '</td>';
                echo '<td>' . esc_html($log['ip']) . '</td>';
                echo '<td>' . esc_html($log['reason']) . '</td>';
                echo '<td>' . esc_html($log['user_agent']) . '</td>';
                echo '<td>';
                echo '<a href="#" class="toggle-data">' . __('Mostrar/Ocultar datos', 'gf-anti-spam') . '</a>';
                echo '<div class="spam-data" style="display:none;"><pre>' . esc_html(print_r($log['form_data'], true)) . '</pre></div>';
                echo '</td>';
                echo '</tr>';
            }
            
            echo '</tbody>';
            echo '</table>';
            
            // JavaScript para mostrar/ocultar datos
            echo '<script type="text/javascript">
            jQuery(document).ready(function($) {
                $(".toggle-data").on("click", function(e) {
                    e.preventDefault();
                    $(this).next(".spam-data").toggle();
                });
            });
            </script>';
        }
        
        echo '</div>';
    }
}

// Inicializar el plugin
function gf_anti_spam_init() {
    new GF_Anti_Spam_Filter();
}
add_action('plugins_loaded', 'gf_anti_spam_init');

// Añadir código JavaScript para medir el tiempo en el formulario
function gf_anti_spam_enqueue_scripts() {
    if (class_exists('GFForms')) {
        wp_add_inline_script('jquery', '
            jQuery(document).ready(function($) {
                // Para cada formulario de Gravity Forms en la página
                $(".gform_wrapper form").each(function() {
                    var form_id = $(this).attr("id").split("_")[2];
                    // Establecer cookie con el tiempo de inicio
                    document.cookie = "gf_form_" + form_id + "_start_time=" + Math.floor(Date.now() / 1000) + "; path=/";
                });
            });
        ');
    }
}
add_action('wp_enqueue_scripts', 'gf_anti_spam_enqueue_scripts');