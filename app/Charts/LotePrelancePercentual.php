<?php

namespace App\Charts;

use App\Models\Leilao;
use ArielMejiaDev\LarapexCharts\LarapexChart;

class LotePrelancePercentual
{
    protected $chart;

    public function __construct(LarapexChart $chart)
    {
        $this->chart = $chart;
    }

    public function build(Leilao $leilao): \ArielMejiaDev\LarapexCharts\PieChart
    {
        $lotes = $leilao->lotes()->get();

        return $this->chart->pieChart()
            ->addData($lotes->pluck('valor_prelance')->toArray())
            ->setLabels($lotes->pluck('id')->toArray())
            ->setXAxis($lotes->pluck('id')->toArray());
    }
}
