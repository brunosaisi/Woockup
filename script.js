    let canvasElemento;
    let stringDimensaoFinal = "0x0";
    let valorPrecoFinal = "0.00";
    // Variável para armazenar o nome original do arquivo enviado pelo cliente
    let nomeArteSelecionada = "arte-customizada";

    let frameTexture;
    let textureEscura; 
    let img;
    let font;

    let slider, spptr, smold, seletorCor, seletorPpt;

    let carregando = false;

            function preload() {
      // Puxa as configurações tratadas globalmente da ponte segura
      const config = window.WPMockupConfig;
      
      frameTexture = loadImage(config.urlRaiz + '/textura.jpeg', () => {}, () => console.log("Erro textura"));
      font = loadFont(config.urlRaiz + '/OCRAEXT.TTF', () => {}, () => console.log("Erro fonte"));
      
      // ALTERAÇÃO: Força o redraw() automático do p5.js assim que o download do arquivo finalizar
      if (config.urlFotoWoo && config.urlFotoWoo !== "") {
          img = loadImage(config.urlFotoWoo, () => {
              console.log("Imagem do produto carregada com sucesso!");
              redraw(); // Acorda o canvas para aplicar o quadro na tela
          }, () => {
              console.log("Erro ao processar imagem WooCommerce");
          });
      }
    }



     function setup() {
      let container = document.getElementById('canvas-container');
      // Mede o espaço disponível na tela e desconta as margens laterais no computador
      let larguraContainer = windowWidth > 768 ? (windowWidth / 2) - 40 : windowWidth - 20;
      let alturaFixa = 400; 

	  setAttributes('preserveDrawingBuffer', true); 
      canvasElemento = createCanvas(larguraContainer, alturaFixa, WEBGL);
      canvasElemento.parent('canvas-container');
      
      slider = select('#sliderWidth');
      spptr = select('#sliderPpt');
      smold = select('#sliderMold');
      
      seletorCor = selectAll('input[name="seletorCorVisual"]');
		
	// Vincula a mudança do Passe-partout salvando o valor direto na variável e redesenhando
      let radiosPpt = document.getElementsByName('seletorPptVisual');
      for (let i = 0; i < radiosPpt.length; i++) {
        radiosPpt[i].addEventListener('change', function(e) {
          corPptVal = e.target.value;
          redraw();
        });
      }
      
      let btnSalvar = select('#btnSalvarMockup');
      if (btnSalvar) {
        btnSalvar.mousePressed(baixarMockupComoArquivo);
      } 
      
      for (let i = 0; i < seletorCor.length; i++) {
        seletorCor[i].changed(() => { redraw(); });
      }

      if (frameTexture) {
        textureEscura = createGraphics(frameTexture.width, frameTexture.height);
        textureEscura.image(frameTexture, 0, 0);
        textureEscura.fill(80, 50, 25, 160); 
        textureEscura.rect(0, 0, frameTexture.width, frameTexture.height);
      }
      
      // ALTERAÇÃO CIRÚRGICA: Só cria o quadrado cinza se REALMENTE não houver URL configurada no WooCommerce
      if (!img && (!window.WPMockupConfig || window.WPMockupConfig.urlFotoWoo === "")) {
          img = createGraphics(600, 600);
          img.background(220);
          img.fill(100);
          img.textAlign(CENTER, CENTER);
          img.textSize(24);
          img.text("Imagem não encontrada no produto...", 300, 300);
      }
    }
	
    function draw() {
      if (!slider) { background(200); return; }
      if (!spptr) { background(200); return; }
      if (!smold) { background(200); return; }
      if (carregando) { background(200); return; }
      if (!img) { background(200); return; }
      if (!img.width) { background(200); return; }

      background(255);
      orbitControl();
      
      ambientLight(150); 
      directionalLight(230, 230, 230, 0.5, 0.7, -1);


      let prop = img.width / img.height; 
      let valorSlider = float(slider.value());
      let wi, hi;

      if (prop >= 1) {
          // Imagem Horizontal (ou Quadrada): O slider define a LARGURA (wi)
          wi = valorSlider;
          hi = wi / prop;
      } else {
          // Imagem Vertical: O slider define a ALTURA (hi)
          hi = valorSlider;
          wi = hi * prop;
      }

      let w = wi + float(spptr.value()) * 2; 
      let h = hi + float(spptr.value()) * 2; 
      let thick = 20; 
      let larg = float(smold.value()); 
 

      let corVal = 'clara';
      let radioAtivo = select('input[name="seletorCorVisual"]:checked');
      if (radioAtivo) {
        corVal = radioAtivo.value();
      }
      
      if (corVal === 'clara') {
        fill(255);
        if (frameTexture) texture(frameTexture);
      }
      if (corVal === 'escura') {
        fill(255);
        if (textureEscura) texture(textureEscura);
      }
      if (corVal === 'preto') {
        fill(40); 
      }
      if (corVal === 'branco') {
        fill(222); 
      }

      noStroke();

      // Superior
      push(); translate(0, -h/2 - larg/2, 0); box(w + larg*2, larg, thick); pop();

      // Inferior
      push(); translate(0, h/2 + larg/2, 0); box(w + 2*larg, larg, thick); pop();
      
      // Esquerdo
      push(); translate(-w/2 - larg/2, 0, 0); rotate(PI/2); box(h + 2*larg, larg, thick); pop();

      // Direito
      push(); translate(w/2 + larg/2, 0, 0); rotate(PI/2); box(h + 2*larg, larg, thick); pop();

      // Medidas no objeto 3D (Sua fórmula base)
      stringDimensaoFinal = ((w + larg * 2) / 10).toFixed(0) + 'x' + ((h + larg * 2) / 10).toFixed(0);
      
      push();
      translate(-w/2 - larg, h/2 + larg, thick/2);
      fill(0); stroke(255); 
      if (font) { textFont(font); }
      textSize(30);
	  text(((w + larg*2)/10).toFixed(0) + 'x' + ((h + larg*2)/10).toFixed(0), 0, 20 );
      pop();

      // Passepartout
            // ALTERAÇÃO: Lógica para ler a cor do Passe-partout do menu de rádio
      let corPptVal = 'branco';
      let radioPptAtivo = select('input[name="seletorPptVisual"]:checked');
      if (radioPptAtivo) {
        corPptVal = radioPptAtivo.value();
      }
		
	// Passepartout (Liso e ultra leve)
      push(); 
      if (corPptVal === 'branco') { fill(255); }
      if (corPptVal === 'preto') { fill(15); }
      if (corPptVal === 'parana') { fill(235, 215, 190); }
      translate(0, 0, 3); 
      plane(w, h); 
      pop();
      
      // Imagem 
      push(); fill(255); texture(img); translate(0, 0, 3.1); plane(wi, hi); pop();
      
      // CÁLCULOS FINANCEIROS
      let custo = 800*(wi*hi/1000000) + (7*float(spptr.value())*(2*w/1000 + 2*h/1000 + 4*float(spptr.value())/1000)*float(spptr.value()))/50000 + (7+15 + 200)*(w*h/1000000) + ((2*w/1000 + 2*h/1000 + 4*larg/1000)*3*larg/10);
      let precofoto = 500;
      let raz = (custo*3 + precofoto)/700;
      let preco = (custo*3 + precofoto)*(0.5*raz);
		
      // Mantido o arredondamento inteiro conforme sua alteração base
      valorPrecoFinal = preco.toFixed(0); 
                  
           // Atualizações de feedback visual nos spans
      if (document.getElementById('fw')) document.getElementById('fw').innerText = (slider.value() / 10);
      if (document.getElementById('pw')) document.getElementById('pw').innerText = (spptr.value() / 10);
      if (document.getElementById('mw')) document.getElementById('mw').innerText = (smold.value() / 10);

      // ==========================================
      // BLOCO FINAL DO DRAW CORRIGIDO E SINCRONIZADO
      // ==========================================
      
      // 1. CAPTURA AS MEDIDAS DOS SPANS VISÍVEIS
      if (document.getElementById('fw')) document.getElementById('fw').innerText = (slider.value() / 10);
      if (document.getElementById('pw')) document.getElementById('pw').innerText = (spptr.value() / 10);
      if (document.getElementById('mw')) document.getElementById('mw').innerText = (smold.value() / 10);

      // 2. CAPTURA IMEDIATA DO PRINT DO CANVAS (Garante que a imagem seja salva sem atrasos)
      let inputBase64 = document.getElementById('wc_mockup_base64');
      let canvasOriginal = document.getElementById('defaultCanvas0');
      if (canvasOriginal) {
          if (inputBase64) {
              inputBase64.value = canvasOriginal.toDataURL('image/jpeg', 0.85);
          }
      }

      // 3. CAPTURA AS CORES ATIVAS DIRETO DOS SELETORES
      let corMolduraAtiva = 'clara';
      let radioCorAtivo = select('input[name="seletorCorVisual"]:checked');
      if (radioCorAtivo) { 
          corMolduraAtiva = radioCorAtivo.value(); 
      }
      
      // 4. ATUALIZA OS INPUTS OCULTOS COM A FICHA TÉCNICA COMPLETA
           // ==========================================
      // CORREÇÃO SEGURANÇA JS: O preço não vai mais via formulário
      // ==========================================
      
      // Atualiza os inputs ocultos de engenharia estrutural
      let inputDimExt = document.getElementById('wc_mockup_dimensoes_ext');
      let inputConfig = document.getElementById('wc_mockup_config_detalhes');
      
      if (inputDimExt) { inputDimExt.value = stringDimensaoFinal; }
       if (inputConfig) { 
// CORREÇÃO: Trocamos o slider.value() estático pelas variáveis wi e hi reais do draw
          let largImagemCm = (wi / 10).toFixed(2);
          let altImagemCm = (hi / 10).toFixed(2);
          
          inputConfig.value = "Imagem: " + largImagemCm + "x" + altImagemCm + "cm | Passepartout: " + (spptr.value()/10) + "cm (" + corPptVal + ") | Moldura: " + (smold.value()/10) + "cm (" + corMolduraAtiva + ")"; 
      }

      // O preço visual continua mudando para o cliente ver, mas o carrinho irá ignorá-lo
      let elPrecoWoo = document.querySelector('.woocommerce-Price-amount bdi');
      if (elPrecoWoo) {
          elPrecoWoo.innerHTML = 'R$ ' + valorPrecoFinal + ',00';
      }


    }
	
    function baixarMockupComoArquivo() {
      if (canvasElemento) {
        let precoLimpo = valorPrecoFinal.replace('.', '-');
        // MODIFICAÇÃO: Troca a palavra fixa "quadro" pelo nome original formatado da imagem do cliente
        let nomeDoArquivoFinal = 'mockup_' + nomeArteSelecionada + '_' + stringDimensaoFinal + '_R$' + precoLimpo;
        saveCanvas(canvasElemento, nomeDoArquivoFinal, 'jpg');
      }
    }
	
	document.addEventListener('DOMContentLoaded', function() {
    let botaoCarrinhoWoo = document.querySelector('form.cart .single_add_to_cart_button');
    if (botaoCarrinhoWoo) {
        botaoCarrinhoWoo.addEventListener('click', function() {
            // Congela e força a última atualização nos inputs ocultos antes do envio
            if (typeof valorPrecoFinal !== 'undefined' && document.getElementById('wc_mockup_preco')) {
                document.getElementById('wc_mockup_preco').value = valorPrecoFinal;
            }
            if (typeof stringDimensaoFinal !== 'undefined' && document.getElementById('wc_mockup_dimensoes_ext')) {
                document.getElementById('wc_mockup_dimensoes_ext').value = stringDimensaoFinal;
            }
            console.log("Valores congelados e enviados para o carrinho!");
        });
    }
});

	// Adicione este evento no final do seu script JS
document.addEventListener('DOMContentLoaded', function() {
    let botaoCarrinhoWoo = document.querySelector('form.cart .single_add_to_cart_button');
    if (botaoCarrinhoWoo) {
        botaoCarrinhoWoo.addEventListener('click', function(e) {
            let inputBase64 = document.getElementById('wc_mockup_base64');
            let canvasOriginal = document.getElementById('defaultCanvas0'); // ID padrão gerado pelo p5.js
            
            if (canvasOriginal && inputBase64) {
                // Converte a imagem atual em formato web leve (jpeg com 85% de qualidade)
                let imagemConvertida = canvasOriginal.toDataURL('image/jpeg', 0.85);
                inputBase64.value = imagemConvertida;
                console.log("Imagem do mockup capturada com sucesso!");
            }
        });
    }
});
