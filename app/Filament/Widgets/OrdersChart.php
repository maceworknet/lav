<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class OrdersChart extends ChartWidget
{
    protected ?string $heading = 'Son 15 Günlük Satış Grafiği (TL)';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = [];
        $labels = [];

        // Past 15 days
        for ($i = 14; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $labels[] = $date->format('d.m');
            
            // Total sales for this day
            $completedStatuses = ['paid', 'preparing', 'assigned_to_courier', 'on_delivery', 'delivered'];
            $totalSales = Order::whereIn('status', $completedStatuses)
                ->whereDate('created_at', $date->toDateString())
                ->sum('total');

            $data[] = (float) $totalSales;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Günlük Satış (TL)',
                    'data' => $data,
                    'borderColor' => '#e11d48', // rose-600
                    'backgroundColor' => '#fecdd3', // rose-200
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
