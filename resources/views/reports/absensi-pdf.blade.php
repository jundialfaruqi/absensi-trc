<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Absensi - {{ $monthName }} {{ $year }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 8px;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 16px;
            text-transform: uppercase;
        }
        .header p {
            margin: 5px 0;
            font-size: 12px;
            color: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 4px 2px;
            text-align: center;
            overflow: hidden;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .name-column {
            width: 120px;
            text-align: left;
            padding-left: 5px;
            font-weight: bold;
        }
        .date-column {
            width: 25px;
        }
        .status-hadir {
            background-color: #dcfce7;
            color: #166534;
        }
        .status-telat {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .status-alfa {
            background-color: #f3f4f6;
            color: #374151;
        }
        .status-izin {
            background-color: #fef9c3;
            color: #854d0e;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 10px;
        }
        .summary-box {
            margin-top: 10px;
            font-size: 9px;
        }
        .page-break {
            page-break-after: always;
        }
        @page {
            margin: 1cm;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>REKAPITULASI ABSENSI PERSONEL</h1>
        <p>OPD: {{ $opdName }} | Periode: {{ $monthName }} {{ $year }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="name-column">Nama Personel</th>
                @foreach ($dates as $date)
                    <th class="date-column">{{ \Carbon\Carbon::parse($date)->format('d') }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($personnels as $p)
                <tr>
                    <td class="name-column">
                        {{ $p->name }}<br>
                        <span style="font-size: 6px; font-weight: normal; color: #777;">{{ $p->penugasan?->name }}</span>
                    </td>
                    @foreach ($dates as $date)
                        @php
                            $a = $p->absensi_map[$date] ?? null;
                            $j = $p->jadwal_map[$date] ?? null;
                            $isPast = \Carbon\Carbon::parse($date)->isPast() && !\Carbon\Carbon::parse($date)->isToday();
                            
                            $display = '';
                            $class = '';
                            
                            if ($a) {
                                if (in_array($a->status_masuk, ['SAKIT', 'IZIN', 'ALFA', 'CUTI'])) {
                                    $display = substr($a->status_masuk, 0, 1);
                                    $class = 'status-izin';
                                } elseif ($a->status_masuk === 'HADIR') {
                                    $display = 'H';
                                    $class = 'status-hadir';
                                } elseif ($a->status_masuk === 'TELAT') {
                                    $display = 'T';
                                    $class = 'status-telat';
                                } else {
                                    $display = 'D'; // Dinas
                                }
                            } elseif ($j && $isPast) {
                                $display = 'A'; // ALFA
                                $class = 'status-alfa';
                            } elseif ($j) {
                                $display = '.'; // Scheduled but not yet
                            } else {
                                $display = '-'; // No schedule
                            }
                        @endphp
                        <td class="{{ $class }}">{{ $display }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary-box">
        <strong>Keterangan:</strong><br>
        H: Hadir | T: Telat | A: Alfa (Mangkir) | S: Sakit | I: Izin | C: Cuti | D: Dinas | -: Lepas/Libur
    </div>

    <div class="footer">
        Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }}
    </div>
</body>
</html>
