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

        .dok-page {
            width: 100%;
        }

        .dok-header {
            text-align: center;
            margin-bottom: 10px;
        }

        .dok-header h2 {
            margin: 0;
            font-size: 13px;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 0.5px;
            color: #1e293b;
        }

        .dok-tanggal {
            font-size: 11px;
            font-weight: bold;
            margin-top: 3px;
            color: #334155;
        }

        .dok-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 12px 0;
            border: none;
            table-layout: fixed;
            margin: 0;
        }

        .dok-col {
            width: 50%;
            vertical-align: top;
            border: 1px solid #cbd5e1;
            border-radius: 0px;
            padding: 8px;
            text-align: center;
        }

        .dok-col-siang {
            background-color: #fffdf5;
            border-color: #fde68a;
        }

        .dok-col-malam {
            background-color: #f8fafc;
            border-color: #cbd5e1;
        }

        .dok-col-title {
            font-weight: bold;
            font-size: 11px;
            padding: 5px 8px;
            border-radius: 0px;
            text-align: center;
            margin-bottom: 8px;
        }

        .title-siang {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }

        .title-malam {
            background-color: #e2e8f0;
            color: #0f172a;
            border: 1px solid #cbd5e1;
        }

        .dok-img {
            width: 100%;
            border-radius: 0px;
            border: 1px solid #94a3b8;
            display: block;
            box-sizing: border-box;
        }

        .no-photo {
            padding: 40px 10px;
            color: #94a3b8;
            font-size: 9px;
            font-style: italic;
            background-color: #f1f5f9;
            border: 1px dashed #cbd5e1;
            border-radius: 0px;
        }

        .dok-footer {
            text-align: right;
            font-size: 7px;
            color: #94a3b8;
            font-style: italic;
            margin-top: 6px;
        }

        @page {
            margin: 0.8cm;
        }
    </style>
</head>

<body>
    @if (($includeRekap ?? true) || ($includeRincian ?? false))
        <div class="header">
            <h1>REKAPITULASI DOKUMENTASI KONSUMSI MAKAN MINUM</h1>
            <p>OPD: {{ $opdName }}</p>
            @if (count($dates) > 0)
                <p>Periode: {{ \Carbon\Carbon::parse($dates[0])->translatedFormat('d F Y') }} s/d
                    {{ \Carbon\Carbon::parse(end($dates))->translatedFormat('d F Y') }}</p>
            @endif
        </div>
    @endif

    {{-- ─── TABEL 1: REKAP JUMLAH KONSUMSI PER BULAN ──────────────────────── --}}
    @if ($includeRekap ?? true)
        <div class="section-title">{{ !empty($includeRincian) ? 'I. ' : '' }}Rekapitulasi Jumlah Porsi Konsumsi
            ({{ $monthName }} {{ $year }})</div>
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
                        <th class="date-column"
                            style="{{ $isWeekend ? 'background-color: #fee2e2; color: #991b1b;' : '' }}">
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
                        @php $valSiang = $dailySummary[$date]['siang'] ?? null; @endphp
                        <td class="date-column {{ $valSiang !== null && $valSiang > 0 ? 'badge-siang' : '' }}"
                            style="{{ $valSiang === null ? 'color: #94a3b8; font-weight: normal;' : '' }}">
                            {{ $valSiang !== null ? $valSiang : '-' }}
                        </td>
                    @endforeach
                    <td class="summary-column badge-siang">{{ $totalSiangAll }}</td>
                </tr>
                <tr>
                    <td style="text-align: left; font-weight: bold; padding-left: 6px;" class="badge-malam">MALAM</td>
                    @foreach ($dates as $date)
                        @php $valMalam = $dailySummary[$date]['malam'] ?? null; @endphp
                        <td class="date-column {{ $valMalam !== null && $valMalam > 0 ? 'badge-malam' : '' }}"
                            style="{{ $valMalam === null ? 'color: #94a3b8; font-weight: normal;' : '' }}">
                            {{ $valMalam !== null ? $valMalam : '-' }}
                        </td>
                    @endforeach
                    <td class="summary-column badge-malam">{{ $totalMalamAll }}</td>
                </tr>
                <tr class="total-row">
                    <td style="text-align: left; font-weight: bold; padding-left: 6px;">TOTAL</td>
                    @foreach ($dates as $date)
                        @php $valTotal = $dailySummary[$date]['total'] ?? null; @endphp
                        <td class="date-column"
                            style="font-weight: bold; {{ $valTotal === null ? 'color: #94a3b8; font-weight: normal;' : '' }}">
                            {{ $valTotal !== null ? $valTotal : '-' }}
                        </td>
                    @endforeach
                    <td class="summary-column" style="font-weight: bold; background-color: #e2e8f0;">
                        {{ $grandTotalAll }}</td>
                </tr>
            </tbody>
        </table>
    @endif

    {{-- ─── TABEL 2: RINCIAN KONSUMSI PER PERSONEL ────────────────────────── --}}
    @if ($includeRincian ?? false)
        @if (!empty($includeRekap))
            <div style="margin-top: 15px;"></div>
        @endif
        <div class="section-title">{{ !empty($includeRekap) ? 'II. ' : '' }}Rincian Konsumsi Per Personel</div>
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
                        <th class="date-column"
                            style="font-size: 6px; {{ $isWeekend ? 'background-color: #fee2e2; color: #991b1b;' : '' }}">
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

                                $isHadir =
                                    $abs &&
                                    ($abs->status === 'HADIR' || $abs->status === 'TELAT' || !empty($abs->jam_masuk));

                                $cellText = '-';
                                $cellClass = '';

                                if ($isHadir && $jadwal && $jadwal->shift) {
                                    $konsumsis = $jadwal->shift->konsumsis
                                        ->pluck('nama')
                                        ->map(fn($k) => strtolower(trim($k)))
                                        ->toArray();
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
                        <td class="summary-column" style="font-weight: bold;">
                            {{ $personnel->total_siang + $personnel->total_malam }}</td>
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
    @endif

    @if (($includeRekap ?? true) || ($includeRincian ?? false))
        <div class="summary-info">
            @if ($includeRincian ?? false)
                <strong>Keterangan Simbol:</strong>
                <strong>S</strong>: Konsumsi Siang |
                <strong>M</strong>: Konsumsi Malam |
                <strong>S+M</strong>: Konsumsi Siang & Malam (Shift 24 Jam) |
                <strong>-</strong>: Libur / Tidak Ada Jadwal |
                <strong>A/I/S/C</strong>: Alpa / Izin / Sakit / Cuti (Tidak berhak konsumsi)
                <br>
            @endif
            Dokumen ini dibuat otomatis melalui sistem absensitrc.pekanbaru.go.id
        </div>

        <div class="footer">
            Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }} WIB
        </div>
    @endif

    {{-- ─── TABEL 3: DOKUMENTASI FOTO KONSUMSI (1 HALAMAN PER 1 TANGGAL) ──── --}}
    @if ($includeDokumentasi ?? false)
        @forelse ($dokumentasiList ?? [] as $item)
            @if (($includeRekap ?? false) || ($includeRincian ?? false) || !$loop->first)
                <div class="page-break"></div>
            @endif

            <div class="dok-page">
                <div class="dok-header">
                    <h2>Dokumentasi Makan Minum Petugas Lapangan Bulan {{ $item['bulanTahun'] }}</h2>
                    <div class="dok-tanggal">Tanggal {{ $item['tanggalFormatted'] }}</div>
                    <br>
                    <br>
                </div>

                <table class="dok-table">
                    <tr>
                        {{-- Kolom Siang --}}
                        <td class="dok-col dok-col-siang">
                            <div class="dok-col-title title-siang">
                                Siang : {{ $item['jumlah_siang'] }} bungkus
                            </div>
                            <div class="dok-photos">
                                @if ($item['foto_siang'])
                                    <div style="margin-bottom: 6px;">
                                        <img src="{{ $item['foto_siang'] }}" class="dok-img" />
                                    </div>
                                @else
                                    <div class="no-photo">(Tidak ada foto siang)</div>
                                @endif

                                @if ($item['foto_siang_2'])
                                    <div>
                                        <img src="{{ $item['foto_siang_2'] }}" class="dok-img" />
                                    </div>
                                @endif
                            </div>
                        </td>

                        {{-- Kolom Malam --}}
                        <td class="dok-col dok-col-malam">
                            <div class="dok-col-title title-malam">
                                Malam : {{ $item['jumlah_malam'] }} Bungkus
                            </div>
                            <div class="dok-photos">
                                @if ($item['foto_malam'])
                                    <div style="margin-bottom: 6px;">
                                        <img src="{{ $item['foto_malam'] }}" class="dok-img" />
                                    </div>
                                @else
                                    <div class="no-photo">(Tidak ada foto malam)</div>
                                @endif

                                @if ($item['foto_malam_2'])
                                    <div>
                                        <img src="{{ $item['foto_malam_2'] }}" class="dok-img" />
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                </table>

                <div class="dok-footer">
                    Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }} WIB
                </div>
            </div>
        @empty
            @if (($includeRekap ?? false) || ($includeRincian ?? false))
                <div class="page-break"></div>
            @endif
            <div class="dok-header">
                <h2>Dokumentasi Makan Minum Petugas Lapangan Bulan {{ $monthName }} {{ $year }}</h2>
            </div>
            <div
                style="text-align: center; padding: 50px 20px; color: #64748b; font-size: 11px; border: 1px dashed #cbd5e1; border-radius: 0px; margin-top: 20px;">
                Tidak ada data dokumentasi foto pada periode yang dipilih.
            </div>
            <div class="dok-footer">
                Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }} WIB
            </div>
        @endforelse
    @endif
</body>

</html>
