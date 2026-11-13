<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Daftar Personnel - {{ $opdName }}</title>
    <style>
        @page {
            margin: 15mm 14mm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #111827;
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 16px;
            border-bottom: 1.5px solid #111827;
            padding-bottom: 8px;
        }

        .header h1 {
            margin: 0;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .header p {
            margin: 4px 0 0;
            font-size: 10px;
            color: #4b5563;
        }

        .opd-section {
            margin-bottom: 16px;
        }

        .page-break {
            page-break-after: always;
        }

        .opd-title {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            background-color: #f3f4f6;
            border: 1px solid #374151;
            border-bottom: none;
            padding: 6px 8px;
            color: #111827;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        thead {
            display: table-header-group;
        }

        tr {
            page-break-inside: avoid;
        }

        th,
        td {
            border: 1px solid #374151;
            padding: 6px 8px;
            vertical-align: middle;
        }

        th {
            background-color: #e5e7eb;
            font-weight: bold;
            text-align: center;
            font-size: 9.5px;
        }

        .col-no {
            width: 4%;
            padding: 5px 2px;
            text-align: center;
        }

        .col-name {
            width: 36%;
            text-align: left;
            word-wrap: break-word;
        }

        .col-custom {
            text-align: center;
            word-wrap: break-word;
        }

        tbody td {
            height: 20px;
        }

        .empty-state {
            text-align: center;
            padding: 24px;
            border: 1px solid #d1d5db;
            color: #6b7280;
        }
    </style>
</head>

<body>
    @forelse ($groupedPersonnels as $groupOpdName => $items)
        <div class="opd-section {{ !$loop->last ? 'page-break' : '' }}">
            <div class="header">
                <h1>Daftar Personnel</h1>
                <p>{{ $groupOpdName }} &bull; Dicetak pada {{ $printedAt }}</p>
            </div>
            <div class="opd-title">{{ $groupOpdName }}</div>
            @php
                $customColWidth = number_format(60 / max(count($columns), 1), 2, '.', '');
            @endphp
            <table>
                <thead>
                    <tr>
                        <th class="col-no" style="width: 4%;">No</th>
                        <th class="col-name" style="width: 36%; text-align: left;">Nama Personnel</th>
                        @foreach ($columns as $columnTitle)
                            <th class="col-custom" style="width: {{ $customColWidth }}%;">{{ $columnTitle }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $index => $personnel)
                        <tr>
                            <td class="col-no">{{ $index + 1 }}</td>
                            <td class="col-name">{{ $personnel->name }}</td>
                            @foreach ($columns as $columnTitle)
                                <td class="col-custom"></td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @empty
        <div class="header">
            <h1>Daftar Personnel</h1>
            <p>{{ $opdName }} &bull; Dicetak pada {{ $printedAt }}</p>
        </div>
        <div class="empty-state">Tidak ada data personnel untuk kriteria yang dipilih.</div>
    @endforelse
</body>

</html>

