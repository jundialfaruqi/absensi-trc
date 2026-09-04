<table>
    <thead>
        <tr>
            <th colspan="{{ count($dates) + 4 }}" style="font-size: 16pt; font-weight: bold; text-align: center;">
                REKAPITULASI ABSENSI PERSONEL TRC AMAN 112
            </th>
        </tr>
        @if (count($dates) > 0)
            <tr>
                <th colspan="{{ count($dates) + 4 }}" style="font-size: 10pt; text-align: center; color: #555555;">
                    Periode: {{ \Carbon\Carbon::parse($dates[0])->translatedFormat('d F Y') }} s/d
                    {{ \Carbon\Carbon::parse(end($dates))->translatedFormat('d F Y') }}
                </th>
            </tr>
        @endif
    </thead>
</table>

@php
    $groupedDates = collect($dates)->groupBy(function ($date) {
        return \Carbon\Carbon::parse($date)->translatedFormat('F Y');
    });
@endphp

@foreach ($groupedDates as $monthLabel => $monthDates)
    <table>
        <thead>
            <tr>
                <th colspan="{{ count($monthDates) + 4 }}"
                    style="font-size: 11pt; font-weight: bold; background-color: #000000; color: #ffffff; text-align: left;">
                    {{ str_starts_with(strtoupper($opdName), 'OPD') ? strtoupper($opdName) : 'OPD: ' . strtoupper($opdName) }}
                </th>
            </tr>
            <tr>
                <th colspan="{{ count($monthDates) + 4 }}"
                    style="font-size: 11pt; font-weight: bold; background-color: #e5e7eb; color: #333333; text-align: left;">
                    BULAN: {{ strtoupper($monthLabel) }}
                </th>
            </tr>
            <tr>
                <th rowspan="3"
                    style="border: 1px solid #999999; background-color: #f2f2f2; font-weight: bold; text-align: center; vertical-align: middle;">
                    Nama Personel
                </th>
                <th colspan="{{ count($monthDates) }}"
                    style="border: 1px solid #999999; background-color: #f2f2f2; font-weight: bold; text-align: center;">
                    Hari/Tanggal
                </th>
                <th colspan="3"
                    style="border: 1px solid #999999; background-color: #f2f2f2; font-weight: bold; text-align: center;">
                    Ringkasan
                </th>
            </tr>
            <tr>
                @foreach ($monthDates as $date)
                    @php
                        $carbonDate = \Carbon\Carbon::parse($date);
                        $isWeekend = $carbonDate->isWeekend();
                        $shortDay = substr($carbonDate->translatedFormat('D'), 0, 3);
                        $weekendStyle = $isWeekend
                            ? 'background-color: #fee2e2; color: #991b1b;'
                            : 'background-color: #f2f2f2;';
                    @endphp
                    <th style="border: 1px solid #999999; {{ $weekendStyle }} font-weight: bold; text-align: center;">
                        {{ $shortDay }}
                    </th>
                @endforeach
                <th rowspan="2"
                    style="border: 1px solid #999999; background-color: #f9f9f9; font-weight: bold; text-align: center; vertical-align: middle;">
                    JML
                </th>
                <th rowspan="2"
                    style="border: 1px solid #999999; background-color: #f9f9f9; font-weight: bold; text-align: center; vertical-align: middle;">
                    H
                </th>
                <th rowspan="2"
                    style="border: 1px solid #999999; background-color: #f9f9f9; font-weight: bold; text-align: center; vertical-align: middle;">
                    A
                </th>
            </tr>
            <tr>
                @foreach ($monthDates as $date)
                    @php
                        $isWeekend = \Carbon\Carbon::parse($date)->isWeekend();
                        $weekendStyle = $isWeekend
                            ? 'background-color: #fee2e2; color: #991b1b;'
                            : 'background-color: #f2f2f2;';
                    @endphp
                    <th data-type="n" data-format="00"
                        style="border: 1px solid #999999; {{ $weekendStyle }} font-weight: bold; text-align: center;">
                        {{ (int) \Carbon\Carbon::parse($date)->format('d') }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($personnels as $p)
                @php
                    $jmlHari = 0;
                    $hadir = 0;
                    $alpa = 0;
                @endphp
                <tr>
                    <td style="border: 1px solid #999999; font-weight: bold; text-align: left;">
                        {{ $p->name }}
                    </td>
                    @foreach ($monthDates as $date)
                        @php
                            $a = $p->absensi_map[$date] ?? null;
                            $j = $p->jadwal_map[$date] ?? null;

                            $display = '';
                            $cellStyle = 'text-align: center;';

                            if (
                                $j &&
                                !in_array($j->status, ['LIBUR', 'DINAS']) &&
                                (!$a || !in_array($a->status, ['LIBUR', 'DINAS']))
                            ) {
                                $jmlHari++;
                            }

                            if ($a) {
                                if (in_array($a->status, ['HADIR', 'TELAT'])) {
                                    $display = 'H';
                                    $hadir++;
                                    $cellStyle =
                                        'background-color: #dcfce7; color: #166534; font-weight: bold; text-align: center;';
                                } elseif (in_array($a->status, ['SAKIT', 'IZIN', 'CUTI'])) {
                                    $display = substr($a->status, 0, 1);
                                    $hadir++;
                                    $cellStyle =
                                        'background-color: #e0f2fe; color: #075985; font-weight: bold; text-align: center;';
                                } elseif ($a->status === 'DINAS') {
                                    $display = 'D';
                                    $cellStyle =
                                        'background-color: #e0f2fe; color: #075985; font-weight: bold; text-align: center;';
                                } elseif ($a->status === 'ALPA') {
                                    $display = 'A';
                                    $alpa++;
                                    $cellStyle =
                                        'background-color: #fee2e2; color: #991b1b; font-weight: bold; text-align: center;';
                                } elseif ($a->status === 'LIBUR') {
                                    $display = 'L';
                                    $cellStyle = 'background-color: #f3f4f6; color: #6b7280; text-align: center;';
                                } else {
                                    $display = substr($a->status, 0, 1);
                                }
                            } elseif ($j) {
                                $display = '.';
                                $cellStyle = 'text-align: center; color: #999999;';
                            } else {
                                $display = '-';
                                $cellStyle = 'text-align: center; color: #cccccc;';
                            }
                        @endphp
                        <td style="border: 1px solid #999999; {{ $cellStyle }}">
                            {{ $display }}
                        </td>
                    @endforeach

                    <td
                        style="border: 1px solid #999999; background-color: #f9f9f9; font-weight: bold; text-align: center;">
                        {{ $jmlHari }}
                    </td>
                    <td
                        style="border: 1px solid #999999; background-color: #f9f9f9; font-weight: bold; text-align: center;">
                        {{ $hadir }}
                    </td>
                    <td
                        style="border: 1px solid #999999; background-color: #f9f9f9; font-weight: bold; text-align: center;">
                        {{ $alpa }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($monthDates) + 4 }}"
                        style="border: 1px solid #999999; text-align: center; padding: 10px; color: #666666;">
                        Tidak ada data personel pada OPD ini
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- <table>
        <tr>
            <td></td>
        </tr>
    </table> --}}
@endforeach

@php
    $totalCols = count($dates) + 4;
    $sigCols = $totalCols >= 16 ? 8 : max(4, intval($totalCols / 2));
    $leftCols = $totalCols - $sigCols;
@endphp

<table>
    <tr>
        <td colspan="{{ $leftCols }}" style="font-size: 9pt; color: #444444; vertical-align: top;">
            <strong>Keterangan:</strong> H: Hadir | A: Alpa | S: Sakit | I: Izin | C: Cuti | D: Dinas | L: Libur /
            Lepas Jadwal
        </td>
        <td colspan="{{ $sigCols }}" style="font-size: 10pt; text-align: center;">
            Mengetahui,
        </td>
    </tr>
    <tr>
        <td colspan="{{ $leftCols }}" style="font-size: 8pt; color: #666666; vertical-align: top;">
            Dokumen ini dibuat melalui aplikasi absensitrc.pekanbaru.go.id
        </td>
        <td colspan="{{ $sigCols }}" style="font-size: 10pt; text-align: center;">
            Kepala Bidang Persandian Aplikasi dan Tata Kelola SPBE
        </td>
    </tr>
    <tr>
        <td colspan="{{ $leftCols }}" style="font-size: 8pt; color: #888888; vertical-align: top;">
            Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }}
        </td>
        <td colspan="{{ $sigCols }}" style="font-size: 10pt; text-align: center;">
            Dinas Komunikasi Informatika Statistik dan Persandian
        </td>
    </tr>
    <tr>
        <td colspan="{{ $leftCols }}"></td>
        <td colspan="{{ $sigCols }}" style="font-size: 10pt; text-align: center;">
            Kota Pekanbaru
        </td>
    </tr>
    <tr>
        <td colspan="{{ $leftCols }}"></td>
        <td colspan="{{ $sigCols }}"></td>
    </tr>
    <tr>
        <td colspan="{{ $leftCols }}"></td>
        <td colspan="{{ $sigCols }}"></td>
    </tr>
    <tr>
        <td colspan="{{ $leftCols }}"></td>
        <td colspan="{{ $sigCols }}"></td>
    </tr>
    <tr>
        <td colspan="{{ $leftCols }}"></td>
        <td colspan="{{ $sigCols }}"
            style="font-size: 10pt; font-weight: bold; text-decoration: underline; text-align: center;">
            DENI HIDAYAT, S.T, M.M
        </td>
    </tr>
    <tr>
        <td colspan="{{ $leftCols }}"></td>
        <td colspan="{{ $sigCols }}" style="font-size: 10pt; text-align: center;">
            NIP. 197801062005011006
        </td>
    </tr>
</table>
