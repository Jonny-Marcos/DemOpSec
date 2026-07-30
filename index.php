<?php
$dbFile = __DIR__ . '/presenca.db';

try {
    $pdo = new PDO("sqlite:" . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Garante que a tabela de configuração exista
    $pdo->exec("CREATE TABLE IF NOT EXISTS app_config (config_key TEXT PRIMARY KEY, config_value TEXT)");
    
    // Injeta os valores padrão (Militar) se o banco estiver vazio
    $stmt = $pdo->query("SELECT COUNT(*) FROM app_config");
    if ($stmt->fetchColumn() == 0) {
        $defaults = [
            'titulo_pagina' => 'Registro de Presença em Instrução',
            'subtitulo' => 'Confirme sua participação preenchendo as informações abaixo.',
            'titulo_dashboard' => 'Registro de Instrução',
            'campo_1_label' => 'Posto / Graduação',
            'campo_1_opcoes' => 'Sd, Cb, 3º Sgt, 2º Sgt, 1º Sgt, S Ten, Asp, 2º Ten, 1º Ten, Cap, Maj, TC, Cel',
            'campo_2_label' => 'Cia / Subunidade / Seção',
            'campo_2_opcoes' => '1ª Cia, 2ª Cia, CCAp, EM / Seção, Outro',
            'campo_3_label' => 'Nome de Guerra',
            'campo_3_placeholder' => 'Ex: Silva'
        ];
        $insert = $pdo->prepare("INSERT INTO app_config (config_key, config_value) VALUES (?, ?)");
        foreach ($defaults as $k => $v) { $insert->execute([$k, $v]); }
    }

    // Carrega as configurações para montar a tela
    $stmt = $pdo->query("SELECT config_key, config_value FROM app_config");
    $CONFIG = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Cria a tabela de presenças caso não exista
    $pdo->exec("CREATE TABLE IF NOT EXISTS presencas (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        val_campo_1 TEXT, val_campo_2 TEXT, val_campo_3 TEXT,
        visitor_id TEXT, ip_address TEXT, provedor TEXT, localizacao TEXT,
        user_agent TEXT, resolucao TEXT, idioma TEXT, fuso_horario TEXT, 
        plataforma TEXT, nucleos_cpu TEXT, memoria_ram TEXT, gpu TEXT, 
        bateria TEXT, conexao TEXT, http_headers TEXT, ip_local TEXT, status_vuln TEXT,
        data_hora DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

} catch (PDOException $e) {
    die("Erro no Banco de Dados: " . $e->getMessage());
}

$mensagemSucesso = false;

// Processamento do Formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $headers = [];
    foreach ($_SERVER as $key => $value) {
        if (substr($key, 0, 5) == 'HTTP_') {
            $headers[str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))))] = $value;
        }
    }
    $jsonHeaders = json_encode($headers);

    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $vulns = [];
    if (preg_match('/Windows NT ([0-9\.]+)/', $ua, $m) && floatval($m[1]) < 10.0) $vulns[] = "CRÍTICO: Windows Obsoleto";
    if (preg_match('/Android\s([0-9\.]+)/', $ua, $m) && floatval($m[1]) < 13) $vulns[] = "CRÍTICO: Android Desatualizado";
    if (preg_match('/iPhone OS\s([0-9_]+)/', $ua, $m) && floatval(str_replace('_', '.', $m[1])) < 16.5) $vulns[] = "CRÍTICO: iOS Desatualizado";
    
    if (preg_match('/Edg\/([0-9]+)/', $ua, $m) && intval($m[1]) < 115) $vulns[] = "Edge Desatualizado";
    elseif (preg_match('/Firefox\/([0-9]+)/', $ua, $m) && intval($m[1]) < 115) $vulns[] = "Firefox Desatualizado";
    elseif (preg_match('/Chrome\/([0-9]+)/', $ua, $m) && intval($m[1]) < 115) $vulns[] = "Chrome Desatualizado";
    
    $statusVuln = count($vulns) > 0 ? implode(" | ", $vulns) : "Seguro (Atualizado)";

    $stmt = $pdo->prepare("INSERT INTO presencas (
        val_campo_1, val_campo_2, val_campo_3, visitor_id, ip_address, provedor, localizacao,
        user_agent, resolucao, idioma, fuso_horario, plataforma, nucleos_cpu, memoria_ram, gpu, bateria, conexao,
        http_headers, ip_local, status_vuln
    ) VALUES (
        :v1, :v2, :v3, :visitor_id, :ip_address, :provedor, :localizacao,
        :user_agent, :resolucao, :idioma, :fuso_horario, :plataforma, :nucleos_cpu, :memoria_ram, :gpu, :bateria, :conexao,
        :http_headers, :ip_local, :status_vuln
    )");

    $stmt->execute([
        ':v1' => $_POST['campo_1'] ?? '', ':v2' => $_POST['campo_2'] ?? '', ':v3' => $_POST['campo_3'] ?? '',
        ':visitor_id' => $_POST['visitor_id'] ?? 'N/A', ':ip_address' => $_POST['ip_address'] ?? $_SERVER['REMOTE_ADDR'],
        ':provedor' => $_POST['provedor'] ?? 'Desconhecido', ':localizacao' => $_POST['localizacao'] ?? 'Desconhecido',
        ':user_agent' => $ua, ':resolucao' => $_POST['resolucao'] ?? 'N/A', ':idioma' => $_POST['idioma'] ?? 'N/A',
        ':fuso_horario' => $_POST['fuso_horario'] ?? 'N/A', ':plataforma' => $_POST['plataforma'] ?? 'N/A',
        ':nucleos_cpu' => $_POST['nucleos_cpu'] ?? 'N/A', ':memoria_ram' => $_POST['memoria_ram'] ?? 'N/A',
        ':gpu' => $_POST['gpu'] ?? 'N/A', ':bateria' => $_POST['bateria'] ?? 'N/A', ':conexao' => $_POST['conexao'] ?? 'N/A',
        ':http_headers' => $jsonHeaders, ':ip_local' => $_POST['ip_local'] ?? 'Bloqueado/Oculto', ':status_vuln' => $statusVuln,
    ]);

    $mensagemSucesso = true;
}

// Convertendo as strings de opções em arrays
$opcoes_campo_1 = array_map('trim', explode(',', $CONFIG['campo_1_opcoes']));
$opcoes_campo_2 = array_map('trim', explode(',', $CONFIG['campo_2_opcoes']));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($CONFIG['titulo_pagina']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>const fpPromise = import('https://openfpcdn.io/fingerprintjs/v4').then(FingerprintJS => FingerprintJS.load());</script>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex items-center justify-center p-4 relative overflow-hidden">
    <div class="max-w-md w-full bg-slate-800 border border-slate-700 rounded-2xl p-6 shadow-2xl relative z-10">
        <div class="text-center space-y-2 mb-6">
            <h1 class="text-2xl font-bold text-white tracking-tight"><?= htmlspecialchars($CONFIG['titulo_pagina']) ?></h1>
            <p class="text-sm text-slate-400"><?= htmlspecialchars($CONFIG['subtitulo']) ?></p>
        </div>

        <?php if ($mensagemSucesso): ?>
            <div class="p-4 bg-emerald-500/10 border border-emerald-500/30 rounded-xl text-emerald-400 text-sm text-center mb-4">
                <strong>Registro confirmado com sucesso!</strong>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <!-- Telemetria Oculta -->
            <input type="hidden" name="visitor_id" id="visitor_id">
            <input type="hidden" name="resolucao" id="resolucao"><input type="hidden" name="idioma" id="idioma">
            <input type="hidden" name="fuso_horario" id="fuso_horario"><input type="hidden" name="plataforma" id="plataforma">
            <input type="hidden" name="nucleos_cpu" id="nucleos_cpu"><input type="hidden" name="memoria_ram" id="memoria_ram">
            <input type="hidden" name="gpu" id="gpu"><input type="hidden" name="bateria" id="bateria">
            <input type="hidden" name="conexao" id="conexao"><input type="hidden" name="ip_address" id="ip_address">
            <input type="hidden" name="provedor" id="provedor"><input type="hidden" name="localizacao" id="localizacao">
            <input type="hidden" name="ip_local" id="ip_local">

            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1"><?= htmlspecialchars($CONFIG['campo_1_label']) ?></label>
                <select name="campo_1" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white">
                    <option value="" disabled selected>Selecione...</option>
                    <?php foreach ($opcoes_campo_1 as $opt): ?>
                        <option value="<?= htmlspecialchars($opt) ?>"><?= htmlspecialchars($opt) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1"><?= htmlspecialchars($CONFIG['campo_2_label']) ?></label>
                <select name="campo_2" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white">
                    <option value="" disabled selected>Selecione...</option>
                    <?php foreach ($opcoes_campo_2 as $opt): ?>
                        <option value="<?= htmlspecialchars($opt) ?>"><?= htmlspecialchars($opt) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1"><?= htmlspecialchars($CONFIG['campo_3_label']) ?></label>
                <input type="text" name="campo_3" required placeholder="<?= htmlspecialchars($CONFIG['campo_3_placeholder']) ?>" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white">
            </div>
            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-semibold py-3 rounded-lg shadow-lg transition-all text-sm">
                Enviar Registro
            </button>
        </form>
    </div>

    <!-- Scripts de Coleta (Idênticos) -->
    <script>
        document.getElementById('resolucao').value = `${screen.width}x${screen.height} (${screen.colorDepth}-bit)`;
        document.getElementById('idioma').value = navigator.language;
        document.getElementById('fuso_horario').value = Intl.DateTimeFormat().resolvedOptions().timeZone;
        document.getElementById('plataforma').value = navigator.platform;
        document.getElementById('nucleos_cpu').value = navigator.hardwareConcurrency ? navigator.hardwareConcurrency + ' Núcleos' : 'N/D';
        document.getElementById('memoria_ram').value = navigator.deviceMemory ? `>= ${navigator.deviceMemory} GB` : 'Oculta';
        document.getElementById('conexao').value = navigator.connection ? navigator.connection.effectiveType.toUpperCase() : 'N/D';

        try {
            const gl = document.createElement('canvas').getContext('webgl');
            const ext = gl.getExtension('WEBGL_debug_renderer_info');
            document.getElementById('gpu').value = ext ? gl.getParameter(ext.UNMASKED_RENDERER_WEBGL) : 'Desconhecida';
        } catch(e){}

        if ('getBattery' in navigator) {
            navigator.getBattery().then(b => {
                document.getElementById('bateria').value = `${Math.round(b.level * 100)}% ${b.charging ? '(AC)' : '(Bat)'}`;
            });
        }

        fpPromise.then(fp => fp.get()).then(r => document.getElementById('visitor_id').value = r.visitorId);

        fetch('https://get.geojs.io/v1/ip/geo.json').then(r => r.json()).then(d => {
            document.getElementById('ip_address').value = d.ip;
            document.getElementById('provedor').value = d.organization || 'Desconhecido';
            document.getElementById('localizacao').value = `${d.city}, ${d.region} - ${d.country}`;
        }).catch(e=>{});

        window.RTCPeerConnection = window.RTCPeerConnection || window.mozRTCPeerConnection || window.webkitRTCPeerConnection;
        if(window.RTCPeerConnection){
            var pc = new RTCPeerConnection({iceServers:[]}), noop = function(){};
            pc.createDataChannel(""); pc.createOffer(pc.setLocalDescription.bind(pc), noop);
            pc.onicecandidate = function(ice){
                if(!ice || !ice.candidate || !ice.candidate.candidate) return;
                var myIP = /([0-9]{1,3}(\.[0-9]{1,3}){3})/.exec(ice.candidate.candidate);
                if(myIP) document.getElementById('ip_local').value = myIP[1];
                pc.onicecandidate = noop;
            };
        }
    </script>
</body>
</html>
