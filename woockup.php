<?php
add_shortcode('woockup', 'meu_mockup_quadro');

function meu_mockup_quadro() {
    global $product;
    
    $url_imagem_produto = "";
    $nome_produto_limpo = "arte-customizada";
    
    if ( $product ) {
        $id_imagem = $product->get_image_id();
        if ( $id_imagem ) {
            $dados_img = wp_get_attachment_image_src($id_imagem, 'full');
            // GARANTIA: Extrai estritamente a string da URL (índice 0) se for um array válido
            if ( $dados_img && is_array($dados_img) ) {
                $url_imagem_produto = $dados_img[0]; 
            }
        }
        $nome_produto_limpo = sanitize_title($product->get_name());
    }

    $url_base_site = get_site_url();
    ob_start(); 
    ?>

//html vai aqui, script.js direto no fim do body.
    //COLOQUE ESTE CÓDIGO INTEIRO EM UM SNIPPET PHP
// depois só chame [woockup] direto no código da página do produto, e ele vai puxar a imagem do produto para gerar o mockup.

    <?php
    return ob_get_clean(); 
}
// 1. Anexa os metadados calculados pelo script ao adicionar o item no carrinho

// 2. CÁLCULO FINANCEIRO EXECUTADO EXCLUSIVAMENTE NO SERVIDOR COM TRAVAS DE SEGURANÇA IMUTÁVEIS
add_action('woocommerce_before_calculate_totals', 'aplicar_preco_dinamico_mockup', 20, 1);
function aplicar_preco_dinamico_mockup($cart) {
    if (is_admin()) {
        if (!defined('DOING_AJAX')) {
            return;
        }
    }
    
    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        if (isset($cart_item['mockup_custom_detalhes'])) {
            
            // Extrai as especificações da string para reconstruir os dados matemáticos reais
            $detalhes = $cart_item['mockup_custom_detalhes'];
            
            preg_match('/Imagem: ([\d.]+)x([\d.]+)/', $detalhes, $matches_img);
            preg_match('/Passepartout: ([\d.]+)/', $detalhes, $matches_ppt);
            preg_match('/Moldura: ([\d.]+)/', $detalhes, $matches_mold);
            
            if (count($matches_img) >= 3 && count($matches_ppt) >= 2 && count($matches_mold) >= 2) {
                // Converte de volta para a escala padrão multiplicada por 10 que a sua fórmula usa
				$valor_slider_bruto = floatval($matches_img[1]) * 10;
                $spptr_bruto = floatval($matches_ppt[1]) * 10;
                $smold_bruto = floatval($matches_mold[1]) * 10;
                
                // BLINDAGEM DE LIMITES: Garante que os valores fiquem estritamente dentro das regras dos sliders (Evita fraudes de valores bizarros)
                $valor_slider = max(50, min(900, $valor_slider_bruto));
                $spptr_val = max(0, min(50, $spptr_bruto));
                $smold_val = max(10, min(50, $smold_bruto));
                
				// Valores padrão de segurança caso a imagem falhe
				$w = $wi + ($spptr_val * 2);
                $h = $hi + ($spptr_val * 2);
				$larg = $smold_val;
				
				//Descobre a orientação da arte original no servidor sem usar o operador &&
                $product = $cart_item['data'];
                $id_imagem = $product->get_image_id();
                
                if ($id_imagem) {
                    $dados_img = wp_get_attachment_image_src($id_imagem, 'full');
                    
                    if ($dados_img) {
                        if (is_array($dados_img)) {
                            $largura_original = floatval($dados_img[1]);
                            $altura_original = floatval($dados_img[2]);
                            
                            if ($largura_original > 0) {
                                if ($altura_original > 0) {
                                    $proporcao = $largura_original / $altura_original;
                                    
                                    if ($proporcao >= 1) {
                                    // Imagem Horizontal ou Quadrada: Slider define a largura
                                        $wi = $valor_slider;
                                        $hi = $wi / $proporcao;
                                    } else {
                                    // Imagem Vertical: Slider define a altura
                                        $hi = $valor_slider;
                                        $wi = $hi * $proporcao;
                                    }
                                }
                            }
                        }
                    }
                }
                
                // EXECUÇÃO DA SUA FÓRMULA MATEMÁTICA IDÊNTICA (Mas imutável no PHP)
                $custo = 800 * ($wi * $hi / 1000000) + (7 * $spptr_val * (2 * $w / 1000 + 2 * $h / 1000 + 4 * $spptr_val / 1000) * $spptr_val) / 50000 + (7 + 15 + 200) * ($w * $h / 1000000) + ((2 * $w / 1000 + 2 * $h / 1000 + 4 * $larg / 1000) * 3 * $larg / 10);
                $precofoto = 500;
                $raz = ($custo * 3 + $precofoto) / 700;
                $preco_final_calculado = ($custo * 3 + $precofoto) * (0.5 * $raz);
                
                // Arredonda para inteiro exatamente igual ao script do JS (.toFixed(0))
                $preco_servidor_blindado = round($preco_final_calculado);
                
                // Injeta o preço de forma definitiva e imutável
                $cart_item['data']->set_price($preco_servidor_blindado);
                $cart_item['data']->set_regular_price($preco_servidor_blindado);
                $cart_item['data']->set_sale_price(''); 
            }
            
            // Injeta o mockup de imagem individualizado por linha do carrinho
            if (isset($cart_item['mockup_custom_url_imagem'])) {
                if (!empty($cart_item['mockup_custom_url_imagem'])) {
                    $url_mockup_exclusivo = $cart_item['mockup_custom_url_imagem'];
                    $cart_item['data']->set_image_id('mockup_custom_id_' . $cart_item_key);
                    
                    add_filter('wp_get_attachment_image_src', function($image, $attachment_id, $size, $icon) use ($url_mockup_exclusivo, $cart_item_key) {
                        if ($attachment_id === 'mockup_custom_id_' . $cart_item_key) {
                            return array($url_mockup_exclusivo, 300, 300, false);
                        }
                        return $image;
                    }, 99, 4);
                }
            }
            
            // Ajusta dimensões físicas exclusivas para o cálculo de frete baseado nos dados travados
            if (isset($cart_item['mockup_custom_dim_ext'])) {
                $medidas = explode('x', $cart_item['mockup_custom_dim_ext']);
                if (count($medidas) == 2) {
                    $cart_item['data']->set_width(floatval($medidas[0]));
                    $cart_item['data']->set_length(floatval($medidas[1]));
                }
            }
        }
    }
}

// 3. Mostra os detalhes escolhidos para o cliente na listagem do carrinho e no checkout
// ALTERAÇÃO NO PHP: Junta as informações e troca o divisor padrão "/" por "|"
add_filter('woocommerce_get_item_data', 'exibir_detalhes_mockup_carrinho', 10, 2);
function exibir_detalhes_mockup_carrinho($item_data, $cart_item) {
    $texto_completo = "";

    // 1. Coleta as Dimensões Externas e adiciona a barra vertical na sequência
    if (isset($cart_item['mockup_custom_dim_ext'])) {
        if (!empty($cart_item['mockup_custom_dim_ext'])) {
            $texto_completo .= "Dimensões Externas: " . $cart_item['mockup_custom_dim_ext'] . " cm";
        }
    }

    // 2. Concatena os outros detalhes usando a separação por "|" de forma limpa
    if (isset($cart_item['mockup_custom_detalhes'])) {
        if (!empty($cart_item['mockup_custom_detalhes'])) {
            if ($texto_completo !== "") {
                $texto_completo .= " | "; // Injeta a barra vertical no lugar da "/" padrão
            }
            $texto_completo .= $cart_item['mockup_custom_detalhes'];
        }
    }

    // 3. Devolve para o WooCommerce como uma única linha de metadados sem título
    if ($texto_completo !== "") {
        $item_data[] = array(
            'name'  => '', // Mantém o rótulo escondido
            'value' => $texto_completo
        );
    }

    return $item_data;
}

// 4. Salva as configurações permanentemente na Nota do Pedido do painel admin do WooCommerce
add_action('woocommerce_checkout_create_order_line_item', 'salvar_mockup_no_pedido_final', 10, 4);
function salvar_mockup_no_pedido_final($item, $cart_item_key, $values, $order) {
    if (isset($values['mockup_custom_dim_ext'])) {
        $item->add_meta_data('Dimensões Externas', $values['mockup_custom_dim_ext'] . ' cm');
    }
    if (isset($values['mockup_custom_detalhes'])) {
        $item->add_meta_data('Especificações Técnicas', $values['mockup_custom_detalhes']);
    }
}

// 1. CAPTURA AS VARIÁVEIS DIMENSIONAIS HIGIENIZADAS NO CLIQUE
add_filter('woocommerce_add_cart_item_data', 'salvar_dados_mockup_no_carrinho', 10, 3);
function salvar_dados_mockup_no_carrinho($cart_item_data, $product_id, $variation_id) {
    if (isset($_POST['mockup_dimensoes_ext'])) {
        if ($_POST['mockup_dimensoes_ext'] !== '') {
            $cart_item_data['mockup_custom_dim_ext'] = sanitize_text_field($_POST['mockup_dimensoes_ext']);
            $cart_item_data['mockup_custom_detalhes'] = sanitize_text_field($_POST['mockup_config_detalhes']);
            
            // CONVERSÃO E GRAVAÇÃO DA IMAGEM
            if (isset($_POST['mockup_base64'])) {
                if (!empty($_POST['mockup_base64'])) {
                    $dados_limpos = preg_replace('#^data:image/\w+;base64,#i', '', $_POST['mockup_base64']);
                    $dados_puros = base64_decode($dados_limpos);
                    $pasta_fisica = WP_CONTENT_DIR . '/uploads/mockups-pedidos';
                    
                    if (!file_exists($pasta_fisica)) {
                        wp_mkdir_p($pasta_fisica);
                    }
                    
                    $nome_arquivo = 'quadro_' . time() . '_' . uniqid() . '.jpg';
                    $caminho_final_servidor = $pasta_fisica . '/' . $nome_arquivo;
                    
                    if (file_put_contents($caminho_final_servidor, $dados_puros)) {
                        $cart_item_data['mockup_custom_url_imagem'] = content_url('/uploads/mockups-pedidos/' . $nome_arquivo);
                    }
                }
            }
            
            // A chave única agora não depende mais do preço do formulário, blindando a transação
            $cart_item_data['unique_key'] = md5($product_id . '_' . $_POST['mockup_dimensoes_ext'] . '_' . microtime());
        }
    }
    return $cart_item_data;
}

// 2. BLINDAGEM COMPLETA: CÁLCULO FINANCEIRO EXECUTADO EXCLUSIVAMENTE NO SERVIDOR (PHP)
add_action('woocommerce_before_calculate_totals', 'aplicar_preco_e_imagem_no_carrinho', 20, 1);
function aplicar_preco_e_imagem_no_carrinho($cart) {
    if (is_admin()) {
        if (!defined('DOING_AJAX')) {
            return;
        }
    }
    
    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        if (isset($cart_item['mockup_custom_detalhes'])) {
            
            // Extrai de forma cirúrgica as especificações originais da string para reconstruir os dados matemáticos reais
            $detalhes = $cart_item['mockup_custom_detalhes'];
            
            preg_match('/Imagem: ([\d.]+)x([\d.]+)/', $detalhes, $matches_img);
            preg_match('/Passepartout: ([\d.]+)/', $detalhes, $matches_ppt);
            preg_match('/Moldura: ([\d.]+)/', $detalhes, $matches_mold);
            
            if (count($matches_img) >= 3 && count($matches_ppt) >= 2 && count($matches_mold) >= 2) {
                // Converte de volta para a escala padrão multiplicada por 10 que a sua fórmula usa
                $wi = floatval($matches_img[1]) * 10;
                $hi = floatval($matches_img[2]) * 10;
                $spptr_val = floatval($matches_ppt[1]) * 10;
                $smold_val = floatval($matches_mold[1]) * 10;
                
                // Reconstrói as variáveis W, H e LARG da sua fórmula matemática original
                $w = $wi + ($spptr_val * 2);
                $h = $hi + ($spptr_val * 2);
                $larg = $smold_val;
                
                // EXECUÇÃO DA SUA FÓRMULA MATEMÁTICA IDÊNTICA (Mas imutável no PHP)
                $custo = 800 * ($wi * $hi / 1000000) + (7 * $spptr_val * (2 * $w / 1000 + 2 * $h / 1000 + 4 * $spptr_val / 1000) * $spptr_val) / 50000 + (7 + 15 + 200) * ($w * $h / 1000000) + ((2 * $w / 1000 + 2 * $h / 1000 + 4 * $larg / 1000) * 3 * $larg / 10);
                $precofoto = 500;
                $raz = ($custo * 3 + $precofoto) / 700;
                $preco_final_calculado = ($custo * 3 + $precofoto) * (0.5 * $raz);
                
                // Arredonda para inteiro exatamente igual ao script do JS (.toFixed(0))
                $preco_servidor_blindado = round($preco_final_calculado);
                
                // Injeta o preço de forma definitiva e imutável
                $cart_item['data']->set_price($preco_servidor_blindado);
                $cart_item['data']->set_regular_price($preco_servidor_blindado);
                $cart_item['data']->set_sale_price(''); 
            }
            
            // Injeta o mockup de imagem individualizado por linha
            if (isset($cart_item['mockup_custom_url_imagem'])) {
                if (!empty($cart_item['mockup_custom_url_imagem'])) {
                    $url_mockup_exclusivo = $cart_item['mockup_custom_url_imagem'];
                    $cart_item['data']->set_image_id('mockup_custom_id_' . $cart_item_key);
                    
                    add_filter('wp_get_attachment_image_src', function($image, $attachment_id, $size, $icon) use ($url_mockup_exclusivo, $cart_item_key) {
                        if ($attachment_id === 'mockup_custom_id_' . $cart_item_key) {
                            return array($url_mockup_exclusivo, 300, 300, false);
                        }
                        return $image;
                    }, 99, 4);
                }
            }
            
            // Ajusta dimensões físicas de frete
            if (isset($cart_item['mockup_custom_dim_ext'])) {
                $medidas = explode('x', $cart_item['mockup_custom_dim_ext']);
                if (count($medidas) == 2) {
                    $cart_item['data']->set_width(floatval($medidas[0]));
                    $cart_item['data']->set_length(floatval($medidas[1]));
                }
            }
        }
    }
}

// Adicione este novo filtro ao final do seu arquivo de Snippets
// Substitua a função de exibição no e-mail por esta versão com tamanho ajustável e link
add_filter('woocommerce_order_item_thumbnail', 'forcar_imagem_mockup_nos_emails_woocommerce', 99, 2);
function forcar_imagem_mockup_nos_emails_woocommerce($thumbnail, $item) {
    $meta_data = $item->get_meta_data();
    
    foreach ($meta_data as $meta) {
        // Alinhado com a nova chave oculta contendo o underline
        if ($meta->key === '_mockup_custom_url_imagem' || $meta->key === 'mockup_custom_url_imagem') {
            $url_mockup_exclusivo = $meta->value;
            
            if (!empty($url_mockup_exclusivo)) {
                // ONDE MEXER NO TAMANHO: Altere os valores de width abaixo (Ex: 150 para 150px)
                $largura_email = '150'; 
                
                // Monta a imagem maior e envelopada com o link clicável que abre a foto em tamanho cheio
                return '<a href="' . esc_url($url_mockup_exclusivo) . '" target="_blank" style="text-decoration: none; display: block;">
                            <img src="' . esc_url($url_mockup_exclusivo) . '" alt="Seu Quadro Customizado" width="' . $largura_email . '" style="width: ' . $largura_email . 'px; height: auto; display: block; border-radius: 6px; border: 1px solid #e5e5e5; margin-right: 15px;" />
                        </a>';
            }
        }
    }
    return $thumbnail;
}

// 4. GARANTIA: Salva a URL pública da imagem fisicamente na tabela de itens do pedido no checkout
// Precisamos atualizar a nossa função existente que grava os metadados no checkout para incluir a URL
// Substitua a função de salvamento anterior por esta versão corrigida
add_action('woocommerce_checkout_create_order_line_item', 'atualizar_salvamento_mockup_no_pedido_com_imagem', 15, 4);
function atualizar_salvamento_mockup_no_pedido_com_imagem($item, $cart_item_key, $values, $order) {
    if (isset($values['mockup_custom_url_imagem'])) {
        if (!empty($values['mockup_custom_url_imagem'])) {
            // ALTERAÇÃO: Adicionado o "_" antes do nome. Isso oculta a linha de texto do cliente automaticamente!
            $item->add_meta_data('_mockup_custom_url_imagem', $values['mockup_custom_url_imagem']);
        }
    }
}

// Substitua a função anterior por esta versão com trava de segurança para e-mails
add_filter('woocommerce_order_item_name', 'exibir_mockup_na_pagina_de_pedido_e_conta', 99, 2);
function exibir_mockup_na_pagina_de_pedido_e_conta($item_name, $item) {
    // 1. TRAVA DE SEGURANÇA: Se for o painel administrativo ou um disparo de e-mail, não injeta a segunda imagem
    if (is_admin()) {
        return $item_name;
    }
    
    // Verifica nativamente se o WordPress está gerando o escopo de um e-mail do WooCommerce
    if (doing_action('woocommerce_email_order_details') || doing_filter('woocommerce_email_order_items_table')) {
        return $item_name;
    }

    // 2. Procura pela nossa chave de imagem oculta gravada no banco de dados
    $url_mockup_exclusivo = $item->get_meta('_mockup_custom_url_imagem');
    
    if (empty($url_mockup_exclusivo)) {
        $url_mockup_exclusivo = $item->get_meta('mockup_custom_url_imagem');
    }

    if (!empty($url_mockup_exclusivo)) {
        $largura_miniatura = '90';

        // Monta a tag HTML colocando a imagem clicável colada ao lado esquerdo do nome do produto
        $html_imagem = '<a href="' . esc_url($url_mockup_exclusivo) . '" target="_blank" style="text-decoration: none; display: inline-block; vertical-align: middle; margin-right: 15px; float: left;">
                            <img src="' . esc_url($url_mockup_exclusivo) . '" alt="Seu Quadro Customizado" width="' . $largura_miniatura . '" style="width: ' . $largura_miniatura . 'px; height: auto; display: block; border-radius: 6px; border: 1px solid #e5e5e5; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" />
                        </a>';

        return $html_imagem . '<span style="display: inline-block; vertical-align: middle; padding-top: 10px;">' . $item_name . '</span><div style="clear:both;"></div>';
    }

    return $item_name;
}
// 1. Agenda o evento de limpeza automática se ele não existir
if (!wp_next_scheduled('woockup_limpar_mockups_antigos_cron')) {
    wp_schedule_event(time(), 'daily', 'woockup_limpar_mockups_antigos_cron');
}

// 2. Vincula a função de varredura e exclusão ao evento agendado
// CORREÇÃO DE SEGURANÇA: Limpeza inteligente que protege imagens de pedidos ativos
add_action('woockup_limpar_mockups_antigos_cron', 'woockup_executar_limpeza_de_arquivos');
function woockup_executar_limpeza_de_arquivos() {
    $pasta_fisica = WP_CONTENT_DIR . '/uploads/mockups-pedidos';
    
    if (file_exists($pasta_fisica)) {
        $arquivos = glob($pasta_fisica . '/*.jpg');
        $tempo_limite = 7 * DAY_IN_SECONDS; // 7 dias de vida útil para quem abandonou o carrinho
        $agora = time();
        
        foreach ($arquivos as $arquivo) {
            if (is_file($arquivo)) {
                $data_criacao = filemtime($arquivo);
                
                // Se o arquivo tiver mais de 7 dias na pasta, fazemos a checagem de segurança
                if (($agora - $data_criacao) > $tempo_limite) {
                    $nome_base_arquivo = basename($arquivo);
                    
                    // Consulta o banco de dados para ver se essa imagem específica pertence a alguma compra real
                    global $wpdb;
                    $id_pedido_vinculado = $wpdb->get_var( $wpdb->prepare(
                        "SELECT order_id FROM {$wpdb->prefix}wc_order_product_lookup_meta 
                         WHERE meta_key IN ('_mockup_custom_url_imagem', 'mockup_custom_url_imagem') 
                         AND meta_value LIKE %s LIMIT 1",
                        '%' . $wpdb->esc_like($nome_base_arquivo) . '%'
                    ) );

                    // Se não encontrou nenhum pedido no banco de dados com essa imagem, ela veio de um carrinho abandonado. Pode deletar!
                    if (empty($id_pedido_vinculado)) {
                        unlink($arquivo);
                    }
                }
            }
        }
    }
}


// 3. Remove o agendamento do banco se o snippet for desativado um dia, mantendo o WordPress limpo
register_deactivation_hook(__FILE__, 'woockup_remover_cron_na_desativacao');
function woockup_remover_cron_na_desativacao() {
    $timestamp = wp_next_scheduled('woockup_limpar_mockups_antigos_cron');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'woockup_limpar_mockups_antigos_cron');
    }
}