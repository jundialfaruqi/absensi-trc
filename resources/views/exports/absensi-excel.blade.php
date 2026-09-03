<table>
    <thead>
        <tr>
            <th colspan="{{ count($dates) + 4 }}" style="font-size: 14pt; font-weight: bold; text-align: center;">
                REKAPITULASI ABSENSI PERSONEL
            </th>
        </tr>
        @if (count($dates) > 0)
            <tr>
                <th colspan="{{ count($dates) + 4 }}" style="font-size: 10pt; text-align: center; color: #555555;">
                    Periode: {{ \Carbon\Carbon::parse($dates[0])->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse(end($dates))->translatedFormat('d F Y') }}
                </th>
            </tr>
        @endif
        <tr>
            <th colspan="{{ count($dates) + 4 }}" style="font-size: 11pt; font-weight: bold; background-color: #333333; color: #ffffff; text-align: left;">
                OPD: {{ strtoupper($opdName) }}
            </th>
        </tr>
        <tr>
            <td colspan="{{ count($dates) + 4 }}"></td>
        </tr>
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
                <th colspan="{{ count($monthDates) + 4 }}" style="font-size: 10pt; font-weight: bold; background-color: #e5e7eb; color: #333333; text-align: left;">
                    BULAN: {{ strtoupper($monthLabel) }}
                </th>
            </tr>
            <tr>
                <th rowspan="3" style="border: 1px solid #999999; background-color: #f2f2f2; font-weight: bold; text-align: center; vertical-align: middle;">
                    Nama Personel
                </th>
                <th colspan="{{ count($monthDates) }}" style="border: 1px solid #999999; background-color: #f2f2f2; font-weight: bold; text-align: center;">
                    Tanggal
                </th>
                <th colspan="3" style="border: 1px solid #999999; background-color: #f2f2f2; font-weight: bold; text-align: center;">
                    Ringkasan
                </th>
            </tr>
            <tr>
                @foreach ($monthDates as $date)
                    @php
                        $carbonDate = \Carbon\Carbon::parse($date);
                        $isWeekend = $carbonDate->isWeekend();
                        $shortDay = substr($carbonDate->translatedFormat('D'), 0, 3);
                        $weekendStyle = $isWeekend ? 'background-color: #fee2e2; color: #991b1b;' : 'background-color: #f2f2f2;';
                    @endphp
                    <th style="border: 1px solid #999999; {{ $weekendStyle }} font-weight: bold; text-align: center;">
                        {{ $shortDay }}
                    </th>
                @endforeach
                <th rowspan="2" style="border: 1px solid #999999; background-color: #f9f9f9; font-weight: bold; text-align: center; vertical-align: middle;">
                    JML
                </th>
                <th rowspan="2" style="border: 1px solid #999999; background-color: #f9f9f9; font-weight: bold; text-align: center; vertical-align: middle;">
                    H
                </th>
                <th rowspan="2" style="border: 1px solid #999999; background-color: #f9f9f9; font-weight: bold; text-align: center; vertical-align: middle;">
                    A
                </th>
            </tr>
            <tr>
                @foreach ($monthDates as $date)
                    @php
                        $isWeekend = \Carbon\Carbon::parse($date)->isWeekend();
                        $weekendStyle = $isWeekend ? 'background-color: #fee2e2; color: #991b1b;' : 'background-color: #f2f2f2;';
                    @endphp
                    <th style="border: 1px solid #999999; {{ $weekendStyle }} font-weight: bold; text-align: center;">
                        {{ \Carbon\Carbon::parse($date)->format('d') }}
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

                            if ($j && $j->status !== 'LIBUR') {
                                $jmlHari++;
                            }

                            if ($a) {
                                $display = substr($a->status, 0, 1);

                                if (in_array($a->status, ['HADIR', 'SAKIT', 'IZIN', 'CUTI', 'DINAS'])) {
                                    $hadir++;
                                    if ($a->status === 'HADIR') {
                                        $cellStyle = 'background-color: #dcfce7; color: #166534; font-weight: bold; text-align: center;';
                                    } else {
                                        $cellStyle = 'background-color: #e0f2fe; color: #075985; font-weight: bold; text-align: center;';
                                    }
                                } elseif ($a->status === 'ALPA') {
                                    $alpa++;
                                    $cellStyle = 'background-color: #fee2e2; color: #991b1b; font-weight: bold; text-align: center;';
                                } elseif ($a->status === 'TELAT') {
                                    $hadir++;
                                    $cellStyle = 'background-color: #fef9c3; color: #854d0e; font-weight: bold; text-align: center;';
                                } elseif ($a->status === 'LIBUR') {
                                    $cellStyle = 'background-color: #f3f4f6; color: #6b7280; text-align: center;';
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

                    <td style="border: 1px solid #999999; background-color: #f9f9f9; font-weight: bold; text-align: center;">
                        {{ $jmlHari }}
                    </td>
                    <td style="border: 1px solid #999999; background-color: #f9f9f9; font-weight: bold; text-align: center;">
                        {{ $hadir }}
                    </td>
                    <td style="border: 1px solid #999999; background-color: #f9f9f9; font-weight: bold; text-align: center;">
                        {{ $alpa }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($monthDates) + 4 }}" style="border: 1px solid #999999; text-align: center; padding: 10px; color: #666666;">
                        Tidak ada data personel pada OPD ini
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table>
        <tr>
            <td colspan="{{ count($monthDates) + 4 }}"></td>
        </tr>
    </table>
@endforeach

<table>
    <tr>
        <td colspan="{{ count($dates) + 4 }}" style="font-style: italic; font-size: 9pt; color: #444444;">
            <strong>Keterangan:</strong> H: Hadir | T: Telat | A: Alpa | S: Sakit | I: Izin | C: Cuti | L: Libur | -: Lepas Jadwal
        </td>
    </tr>
    <tr>
        <td colspan="{{ count($dates) + 4 }}" style="font-size: 8pt; color: #666666;">
            Dokumen ini dibuat melalui aplikasi absensitrc.pekanbaru.go.id
        </td>
    </tr>
    <tr>
        <td colspan="{{ count($dates) + 4 }}" style="font-size: 8pt; color: #888888;">
            Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }}
        </td>
    </tr>
</table>
