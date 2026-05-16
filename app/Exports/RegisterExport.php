<?php

namespace App\Exports;

use App\Models\RegisterPelayanan;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RegisterExport implements WithMultipleSheets
{
    public function __construct(private array $filters = []) {}

    public function sheets(): array
    {
        return [
            new RegisterSheetExport($this->filters),
            new RekapSheetExport($this->filters),
        ];
    }
}

// ── Sheet 1: Data Register ────────────────────────────────────────────────────

class RegisterSheetExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithTitle
{
    private int $no = 0;

    public function __construct(private array $filters = []) {}

    public function title(): string
    {
        return 'Register Pelayanan';
    }

    public function query()
    {
        $query = RegisterPelayanan::with(['penduduk', 'petugas', 'surat'])
            ->orderBy('tanggal_pelayanan')
            ->orderBy('id');

        if (! empty($this->filters['dari'])) {
            $query->whereDate('tanggal_pelayanan', '>=', $this->filters['dari']);
        }
        if (! empty($this->filters['sampai'])) {
            $query->whereDate('tanggal_pelayanan', '<=', $this->filters['sampai']);
        }
        if (! empty($this->filters['pedukuhan'])) {
            $query->where('pedukuhan_pemohon', $this->filters['pedukuhan']);
        }
        if (! empty($this->filters['tahun'])) {
            $query->whereYear('tanggal_pelayanan', $this->filters['tahun']);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'No',
            'No Register',
            'Tanggal',
            'Jenis Pelayanan',
            'Nomor Surat',
            'Nama Pemohon',
            'NIK',
            'Pedukuhan',
            'RT',
            'RW',
            'Petugas',
            'Keterangan',
        ];
    }

    public function map($row): array
    {
        return [
            ++$this->no,
            $row->nomor_register,
            $row->tanggal_pelayanan->format('d/m/Y'),
            $row->jenis_pelayanan,
            $row->surat?->nomor_surat ?? '-',
            $row->penduduk?->nama_lengkap ?? '-',
            $row->penduduk?->nik ?? '-',
            $row->pedukuhan_pemohon ?? '-',
            $row->penduduk?->rt ?? '-',
            $row->penduduk?->rw ?? '-',
            $row->petugas?->name ?? '-',
            $row->keterangan ?? '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Header row bold + background
            1 => [
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '2E7D32']],
                'alignment' => ['horizontal' => 'center'],
            ],
        ];
    }
}

// ── Sheet 2: Rekap ────────────────────────────────────────────────────────────

class RekapSheetExport implements WithTitle, WithEvents
{
    public function __construct(private array $filters = []) {}

    public function title(): string
    {
        return 'Rekap';
    }



    public function registerEvents(): array
    {
        return [
            \Maatwebsite\Excel\Events\AfterSheet::class => function(\Maatwebsite\Excel\Events\AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $tahun = $this->filters['tahun'] ?? now()->year;

                $query = RegisterPelayanan::whereYear('tanggal_pelayanan', $tahun);
                if (! empty($this->filters['pedukuhan'])) {
                    $query->where('pedukuhan_pemohon', $this->filters['pedukuhan']);
                }

                $total = $query->count();

                // ── Judul ─────────────────────────────────────────────
                $sheet->setCellValue('A1', "REKAP PELAYANAN SURAT TAHUN {$tahun}");
                $sheet->mergeCells('A1:C1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 13],
                    'alignment' => ['horizontal' => 'center'],
                ]);

                $sheet->setCellValue('A2', "Total Pelayanan: {$total}");
                $sheet->mergeCells('A2:C2');

                // ── Rekap per Jenis ───────────────────────────────────
                $sheet->setCellValue('A4', 'REKAP PER JENIS SURAT');
                $sheet->getStyle('A4')->getFont()->setBold(true);
                $sheet->setCellValue('A5', 'Jenis Pelayanan');
                $sheet->setCellValue('B5', 'Jumlah');
                $sheet->getStyle('A5:B5')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1565C0']],
                ]);

                $perJenis = RegisterPelayanan::whereYear('tanggal_pelayanan', $tahun)
                    ->selectRaw('jenis_pelayanan, COUNT(*) as jumlah')
                    ->groupBy('jenis_pelayanan')
                    ->orderByDesc('jumlah')
                    ->get();

                $row = 6;
                foreach ($perJenis as $item) {
                    $sheet->setCellValue("A{$row}", $item->jenis_pelayanan);
                    $sheet->setCellValue("B{$row}", $item->jumlah);
                    $row++;
                }

                // ── Rekap per Pedukuhan ───────────────────────────────
                $startRow = $row + 2;
                $sheet->setCellValue("A{$startRow}", 'REKAP PER PEDUKUHAN');
                $sheet->getStyle("A{$startRow}")->getFont()->setBold(true);
                $startRow++;
                $sheet->setCellValue("A{$startRow}", 'Pedukuhan');
                $sheet->setCellValue("B{$startRow}", 'Jumlah');
                $sheet->getStyle("A{$startRow}:B{$startRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '6A1B9A']],
                ]);

                $perPedukuhan = RegisterPelayanan::whereYear('tanggal_pelayanan', $tahun)
                    ->whereNotNull('pedukuhan_pemohon')
                    ->selectRaw('pedukuhan_pemohon, COUNT(*) as jumlah')
                    ->groupBy('pedukuhan_pemohon')
                    ->orderBy('pedukuhan_pemohon')
                    ->get();

                $startRow++;
                foreach ($perPedukuhan as $item) {
                    $sheet->setCellValue("A{$startRow}", $item->pedukuhan_pemohon);
                    $sheet->setCellValue("B{$startRow}", $item->jumlah);
                    $startRow++;
                }

                // Auto width kolom
                foreach (['A', 'B', 'C'] as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}