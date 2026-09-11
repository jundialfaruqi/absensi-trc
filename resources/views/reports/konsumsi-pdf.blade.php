<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Rekap Dokumentasi Konsumsi - {{ $monthName }} {{ $year }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 7.5px;
            margin: 0;
            padding: 0;
            color: #222;
        }

        .header {
            text-align: center;
            margin-bottom: 12px;
        }

        .header h1 {
            margin: 0;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .header p {
            margin: 2px 0;
            font-size: 9px;
            color: #555;
        }

        .section-title {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 10px;
            margin-bottom: 4px;
            padding: 3px 6px;
            background-color: #f3f4f6;
            border-left: 3px solid #0284c7;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            table-layout: auto;
        }

        th,
        td {
            border: 1px solid #999;
            padding: 3px 2px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7px;
        }

        .name-column {
            width: 85px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            text-align: left;
            padding: 3px 6px;
            font-weight: bold;
        }

        .date-column {
            width: 18px;
        }

        .summary-column {
            width: 22px;
            background-color: #f8fafc;
            font-weight: bold;
        }

        .badge-siang {
            background-color: #fef3c7;
            color: #b45309;
            font-weight: bold;
        }

        .badge-malam {
            background-color: #e0e7ff;
            color: #4338ca;
            font-weight: bold;
        }

        .badge-both {
            background-color: #dcfce7;
            color: #15803d;
            font-weight: bold;
        }

        .total-row {
            background-color: #f1f5f9;
            font-weight: bold;
        }

        .summary-info {
            font-size: 7.5px;
            margin-top: 10px;
            margin-bottom: 15px;
        }

        .footer {
            margin-top: 15px;
            text-align: right;
            font-size: 8px;
            font-style: italic;
        }

        .page-break {
            page-break-after: always;
        }

        @page {
            margin: 0.8cm;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>REKAPITULASI DOKUMENTASI KONSUMSI MAKAN MINUM</h1>
        <p>OPD: {{ $opdName }}</p>
        @if (count($dates) > 0)
            <p>Periode: {{ \Carbon\Carbon::parse($dates[0])->translatedFormat('d F Y') }} s/d
                {{ \Carbon\Carbon::parse(end($dates))->translatedFormat('d F Y') }}</p>
        @endif
    </div>

    {{-- ─── TABEL 1: REKAP JUMLAH KONSUMSI PER BULAN ──────────────────────── --}}
    <div class="section-title">I. Rekapitulasi Jumlah Porsi Konsumsi ({{ $monthName }} {{ $year }})</div>
    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 85px; text-align: left; padding-left: 6px;">BULAN</th>
                <th colspan="{{ count($dates) }}">TANGGAL / HARI</th>
                <th rowspan="2" class="summary-column">TOTAL</th>
            </tr>
            <tr>
                @foreach ($dates as $date)
                    @php
                        $carbonDate = \Carbon\Carbon::parse($date);
                        $isWeekend = $carbonDate->isWeekend();
                        $dayName = substr($carbonDate->translatedFormat('D'), 0, 3);
                    @endphp
                    <th class="date-column" style="{{ $isWeekend ? 'background-color: #fee2e2; color: #991b1b;' : '' }}">
                        <div style="font-size: 5px; opacity: 0.8;">{{ $dayName }}</div>
                        <div style="font-size: 7px; font-weight: bold;">{{ $carbonDate->format('d') }}</div>
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: left; font-weight: bold; padding-left: 6px;" class="badge-siang">SIANG</td>
                @foreach ($dates as $date)
                    <td class="date-column {{ ($dailySummary[$date]['siang'] ?? 0) > 0 ? 'badge-siang' : '' }}">
                        {{ $dailySummary[$date]['siang'] ?? 0 }}
                    </td>
                @endforeach
                <td class="summary-column badge-siang">{{ $totalSiangAll }}</td>
            </tr>
            <tr>
                <td style="text-align: left; font-weight: bold; padding-left: 6px;" class="badge-malam">MALAM</td>
                @foreach ($dates as $date)
                    <td class="date-column {{ ($dailySummary[$date]['malam'] ?? 0) > 0 ? 'badge-malam' : '' }}">
                        {{ $dailySummary[$date]['malam'] ?? 0 }}
                    </td>
                @endforeach
                <td class="summary-column badge-malam">{{ $totalMalamAll }}</td>
            </tr>
            <tr class="total-row">
                <td style="text-align: left; font-weight: bold; padding-left: 6px;">TOTAL</td>
                @foreach ($dates as $date)
                    <td class="date-column" style="font-weight: bold;">
                        {{ $dailySummary[$date]['total'] ?? 0 }}
                    </td>
                @endforeach
                <td class="summary-column" style="font-weight: bold; background-color: #e2e8f0;">{{ $grandTotalAll }}</td>
            </tr>
        </tbody>
    </table>

    {{-- ─── TABEL 2: RINCIAN KONSUMSI PER PERSONEL ────────────────────────── --}}
    <div class="section-title">II. Rincian Konsumsi Per Personel</div>
    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 20px;">NO</th>
                <th rowspan="2" class="name-column">NAMA PERSONEL</th>
                <th rowspan="2" style="width: 35px;">REGU</th>
                <th colspan="{{ count($dates) }}">KONSUMSI HARIAN</th>
                <th colspan="3" class="summary-column">TOTAL</th>
            </tr>
            <tr>
                @foreach ($dates as $date)
                    @php
                        $carbonDate = \Carbon\Carbon::parse($date);
                        $isWeekend = $carbonDate->isWeekend();
                    @endphp
                    <th class="date-column" style="font-size: 6px; {{ $isWeekend ? 'background-color: #fee2e2; color: #991b1b;' : '' }}">
                        {{ $carbonDate->format('d') }}
                    </th>
                @endforeach
                <th class="summary-column" style="font-size: 6px;">SIANG</th>
                <th class="summary-column" style="font-size: 6px;">MALAM</th>
                <th class="summary-column" style="font-size: 6px;">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($personnels as $index => $personnel)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="name-column">{{ $personnel->name }}</td>
                    <td>{{ $personnel->regu ?? '-' }}</td>
                    @foreach ($dates as $date)
                        @php
                            $abs = $personnel->absensi_map->get($date);
                            $jadwal = $personnel->jadwal_map->get($date);

                            $isHadir = $abs && (
                                $abs->status === 'HADIR' ||
                                $abs->status === 'TELAT' ||
                                !empty($abs->jam_masuk)
                            );

                            $cellText = '-';
                            $cellClass = '';

                            if ($isHadir && $jadwal && $jadwal->shift) {
                                $konsumsis = $jadwal->shift->konsumsis->pluck('nama')->map(fn ($k) => strtolower(trim($k)))->toArray();
                                $hasSiang = in_array('siang', $konsumsis);
                                $hasMalam = in_array('malam', $konsumsis);

                                if ($hasSiang && $hasMalam) {
                                    $cellText = 'S+M';
                                    $cellClass = 'badge-both';
                                } elseif ($hasSiang) {
                                    $cellText = 'S';
                                    $cellClass = 'badge-siang';
                                } elseif ($hasMalam) {
                                    $cellText = 'M';
                                    $cellClass = 'badge-malam';
                                } else {
                                    $cellText = 'H';
                                }
                            } elseif ($abs && in_array($abs->status, ['ALPA', 'IZIN', 'SAKIT', 'CUTI'])) {
                                $cellText = substr($abs->status, 0, 1);
                            }
                        @endphp
                        <td class="date-column {{ $cellClass }}">{{ $cellText }}</td>
                    @endforeach
                    <td class="summary-column">{{ $personnel->total_siang }}</td>
                    <td class="summary-column">{{ $personnel->total_malam }}</td>
                    <td class="summary-column" style="font-weight: bold;">{{ $personnel->total_siang + $personnel->total_malam }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($dates) + 6 }}" style="padding: 10px; text-align: center; color: #888;">
                        Tidak ada data personel pada periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary-info">
        <strong>Keterangan Simbol:</strong> 
        <strong>S</strong>: Konsumsi Siang | 
        <strong>M</strong>: Konsumsi Malam | 
        <strong>S+M</strong>: Konsumsi Siang & Malam (Shift 24 Jam) | 
        <strong>-</strong>: Libur / Tidak Ada Jadwal | 
        <strong>A/I/S/C</strong>: Alpa / Izin / Sakit / Cuti (Tidak berhak konsumsi)
        <br>
        Dokumen ini dibuat otomatis melalui sistem absensitrc.pekanbaru.go.id
    </div>

    <div class="footer">
        Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }} WIB
    </div>
</body>

</html>
