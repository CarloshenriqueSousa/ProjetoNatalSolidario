<?php
/**
 * RelatorioExportController — Exportação de Relatórios para PDF e CSV (Camada B)
 */
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/Produto.php';
require_once __DIR__ . '/../models/Rifa.php';
require_once __DIR__ . '/../models/Financeiro.php';

use Dompdf\Dompdf;
use Dompdf\Options;

class RelatorioExportController extends Controller {

    public function exportEstoque(): void {
        Auth::requireLogin();
        $user = Auth::user();
        $perfil = $user['perfil'];

        $turmaId = in_array($perfil, ['coleta', 'turma'], true) ? $user['turma_id'] : (isset($_GET['turma_id']) ? (int)$_GET['turma_id'] : null);

        if ($turmaId) {
            $produtos = Produto::getByTurma($turmaId);
            $titulo = "Relatório de Estoque — Turma ID: {$turmaId}";
        } else {
            $produtos = Produto::getAll();
            $titulo = "Relatório de Estoque Geral — Natal Solidário";
        }

        $format = $_GET['format'] ?? 'pdf';

        if ($format === 'csv') {
            $this->exportEstoqueCSV($titulo, $produtos);
        } else {
            $this->exportEstoquePDF($titulo, $produtos);
        }
    }

    public function exportPrestacao(): void {
        Auth::requireRole(['admin', 'subadmin', 'coordenador']);
        
        $rifas = Rifa::getResumoVendasPorTurma();
        $lotes = Rifa::getAll();
        $titulo = "Relatório de Prestação de Contas das Turmas — Natal Solidário";

        $format = $_GET['format'] ?? 'pdf';

        if ($format === 'csv') {
            $this->exportPrestacaoCSV($titulo, $rifas, $lotes);
        } else {
            $this->exportPrestacaoPDF($titulo, $rifas, $lotes);
        }
    }

    private function exportEstoquePDF(string $titulo, array $produtos): void {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new Dompdf($options);

        $html = '
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <title>' . htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') . '</title>
            <style>
                body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #1e293b; line-height: 1.4; margin: 20px; }
                h1 { font-size: 18px; color: #0f172a; margin-bottom: 4px; border-bottom: 2px solid #e2e8f0; padding-bottom: 6px; }
                .meta { font-size: 10px; color: #64748b; margin-bottom: 20px; }
                table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                th { background-color: #f1f5f9; color: #334155; font-weight: bold; text-align: left; padding: 8px 6px; border-bottom: 2px solid #cbd5e1; font-size: 10px; }
                td { padding: 7px 6px; border-bottom: 1px solid #e2e8f0; font-size: 10px; }
                .badge { padding: 2px 6px; border-radius: 4px; font-weight: bold; font-size: 9px; text-transform: uppercase; background: #e2e8f0; }
                .footer { position: fixed; bottom: 0; left: 0; right: 0; text-align: center; font-size: 8px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 4px; }
            </style>
        </head>
        <body>
            <h1>🎄 ' . htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') . '</h1>
            <div class="meta">Gerado em: ' . date('d/m/Y H:i:s') . ' | Sistema Natal Solidário</div>
            
            <table>
                <thead>
                    <tr>
                        <th>Código Lote</th>
                        <th>Turma</th>
                        <th>Categoria</th>
                        <th>Registrado Por</th>
                        <th>Data Registro</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($produtos as $p) {
            $html .= '<tr>
                <td><strong>' . htmlspecialchars($p['codigo_lote'], ENT_QUOTES, 'UTF-8') . '</strong></td>
                <td>' . htmlspecialchars($p['turma_nome'], ENT_QUOTES, 'UTF-8') . '</td>
                <td><span class="badge">' . htmlspecialchars(strtoupper($p['categoria']), ENT_QUOTES, 'UTF-8') . '</span></td>
                <td>' . htmlspecialchars($p['usuario_nome'], ENT_QUOTES, 'UTF-8') . '</td>
                <td>' . date('d/m/Y H:i', strtotime($p['criado_em'])) . '</td>
            </tr>';
        }

        if (empty($produtos)) {
            $html .= '<tr><td colspan="5" style="text-align:center;">Nenhum produto cadastrado.</td></tr>';
        }

        $html .= '
                </tbody>
            </table>
            
            <div class="footer">Natal Solidário — Relatório Oficial emitido pelo Sistema de Gerenciamento Escola</div>
        </body>
        </html>';

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("relatorio_estoque_" . date('Ymd_His') . ".pdf", ["Attachment" => true]);
        exit;
    }

    private function exportPrestacaoPDF(string $titulo, array $rifas, array $lotes): void {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new Dompdf($options);

        $html = '
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <title>' . htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') . '</title>
            <style>
                body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #1e293b; line-height: 1.4; margin: 20px; }
                h1 { font-size: 18px; color: #0f172a; margin-bottom: 4px; border-bottom: 2px solid #e2e8f0; padding-bottom: 6px; }
                h2 { font-size: 14px; color: #334155; margin-top: 20px; margin-bottom: 8px; }
                .meta { font-size: 10px; color: #64748b; margin-bottom: 15px; }
                table { width: 100%; border-collapse: collapse; margin-top: 8px; margin-bottom: 20px; }
                th { background-color: #f1f5f9; color: #334155; font-weight: bold; text-align: left; padding: 8px 6px; border-bottom: 2px solid #cbd5e1; font-size: 10px; }
                td { padding: 7px 6px; border-bottom: 1px solid #e2e8f0; font-size: 10px; }
                .badge { padding: 2px 6px; border-radius: 4px; font-weight: bold; font-size: 9px; text-transform: uppercase; }
                .badge-success { background: #dcfce7; color: #15803d; }
                .badge-warning { background: #fef3c7; color: #b45309; }
                .badge-danger { background: #fee2e2; color: #b91c1c; }
                .footer { position: fixed; bottom: 0; left: 0; right: 0; text-align: center; font-size: 8px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 4px; }
            </style>
        </head>
        <body>
            <h1>🎟️ ' . htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') . '</h1>
            <div class="meta">Gerado em: ' . date('d/m/Y H:i:s') . ' | Sistema Natal Solidário</div>

            <h2>Resumo Consolidado por Turma</h2>
            <table>
                <thead>
                    <tr>
                        <th>Turma</th>
                        <th>Entregues</th>
                        <th>Vendidas</th>
                        <th>Devolvidas</th>
                        <th>Perdidas</th>
                        <th>Valor Arrecadado</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($rifas as $r) {
            $html .= '<tr>
                <td><strong>' . htmlspecialchars($r['turma_nome'], ENT_QUOTES, 'UTF-8') . '</strong></td>
                <td>' . $r['total_entregue'] . '</td>
                <td>' . $r['total_vendida'] . '</td>
                <td>' . $r['total_devolvida'] . '</td>
                <td>' . $r['total_perdida'] . '</td>
                <td>R$ ' . number_format($r['valor_total_arrecadado'], 2, ',', '.') . '</td>
            </tr>';
        }

        $html .= '
                </tbody>
            </table>

            <h2>Detalhamento dos Lotes de Rifas</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Turma</th>
                        <th>Líder</th>
                        <th>Status</th>
                        <th>Qtd Entregue</th>
                        <th>Valor Entregue</th>
                        <th>Diferença</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($lotes as $l) {
            $statusClass = 'badge-success';
            if ($l['status'] === 'em_atraso') $statusClass = 'badge-warning';
            if ($l['status'] === 'com_divergencia') $statusClass = 'badge-danger';

            $html .= '<tr>
                <td>#' . $l['id'] . '</td>
                <td>' . htmlspecialchars($l['turma_nome'], ENT_QUOTES, 'UTF-8') . '</td>
                <td>' . htmlspecialchars($l['lider_nome'], ENT_QUOTES, 'UTF-8') . '</td>
                <td><span class="badge ' . $statusClass . '">' . htmlspecialchars(strtoupper($l['status']), ENT_QUOTES, 'UTF-8') . '</span></td>
                <td>' . $l['quantidade_entregue'] . '</td>
                <td>R$ ' . number_format($l['valor_entregue'] ?? 0, 2, ',', '.') . '</td>
                <td>R$ ' . number_format($l['diferenca'] ?? 0, 2, ',', '.') . '</td>
            </tr>';
        }

        $html .= '
                </tbody>
            </table>

            <div class="footer">Natal Solidário — Prestação de Contas Oficial de Rifas Escolares</div>
        </body>
        </html>';

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream("prestacao_contas_rifas_" . date('Ymd_His') . ".pdf", ["Attachment" => true]);
        exit;
    }

    private function exportEstoqueCSV(string $titulo, array $produtos): void {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="estoque_' . date('Ymd_His') . '.csv"');
        echo "\xEF\xBB\xBF";
        $out = fopen('php://output', 'w');
        fputcsv($out, [$titulo]);
        fputcsv($out, ['Código Lote', 'Turma', 'Categoria', 'Registrado Por', 'Data Registro']);
        foreach ($produtos as $p) {
            fputcsv($out, [$p['codigo_lote'], $p['turma_nome'], $p['categoria'], $p['usuario_nome'], $p['criado_em']]);
        }
        fclose($out);
        exit;
    }

    private function exportPrestacaoCSV(string $titulo, array $rifas, array $lotes): void {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="prestacao_contas_' . date('Ymd_His') . '.csv"');
        echo "\xEF\xBB\xBF";
        $out = fopen('php://output', 'w');
        fputcsv($out, [$titulo]);
        fputcsv($out, ['Turma', 'Entregues', 'Vendidas', 'Devolvidas', 'Perdidas', 'Valor Arrecadado']);
        foreach ($rifas as $r) {
            fputcsv($out, [$r['turma_nome'], $r['total_entregue'], $r['total_vendida'], $r['total_devolvida'], $r['total_perdida'], $r['valor_total_arrecadado']]);
        }
        fclose($out);
        exit;
    }
}
