<?php
 
namespace App\Exports;
 
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Models\Opd;
 
class JadwalTemplateExport implements WithMultipleSheets
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
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];
 
        if ($this->opdId) {
            // Case: Admin OPD - Only one sheet for their OPD
            $opd = Opd::findOrFail($this->opdId);
            $sheets[] = new JadwalOpdSheet($this->month, $this->year, $this->opdId, $opd->name);
        } else {
            // Case: Super Admin - Multiple sheets, one for each OPD
            $opds = Opd::orderBy('name')->get();
            
            foreach ($opds as $opd) {
                // Only create sheet if OPD has personnel
                if ($opd->personnels()->exists()) {
                    $sheets[] = new JadwalOpdSheet($this->month, $this->year, $opd->id, $opd->name);
                }
            }

            // Fallback if no personnel in any OPD
            if (empty($sheets)) {
                $sheets[] = new JadwalOpdSheet($this->month, $this->year, 0, 'No Personnel');
            }
        }
 
        return $sheets;
    }
}
