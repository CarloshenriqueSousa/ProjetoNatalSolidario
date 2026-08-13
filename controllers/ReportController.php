<?php
/**
 * Report Controller compiles and exports CSV, PDF, and Excel reports
 */
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportController extends BaseController {

    public function index() {
        $this->requireAuth();

        $classModel = new ClassModel();
        $classes = $classModel->getAll();
        $isClass = has_role('turma');

        $this->render('reports/index', [
            'title' => 'Gerar Relatórios - Natal Solidário',
            'classes' => $classes,
            'isClass' => $isClass
        ]);
    }

    public function export() {
        $this->requireAuth();

        $type = $_GET['type'] ?? 'estoque'; // 'estoque', 'vencidos', 'proximos', 'turma', 'movimentacoes'
        $format = $_GET['format'] ?? 'csv'; // 'csv', 'excel', 'pdf'
        $turmaId = isset($_GET['turma_id']) ? (int)$_GET['turma_id'] : null;

        $isClass = has_role('turma');
        if ($isClass) {
            // Enforce classroom limits
            $turmaId = $_SESSION['user_turma_id'];
        }

        // Fetch data based on type
        $data = $this->compileReportData($type, $turmaId);
        $headers = $data['headers'];
        $rows = $data['rows'];
        $title = $data['title'];

        if ($format === 'csv') {
            $this->exportCSV($title, $headers, $rows);
        } elseif ($format === 'excel') {
            $this->exportExcel($title, $headers, $rows);
        } elseif ($format === 'pdf') {
            $this->exportPDF($title, $headers, $rows);
        } else {
            $_SESSION['error'] = "Formato de exportação inválido.";
            redirect('reports');
        }
    }

    /**
     * Compile headers and rows based on report types
     */
    private function compileReportData($type, $turmaId = null) {
        $productModel = new Product();
        $classModel = new ClassModel();

        $headers = [];
        $rows = [];
        $title = '';

        switch ($type) {
            case 'vencidos':
                $title = 'Relatório de Produtos Vencidos';
                $products = $productModel->getAll(['validade' => 'vencidos', 'turma_id' => $turmaId]);
                $headers = ['ID', 'Produto', 'Categoria', 'Quantidade', 'Lote', 'Turma', 'Validade', 'Cadastro'];
                foreach ($products as $p) {
                    $rows[] = [
                        $p['id'],
                        $p['nome'],
                        ucfirst($p['tipo']),
                        $p['quantidade'],
                        $p['lote_codigo'],
                        $p['turma_nome'],
                        format_date($p['alimento_data_validade']),
                        format_datetime($p['created_at'])
                    ];
                }
                break;

            case 'proximos':
                $title = 'Relatório de Produtos Próximos do Vencimento';
                $products = $productModel->getAll(['validade' => 'proximos', 'turma_id' => $turmaId]);
                $headers = ['ID', 'Produto', 'Categoria', 'Quantidade', 'Lote', 'Turma', 'Validade', 'Cadastro'];
                foreach ($products as $p) {
                    $rows[] = [
                        $p['id'],
                        $p['nome'],
                        ucfirst($p['tipo']),
                        $p['quantidade'],
                        $p['lote_codigo'],
                        $p['turma_nome'],
                        format_date($p['alimento_data_validade']),
                        format_datetime($p['created_at'])
                    ];
                }
                break;

            case 'turma':
                $className = 'Todas';
                if ($turmaId) {
                    $c = $classModel->getById($turmaId);
                    $className = $c ? $c['nome'] : 'Desconhecida';
                }
                $title = 'Relatório de Estoque da Turma: ' . $className;
                $products = $productModel->getAll(['turma_id' => $turmaId]);
                $headers = ['ID', 'Produto', 'Categoria', 'Quantidade', 'Lote', 'Detalhes', 'Cadastro'];
                foreach ($products as $p) {
                    $detalhes = '';
                    if ($p['tipo'] === 'roupa') {
                        $detalhes = "Qualidade: {$p['roupa_qualidade']} | Faixa: {$p['roupa_faixa_etaria']}";
                    } elseif ($p['tipo'] === 'brinquedo') {
                        $detalhes = "Faixa: {$p['brinquedo_faixa_etaria']}";
                    } elseif ($p['tipo'] === 'alimento') {
                        $detalhes = "Categoria: {$p['alimento_categoria']} | Validade: " . format_date($p['alimento_data_validade']);
                    }

                    $rows[] = [
                        $p['id'],
                        $p['nome'],
                        ucfirst($p['tipo']),
                        $p['quantidade'],
                        $p['lote_codigo'],
                        $detalhes,
                        format_datetime($p['created_at'])
                    ];
                }
                break;

            case 'movimentacoes':
                $title = 'Relatório de Movimentações de Estoque';
                $movements = $productModel->getRecentMovements(200, $turmaId);
                $headers = ['ID', 'Tipo', 'Produto', 'Tipo de Produto', 'Lote', 'Quantidade', 'Operador', 'Motivo', 'Data/Hora'];
                foreach ($movements as $m) {
                    $rows[] = [
                        $m['id'],
                        $m['tipo'],
                        $m['produto_nome'],
                        ucfirst($m['produto_tipo']),
                        $m['lote_codigo'],
                        $m['quantidade'],
                        $m['usuario_nome'],
                        $m['motivo'] ?? '-',
                        format_datetime($m['created_at'])
                    ];
                }
                break;

            case 'estoque':
            default:
                $title = 'Relatório de Estoque Geral';
                $products = $productModel->getAll(['turma_id' => $turmaId]);
                $headers = ['ID', 'Produto', 'Categoria', 'Quantidade', 'Lote', 'Turma', 'Detalhes', 'Cadastro'];
                foreach ($products as $p) {
                    $detalhes = '';
                    if ($p['tipo'] === 'roupa') {
                        $detalhes = "Qualidade: {$p['roupa_qualidade']} | Faixa: {$p['roupa_faixa_etaria']}";
                    } elseif ($p['tipo'] === 'brinquedo') {
                        $detalhes = "Faixa: {$p['brinquedo_faixa_etaria']}";
                    } elseif ($p['tipo'] === 'alimento') {
                        $detalhes = "Categoria: {$p['alimento_categoria']} | Validade: " . format_date($p['alimento_data_validade']);
                    }

                    $rows[] = [
                        $p['id'],
                        $p['nome'],
                        ucfirst($p['tipo']),
                        $p['quantidade'],
                        $p['lote_codigo'],
                        $p['turma_nome'],
                        $detalhes,
                        format_datetime($p['created_at'])
                    ];
                }
                break;
        }

        return [
            'title' => $title,
            'headers' => $headers,
            'rows' => $rows
        ];
    }

    /**
     * Export native CSV format
     */
    private function exportCSV($title, $headers, $rows) {
        $filename = strtolower(str_replace(' ', '_', $title)) . '_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        // Output UTF-8 BOM for Excel compatibility
        echo "\xEF\xBB\xBF";

        $output = fopen('php://output', 'w');
        
        // Write report metadata
        fputcsv($output, [$title]);
        fputcsv($output, ['Gerado em: ' . date('d/m/Y H:i')]);
        fputcsv($output, []); // Empty spacing line
        
        // Write headers and rows
        fputcsv($output, $headers);
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
    }

    /**
     * Export using PhpSpreadsheet Excel format
     */
    private function exportExcel($title, $headers, $rows) {
        $filename = strtolower(str_replace(' ', '_', $title)) . '_' . date('Ymd_His') . '.xlsx';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Relatório');

        // Style the Title Row
        $sheet->setCellValue('A1', $title);
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        
        $sheet->setCellValue('A2', 'Gerado em: ' . date('d/m/Y H:i'));
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10);
        
        // Headers starting row 4
        $colIdx = 'A';
        foreach ($headers as $header) {
            $cell = $colIdx . '4';
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getStyle($cell)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                  ->getStartColor()->setARGB('FFE2E8F0'); // Light grey background
            $colIdx++;
        }

        // Rows starting row 5
        $rowIdx = 5;
        foreach ($rows as $row) {
            $colIdx = 'A';
            foreach ($row as $val) {
                $sheet->setCellValue($colIdx . $rowIdx, $val);
                $colIdx++;
            }
            $rowIdx++;
        }

        // Auto size columns
        $colIdx = 'A';
        foreach ($headers as $header) {
            $sheet->getColumnDimension($colIdx)->setAutoSize(true);
            $colIdx++;
        }

        $writer = new Xlsx($spreadsheet);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }

    /**
     * Export stylized HTML layout into PDF via Dompdf
     */
    private function exportPDF($title, $headers, $rows) {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'Helvetica');
        
        $dompdf = new Dompdf($options);

        // Build premium HTML styling template for printing
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>' . e($title) . '</title>
            <style>
                body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #334155; line-height: 1.5; margin: 20px; }
                h1 { font-size: 20px; font-weight: bold; color: #0f172a; margin-bottom: 5px; }
                .date { font-size: 10px; color: #64748b; margin-bottom: 25px; font-style: italic; }
                table { width: 100%; border-collapse: collapse; margin-top: 10px; page-break-inside: auto; }
                tr { page-break-inside: avoid; page-break-after: auto; }
                th { background-color: #f1f5f9; color: #475569; font-weight: bold; text-align: left; padding: 10px 8px; border-bottom: 2px solid #cbd5e1; }
                td { padding: 9px 8px; border-bottom: 1px solid #e2e8f0; vertical-align: middle; }
                .footer { position: fixed; bottom: 0; left: 0; right: 0; height: 30px; text-align: center; font-size: 9px; color: #94a3b8; }
            </style>
        </head>
        <body>
            <h1>' . e($title) . '</h1>
            <div class="date">Gerado em: ' . date('d/m/Y H:i') . ' | Natal Solidário</div>
            
            <table>
                <thead>
                    <tr>';
                    foreach ($headers as $header) {
                        $html .= '<th>' . e($header) . '</th>';
                    }
                    $html .= '</tr>
                </thead>
                <tbody>';
                foreach ($rows as $row) {
                    $html .= '<tr>';
                    foreach ($row as $val) {
                        $html .= '<td>' . e($val) . '</td>';
                    }
                    $html .= '</tr>';
                }
                $html .= '
                </tbody>
            </table>
            
            <div class="footer">
                Natal Solidário - Sistema de Controle de Estoque
            </div>
        </body>
        </html>';

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape'); // Landscape fits more tables columns comfortably
        $dompdf->render();

        $filename = strtolower(str_replace(' ', '_', $title)) . '_' . date('Ymd_His') . '.pdf';
        
        $dompdf->stream($filename, ["Attachment" => true]);
        exit;
    }
}
