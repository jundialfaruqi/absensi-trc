<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Rekap Absensi - {{ $monthName }} {{ $year }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 7px;
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 14px;
            text-transform: uppercase;
        }

        .header p {
            margin: 2px 0;
            font-size: 10px;
            color: #555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
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
        }

        .name-column {
            width: 60px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            text-align: left;
            padding: 3px 8px;
            font-weight: bold;
            /* width: auto;
            white-space: nowrap;
            text-align: left;
            padding: 3px 8px;
            font-weight: bold; */
        }

        .date-column {
            width: 20px;
        }

        .summary-column {
            width: 25px;
            background-color: #f9f9f9;
            font-weight: bold;
        }

        .status-hadir {
            background-color: #dcfce7;
            color: #166534;
        }

        .status-telat {
            background-color: #fef9c3;
            color: #854d0e;
        }

        .status-alfa {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .status-izin {
            background-color: #e0f2fe;
            color: #075985;
        }

        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 8px;
            font-style: italic;
        }

        .summary-info {
            font-size: 8px;
            margin-bottom: 15px;
        }

        .opd-title {
            background-color: #444;
            color: white;
            padding: 5px 10px;
            font-size: 10px;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        .month-title {
            background-color: #f2f2f2;
            border: 1px solid #999;
            border-bottom: none;
            padding: 4px 10px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            color: #333;
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
        <h1>REKAPITULASI ABSENSI PERSONEL</h1>
        @if (count($dates) > 0)
            <p>Periode: {{ \Carbon\Carbon::parse($dates[0])->translatedFormat('d F Y') }} s/d
                {{ \Carbon\Carbon::parse(end($dates))->translatedFormat('d F Y') }}</p>
        @endif
    </div>

    @php
        $groupedDates = collect($dates)->groupBy(function ($date) {
            return \Carbon\Carbon::parse($date)->translatedFormat('F Y');
        });
    @endphp

    @foreach ($personnels->groupBy('opd_id') as $opdId => $group)
        @php
            $currentOpdName = $group->first()->opd->name ?? 'TANPA OPD';
        @endphp

        <div class="opd-title">OPD: {{ $currentOpdName }}</div>

        @foreach ($groupedDates as $monthLabel => $monthDates)
            <div class="month-title">Bulan: {{ $monthLabel }}</div>
            <table>
                <thead>
                    <tr>
                        <th rowspan="3" class="name-column">Nama Personel</th>
                        <th colspan="{{ count($monthDates) }}">Tanggal</th>
                        <th colspan="3" class="summary-column">Ringkasan</th>
                    </tr>
                    <tr>
                        @foreach ($monthDates as $date)
                            @php
                                $carbonDate = \Carbon\Carbon::parse($date);
                                $isWeekend = $carbonDate->isWeekend();
                                $dayName = $carbonDate->translatedFormat('D');
                                $shortDay = substr($dayName, 0, 3);
                                $weekendStyle = $isWeekend ? 'background-color: #fee2e2; color: #991b1b;' : '';
                            @endphp
                            <th class="date-column" style="font-size: 5px; {{ $weekendStyle }}">{{ $shortDay }}
                            </th>
                        @endforeach
                        <th rowspan="2" class="summary-column">JML</th>
                        <th rowspan="2" class="summary-column">H</th>
                        <th rowspan="2" class="summary-column">A</th>
                    </tr>
                    <tr>
                        @foreach ($monthDates as $date)
                            @php
                                $isWeekend = \Carbon\Carbon::parse($date)->isWeekend();
                                $weekendStyle = $isWeekend ? 'background-color: #fee2e2; color: #991b1b;' : '';
                            @endphp
                            <th class="date-column" style="{{ $weekendStyle }}">
                                {{ \Carbon\Carbon::parse($date)->format('d') }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($group as $p)
                        @php
                            $jmlHari = 0;
                            $hadir = 0;
                            $alfa = 0;
                        @endphp
                        <tr>
                            <td class="name-column">
                                {{ $p->name }}
                            </td>
                            @foreach ($monthDates as $date)
                                @php
                                    $a = $p->absensi_map[$date] ?? null;
                                    $j = $p->jadwal_map[$date] ?? null;

                                    $display = '';
                                    $class = '';

                                    if ($j && $j->status !== 'LIBUR') {
                                        $jmlHari++;
                                    }

                                    if ($a) {
                                        $display = substr($a->status, 0, 1);

                                        // Warna dan Statistik
                                        if (in_array($a->status, ['HADIR', 'SAKIT', 'IZIN', 'CUTI', 'DINAS'])) {
                                            $hadir++;
                                            $class = $a->status === 'HADIR' ? 'status-hadir' : 'status-izin';
                                        } elseif ($a->status === 'ALFA') {
                                            $alfa++;
                                            $class = 'status-alfa';
                                        }
                                    } elseif ($j) {
                                        $display = '.';
                                    } else {
                                        $display = '-';
                                    }
                                @endphp
                                <td class="{{ $class }}">{{ $display }}</td>
                            @endforeach

                            <td class="summary-column">{{ $jmlHari }}</td>
                            <td class="summary-column">{{ $hadir }}</td>
                            <td class="summary-column">{{ $alfa }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach

        @if (!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach

    <div class="summary-info">
        <strong>Keterangan:</strong> H: Hadir | T: Telat | A: Alfa | S: Sakit | I: Izin | C: Cuti | L: Libur | -: Lepas
        Jadwal
        <br>
        Dokumen ini dibuat melalui aplikasi absensitrc.pekanbaru.go.id
    </div>

    <div class="footer">
        Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }}
    </div>
</body>

</html>
