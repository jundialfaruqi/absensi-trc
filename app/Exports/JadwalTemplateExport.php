<?php
 
namespace App\Exports;
 
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use App\Models\Personnel;
use Carbon\Carbon;
 
class JadwalTemplateExport implements FromCollection, WithHeadings, ShouldAutoSize, WithTitle
{
    protected $month;
    protected $year;
    protected $opdId;
 
    public function __construct($month = null, $year = null, $opdId = null)
    {
        $this->month = $month ?: date('m');
        $this->year = $year ?: date('Y');
        $this->opdId = $opdId;
    }
 
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $personnels = Personnel::when($this->opdId, function ($q) {
                $q->where('opd_id', $this->opdId);
            })
            ->orderBy('name')
            ->get();
        $data = collect();
 
        foreach ($personnels as $p) {
            $row = [
                'id' => $p->id,
                'nama' => $p->name,
            ];
 
            // Add empty columns for each day
            $daysInMonth = Carbon::create($this->year, $this->month, 1)->daysInMonth;
            for ($i = 1; $i <= $daysInMonth; $i++) {
                $row[] = ''; // Empty for user to fill
            }
 
            $data->push($row);
        }
 
        return $data;
    }
 
    public function headings(): array
    {
        $headings = [
            'ID Personnel',
            'Nama Personnel',
        ];
 
        $daysInMonth = Carbon::create($this->year, $this->month, 1)->daysInMonth;
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $headings[] = (string)$i;
        }
 
        return $headings;
    }
 
    public function title(): string
    {
        return 'Jadwal ' . Carbon::create($this->year, $this->month, 1)->translatedFormat('F Y');
    }
}
