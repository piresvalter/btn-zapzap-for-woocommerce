<?php
/**
 * Plugin Name: Btn ZapZap For WooCommerce
 * Description: Adiciona um botão "Dúvidas? Clique aqui!" na página single de produtos do WooCommerce e permite que seja usado como shortcode.
 * Version: 1.0
 * Author: Valter Pires
 * License: GPL-2.0-or-later
 */

// Evita acesso direto ao arquivo
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Adiciona o botão à página single do produto
add_action('woocommerce_single_product_summary', 'generate_whatsapp_button', 35);

// Função que gera o botão do WhatsApp
function generate_whatsapp_button() {
    global $product;

    if (!$product instanceof WC_Product) {
        return ''; // Não faz nada se não for produto.
    }

    // Obter o número do WhatsApp da configuração
    $whatsapp_number = get_option('whatsapp_store_number', '5511999999999'); // Número padrão

    // Dados do produto
    $product_name = esc_attr($product->get_name());
    $product_link = esc_url(get_permalink());
    $product_price = esc_attr($product->get_price());

    // Formatação da mensagem do WhatsApp
    $message = "Oi, tudo bem? Quero saber mais sobre o produto: " . urlencode($product_name) . ", Link: " . urlencode($product_link) . " e Valor: R$ " . urlencode($product_price);

    // URL do WhatsApp
    $whatsapp_url = "https://api.whatsapp.com/send?phone={$whatsapp_number}&text={$message}";

    // HTML do botão
    $button_html = '<a href="' . esc_url($whatsapp_url) . '" class="whatsapp-button" target="_blank" style="display: block; width: 100%; border: 2px solid #25D366; background-color: #fff; color: #25D366; text-align: center; padding: 10px; text-decoration: none; font-weight: bold; border-radius: 5px; font-size: 16px;">';
    $button_html .= '<i class="fab fa-whatsapp" style="margin-right: 8px;"></i> Dúvidas? Clique aqui!';
    $button_html .= '</a>';

    return $button_html;
}

// Criação do shortcode
add_shortcode('whatsapp_button', 'generate_whatsapp_button');

// Carregar o Font Awesome para o ícone do WhatsApp
add_action('wp_enqueue_scripts', 'load_font_awesome');

function load_font_awesome() {
    wp_enqueue_style('font-awesome', plugin_dir_url(__FILE__) . 'css/font-awesome.min.css', array(), '5.15.4');
}

// Adiciona a página de opções ao menu do admin
add_action('admin_menu', 'whatsapp_button_menu');

function whatsapp_button_menu() {
    add_options_page('Configurações do WhatsApp', 'WhatsApp Config', 'manage_options', 'whatsapp-button', 'whatsapp_button_options');
}

// Exibe a página de opções
function whatsapp_button_options() {
    if (!current_user_can('manage_options')) {
        return;
    }

    // Checa se as configurações foram salvas
    if (isset($_POST['whatsapp_save']) && isset($_POST['whatsapp_number'])) {
        // Verifica o nonce
        check_admin_referer('whatsapp_button_save');

        // Usa wp_unslash para lidar com o slashing e sanitização
        $whatsapp_number = sanitize_text_field(wp_unslash($_POST['whatsapp_number']));
        update_option('whatsapp_store_number', $whatsapp_number);
        echo '<div class="updated"><p>Configurações salvas.</p></div>';
    }

    $whatsapp_number = get_option('whatsapp_store_number', '5511999999999'); // Número padrão
    ?>
    <div class="wrap">
        <h1>Configurações do WhatsApp</h1>
        <form method="post">
            <?php wp_nonce_field('whatsapp_button_save'); ?>
            <label for="whatsapp_number">Número do WhatsApp da Loja:</label>
            <input type="text" id="whatsapp_number" name="whatsapp_number" value="<?php echo esc_attr($whatsapp_number); ?>" style="width: 100%;">
            <p>Formato: +55XXXXXXXXXXXX (inclua o código do país e o DDD)</p>
            <p><input type="submit" name="whatsapp_save" class="button button-primary" value="Salvar"></p>
        </form>
    </div>
    <?php
}
